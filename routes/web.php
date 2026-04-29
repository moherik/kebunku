<?php

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GardenController;
use App\Http\Controllers\PlantingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProgressLogController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Welcome');
});

Route::get('calendar', [CalendarController::class, 'index'])->name('calendar');
Route::get('kebun/{garden}', [GardenController::class, 'show'])->name('garden.show');
Route::get('tanaman/{planting}', function ($planting) {
    return "Halaman detail penanaman public (Threads Progress Log) sedang dalam pengembangan.";
})->name('public.plantings.show');


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
        Route::delete('plantings/{planting}/progress/{log}', [ProgressLogController::class, 'destroy'])
            ->name('plantings.progress.destroy');
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

require __DIR__.'/auth.php';

