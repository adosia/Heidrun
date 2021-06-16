<?php

/**
 * Imports
 */
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Settings\SettingsController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\DropWallets\DropWalletsController;
use App\Http\Controllers\ManageQueue\ManageQueueController;
use App\Http\Controllers\ManageAdmins\ManageAdminsController;
use App\Http\Controllers\PaymentWallets\PaymentWalletsController;

/**
 * Home Routes
 */
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('env-error', [HomeController::class, 'envError'])->name('env-error');

/**
 * Authentication Routes
 */
Route::prefix('login')->middleware('guest')->group(function() {
    Route::get('/', [LoginController::class, 'index'])->name('login-form');
    Route::post('/', [LoginController::class, 'login'])->name('login-handler');
});
Route::get('logout', [LogoutController::class, 'index'])->name('logout-handler');

/**
 * Dashboard Routes
 */
route::prefix('dashboard')->middleware('auth')->group(function() {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');
});

/**
 * Payment Wallets Routes
 */
route::prefix('payment-wallets')->middleware('auth')->group(function() {
    Route::get('/', [PaymentWalletsController::class, 'index'])->name('payment-wallets.index');
    Route::get('create', [PaymentWalletsController::class, 'createForm'])->name('payment-wallets.create-form');
    Route::post('create', [PaymentWalletsController::class, 'createWallet'])->name('payment-wallets.create-wallet');
    Route::get('{walletId}/show', [PaymentWalletsController::class, 'show'])->name('payment-wallets.show');
});

/**
 * Drop Wallets Routes
 */
route::prefix('drop-wallets')->middleware('auth')->group(function() {
    Route::get('/', [DropWalletsController::class, 'index'])->name('drop-wallets.index');
    Route::get('create', [DropWalletsController::class, 'createForm'])->name('drop-wallets.create-form');
    Route::post('create', [DropWalletsController::class, 'createWallet'])->name('drop-wallets.create-wallet');
    Route::get('{walletId}/show', [DropWalletsController::class, 'show'])->name('drop-wallets.show');
});

/**
 * Manage Admins Routes
 */
route::prefix('manage-admins')->middleware('auth')->group(function() {
    Route::get('/', [ManageAdminsController::class, 'index'])->name('manage-admins.index');
});

/**
 * Manage Queue Routes
 */
route::prefix('manage-queue')->middleware('auth')->group(function() {
    Route::get('/', [ManageQueueController::class, 'index'])->name('manage-queue.index');
});

/**
 * Settings Routes
 */
route::prefix('settings')->middleware('auth')->group(function() {
    Route::get('/', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/', [SettingsController::class, 'update'])->name('settings.update');
});
