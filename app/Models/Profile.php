<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'tagline',
        'bio',
        'profile_image',
        'secondary_email',
        'phone',
        'location',
        'linkedin_url',
        'github_url',
        'availability_status',
        'fiverr_url',
        'youtube_url',
        'timezone',
        'language',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
