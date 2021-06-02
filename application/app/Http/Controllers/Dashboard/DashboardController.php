<?php

namespace App\Http\Controllers\Dashboard;

use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\Foundation\Application;

class DashboardController extends Controller
{
    /**
     * @return Application|Factory|View
     */
    public function index()
    {
        return view('dashboard.index');
    }
}
