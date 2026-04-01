<?php

namespace App\Models\YouTube;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YouTubeWeeklySnapshot extends Model
{
    protected $table = 'youtube_weekly_snapshots';

    protected $fillable = [
        'user_id',
        'week_start',
        'subscriber_count',
        'view_count',
        'video_count',
        'estimated_watch_hours',
    ];

    protected function casts(): array
    {
        return [
            'week_start' => 'date',
            'subscriber_count' => 'integer',
            'view_count' => 'integer',
            'video_count' => 'integer',
            'estimated_watch_hours' => 'decimal:2',
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
