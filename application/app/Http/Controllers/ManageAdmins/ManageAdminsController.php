<?php

namespace App\Http\Controllers\ManageAdmins;

use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\Foundation\Application;

class ManageAdminsController extends Controller
{
    /**
     * @return Application|Factory|View
     */
    public function index()
    {
        return view('manage-admins.index');
    }
}
