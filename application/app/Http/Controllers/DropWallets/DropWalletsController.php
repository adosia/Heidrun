<?php

namespace App\Http\Controllers\DropWallets;

use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\Foundation\Application;

class DropWalletsController extends Controller
{
    /**
     * @return Application|Factory|View
     */
    public function index()
    {
        return view('drop-wallets.index');
    }
}
