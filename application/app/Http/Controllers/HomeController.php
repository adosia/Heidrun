<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class HomeController extends Controller
{
    /**
     * @return RedirectResponse
     */
    public function index(): RedirectResponse
    {
        return redirect()
            ->route('dashboard.index');
    }
}
