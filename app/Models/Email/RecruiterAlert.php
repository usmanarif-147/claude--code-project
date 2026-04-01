<?php

namespace App\Models\Email;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecruiterAlert extends Model
{
    protected $fillable = [
        'email_id',
        'alert_type',
        'confidence_score',
        'detected_signals',
        'is_read',
        'is_dismissed',
        'urgency',
        'notified_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'is_dismissed' => 'boolean',
            'detected_signals' => 'array',
            'notified_at' => 'datetime',
            'confidence_score' => 'decimal:2',
        ];
    }

    public function email(): BelongsTo
    {
        return $this->belongsTo(Email::class);
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    public function scopeUndismissed(Builder $query): Builder
    {
        return $query->where('is_dismissed', false);
    }

    public function scopeUrgent(Builder $query): Builder
    {
        return $query->where('urgency', 'urgent');
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('alert_type', $type);
    }
}
