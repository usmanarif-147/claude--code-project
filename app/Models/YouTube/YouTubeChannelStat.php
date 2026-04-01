<?php

namespace App\Models\YouTube;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YouTubeChannelStat extends Model
{
    protected $table = 'youtube_channel_stats';

    protected $fillable = [
        'user_id',
        'channel_id',
        'channel_title',
        'channel_thumbnail_url',
        'subscriber_count',
        'total_view_count',
        'video_count',
        'estimated_watch_hours',
        'monthly_revenue',
        'fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'subscriber_count' => 'integer',
            'total_view_count' => 'integer',
            'video_count' => 'integer',
            'estimated_watch_hours' => 'decimal:2',
            'monthly_revenue' => 'decimal:2',
            'fetched_at' => 'datetime',
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
}
