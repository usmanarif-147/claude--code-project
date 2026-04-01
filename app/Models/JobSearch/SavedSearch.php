<?php

namespace App\Models\JobSearch;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedSearch extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'preferred_titles',
        'preferred_tech',
        'location_type',
        'location_value',
        'min_salary',
        'salary_currency',
        'experience_level',
        'enabled_platforms',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'preferred_titles' => 'array',
            'preferred_tech' => 'array',
            'enabled_platforms' => 'array',
            'min_salary' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
