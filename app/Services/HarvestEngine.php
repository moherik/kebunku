<?php

namespace App\Services;

use App\Models\Crop;
use App\Models\Planting;
use Carbon\Carbon;
use InvalidArgumentException;

class HarvestEngine
{
    /**
     * Calculate the estimated harvest date based on planted date and crop's default HST.
     *
     * @param Carbon $plantedAt The date when the crop was planted
     * @param Crop $crop The crop type containing default_hst
     * @return Carbon The estimated harvest date
     */
    public function calculateHarvestDate(Carbon $plantedAt, Crop $crop): Carbon
    {
        return $plantedAt->copy()->addDays($crop->default_hst);
    }

    /**
     * Allow a farmer to manually adjust the estimated harvest date.
     * Useful when growth is delayed due to weather, pests, etc.
     *
     * @param Planting $planting The planting to adjust
     * @param Carbon $newDate The new estimated harvest date
     * @return Planting The updated planting
     * @throws InvalidArgumentException If the new date is before the planting date
     */
    public function adjustHarvestDate(Planting $planting, Carbon $newDate): Planting
    {
        if ($newDate->lte($planting->planted_at)) {
            throw new InvalidArgumentException(
                'Tanggal panen harus setelah tanggal tanam.'
            );
        }

        $planting->update(['estimated_harvest_at' => $newDate]);

        return $planting->fresh();
    }

    /**
     * Create a new planting with auto-calculated harvest date.
     *
     * @param array $data Planting data (user_id, crop_id, planted_at, etc.)
     * @return Planting The created planting
     */
    public function createPlanting(array $data): Planting
    {
        $crop = Crop::findOrFail($data['crop_id']);
        $plantedAt = Carbon::parse($data['planted_at']);

        $data['estimated_harvest_at'] = $this->calculateHarvestDate($plantedAt, $crop);

        return Planting::create($data);
    }

    /**
     * Auto-update planting status based on current date vs estimated harvest.
     *
     * Status transitions:
     * - If harvest date is within 7 days → 'pre-order' (if currently 'growing')
     * - If harvest date has passed → 'ready' (if currently 'growing' or 'pre-order')
     *
     * @param Planting $planting The planting to refresh
     * @return Planting The updated planting
     */
    public function refreshStatus(Planting $planting): Planting
    {
        $now = now();
        $harvestDate = $planting->estimated_harvest_at;

        // Don't auto-change if already harvested
        if ($planting->status === 'harvested') {
            return $planting;
        }

        if ($now->gte($harvestDate)) {
            // Harvest date has passed — mark as ready
            $planting->update(['status' => 'ready']);
        } elseif ($now->diffInDays($harvestDate, false) <= 7 && $planting->status === 'growing') {
            // Within 7 days of harvest — open for pre-orders
            $planting->update(['status' => 'pre-order']);
        }

        return $planting->fresh();
    }

    /**
     * Mark a planting as harvested.
     *
     * @param Planting $planting The planting to mark
     * @param Carbon|null $actualDate The actual harvest date (defaults to today)
     * @return Planting The updated planting
     */
    public function markHarvested(Planting $planting, ?Carbon $actualDate = null): Planting
    {
        $planting->update([
            'status' => 'harvested',
            'actual_harvest_at' => $actualDate ?? now(),
        ]);

        return $planting->fresh();
    }
}
