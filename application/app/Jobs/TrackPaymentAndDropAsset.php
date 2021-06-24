<?php

namespace App\Jobs;

use Exception;
use Throwable;
use Illuminate\Contracts\Container\BindingResolutionException;

class TrackPaymentAndDropAsset extends BaseJob
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
        $this->checkIsValidJobType(JOB_TYPE_TRACK_PAYMENT_AND_DROP_ASSET);

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

        // Read drop wallet address
        $dropWalletAddress = $this->readWalletAddress(
            WALLET_TYPE_DROP,
            $payload['drop_wallet_name']
        );

        // Load drop wallet utxos
        $dropWalletUTXOs = $this->loadWalletUTXOs($dropWalletAddress);

        // Find expected payment
        $expectedPayment = $this->findExpectedPayment(
            $paymentWalletUTXOs,
            (int) $payload['expected_lovelace']
        );

        // Find drop asset
        $dropAsset = $this->findDropAsset(
            $dropWalletUTXOs,
            $payload['drop']['policy_id'],
            $payload['drop']['asset_name']
        );

        // Check if drop asset exists
        if (is_null($dropAsset)) {
            throw new Exception(sprintf(
                'Asset "%s.%s" does not exist in drop wallet',
                $payload['drop']['policy_id'],
                $payload['drop']['asset_name']
            ));
        }

        // Check if required drop quantity exists
        if ($dropAsset['available_quantity'] < $payload['drop']['quantity']) {
            throw new Exception(sprintf(
                'Insufficient asset quantity "%s.%s" in the drop wallet, cannot drop %d because there are only %d left',
                $payload['drop']['policy_id'],
                $payload['drop']['asset_name'],
                $payload['drop']['quantity'],
                $dropAsset['available_quantity']
            ));
        }

        // If expected payment was found
        if (!is_null($expectedPayment))
        {
            // Add log
            $this->heidrunJob->addLog('Expected payment was found: ' . json_encode($expectedPayment));

            // Init tx path
            $txPath = sprintf('%s/Drops/%d', PRIVATE_DIR, $this->heidrunJob->id);
            if (is_dir($txPath)) {
                rrmdir($txPath);
            }
            mkdir($txPath, 0700, true);

            // Attempt to drop asset
            try {

                // Add log
                $this->heidrunJob->addLog('Building raw cardano transaction to drop asset');

                // Query current slot number
                $currentSlotNumber = $this->queryCurrentSlotNumber();
                $txInvalidHereafterSlot = $currentSlotNumber + 3600; // 1 Hour

                // Calculate minUTXO & set tx fee in lovelace
                $minUTXOInLovelace = $expectedPayment['lovelace'] - DROP_ASSET_TX_FEE_IN_LOVELACE;
                $txFeeInLovelace = DROP_ASSET_TX_FEE_IN_LOVELACE;

                // Generate fund tx in
                $fundTxIn = sprintf('--tx-in %s#%d', $expectedPayment['tx_hash'], $expectedPayment['tx_index']);

                // Generate asset tx in
                $assetTxIn = sprintf('--tx-in %s', $dropAsset['utxo']);

                // Generate asset tx out [drop asset to receiver address]
                $assetTxOut1 = sprintf(
                    '--tx-out="%s + %d + %d %s.%s"',
                    $payload['drop']['receiver_address'],
                    $minUTXOInLovelace,
                    $payload['drop']['quantity'],
                    $dropAsset['policy_id'],
                    $dropAsset['asset_name']
                );

                // Generate asset tx out [return remaining asset back to drop wallet]
                $assetTxOut2 = sprintf(
                    '--tx-out="%s + %d + %d %s.%s"',
                    $dropWalletAddress,
                    $dropAsset['lovelace'],
                    $dropAsset['available_quantity'] - $payload['drop']['quantity'],
                    $dropAsset['policy_id'],
                    $dropAsset['asset_name']
                );

                // Build transaction with fee
                $txWithFee =
                    "cardano-cli transaction build-raw \\" . PHP_EOL .
                    "--fee {$txFeeInLovelace} \\" . PHP_EOL .
                    "{$fundTxIn} \\" . PHP_EOL .
                    "{$assetTxIn} \\" . PHP_EOL .
                    "{$assetTxOut1} \\" . PHP_EOL .
                    "{$assetTxOut2} \\" . PHP_EOL .
                    "--invalid-hereafter {$txInvalidHereafterSlot} \\" . PHP_EOL .
                    "--out-file {$txPath}/drop-asset-with-fee.raw";
                shellExec($txWithFee);

                // Add log
                $this->heidrunJob->addLog('Signing raw cardano transaction to drop asset');

                // Sign transaction
                shellExec(sprintf(
                    "cardano-cli transaction sign " .
                    "--signing-key-file %s/payment.skey " .
                    "--signing-key-file %s/payment.skey " .
                    "--tx-body-file %s/drop-asset-with-fee.raw " .
                    "--out-file %s/drop-asset.signed " .
                    "%s",
                    WALLET_DIR . '/' . $payload['payment_wallet_name'],
                    WALLET_DIR . '/' . $payload['drop_wallet_name'],
                    $txPath,
                    $txPath,
                    cardanoNetworkFlag()
                ));

                // Add log
                $this->heidrunJob->addLog('Submitting singed cardano transaction to drop asset');

                // Serialise singed transaction
                $cborHex = json_decode(file_get_contents("{$txPath}/drop-asset.signed"), true)['cborHex'];
                $serialisedSignedTransaction = hex2bin($cborHex);

                // Submit singed transaction
                $dropTxHash = $this->blockFrostService()->submitTx($serialisedSignedTransaction);

                // Add Log & update status
                $this->heidrunJob->addLog(
                    'Successfully dropped asset, got tx hash: ' . $dropTxHash,
                    JOB_STATUS_SUCCESS
                );

            } catch (Throwable $exception) {

                // Add log
                $this->heidrunJob->addLog(sprintf(
                    'Failed to drop asset: %s, re-trying in %d seconds',
                    $exception->getMessage(),
                    JOB_RETRY_INTERVAL_SECONDS
                ));

                // Release this job back on the queue and re-try later
                $this->release(JOB_RETRY_INTERVAL_SECONDS);

            } finally {

                // Clean-up
                if (is_dir($txPath)) {
                    rrmdir($txPath);
                }

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
     * @param array $dropWalletUTXOs
     * @param string $policyId
     * @param $assetName
     * @return array|null
     */
    private function findDropAsset(array $dropWalletUTXOs, string $policyId, $assetName): ?array
    {
        $dropAsset = null;
        $assetId = $policyId . bin2hex($assetName);
        $lovelace = 0;

        foreach ($dropWalletUTXOs as $utxo) {
            foreach ($utxo['amount'] as $amount) {
                if ($amount['unit'] === 'lovelace') {
                    $lovelace = (int) $amount['quantity'];
                    continue;
                }
                if ($amount['unit'] === $assetId) {
                    $dropAsset = [
                        'utxo' => sprintf('%s#%d', $utxo['tx_hash'], $utxo['tx_index']),
                        'lovelace' => $lovelace,
                        'policy_id' => $policyId,
                        'asset_name' => $assetName,
                        'available_quantity' => (int) $amount['quantity'],
                    ];
                    break;
                }
            }
            if ($dropAsset) {
                break;
            }
        }

        return $dropAsset;
    }

    /**
     * @return int
     * @throws BindingResolutionException
     */
    private function queryCurrentSlotNumber(): int
    {
        $response = $this->blockFrostService()->get('blocks/latest');

        return (int) $response['slot'];
    }
}
