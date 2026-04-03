<?php

namespace App\Models\YouTube;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class YouTubeSubscription extends Model
{
    protected $table = 'youtube_subscriptions';

    protected $fillable = [
        'user_id',
        'channel_id',
        'channel_title',
        'channel_thumbnail_url',
        'channel_description',
        'subscriber_count',
        'video_count',
        'last_video_at',
        'subscribed_at',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'subscriber_count' => 'integer',
            'video_count' => 'integer',
            'last_video_at' => 'datetime',
            'subscribed_at' => 'datetime',
            'synced_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function videos(): HasMany
    {
        return $this->hasMany(YouTubeSubscriptionVideo::class, 'subscription_id');
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeStale(Builder $query, int $hours = 6): Builder
    {
        return $query->where(function (Builder $q) use ($hours) {
            $q->whereNull('synced_at')
                ->orWhere('synced_at', '<', now()->subHours($hours));
        });
    }
}
