<?php

namespace App\Jobs;

use Exception;
use Throwable;
use App\Models\Job;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class TrackPaymentAndCallback extends BaseJob
{
    /**
     * @var Job $heidrunJob
     */
    public $heidrunJob;

    /**
     * Create a new job instance.
     *
     * @param Job $heidrunJob
     */
    public function __construct(Job $heidrunJob)
    {
        $this->heidrunJob = $heidrunJob;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception
     */
    public function handle()
    {
        // Check for valid job type
        if ($this->heidrunJob->type !== JOB_TYPE_TRACK_PAYMENT_AND_CALLBACK) {
            $errorMessage = sprintf('Invalid job type, could not be handled by %s', __CLASS__);
            $this->heidrunJob->addLog($errorMessage);
            throw new Exception($errorMessage);
        }

        // Check for valid job status
        $validJobStatuses = [JOB_STATUS_PENDING, JOB_STATUS_PROCESSING];
        if (!in_array($this->heidrunJob->status, $validJobStatuses)) {
            $errorMessage = sprintf(
                'Invalid job status "%s", expecting %s',
                $this->heidrunJob->status,
                implode(' or ', $validJobStatuses)
            );
            $this->heidrunJob->addLog($errorMessage);
            throw new Exception($errorMessage);
        }

        // TODO: check attempts

        // Add log
        $this->heidrunJob->addLog('Looking for expected payment');

        // Update job's status & attempts
        $this->heidrunJob->update([
            'status' => JOB_STATUS_PROCESSING,
            'attempts' => $this->heidrunJob->attempts + 1,
        ]);

        // Decode payload
        $payload = json_decode($this->heidrunJob->payload, true);

        // Read payment wallet address
        $paymentWalletAddress = $this->readPaymentWalletAddress($payload['payment_wallet_name']);

        // Load wallet address utxos
        $paymentWalletUTXOs = $this->loadUTXOs($paymentWalletAddress);

        // Find expected payment
        $expectedPayment = $this->findExpectedPayment(
            $paymentWalletUTXOs,
            (int) $payload['expected_lovelace']
        );

        // If expected payment was found
        if (!is_null($expectedPayment))
        {
            // Add log
            $this->heidrunJob->addLog('Expected payment was found: ' . json_encode($expectedPayment));

            // Attempt to execute callback
            try {

                // Callback
                $responseBody = $this->callback($payload['callback'], $expectedPayment);

                // Add Log
                $this->heidrunJob->addLog('Successfully executed callback, got response: ' . $responseBody);

                // Update job status
                $this->heidrunJob->update([
                    'status' => JOB_STATUS_SUCCESS,
                ]);

            } catch (Throwable $exception) {

                // Add log
                $this->heidrunJob->addLog('Failed to execute callback: ' . $exception->getMessage() . ', re-trying in 30 seconds');

                // Release this job back on the queue and re-try after 30 seconds
                $this->release(30);

            }
        }
        else
        {
            // Add log
            $this->heidrunJob->addLog('Expected payment was not found, re-trying in 30 seconds');

            // Release this job back on the queue and re-try after 30 seconds
            $this->release(30);
        }
    }

    /**
     * @param $paymentWalletName
     * @return string
     * @throws Exception
     */
    private function readPaymentWalletAddress($paymentWalletName): string
    {
        $paymentWalletAddress = file_get_contents(sprintf(
            '%s/%s/payment.addr',
            WALLET_DIR,
            $paymentWalletName,
        ));

        if (empty($paymentWalletAddress)) {
            throw new Exception(sprintf(
                'Failed to read the address of payment wallet "%s"',
                $paymentWalletName
            ));
        }

        return $paymentWalletAddress;
    }

    /**
     * @param string $paymentWalletAddress
     * @return array
     */
    private function loadUTXOs(string $paymentWalletAddress): array
    {
        $utxos = [];

        try {
            $utxos = $this->blockFrostService()->get("addresses/{$paymentWalletAddress}/utxos");
        } catch (Throwable $exception) { }

        return $utxos;
    }

    /**
     * @param array $paymentWalletUTXOs
     * @param int $expectedLovelace
     * @return array|null
     */
    private function findExpectedPayment(array $paymentWalletUTXOs, int $expectedLovelace): ?array
    {
        $result = null;

        foreach ($paymentWalletUTXOs as $utxo) {
            foreach ($utxo['amount'] as $amount) {
                if (
                    $amount['unit'] ?? '' === 'lovelace' &&
                    $amount['quantity'] ?? 0 === $expectedLovelace
                ) {
                    $result = [
                        'tx_hash' => $utxo['tx_hash'],
                        'tx_index' => $utxo['tx_index'],
                        'output_index' => $utxo['output_index'],
                        'block' => $utxo['block'],
                    ];
                    break;
                }
            }
            if ($result) {
                break;
            }
        }

        return $result;
    }

    /**
     * @param array $callback
     * @param array $expectedPayment
     * @return string
     * @throws Exception
     */
    private function callback(array $callback, array $expectedPayment): string
    {
        /** @var Response $response */
        $response = Http::withHeaders(['powered_by' => env('APP_NAME')])->{$callback['request_type']}(
            $callback['request_url'],
            [
                'expectedPayment' => $expectedPayment,
                'callbackData' => $callback['request_params'] ?? null,
            ]
        );

        if (!$response->successful()) {
            throw new Exception($response->body());
        }

        return $response->body();
    }
}
