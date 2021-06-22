<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\JobService;
use Illuminate\Http\Request;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use App\Jobs\TrackPaymentAndCallback;
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
     * JobController constructor.
     * @param JobService $jobService
     * @param WalletService $walletService
     */
    public function __construct(
        JobService $jobService,
        WalletService $walletService
    )
    {
        $this->jobService = $jobService;
        $this->walletService = $walletService;
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

        // Create job
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
     */
    private function handleTrackPaymentAndDropAsset(Request $request): JsonResponse
    {
        return $this->errorResponse('Not yet implemented');
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
}
