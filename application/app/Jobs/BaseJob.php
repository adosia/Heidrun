<?php

namespace App\Jobs;

use Exception;
use Throwable;
use App\Models\Job;
use Illuminate\Bus\Queueable;
use App\Services\BlockFrostService;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Container\BindingResolutionException;

/**
 * Class BaseJob
 * @package App\Jobs
 */
class BaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Job $heidrunJob
     */
    public $heidrunJob;

    /**
     * @var BlockFrostService $blockFrostService
     */
    private $blockFrostService;

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
     * @param string $targetJobType
     * @throws Exception
     */
    protected function checkIsValidJobType(string $targetJobType): void
    {
        if ($this->heidrunJob->type !== $targetJobType) {
            throw new Exception(sprintf(
                'Invalid job type, was expecting "%s"',
                $targetJobType
            ));
        }
    }

    /**
     * @param array $targetJobStatuses
     * @throws Exception
     */
    protected function checkIsValidJobStatus(array $targetJobStatuses = [JOB_STATUS_PENDING, JOB_STATUS_PROCESSING]): void
    {
        if (!in_array($this->heidrunJob->status, $targetJobStatuses)) {
            throw new Exception(sprintf(
                'Invalid job status "%s", expecting %s',
                $this->heidrunJob->status,
                implode(' or ', $targetJobStatuses)
            ));
        }
    }

    /**
     * @param int $targetMaxAttempts
     * @throws Exception
     */
    protected function checkIfMaxAttemptsExceeded(int $targetMaxAttempts = JOB_MAX_ATTEMPTS): void
    {
        if ($this->heidrunJob->attempts > $targetMaxAttempts) {
            throw new Exception(sprintf(
                'Failed to find expected payment after max %d attempts',
                $targetMaxAttempts
            ));
        }
    }

    /**
     * Add log, update status and attempts
     */
    protected function beginJobProcessing(): void
    {
        $this->heidrunJob->addLog('Begin job processing');
        $this->heidrunJob->update([
            'status' => JOB_STATUS_PROCESSING,
            'attempts' => $this->heidrunJob->attempts + 1,
        ]);
    }

    /**
     * @param string $walletType
     * @param string $walletName
     * @return string
     * @throws Exception
     */
    protected function readWalletAddress(string $walletType, string $walletName): string
    {
        $walletAddress = file_get_contents(sprintf(
            '%s/%s/payment.addr',
            WALLET_DIR,
            $walletName,
        ));

        if (empty($walletAddress)) {
            throw new Exception(sprintf(
                'Failed to read the address of %s wallet "%s"',
                $walletType,
                $walletName
            ));
        }

        return $walletAddress;
    }

    /**
     * @param string $walletAddress
     * @return array
     */
    protected function loadWalletUTXOs(string $walletAddress): array
    {
        $utxos = [];

        try {
            $utxos = $this->blockFrostService()->get("addresses/{$walletAddress}/utxos");
        } catch (Throwable $exception) { }

        return $utxos;
    }

    /**
     * @param array $walletUTXOs
     * @param int $expectedLovelace
     * @return array|null
     */
    protected function findExpectedPayment(array $walletUTXOs, int $expectedLovelace): ?array
    {
        $result = null;

        foreach ($walletUTXOs as $utxo) {
            foreach ($utxo['amount'] as $amount) {
                if ($amount['unit']  === 'lovelace' && (int) $amount['quantity'] === $expectedLovelace ) {
                    $result = [
                        'lovelace' => $expectedLovelace,
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
     * @return BlockFrostService
     * @throws BindingResolutionException
     */
    protected function blockFrostService(): BlockFrostService
    {
        if (!$this->blockFrostService) {
            $this->blockFrostService = app()->make(BlockFrostService::class);
        }

        return $this->blockFrostService;
    }

    /**
     * @param Throwable $exception
     */
    public function failed(Throwable $exception): void
    {
        if ($this->heidrunJob) {
            Log::error(
                'Heidrun job failed to process',
                [
                    'error' => $exception->getMessage(),
                    'jobId' => $this->heidrunJob->id,
                    'stackTrace' => $exception->getTrace(),
                ]
            );

            $this->heidrunJob->addLog(
                $exception->getMessage(),
                JOB_STATUS_ERROR
            );
        }
    }
}
