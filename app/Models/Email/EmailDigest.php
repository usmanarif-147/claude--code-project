<?php

namespace App\Models\Email;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EmailDigest extends Model
{
    protected $fillable = [
        'digest_date',
        'period_start',
        'period_end',
        'total_emails',
        'unread_count',
        'summary',
        'categories_breakdown',
        'highlights',
        'ai_model_used',
        'generated_at',
        'status',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'digest_date' => 'date',
            'period_start' => 'datetime',
            'period_end' => 'datetime',
            'categories_breakdown' => 'array',
            'highlights' => 'array',
            'generated_at' => 'datetime',
        ];
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeForDate(Builder $query, $date): Builder
    {
        return $query->where('digest_date', $date);
    }
}
