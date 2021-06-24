<?php

namespace App\Http\Controllers\Api\V1;

use Throwable;
use App\Services\JobService;
use Illuminate\Http\Request;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use App\Services\BlockFrostService;
use App\Jobs\TrackPaymentAndCallback;
use App\Jobs\TrackPaymentAndDropAsset;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class JobController extends BaseApi
{
    /**
     * @var array $validJobTypes
     */
    private $validJobTypes = [
        JOB_TYPE_TRACK_PAYMENT_AND_CALLBACK,
        JOB_TYPE_TRACK_PAYMENT_AND_DROP_ASSET,
    ];

    /**
     * @var JobService $jobService
     */
    private $jobService;

    /**
     * @var WalletService $walletService
     */
    private $walletService;

    /**
     * @var BlockFrostService $blockFrostService
     */
    private $blockFrostService;

    /**
     * JobController constructor.
     * @param JobService $jobService
     * @param WalletService $walletService
     * @param BlockFrostService $blockFrostService
     */
    public function __construct(
        JobService $jobService,
        WalletService $walletService,
        BlockFrostService $blockFrostService
    )
    {
        $this->jobService = $jobService;
        $this->walletService = $walletService;
        $this->blockFrostService = $blockFrostService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function create(Request $request): JsonResponse
    {
        switch ($request->type) {

            case JOB_TYPE_TRACK_PAYMENT_AND_CALLBACK:
                return $this->handleTrackPaymentAndCallback($request);

            case JOB_TYPE_TRACK_PAYMENT_AND_DROP_ASSET:
                return $this->handleTrackPaymentAndDropAsset($request);

            default:
                return $this->errorResponse(
                    sprintf(
                        'Invalid job type, acceptable values are: %s',
                        implode(' or ', $this->validJobTypes)
                    ),
                    Response::HTTP_BAD_REQUEST
                );

        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    private function handleTrackPaymentAndCallback(Request $request): JsonResponse
    {
        // Generate valid callback request types
        $validCallbackRequestTypes = implode(',', [
            CALLBACK_REQUEST_TYPE_GET,
            CALLBACK_REQUEST_TYPE_POST,
        ]);

        // Validate request body
        $validator = Validator::make($request->all(), [
            'payment_wallet_name' => ['required', 'alpha_num'],
            'expected_lovelace' => ['required', 'integer'],
            'callback.request_url' => ['required', 'url'],
            'callback.request_type' => ['required', 'in:' . $validCallbackRequestTypes],
            'callback.request_params' => ['nullable'],
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return $this->errorResponse(
                sprintf(
                    'Validation error: %s',
                    implode(' ', $validator->errors()->all())
                ),
                Response::HTTP_BAD_REQUEST
            );
        }

        // Check if payment wallet exists
        if (!$this->walletExists($request->payment_wallet_name, WALLET_TYPE_PAYMENT)) {
            return $this->errorResponse(
                sprintf(
                    'Payment wallet "%s" does not exist',
                    $request->payment_wallet_name
                ),
                Response::HTTP_BAD_REQUEST
            );
        }

        // Create new job
        $job = $this->jobService->createJob(
            JOB_TYPE_TRACK_PAYMENT_AND_CALLBACK,
            $validator->validated()
        );

        // Dispatch job
        dispatch(new TrackPaymentAndCallback($job));

        // Success
        return $this->successResponse(
            [
                'message' => 'Job successfully created & scheduled',
                'job_id' => $job->id,
            ],
            Response::HTTP_CREATED
        );
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    private function handleTrackPaymentAndDropAsset(Request $request): JsonResponse
    {
        // Validate request body
        $validator = Validator::make($request->all(), [
            'payment_wallet_name' => ['required', 'alpha_num'],
            'drop_wallet_name' => ['required', 'alpha_num'],
            'expected_lovelace' => ['required', 'integer'],
            'drop.policy_id' => ['required', 'string'],
            'drop.asset_name' => ['required', 'string'],
            'drop.quantity' => ['required', 'integer'],
            'drop.receiver_address' => ['required', 'string'],
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return $this->errorResponse(
                sprintf(
                    'Validation error: %s',
                    implode(' ', $validator->errors()->all())
                ),
                Response::HTTP_BAD_REQUEST
            );
        }

        // Check if payment wallet exists
        if (!$this->walletExists($request->payment_wallet_name, WALLET_TYPE_PAYMENT)) {
            return $this->errorResponse(
                sprintf(
                    'Payment wallet "%s" does not exist',
                    $request->payment_wallet_name
                ),
                Response::HTTP_BAD_REQUEST
            );
        }

        // Check if drop wallet exists
        if (!$this->walletExists($request->drop_wallet_name, WALLET_TYPE_DROP)) {
            return $this->errorResponse(
                sprintf(
                    'Drop wallet "%s" does not exist',
                    $request->payment_wallet_name
                ),
                Response::HTTP_BAD_REQUEST
            );
        }

        // Get validated request payload
        $requestPayload = $validator->validated();

        // Load drop wallet's address
        $dropWalletAddress = file_get_contents(sprintf(
            "%s/%s/payment.addr",
            WALLET_DIR,
            $requestPayload['drop_wallet_name']
        ));

        // Find available drop asset quantity in drop wallet
        $availableDropAssetQuantity = $this->loadAvailableAssetQuantityFromDropWallet(
            $dropWalletAddress,
            $requestPayload['drop']['policy_id'],
            $requestPayload['drop']['asset_name']
        );

        // Check if drop asset and quantity exists in drop wallet
        if (is_null($availableDropAssetQuantity) || $availableDropAssetQuantity < $requestPayload['drop']['quantity']) {
            if (is_null($availableDropAssetQuantity)) {
                $errorMessage = sprintf(
                    'Asset "%s.%s" does not exist in drop wallet',
                    $requestPayload['drop']['policy_id'],
                    $requestPayload['drop']['asset_name']
                );
            } else {
                $errorMessage = sprintf(
                    'Insufficient asset quantity "%s.%s" in the drop wallet, cannot drop %d because there are only %d left',
                    $requestPayload['drop']['policy_id'],
                    $requestPayload['drop']['asset_name'],
                    $requestPayload['drop']['quantity'],
                    $availableDropAssetQuantity
                );
            }
            return $this->errorResponse(
                $errorMessage,
                Response::HTTP_BAD_REQUEST
            );
        }

        // Create new job
        $job = $this->jobService->createJob(
            JOB_TYPE_TRACK_PAYMENT_AND_DROP_ASSET,
            $requestPayload
        );

        // Dispatch job
        dispatch(new TrackPaymentAndDropAsset($job));

        // Success
        return $this->successResponse(
            [
                'message' => 'Job successfully created & scheduled',
                'job_id' => $job->id,
            ],
            Response::HTTP_CREATED
        );
    }

    /**
     * @param string $walletName
     * @param string $walletType
     * @return bool
     */
    private function walletExists(string $walletName, string $walletType): bool
    {
        return !is_null($this->walletService->find(
            $walletName,
            $walletType,
            env('CARDANO_NETWORK')
        ));
    }

    /**
     * @param string $dropWalletAddress
     * @param string $policyId
     * @param string $assetName
     * @return int|null
     */
    private function loadAvailableAssetQuantityFromDropWallet(
        string $dropWalletAddress,
        string $policyId,
        string $assetName
    ): ?int
    {
        $assetQuantity = null;

        try {

            $dropWalletUTXOs = $this->blockFrostService->get("addresses/{$dropWalletAddress}/utxos");
            $assetId = $policyId . bin2hex($assetName);

            foreach ($dropWalletUTXOs as $utxo) {
                foreach ($utxo['amount'] as $amount) {
                    if ($amount['unit'] === $assetId) {
                        $assetQuantity = (int) $amount['quantity'];
                        break;
                    }
                }
                if ($assetQuantity) {
                    break;
                }
            }

        } catch (Throwable $exception) { }

        return $assetQuantity;
    }
}
