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
