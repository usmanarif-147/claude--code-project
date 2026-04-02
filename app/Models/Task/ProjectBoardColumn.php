<?php

namespace App\Models\Task;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectBoardColumn extends Model
{
    protected $fillable = [
        'board_id',
        'name',
        'color',
        'sort_order',
        'is_completed_column',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_completed_column' => 'boolean',
        ];
    }

    // --- Relationships ---

    public function board(): BelongsTo
    {
        return $this->belongsTo(ProjectBoard::class, 'board_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class, 'column_id')->orderBy('position');
    }

    // --- Scopes ---

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }
}
