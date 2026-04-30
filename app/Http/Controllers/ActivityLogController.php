<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Planting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ActivityLogController extends Controller
{
    /**
     * Display activities for a specific planting.
     */
    public function index(Planting $planting)
    {
        $this->authorizePlanting($planting);

        $planting->load(['crop', 'garden']);

        $activities = $planting->activityLogs()
            ->orderByDesc('activity_date')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($a) => [
                ...$a->toArray(),
                'type_label' => $a->type_label,
                'type_icon' => $a->type_icon,
                'photo_url' => $a->photo_url,
                'formatted_quantity' => $a->formatted_quantity,
            ]);

        return Inertia::render('Farmer/Activities/Index', [
            'planting' => $planting,
            'activities' => $activities,
            'activityTypes' => ActivityLog::TYPE_LABELS,
            'activityIcons' => ActivityLog::TYPE_ICONS,
        ]);
    }

    /**
     * Show the form for creating a new activity.
     */
    public function create(Planting $planting)
    {
        $this->authorizePlanting($planting);

        $planting->load('crop');

        return Inertia::render('Farmer/Activities/Create', [
            'planting' => $planting,
            'activityTypes' => ActivityLog::TYPE_LABELS,
            'activityIcons' => ActivityLog::TYPE_ICONS,
        ]);
    }

    /**
     * Store a newly created activity.
     */
    public function store(Request $request, Planting $planting)
    {
        $this->authorizePlanting($planting);

        $validated = $request->validate([
            'type' => 'required|in:' . implode(',', array_keys(ActivityLog::TYPE_LABELS)),
            'activity_date' => 'required|date',
            'note' => 'nullable|string|max:2000',
            'photo' => 'nullable|image|max:5120',
            'quantity' => 'nullable|numeric|min:0',
            'quantity_unit' => 'nullable|string|max:20',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('activity-photos', 'public');
        }

        $planting->activityLogs()->create([
            'user_id' => auth()->id(),
            'type' => $validated['type'],
            'activity_date' => $validated['activity_date'],
            'note' => $validated['note'] ?? null,
            'photo_path' => $photoPath,
            'quantity' => $validated['quantity'] ?? null,
            'quantity_unit' => $validated['quantity_unit'] ?? null,
        ]);

        return redirect()
            ->route('farmer.plantings.activities.index', $planting)
            ->with('success', 'Aktivitas berhasil dicatat!');
    }

    /**
     * Show the form for editing the activity log.
     */
    public function edit(Planting $planting, ActivityLog $activity)
    {
        $this->authorizePlanting($planting);

        if ($activity->planting_id !== $planting->id) {
            abort(403);
        }

        $planting->load('crop');

        return Inertia::render('Farmer/Activities/Create', [
            'planting' => $planting,
            'activity' => $activity,
            'activityTypes' => ActivityLog::TYPE_LABELS,
            'activityIcons' => ActivityLog::TYPE_ICONS,
        ]);
    }

    /**
     * Update the specified activity log.
     */
    public function update(Request $request, Planting $planting, ActivityLog $activity)
    {
        $this->authorizePlanting($planting);

        if ($activity->planting_id !== $planting->id) {
            abort(403);
        }

        $validated = $request->validate([
            'type' => 'required|in:' . implode(',', array_keys(ActivityLog::TYPE_LABELS)),
            'activity_date' => 'required|date',
            'note' => 'nullable|string|max:2000',
            'photo' => 'nullable|image|max:5120',
            'quantity' => 'nullable|numeric|min:0',
            'quantity_unit' => 'nullable|string|max:20',
        ]);

        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($activity->photo_path) {
                Storage::disk('public')->delete($activity->photo_path);
            }
            $activity->photo_path = $request->file('photo')->store('activity-photos', 'public');
        }

        $activity->update([
            'type' => $validated['type'],
            'activity_date' => $validated['activity_date'],
            'note' => $validated['note'] ?? null,
            'quantity' => $validated['quantity'] ?? null,
            'quantity_unit' => $validated['quantity_unit'] ?? null,
        ]);

        return redirect()
            ->route('farmer.plantings.activities.index', $planting)
            ->with('success', 'Aktivitas berhasil diperbarui!');
    }

    /**
     * Delete an activity log.
     */
    public function destroy(Planting $planting, ActivityLog $activity)
    {
        $this->authorizePlanting($planting);

        if ($activity->planting_id !== $planting->id) {
            abort(403);
        }

        // Delete photo if exists
        if ($activity->photo_path) {
            Storage::disk('public')->delete($activity->photo_path);
        }

        $activity->delete();

        return redirect()
            ->route('farmer.plantings.activities.index', $planting)
            ->with('success', 'Aktivitas berhasil dihapus.');
    }

    /**
     * Ensure the authenticated user owns the planting.
     */
    private function authorizePlanting(Planting $planting): void
    {
        if ($planting->user_id !== auth()->id()) {
            abort(403);
        }
    }
}
