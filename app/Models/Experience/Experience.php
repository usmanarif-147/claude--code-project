<?php

namespace App\Models\Experience;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Experience extends Model
{
    protected $fillable = [
        'type',
        'role',
        'company',
        'start_date',
        'end_date',
        'is_current',
        'description',
        'degree',
        'field_of_study',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function responsibilities(): HasMany
    {
        return $this->hasMany(ExperienceResponsibility::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }

    public function scopeWork(Builder $query): Builder
    {
        return $query->where('type', 'work');
    }

    public function scopeEducation(Builder $query): Builder
    {
        return $query->where('type', 'education');
    }
}
