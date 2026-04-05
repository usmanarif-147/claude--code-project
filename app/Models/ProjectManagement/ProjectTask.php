<?php

namespace App\Models\ProjectManagement;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class ProjectTask extends Model
{
    protected $fillable = [
        'board_id',
        'column_id',
        'user_id',
        'title',
        'description',
        'priority',
        'target_date',
        'tags',
        'position',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'target_date' => 'date',
            'tags' => 'array',
            'position' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    // --- Relationships ---

    public function board(): BelongsTo
    {
        return $this->belongsTo(ProjectBoard::class, 'board_id');
    }

    public function column(): BelongsTo
    {
        return $this->belongsTo(ProjectBoardColumn::class, 'column_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProjectTaskImage::class)->orderBy('sort_order');
    }

    // --- Scopes ---

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForBoard(Builder $query, int $boardId): Builder
    {
        return $query->where('board_id', $boardId);
    }

    public function scopeForColumn(Builder $query, int $columnId): Builder
    {
        return $query->where('column_id', $columnId);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position');
    }

    public function scopeByPriority(Builder $query): Builder
    {
        return $query->orderByRaw("CASE priority WHEN 'urgent' THEN 0 WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 END");
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereNotNull('completed_at');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('completed_at');
    }

    public function scopeForDate(Builder $query, Carbon $date): Builder
    {
        return $query->whereDate('target_date', $date);
    }

    public function scopeForDateRange(Builder $query, Carbon $start, Carbon $end): Builder
    {
        return $query->whereBetween('target_date', [$start->startOfDay(), $end->endOfDay()]);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }
}
