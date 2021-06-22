<?php

namespace App\Jobs;

use Throwable;
use App\Models\Job;
use Illuminate\Bus\Queueable;
use App\Services\BlockFrostService;
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
     * @return BlockFrostService
     * @throws BindingResolutionException
     */
    public function blockFrostService(): BlockFrostService
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
            $this->heidrunJob->addLog(
                $exception->getMessage(),
                JOB_STATUS_ERROR
            );
        }
    }
}
