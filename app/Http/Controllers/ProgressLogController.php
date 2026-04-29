<?php

namespace App\Http\Controllers;

use App\Models\Planting;
use App\Models\ProgressLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProgressLogController extends Controller
{
    /**
     * Show the form for creating a new progress log.
     */
    public function create(Planting $planting): Response
    {
        Gate::authorize('update', $planting);

        return Inertia::render('Farmer/ProgressLog/Create', [
            'planting' => $planting->load('crop'),
        ]);
    }

    /**
     * Store a newly created progress log.
     * Handles offline_uuid for deduplication from PWA sync.
     */
    public function store(Request $request, Planting $planting)
    {
        Gate::authorize('update', $planting);

        $validated = $request->validate([
            'note' => 'nullable|string|max:2000',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120', // 5MB max
            'offline_uuid' => 'nullable|string|max:36',
        ]);

        // Deduplicate offline synced entries
        if (!empty($validated['offline_uuid'])) {
            $existing = ProgressLog::where('offline_uuid', $validated['offline_uuid'])->first();
            if ($existing) {
                return redirect()
                    ->route('farmer.plantings.show', $planting)
                    ->with('info', 'Catatan sudah tersinkronisasi sebelumnya.');
            }
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store(
                'progress-logs/' . $planting->id,
                'public'
            );
        }

        $planting->progressLogs()->create([
            'note' => $validated['note'],
            'photo_path' => $photoPath,
            'offline_uuid' => $validated['offline_uuid'] ?? null,
        ]);

        return redirect()
            ->route('farmer.plantings.show', $planting)
            ->with('success', 'Catatan perkembangan berhasil ditambahkan!');
    }

    /**
     * Remove the specified progress log.
     */
    public function destroy(Planting $planting, ProgressLog $log)
    {
        Gate::authorize('update', $planting);

        // Delete the photo file if it exists
        if ($log->photo_path) {
            Storage::disk('public')->delete($log->photo_path);
        }

        $log->delete();

        return redirect()
            ->route('farmer.plantings.show', $planting)
            ->with('success', 'Catatan berhasil dihapus.');
    }
}
