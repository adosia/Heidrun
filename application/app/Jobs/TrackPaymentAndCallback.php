<?php

namespace App\Jobs;

use Exception;
use Throwable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class TrackPaymentAndCallback extends BaseJob
{
    /**
     * Execute the job.
     *
     * @return void
     * @throws Exception|Throwable
     */
    public function handle()
    {
        // Check for valid job type
        $this->checkIsValidJobType(JOB_TYPE_TRACK_PAYMENT_AND_CALLBACK);

        // Check for valid job status
        $this->checkIsValidJobStatus();

        // Check if max job attempts exceeded
        $this->checkIfMaxAttemptsExceeded();

        // Add log, update status and attempts
        $this->beginJobProcessing();

        // Decode payload
        $payload = json_decode($this->heidrunJob->payload, true);

        // Read payment wallet address
        $paymentWalletAddress = $this->readWalletAddress(
            WALLET_TYPE_PAYMENT,
            $payload['payment_wallet_name']
        );

        // Load payment wallet utxos
        $paymentWalletUTXOs = $this->loadWalletUTXOs($paymentWalletAddress);

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

                // Add Log & update status
                $this->heidrunJob->addLog(
                    'Successfully executed callback, got response: ' . $responseBody,
                    JOB_STATUS_SUCCESS
                );

            } catch (Throwable $exception) {

                // Add log
                $this->heidrunJob->addLog(sprintf(
                    'Failed to execute callback: %s, re-trying in %d seconds',
                    $exception->getMessage(),
                    JOB_RETRY_INTERVAL_SECONDS
                ));

                // Release this job back on the queue and re-try later
                $this->release(JOB_RETRY_INTERVAL_SECONDS);

            }
        }
        else
        {
            // Add log
            $this->heidrunJob->addLog(sprintf(
                'Expected payment was not found, re-trying in %d seconds',
                JOB_RETRY_INTERVAL_SECONDS
            ));

            // Release this job back on the queue and re-try later
            $this->release(JOB_RETRY_INTERVAL_SECONDS);
        }
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
        $response = Http::withHeaders(['powered-by' => env('APP_NAME')])->{$callback['request_type']}(
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
