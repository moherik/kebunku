<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GardenController;
use App\Http\Controllers\PlantingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgressLogController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WeatherController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('homepage');

Route::get('calendar', [CalendarController::class, 'index'])->name('calendar');
Route::get('kebun/{garden}', [GardenController::class, 'show'])->name('garden.show');
Route::get('tanaman/{planting}', function ($planting) {
    return "Halaman detail penanaman public (Threads Progress Log) sedang dalam pengembangan.";
})->name('public.plantings.show');


/*
|--------------------------------------------------------------------------
| API Proxy Routes
|--------------------------------------------------------------------------
*/
Route::get('/api/wilayah/{type}/{code?}', [\App\Http\Controllers\WilayahController::class, 'fetch'])->name('wilayah.fetch');

/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard (role-based)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile management (from Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |----------------------------------------------------------------------
    | Farmer Routes
    |----------------------------------------------------------------------
    */
    Route::middleware('role:farmer')->prefix('farmer')->name('farmer.')->group(function () {
        // Planting CRUD
        Route::resource('plantings', PlantingController::class);

        // Adjust harvest date
        Route::patch('plantings/{planting}/adjust-harvest', [PlantingController::class, 'adjustHarvest'])
            ->name('plantings.adjust-harvest');

        // Progress logs
        Route::get('plantings/{planting}/progress/create', [ProgressLogController::class, 'create'])
            ->name('plantings.progress.create');
        Route::post('plantings/{planting}/progress', [ProgressLogController::class, 'store'])
            ->name('plantings.progress.store');
        Route::get('plantings/{planting}/progress/{log}/edit', [ProgressLogController::class, 'edit'])
            ->name('plantings.progress.edit');
        Route::patch('plantings/{planting}/progress/{log}', [ProgressLogController::class, 'update'])
            ->name('plantings.progress.update');
        Route::delete('plantings/{planting}/progress/{log}', [ProgressLogController::class, 'destroy'])
            ->name('plantings.progress.destroy');

        // Activity logs (per planting)
        Route::get('plantings/{planting}/activities', [ActivityLogController::class, 'index'])
            ->name('plantings.activities.index');
        Route::get('plantings/{planting}/activities/create', [ActivityLogController::class, 'create'])
            ->name('plantings.activities.create');
        Route::post('plantings/{planting}/activities', [ActivityLogController::class, 'store'])
            ->name('plantings.activities.store');
        Route::get('plantings/{planting}/activities/{activity}/edit', [ActivityLogController::class, 'edit'])
            ->name('plantings.activities.edit');
        Route::patch('plantings/{planting}/activities/{activity}', [ActivityLogController::class, 'update'])
            ->name('plantings.activities.update');
        Route::delete('plantings/{planting}/activities/{activity}', [ActivityLogController::class, 'destroy'])
            ->name('plantings.activities.destroy');

        // Financial tracking
        Route::get('finances', [TransactionController::class, 'index'])->name('finances.index');
        Route::post('finances', [TransactionController::class, 'store'])->name('finances.store');
        Route::delete('finances/{transaction}', [TransactionController::class, 'destroy'])->name('finances.destroy');

        // Weather
        Route::get('weather', [WeatherController::class, 'index'])->name('weather.index');
        Route::get('weather/{garden}', [WeatherController::class, 'fetch'])->name('weather.fetch');
    });

    /*
    |----------------------------------------------------------------------
    | Buyer Routes
    |----------------------------------------------------------------------
    */
    Route::middleware('role:buyer')->prefix('buyer')->name('buyer.')->group(function () {
        Route::get('plantings/{planting}', [CalendarController::class, 'show'])->name('planting.show');
    });
});

require __DIR__ . '/auth.php';

