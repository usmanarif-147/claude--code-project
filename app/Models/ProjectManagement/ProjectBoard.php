<?php

namespace App\Models\ProjectManagement;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectBoard extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'sort_order',
        'share_token',
        'is_shared',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_shared' => 'boolean',
        ];
    }

    // --- Accessors ---

    public function getShareUrlAttribute(): ?string
    {
        return $this->share_token ? url("/shared/project/{$this->share_token}") : null;
    }

    // --- Relationships ---

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function columns(): HasMany
    {
        return $this->hasMany(ProjectBoardColumn::class, 'board_id')->orderBy('sort_order');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class, 'board_id');
    }

    public function diagrams(): HasMany
    {
        return $this->hasMany(Diagram::class, 'board_id');
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(ProjectRequirement::class, 'board_id');
    }

    // --- Scopes ---

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeShared(Builder $query): Builder
    {
        return $query->where('is_shared', true);
    }
}
