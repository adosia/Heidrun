<?php

namespace App\Services;

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
}
