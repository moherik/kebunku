<?php

namespace App\Http\Controllers;

use App\Models\Crop;
use App\Models\Planting;
use App\Services\HarvestEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PlantingController extends Controller
{
    public function __construct(
        private HarvestEngine $harvestEngine
    ) {}

    /**
     * Display a listing of the farmer's plantings.
     */
    public function index(Request $request): Response
    {
        $plantings = $request->user()
            ->plantings()
            ->with('crop')
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(10);

        return Inertia::render('Farmer/Plantings/Index', [
            'plantings' => $plantings,
            'filters' => $request->only('status'),
        ]);
    }

    /**
     * Show the form for creating a new planting.
     */
    public function create(): Response
    {
        $crops = Crop::orderBy('name')->get();

        return Inertia::render('Farmer/Plantings/Create', [
            'crops' => $crops,
        ]);
    }

    /**
     * Store a newly created planting.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'crop_id' => 'required|exists:crops,id',
            'planted_at' => 'required|date|before_or_equal:today',
            'area_hectares' => 'nullable|numeric|min:0.01|max:9999',
            'plant_count' => 'nullable|integer|min:1|max:999999',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['user_id'] = $request->user()->id;
        $garden = $request->user()->gardens()->first();
        if ($garden) {
            $validated['garden_id'] = $garden->id;
        }

        $planting = $this->harvestEngine->createPlanting($validated);

        return redirect()
            ->route('farmer.plantings.show', $planting)
            ->with('success', 'Penanaman berhasil dicatat!');
    }

    /**
     * Display the specified planting with progress logs.
     */
    public function show(Request $request, Planting $planting): Response
    {
        // Ensure the farmer owns this planting
        Gate::authorize('view', $planting);

        $planting->load(['crop', 'progressLogs']);
        
        $recentActivities = $planting->activityLogs()
            ->latest('activity_date')
            ->latest('id')
            ->limit(3)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'type_label' => $activity->type_label,
                    'type_icon' => $activity->type_icon,
                    'activity_date' => $activity->activity_date->format('Y-m-d'),
                    'note' => $activity->note,
                ];
            });

        // Refresh status based on current date
        $this->harvestEngine->refreshStatus($planting);

        return Inertia::render('Farmer/Plantings/Show', [
            'planting' => $planting,
            'recentActivities' => $recentActivities,
        ]);
    }

    /**
     * Show the form for editing the specified planting.
     */
    public function edit(Request $request, Planting $planting): Response
    {
        Gate::authorize('update', $planting);

        $crops = Crop::orderBy('name')->get();

        return Inertia::render('Farmer/Plantings/Create', [
            'planting' => $planting->load('crop'),
            'crops' => $crops,
        ]);
    }

    /**
     * Update the specified planting.
     */
    public function update(Request $request, Planting $planting)
    {
        Gate::authorize('update', $planting);

        $validated = $request->validate([
            'status' => 'sometimes|in:growing,pre-order,ready,harvested',
            'area_hectares' => 'nullable|numeric|min:0.01|max:9999',
            'plant_count' => 'nullable|integer|min:1|max:999999',
            'notes' => 'nullable|string|max:1000',
        ]);

        // If marking as harvested, set the actual harvest date
        if (($validated['status'] ?? null) === 'harvested') {
            $this->harvestEngine->markHarvested($planting);
        } else {
            $planting->update($validated);
        }

        return redirect()
            ->route('farmer.plantings.show', $planting)
            ->with('success', 'Penanaman berhasil diperbarui!');
    }

    /**
     * Remove the specified planting.
     */
    public function destroy(Request $request, Planting $planting)
    {
        Gate::authorize('delete', $planting);

        $planting->delete();

        return redirect()
            ->route('farmer.plantings.index')
            ->with('success', 'Penanaman berhasil dihapus.');
    }

    /**
     * Adjust the estimated harvest date (when growth is delayed).
     */
    public function adjustHarvest(Request $request, Planting $planting)
    {
        Gate::authorize('update', $planting);

        $validated = $request->validate([
            'estimated_harvest_at' => 'required|date|after:' . $planting->planted_at->toDateString(),
        ]);

        $this->harvestEngine->adjustHarvestDate(
            $planting,
            Carbon::parse($validated['estimated_harvest_at'])
        );

        return redirect()
            ->route('farmer.plantings.show', $planting)
            ->with('success', 'Estimasi panen berhasil disesuaikan!');
    }
}
