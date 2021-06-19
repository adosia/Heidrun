<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\JobController;

/**
 * V1 Api endpoint group
 */
Route::prefix('v1')->group(function() {

    /**
     * Job api endpoints
     */
    Route::prefix('job')->group(function() {
        Route::post('create', [JobController::class, 'create']);
    });

});
