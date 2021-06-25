<?php

namespace App\Http\Controllers\Dashboard;

use App\Services\CardanoCliService;
use App\Services\StatsService;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\Foundation\Application;

class DashboardController extends Controller
{
    /**
     * @var CardanoCliService $cardanoCliService
     */
    private $cardanoCliService;

    /**
     * @var StatsService $statsService
     */
    private $statsService;

    /**
     * DashboardController constructor.
     * @param CardanoCliService $cardanoCliService
     * @param StatsService $statsService
     */
    public function __construct(
        CardanoCliService $cardanoCliService,
        StatsService $statsService
    )
    {
        $this->cardanoCliService = $cardanoCliService;
        $this->statsService = $statsService;
    }

    /**
     * @return Application|Factory|View
     */
    public function index()
    {
        $cardanoCliVersion = $this->cardanoCliService->versionInfo();
        $stats = $this->statsService->summary();

        return view(
            'dashboard.index',
            compact('stats', 'cardanoCliVersion')
        );
    }
}
