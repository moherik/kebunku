<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BmkgWeatherService
{
    /**
     * BMKG Public API base URL.
     */
    private const BMKG_API_URL = 'https://api.bmkg.go.id/publik/prakiraan-cuaca';

    /**
     * Cache TTL in seconds (3 hours — BMKG updates 2x/day).
     */
    private const CACHE_TTL = 10800;

    /**
     * Fetch weather forecast for a given BMKG adm4 code.
     *
     * @param string $adm4Code Kode wilayah tingkat kelurahan (e.g., "31.71.03.1001")
     * @return array|null Parsed weather data or null on failure
     */
    public function fetchForecast(string $adm4Code): ?array
    {
        $cacheKey = "bmkg_weather_{$adm4Code}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($adm4Code) {
            try {
                $response = Http::timeout(10)->get(self::BMKG_API_URL, [
                    'adm4' => $adm4Code,
                ]);

                if (!$response->successful()) {
                    Log::warning("BMKG API returned {$response->status()} for adm4: {$adm4Code}");
                    return null;
                }

                $data = $response->json();
                return $this->parseResponse($data);
            } catch (\Exception $e) {
                Log::error("BMKG Weather API error: {$e->getMessage()}", [
                    'adm4_code' => $adm4Code,
                ]);
                return null;
            }
        });
    }

    /**
     * Parse BMKG API response into a clean structure.
     */
    private function parseResponse(array $data): ?array
    {
        if (empty($data['data']) || !is_array($data['data'])) {
            return null;
        }

        $locationData = $data['data'][0] ?? null;
        if (!$locationData) {
            return null;
        }

        $location = [
            'provinsi' => $locationData['lokasi']['provinsi'] ?? null,
            'kota' => $locationData['lokasi']['kotkab'] ?? null,
            'kecamatan' => $locationData['lokasi']['kecamatan'] ?? null,
            'kelurahan' => $locationData['lokasi']['desa'] ?? null,
        ];

        $forecasts = [];
        foreach ($locationData['cuaca'] ?? [] as $dayGroup) {
            foreach ($dayGroup as $entry) {
                $forecasts[] = [
                    'datetime' => $entry['local_datetime'] ?? $entry['utc_datetime'] ?? null,
                    'weather' => $entry['weather_desc'] ?? null,
                    'weather_icon' => $this->getWeatherEmoji($entry['weather'] ?? null),
                    'temp' => $entry['t'] ?? null,
                    'humidity' => $entry['hu'] ?? null,
                    'wind_speed' => $entry['ws'] ?? null,
                    'wind_direction' => $entry['wd_to'] ?? null,
                    'visibility' => $entry['vs_text'] ?? null,
                ];
            }
        }

        // Group forecasts by date
        $grouped = [];
        foreach ($forecasts as $forecast) {
            if (!$forecast['datetime']) continue;
            $date = substr($forecast['datetime'], 0, 10);
            $grouped[$date][] = $forecast;
        }

        return [
            'location' => $location,
            'forecasts' => $grouped,
            'fetched_at' => now()->toIso8601String(),
            'source' => 'BMKG',
        ];
    }

    /**
     * Map BMKG weather code to emoji.
     */
    private function getWeatherEmoji(?int $code): string
    {
        return match ($code) {
            0       => '☀️',  // Cerah
            1, 2    => '⛅',  // Cerah Berawan
            3       => '☁️',  // Berawan
            4       => '🌧️',  // Berawan Tebal
            5       => '🌫️',  // Udara Kabur
            10      => '🌫️',  // Asap
            45      => '🌫️',  // Kabut
            60      => '🌧️',  // Hujan Ringan
            61      => '🌧️',  // Hujan Sedang
            63      => '🌧️',  // Hujan Lebat
            80      => '🌦️',  // Hujan Lokal
            95, 97  => '⛈️',  // Hujan Petir
            default => '🌤️',
        };
    }

    /**
     * Clear cached weather for a specific location.
     */
    public function clearCache(string $adm4Code): void
    {
        Cache::forget("bmkg_weather_{$adm4Code}");
    }
}
