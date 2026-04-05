<?php

namespace App\Models\ProjectManagement;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class WeeklyReview extends Model
{
    protected $fillable = [
        'user_id',
        'week_start',
        'week_end',
        'total_planned',
        'total_completed',
        'total_carried_over',
        'category_breakdown',
        'ai_summary',
        'ai_focus_areas',
        'ai_generated_at',
    ];

    protected function casts(): array
    {
        return [
            'week_start' => 'date',
            'week_end' => 'date',
            'category_breakdown' => 'array',
            'ai_focus_areas' => 'array',
            'ai_generated_at' => 'datetime',
            'total_planned' => 'integer',
            'total_completed' => 'integer',
            'total_carried_over' => 'integer',
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

    public function scopeForWeek(Builder $query, Carbon $weekStart): Builder
    {
        return $query->where('week_start', $weekStart->toDateString());
    }

    public function getCompletionPercentageAttribute(): int
    {
        if ($this->total_planned === 0) {
            return 0;
        }

        return (int) floor(($this->total_completed / $this->total_planned) * 100);
    }

    public function getHasAiSummaryAttribute(): bool
    {
        return $this->ai_summary !== null;
    }
}
