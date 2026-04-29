<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Crop extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'variety',
        'default_hst',
        'estimated_yield_per_plant_kg',
        'description',
        'icon',
    ];

    /**
     * Get all plantings for this crop.
     */
    public function plantings(): HasMany
    {
        return $this->hasMany(Planting::class);
    }

    /**
     * Get the display name with variety.
     */
    public function getFullNameAttribute(): string
    {
        return $this->variety
            ? "{$this->name} ({$this->variety})"
            : $this->name;
    }
}
