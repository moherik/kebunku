<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plantings()
    {
        return $this->hasMany(Planting::class);
    }
}
