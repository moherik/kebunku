<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Planting;
use App\Models\ProgressLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SyncController extends Controller
{
    /**
     * Batch sync progress logs from PWA offline queue.
     * Accepts an array of progress log entries with optional base64-encoded photos.
     */
    public function syncProgressLogs(Request $request): JsonResponse
    {
        $request->validate([
            'logs' => 'required|array|max:50',
            'logs.*.planting_id' => 'required|exists:plantings,id',
            'logs.*.note' => 'nullable|string|max:2000',
            'logs.*.offline_uuid' => 'required|string|max:36',
            'logs.*.photo_base64' => 'nullable|string',
            'logs.*.created_at' => 'nullable|date',
        ]);

        $user = $request->user();
        $results = [];

        foreach ($request->logs as $logData) {
            // Verify the farmer owns this planting
            $planting = Planting::where('id', $logData['planting_id'])
                ->where('user_id', $user->id)
                ->first();

            if (!$planting) {
                $results[] = [
                    'offline_uuid' => $logData['offline_uuid'],
                    'status' => 'error',
                    'message' => 'Penanaman tidak ditemukan.',
                ];
                continue;
            }

            // Skip duplicates
            $existing = ProgressLog::where('offline_uuid', $logData['offline_uuid'])->first();
            if ($existing) {
                $results[] = [
                    'offline_uuid' => $logData['offline_uuid'],
                    'status' => 'duplicate',
                    'id' => $existing->id,
                ];
                continue;
            }

            // Handle base64 photo upload
            $photoPath = null;
            if (!empty($logData['photo_base64'])) {
                $photoPath = $this->storeBase64Photo(
                    $logData['photo_base64'],
                    $planting->id
                );
            }

            $log = $planting->progressLogs()->create([
                'note' => $logData['note'] ?? null,
                'photo_path' => $photoPath,
                'offline_uuid' => $logData['offline_uuid'],
                'created_at' => $logData['created_at'] ?? now(),
            ]);

            $results[] = [
                'offline_uuid' => $logData['offline_uuid'],
                'status' => 'created',
                'id' => $log->id,
            ];
        }

        return response()->json([
            'message' => 'Sinkronisasi selesai.',
            'results' => $results,
        ]);
    }

    /**
     * Store a base64-encoded photo to disk.
     */
    private function storeBase64Photo(string $base64, int $plantingId): ?string
    {
        try {
            // Extract the actual base64 data (remove data:image/...;base64, prefix)
            $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
            $imageData = base64_decode($imageData);

            if ($imageData === false) {
                return null;
            }

            $filename = 'progress-logs/' . $plantingId . '/' . uniqid() . '.jpg';
            Storage::disk('public')->put($filename, $imageData);

            return $filename;
        } catch (\Exception $e) {
            return null;
        }
    }
}
