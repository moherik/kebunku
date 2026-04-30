<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WilayahController extends Controller
{
    /**
     * Proxy requests to wilayah.id to bypass CORS issues
     * and cache the responses to improve performance.
     */
    public function fetch($type, $code = null)
    {
        $allowedTypes = ['provinces', 'regencies', 'districts', 'villages'];
        
        if (!in_array($type, $allowedTypes)) {
            return response()->json(['error' => 'Invalid wilayah type'], 400);
        }

        $url = "https://wilayah.id/api/{$type}";
        if ($code) {
            $url .= "/{$code}";
        }
        $url .= ".json";

        $cacheKey = "wilayah_{$type}_" . ($code ?? 'all');

        $data = Cache::get($cacheKey);

        if (!$data) {
            try {
                $response = Http::timeout(10)->get($url);
                
                if ($response->successful()) {
                    $data = $response->json();
                    // Cache for 30 days since regional data rarely changes
                    Cache::put($cacheKey, $data, now()->addDays(30));
                } else {
                    return response()->json(['error' => 'Failed to fetch from upstream'], $response->status());
                }
            } catch (\Exception $e) {
                return response()->json(['error' => 'Connection to upstream failed'], 500);
            }
        }

        return response()->json($data);
    }
}
