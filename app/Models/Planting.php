<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Planting extends Model
{
    use HasFactory;

    protected $appends = ['estimated_yield_kg'];
    protected $fillable = [
        'user_id',
        'garden_id',
        'crop_id',
        'planted_at',
        'estimated_harvest_at',
        'actual_harvest_at',
        'status',
        'area_hectares',
        'plant_count',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'planted_at' => 'date',
            'estimated_harvest_at' => 'date',
            'actual_harvest_at' => 'date',
            'area_hectares' => 'decimal:2',
            'plant_count' => 'integer',
        ];
    }

    /**
     * Get the farmer who owns this planting.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the garden that this planting belongs to.
     */
    public function garden(): BelongsTo
    {
        return $this->belongsTo(Garden::class);
    }

    /**
     * Get the crop type for this planting.
     */
    public function crop(): BelongsTo
    {
        return $this->belongsTo(Crop::class);
    }

    /**
     * Get all progress logs for this planting.
     */
    public function progressLogs(): HasMany
    {
        return $this->hasMany(ProgressLog::class)->orderByDesc('created_at');
    }

    /**
     * Get the number of days remaining until estimated harvest.
     */
    public function getDaysRemainingAttribute(): int
    {
        return max(0, (int) now()->diffInDays($this->estimated_harvest_at, false));
    }

    /**
     * Get the progress percentage (0-100) based on elapsed time.
     */
    public function getProgressPercentageAttribute(): int
    {
        $totalDays = $this->planted_at->diffInDays($this->estimated_harvest_at);

        if ($totalDays <= 0) {
            return 100;
        }

        $elapsedDays = $this->planted_at->diffInDays(now());
        $percentage = (int) round(($elapsedDays / $totalDays) * 100);

        return min(100, max(0, $percentage));
    }

    /**
     * Get the HST (Hari Setelah Tanam) — days since planting.
     */
    public function getHstAttribute(): int
    {
        return (int) $this->planted_at->diffInDays(now());
    }

    /**
     * Get estimated yield in Kg based on plant count and crop yield factor.
     */
    public function getEstimatedYieldKgAttribute(): ?float
    {
        if ($this->plant_count && $this->crop && $this->crop->estimated_yield_per_plant_kg) {
            return (float) ($this->plant_count * $this->crop->estimated_yield_per_plant_kg);
        }
        return null;
    }

    /**
     * Scope: only plantings visible to buyers (not harvested).
     */
    public function scopeForBuyers($query)
    {
        return $query->whereIn('status', ['growing', 'pre-order', 'ready']);
    }

    /**
     * Scope: upcoming harvests within a date range.
     */
    public function scopeHarvestBetween($query, Carbon $start, Carbon $end)
    {
        return $query->whereBetween('estimated_harvest_at', [$start, $end]);
    }
}
