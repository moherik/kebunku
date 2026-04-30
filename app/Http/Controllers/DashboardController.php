<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Planting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the role-based dashboard.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        if ($user->isFarmer()) {
            return $this->farmerDashboard($user);
        }

        return $this->buyerDashboard();
    }

    /**
     * Farmer dashboard with planting stats and recent activity.
     */
    private function farmerDashboard($user): Response
    {
        $plantings = $user->plantings()
            ->with('crop')
            ->latest()
            ->limit(5)
            ->get();

        $stats = [
            'total_plantings' => $user->plantings()->count(),
            'growing' => $user->plantings()->where('status', 'growing')->count(),
            'pre_order' => $user->plantings()->where('status', 'pre-order')->count(),
            'ready' => $user->plantings()->where('status', 'ready')->count(),
            'harvested' => $user->plantings()->where('status', 'harvested')->count(),
        ];

        $recentActivities = $user->activityLogs()
            ->with('planting.crop')
            ->orderByDesc('activity_date')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn($a) => [
                ...$a->toArray(),
                'type_label' => $a->type_label,
                'type_icon' => $a->type_icon,
                'formatted_quantity' => $a->formatted_quantity,
            ]);

        $financialSummary = [
            'expenses' => (float) $user->transactions()->expenses()->sum('amount'),
            'income' => (float) $user->transactions()->incomes()->sum('amount'),
        ];
        $financialSummary['profit'] = $financialSummary['income'] - $financialSummary['expenses'];

        $activeGarden = $user->gardens()->whereNotNull('bmkg_adm4_code')->first();

        $upcomingHarvests = $user->plantings()
            ->with('crop')
            ->where('status', '!=', 'harvested')
            ->orderBy('estimated_harvest_at')
            ->limit(3)
            ->get();

        // Prepare Calendar Events
        $calendarEvents = [];

        // Add Plantings (Planting date and Harvest date)
        $allPlantings = $user->plantings()->with('crop')->get();
        foreach ($allPlantings as $p) {
            // Planting event
            $calendarEvents[] = [
                'id' => 'plant_' . $p->id,
                'title' => '🌱 Tanam: ' . ($p->crop->name ?? 'Tanaman'),
                'start' => $p->planted_at->format('Y-m-d'),
                'backgroundColor' => '#10b981', // emerald-500
                'url' => route('farmer.plantings.show', $p->id)
            ];

            // Harvest event
            $harvestDate = $p->actual_harvest_at ?? $p->estimated_harvest_at;
            if ($harvestDate) {
                $calendarEvents[] = [
                    'id' => 'harvest_' . $p->id,
                    'title' => '🌾 Panen: ' . ($p->crop->name ?? 'Tanaman'),
                    'start' => $harvestDate->format('Y-m-d'),
                    'backgroundColor' => '#f59e0b', // amber-500
                    'url' => route('farmer.plantings.show', $p->id)
                ];
            }
        }

        // Add Activities
        $allActivities = $user->activityLogs()->with('planting.crop')->get();
        foreach ($allActivities as $a) {
            $cropName = $a->planting->crop->name ?? 'Tanaman';
            $calendarEvents[] = [
                'id' => 'act_' . $a->id,
                'title' => $a->type_icon . ' ' . $a->type_label . ': ' . $cropName,
                'start' => $a->activity_date->format('Y-m-d'),
                'backgroundColor' => '#3b82f6', // blue-500
                'url' => route('farmer.plantings.activities.index', $a->planting_id)
            ];
        }

        return Inertia::render('Dashboard', [
            'userRole' => 'farmer',
            'stats' => $stats,
            'recentPlantings' => $plantings,
            'recentActivities' => $recentActivities,
            'upcomingHarvests' => $upcomingHarvests,
            'financialSummary' => $financialSummary,
            'calendarEvents' => $calendarEvents,
            'activeGarden' => $activeGarden ? [
                'id' => $activeGarden->id,
                'name' => $activeGarden->name,
                'kelurahan' => null, // Will be filled by API
            ] : null,
        ]);
    }

    /**
     * Buyer dashboard with upcoming harvests overview.
     */
    private function buyerDashboard(): Response
    {
        $upcomingHarvests = Planting::forBuyers()
            ->with(['crop', 'user'])
            ->where('estimated_harvest_at', '>=', now())
            ->orderBy('estimated_harvest_at')
            ->limit(10)
            ->get();

        $stats = [
            'total_available' => Planting::forBuyers()->count(),
            'ready_now' => Planting::where('status', 'ready')->count(),
            'coming_this_week' => Planting::forBuyers()
                ->whereBetween('estimated_harvest_at', [now(), now()->addDays(7)])
                ->count(),
        ];

        return Inertia::render('Dashboard', [
            'userRole' => 'buyer',
            'stats' => $stats,
            'upcomingHarvests' => $upcomingHarvests,
        ]);
    }
}
