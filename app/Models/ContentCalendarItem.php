<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class ContentCalendarItem extends Model
{
    protected $fillable = [
        'title',
        'type',
        'description',
        'planned_date',
        'status',
        'published_at',
        'color',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'planned_date' => 'date',
            'published_at' => 'datetime',
            'sort_order' => 'integer',
        ];
    }

    public function scopePlanned(Builder $query): Builder
    {
        return $query->where('status', 'planned');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeInDateRange(Builder $query, Carbon $start, Carbon $end): Builder
    {
        return $query->whereBetween('planned_date', [$start, $end]);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function getIsPublishedAttribute(): bool
    {
        return $this->status === 'published';
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'video' => 'Video',
            'blog' => 'Blog',
            default => ucfirst($this->type),
        };
    }
}
