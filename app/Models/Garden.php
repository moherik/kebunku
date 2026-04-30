<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Garden extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'address',
        'description',
        'photo_path',
        'cover_path',
        'instagram_link',
        'facebook_link',
        'youtube_link',
        'whatsapp_number',
        'latitude',
        'longitude',
        'bmkg_adm4_code',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plantings(): HasMany
    {
        return $this->hasMany(Planting::class);
    }

    /**
     * Get all activity logs through plantings.
     */
    public function activityLogs(): HasManyThrough
    {
        return $this->hasManyThrough(ActivityLog::class, Planting::class);
    }

    /**
     * Check if this garden has location data for weather.
     */
    public function hasWeatherLocation(): bool
    {
        return !empty($this->bmkg_adm4_code);
    }
}
