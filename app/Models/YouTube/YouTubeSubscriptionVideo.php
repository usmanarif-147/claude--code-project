<?php

namespace App\Models\YouTube;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class YouTubeSubscriptionVideo extends Model
{
    protected $table = 'youtube_subscription_videos';

    protected $fillable = [
        'user_id',
        'subscription_id',
        'video_id',
        'title',
        'description',
        'thumbnail_url',
        'channel_title',
        'published_at',
        'duration',
        'view_count',
        'like_count',
        'comment_count',
        'category_id',
        'default_language',
        'tags',
        'is_new',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'view_count' => 'integer',
            'like_count' => 'integer',
            'comment_count' => 'integer',
            'tags' => 'array',
            'is_new' => 'boolean',
            'category_id' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(YouTubeSubscription::class, 'subscription_id');
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderByDesc('published_at');
    }

    public function scopeNew(Builder $query): Builder
    {
        return $query->where('is_new', true);
    }

    public function scopeForChannel(Builder $query, int $subscriptionId): Builder
    {
        return $query->where('subscription_id', $subscriptionId);
    }

    public function scopePublishedBetween(Builder $query, $from, $to): Builder
    {
        return $query->whereBetween('published_at', [$from, $to]);
    }
}
