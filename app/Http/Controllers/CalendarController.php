<?php

namespace App\Http\Controllers;

use App\Models\Planting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class CalendarController extends Controller
{
    /**
     * Display the buyer's harvest calendar page.
     */
    public function index(): Response
    {
        $upcomingPlantings = Planting::forBuyers()
            ->with(['crop', 'user:id,name,phone,address', 'garden'])
            ->where('status', '!=', 'harvested')
            ->orderBy('estimated_harvest_at', 'asc')
            ->get();

        return Inertia::render('Buyer/Calendar', [
            'upcomingPlantings' => $upcomingPlantings
        ]);
    }

    /**
     * Return calendar events as JSON for FullCalendar.
     * Used by both Inertia page load and API calls.
     */
    public function events(Request $request)
    {
        $request->validate([
            'start' => 'nullable|string',
            'end' => 'nullable|string',
            'status' => 'nullable|in:growing,pre-order,ready,harvested',
            'crop_id' => 'nullable|exists:crops,id',
        ]);

        $startStr = explode('T', $request->get('start', now()->startOfMonth()->toDateString()))[0];
        $endStr = explode('T', $request->get('end', now()->endOfMonth()->addMonths(2)->toDateString()))[0];

        $start = Carbon::parse($startStr);
        $end = Carbon::parse($endStr);

        $plantings = Planting::forBuyers()
            ->with(['crop', 'user:id,name,phone,address', 'garden'])
            ->harvestBetween($start, $end)
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->crop_id, fn($q, $id) => $q->where('crop_id', $id))
            ->get();

        $events = $plantings->map(function (Planting $planting) {
            return [
                'id' => $planting->id,
                'title' => $planting->crop->icon . ' ' . $planting->crop->full_name,
                'start' => $planting->estimated_harvest_at->toDateString(),
                'backgroundColor' => $this->statusColor($planting->status),
                'borderColor' => $this->statusColor($planting->status),
                'textColor' => '#FFFFFF',
                'extendedProps' => [
                    'planting_id' => $planting->id,
                    'crop_name' => $planting->crop->full_name,
                    'crop_icon' => $planting->crop->icon,
                    'farmer_name' => $planting->garden ? $planting->garden->name : $planting->user->name,
                    'farmer_phone' => $planting->garden && $planting->garden->whatsapp_number ? $planting->garden->whatsapp_number : $planting->user->phone,
                    'farmer_address' => $planting->user->address,
                    'planted_at' => $planting->planted_at->toDateString(),
                    'estimated_harvest_at' => $planting->estimated_harvest_at->toDateString(),
                    'status' => $planting->status,
                    'area_hectares' => $planting->area_hectares,
                    'plant_count' => $planting->plant_count,
                    'estimated_yield_kg' => $planting->estimated_yield_kg,
                    'hst' => $planting->hst,
                    'days_remaining' => $planting->days_remaining,
                    'progress_percentage' => $planting->progress_percentage,
                    'garden' => $planting->garden,
                ],
            ];
        });

        return response()->json($events);
    }

    /**
     * Display planting details for a buyer.
     */
    public function show(Planting $planting): Response
    {
        $planting->load(['crop', 'user:id,name,phone,address', 'progressLogs']);

        return Inertia::render('Buyer/PlantingDetail', [
            'planting' => $planting,
        ]);
    }

    /**
     * Map planting status to color codes.
     */
    private function statusColor(string $status): string
    {
        return match ($status) {
            'growing' => '#2D6A4F',     // Forest Green
            'pre-order' => '#E9C46A',   // Harvest Gold
            'ready' => '#F4A261',        // Warm Orange
            'harvested' => '#6C757D',    // Cool Gray
            default => '#2D6A4F',
        };
    }
}
