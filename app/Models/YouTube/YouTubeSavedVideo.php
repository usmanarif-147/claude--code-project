<?php

namespace App\Models\YouTube;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YouTubeSavedVideo extends Model
{
    protected $table = 'youtube_saved_videos';

    protected $fillable = [
        'user_id',
        'video_id',
        'title',
        'thumbnail_url',
        'channel_title',
        'channel_id',
        'published_at',
        'duration',
        'view_count',
        'notes',
        'saved_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'saved_at' => 'datetime',
            'view_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderByDesc('saved_at');
    }
}
