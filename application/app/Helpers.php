<?php

use Illuminate\Support\Facades\Log;

/**
 * @param string $command
 * @return string
 * @throws Exception
 */
function shellExec(string $command): string {
    $descriptorSpec = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w'],
    ];
    $process = proc_open($command, $descriptorSpec, $pipes, PRIVATE_DIR);
    if (is_resource($process)) {
        $stdOut = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stdErr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $retCode = proc_close($process);
        if ($retCode !== 0) {
            $stdErr = trim($stdErr);
            throw new Exception("Command [{$command}] failed - #{$retCode} {$stdErr}");
        }
        if (empty($stdOut)) {
            $stdOut = 'EmptyResponse';
        }
        return trim($stdOut);
    } else {
        throw new Exception("Command [{$command}] failed - could not open process");
    }
}

/**
 * @param string $message
 * @param Throwable $exception
 */
function logError(string $message, Throwable $exception): void {
    Log::error(
        $message,
        [
            'error' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]
    );
}

/**
 * @return string
 */
function cardanoNetworkFlag(): string {
    return env('CARDANO_NETWORK') === NETWORK_TESTNET
        ? '--testnet-magic 1097911063'
        : '--mainnet';
}

/**
 * @param array $item
 * @return string
 */
function parseADAInfo(array $item): string {
    return sprintf(
        '<button class="btn btn-primary mr-2"><strong>%s</strong> ₳DA</button>',
        $item['quantity'] / 1000000
    );
}

/**
 * @param array $item
 * @return string
 */
function parseAssetInfo(array $item): string {
    $policyId = substr($item['unit'], 0, 56);
    $assetName = hex2bin(substr($item['unit'], 56, strlen($item['unit'])));
    $friendlyQuantity = friendlyQuantity((int) $item['quantity']);
    return sprintf(
        '<button class="btn btn-primary mr-2"><strong>%s</strong> %s.%s</button>',
        $friendlyQuantity,
        $policyId,
        $assetName
    );
}

/**
 * @param int $number
 * @return string
 */
function friendlyQuantity(int $number): string {
    if ($number > 1000) {
        $x = round($number);
        $x_number_format = number_format($x);
        $x_array = explode(',', $x_number_format);
        $x_parts = ['K', 'M', 'N', 'T'];
        $x_count_parts = count($x_array) - 1;
        $x_display = $x_array[0] . ((int) $x_array[1][0] !== 0 ? '.' . $x_array[1][0] : '');
        $x_display .= $x_parts[$x_count_parts - 1];
        return $x_display;
    }
    return (string) $number;
}

/**
 * @param string $txHash
 * @return string
 */
function txExplorerUrl(string $txHash): string {
    return sprintf(
        'https://explorer.cardano-%s.iohkdev.io/en/transaction?id=%s',
        env('CARDANO_NETWORK'),
        $txHash
    );
}

/**
 * @param string $blockNo
 * @return string
 */
function blockExplorerUrl(string $blockNo): string {
    return sprintf(
        'https://explorer.cardano-%s.iohkdev.io/en/block?id=%s',
        env('CARDANO_NETWORK'),
        $blockNo
    );
}

/**
 * @param $dir
 */
function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir. DIRECTORY_SEPARATOR . $object) && !is_link($dir . "/" . $object)) {
                    rrmdir($dir . DIRECTORY_SEPARATOR . $object);
                } else {
                    unlink($dir . DIRECTORY_SEPARATOR . $object);
                }
            }
        }
        rmdir($dir);
    }
}
