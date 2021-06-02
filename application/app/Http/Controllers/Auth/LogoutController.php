<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class LogoutController extends Controller
{
    /**
     * @return RedirectResponse
     */
    public function index(): RedirectResponse
    {
        Auth::logout();

        return redirect()
            ->route('login-form')
            ->with('success', 'Successfully logged out of Heidrun admin account.');
    }
}
