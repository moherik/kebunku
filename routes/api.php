<?php

use App\Http\Controllers\Api\SyncController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('calendar/events', [\App\Http\Controllers\CalendarController::class, 'events']);

Route::middleware('auth:sanctum')->group(function () {
    // PWA offline sync endpoint
    Route::post('sync/progress-logs', [SyncController::class, 'syncProgressLogs']);
});

