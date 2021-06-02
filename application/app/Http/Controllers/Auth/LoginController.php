<?php

namespace App\Http\Controllers\Auth;

use Exception;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Foundation\Application;

class LoginController extends Controller
{
    /**
     * @return Application|Factory|View
     */
    public function index()
    {
        return view('login.form');
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function login(Request $request): RedirectResponse
    {
        try {

            // Request validation rules
            $validator = Validator::make($request->all(), [
                'email' => ['required', 'email'],
                'password' => ['required'],
                'remember' => ['nullable', 'in:yes'],
            ]);

            // Check if validation failed
            if ($validator->fails()) {
                return redirect()
                    ->back()
                    ->with('error', implode(' ', $validator->errors()->all()));
            }

            // Try to login user
            $rememberMe = $request->has('remember') && $request->remember == 'yes';
            $credentials = $request->only(['email', 'password']);
            if (Auth::attempt($credentials, $rememberMe)) {
                return redirect()
                    ->intended(route('dashboard.index'))
                    ->with('success', 'Successfully logged in as Heidrun admin.');
            }

            // Login failed
            throw new Exception('Invalid credentials');

        } catch (Throwable $exception) {

            // Log error
            Log::error('Admin login failed', [
                'error' => $exception->getMessage(),
                'email' => $request->email ?? 'unknown',
            ]);

            // Handle error
            return redirect()
                ->route('login-form')
                ->with('error', 'Sorry, admin login details are not valid.');

        }
    }
}
