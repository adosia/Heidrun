<?php

namespace App\Http\Controllers\Dashboard;

use App\Services\CardanoCliService;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\Foundation\Application;

class DashboardController extends Controller
{
    /**
     * @var CardanoCliService
     */
    private $cardanoCliService;

    /**
     * DashboardController constructor.
     * @param CardanoCliService $cardanoCliService
     */
    public function __construct(CardanoCliService $cardanoCliService)
    {
        $this->cardanoCliService = $cardanoCliService;
    }

    /**
     * @return Application|Factory|View
     */
    public function index()
    {
        $cardanoCliVersion = $this->cardanoCliService->versionInfo();

        return view(
            'dashboard.index',
            compact('cardanoCliVersion')
        );
    }
}
