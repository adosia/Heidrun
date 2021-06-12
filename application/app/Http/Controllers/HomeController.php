<?php

namespace App\Http\Controllers;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\Foundation\Application;

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

    /**
     * @return Application|Factory|View
     */
    public function envError(Request $request)
    {
        $errorMessage = null;

        try {
            $errorMessage = Crypt::decryptString($request->message);
        } catch (Throwable $exception) { }

        if (empty($errorMessage)) {
            $errorMessage = 'Unknown environment error occurred';
        }

        return view('errors.env-error', compact('errorMessage'));
    }
}
