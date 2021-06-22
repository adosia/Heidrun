<?php

namespace App\Jobs;

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
     * @var BlockFrostService $blockFrostService
     */
    private $blockFrostService;

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
}
