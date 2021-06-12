<?php

namespace App\Services;

use Exception;

/**
 * Class CardanoCliService
 * @package App\Services
 */
class CardanoCliService
{
    /**
     * @return string
     */
    public function versionInfo(): string
    {
        try {
            return shellExec('cardano-cli --version');
        } catch (\Throwable $exception) {
            logError('Failed to query cardano-cli version info', $exception);
        }

        return '';
    }

    /**
     * @param $walletName
     * @return string
     * @throws Exception
     */
    public function createWallet($walletName): string
    {
        // Generate new wallet dir path
        $newWalletDir = WALLET_DIR . "/{$walletName}";

        // Check if wallet already exists
        if (is_dir($newWalletDir)) {
            throw new Exception(sprintf(
                'Wallet "%s" already exist, choose another name',
                $walletName
            ));
        }

        // Create wallet directory
        shellExec("mkdir -p {$newWalletDir}");

        // Generate payment keys
        shellExec(sprintf(
            "cardano-cli address key-gen " .
            "--verification-key-file %s/payment.vkey " .
            "--signing-key-file %s/payment.skey",
            $newWalletDir,
            $newWalletDir
        ));

        // Build payment address
        shellExec(sprintf(
            "cardano-cli address build " .
            "--payment-verification-key-file %s/payment.vkey " .
            "--out-file %s/payment.addr " .
            "%s",
            $newWalletDir,
            $newWalletDir,
            cardanoNetworkFlag()
        ));

        // Return new wallet address
        return file_get_contents("{$newWalletDir}/payment.addr");
    }
}
