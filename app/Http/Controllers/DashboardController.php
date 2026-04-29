<?php

namespace App\Http\Controllers;

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

        return Inertia::render('Dashboard', [
            'userRole' => 'farmer',
            'stats' => $stats,
            'recentPlantings' => $plantings,
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
