<?php

namespace App\Http\Controllers;

use App\Models\Garden;
use App\Services\BmkgWeatherService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WeatherController extends Controller
{
    public function __construct(
        private BmkgWeatherService $weatherService
    ) {}

    /**
     * Display weather dashboard for the farmer's gardens.
     */
    public function index()
    {
        $user = auth()->user();

        $gardens = $user->gardens()
            ->select('id', 'name', 'address', 'bmkg_adm4_code', 'latitude', 'longitude')
            ->get();

        // Fetch weather for the first garden that has a BMKG code
        $activeGarden = $gardens->first(fn($g) => $g->hasWeatherLocation());
        $weatherData = null;

        if ($activeGarden) {
            $weatherData = $this->weatherService->fetchForecast($activeGarden->bmkg_adm4_code);
        }

        return Inertia::render('Farmer/Weather/Index', [
            'gardens' => $gardens,
            'activeGardenId' => $activeGarden?->id,
            'weatherData' => $weatherData,
        ]);
    }

    /**
     * Fetch weather data for a specific garden via API.
     */
    public function fetch(Garden $garden)
    {
        if ($garden->user_id !== auth()->id()) {
            abort(403);
        }

        if (!$garden->hasWeatherLocation()) {
            return response()->json([
                'error' => 'Kebun ini belum memiliki lokasi BMKG. Silakan atur di pengaturan kebun.',
            ], 422);
        }

        $weatherData = $this->weatherService->fetchForecast($garden->bmkg_adm4_code);

        if (!$weatherData) {
            return response()->json([
                'error' => 'Gagal mengambil data cuaca dari BMKG. Coba lagi nanti.',
            ], 503);
        }

        return response()->json($weatherData);
    }
}
