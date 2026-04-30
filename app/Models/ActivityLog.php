<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'planting_id',
        'user_id',
        'type',
        'activity_date',
        'note',
        'photo_path',
        'quantity',
        'quantity_unit',
    ];

    protected function casts(): array
    {
        return [
            'activity_date' => 'date',
            'quantity' => 'decimal:2',
        ];
    }

    /**
     * Activity type labels in Bahasa Indonesia.
     */
    public const TYPE_LABELS = [
        'watering'      => 'Penyiraman',
        'fertilizing'   => 'Pemupukan',
        'spraying'      => 'Penyemprotan',
        'weeding'       => 'Penyiangan',
        'pruning'       => 'Pemangkasan',
        'harvesting'    => 'Panen',
        'seeding'       => 'Persemaian',
        'transplanting' => 'Pindah Tanam',
        'soil_prep'     => 'Olah Tanah',
        'other'         => 'Lainnya',
    ];

    /**
     * Activity type icons (emoji).
     */
    public const TYPE_ICONS = [
        'watering'      => '💧',
        'fertilizing'   => '🧪',
        'spraying'      => '🔫',
        'weeding'       => '🌿',
        'pruning'       => '✂️',
        'harvesting'    => '🌾',
        'seeding'       => '🌱',
        'transplanting' => '🪴',
        'soil_prep'     => '🚜',
        'other'         => '📝',
    ];

    /**
     * Get the planting this activity belongs to.
     */
    public function planting(): BelongsTo
    {
        return $this->belongsTo(Planting::class);
    }

    /**
     * Get the user who logged this activity.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the Indonesian label for this activity type.
     */
    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->type] ?? $this->type;
    }

    /**
     * Get the icon for this activity type.
     */
    public function getTypeIconAttribute(): string
    {
        return self::TYPE_ICONS[$this->type] ?? '📝';
    }

    /**
     * Get the full URL for the photo.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo_path) {
            return null;
        }

        return asset('storage/' . $this->photo_path);
    }

    /**
     * Get formatted quantity with unit.
     */
    public function getFormattedQuantityAttribute(): ?string
    {
        if (!$this->quantity) {
            return null;
        }

        return rtrim(rtrim(number_format($this->quantity, 2, ',', '.'), '0'), ',') . ' ' . ($this->quantity_unit ?? '');
    }

    /**
     * Scope: activities for a specific planting.
     */
    public function scopeForPlanting($query, int $plantingId)
    {
        return $query->where('planting_id', $plantingId);
    }

    /**
     * Scope: recent activities within N days.
     */
    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('activity_date', '>=', now()->subDays($days));
    }
}
