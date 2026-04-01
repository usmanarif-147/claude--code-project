<?php

namespace App\Models\YouTube;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YouTubeVideo extends Model
{
    protected $table = 'youtube_videos';

    protected $fillable = [
        'user_id',
        'video_id',
        'title',
        'thumbnail_url',
        'published_at',
        'view_count',
        'like_count',
        'comment_count',
        'duration',
    ];

    protected function casts(): array
    {
        return [
            'view_count' => 'integer',
            'like_count' => 'integer',
            'comment_count' => 'integer',
            'published_at' => 'datetime',
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
        return $query->orderByDesc('published_at');
    }
}
