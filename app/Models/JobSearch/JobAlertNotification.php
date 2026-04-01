<?php

namespace App\Models\JobSearch;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobAlertNotification extends Model
{
    public const VIA_DASHBOARD = 'dashboard';

    public const VIA_EMAIL = 'email';

    public const VIA_BOTH = 'both';

    protected $fillable = [
        'user_id',
        'job_listing_id',
        'match_score',
        'match_summary',
        'is_read',
        'notified_via',
        'notified_at',
    ];

    protected function casts(): array
    {
        return [
            'match_score' => 'integer',
            'is_read' => 'boolean',
            'notified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobListing(): BelongsTo
    {
        return $this->belongsTo(JobListing::class);
    }

    public function jobAlert(): BelongsTo
    {
        return $this->belongsTo(JobAlert::class, 'user_id', 'user_id');
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    public function scopeRead(Builder $query): Builder
    {
        return $query->where('is_read', true);
    }
}
