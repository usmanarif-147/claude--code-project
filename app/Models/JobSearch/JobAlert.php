<?php

namespace App\Models\JobSearch;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobAlert extends Model
{
    public const FREQUENCY_INSTANT = 'instant';

    public const FREQUENCY_DAILY = 'daily';

    public const FREQUENCY_WEEKLY = 'weekly';

    public const ALL_FREQUENCIES = [
        self::FREQUENCY_INSTANT,
        self::FREQUENCY_DAILY,
        self::FREQUENCY_WEEKLY,
    ];

    protected $fillable = [
        'user_id',
        'is_enabled',
        'min_score_threshold',
        'frequency',
        'notify_dashboard',
        'notify_email',
        'last_digest_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'min_score_threshold' => 'integer',
            'notify_dashboard' => 'boolean',
            'notify_email' => 'boolean',
            'last_digest_sent_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(JobAlertNotification::class, 'user_id', 'user_id');
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }
}
