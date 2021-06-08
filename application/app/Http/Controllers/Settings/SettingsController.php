<?php

namespace App\Http\Controllers\Settings;

use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\Foundation\Application;

class SettingsController extends Controller
{
    /**
     * @return Application|Factory|View
     */
    public function index()
    {
        return view('settings.index');
    }
}
