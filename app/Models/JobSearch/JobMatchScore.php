<?php

namespace App\Models\JobSearch;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobMatchScore extends Model
{
    public const PROVIDER_CLAUDE = 'claude';

    public const PROVIDER_OPENAI = 'openai';

    protected $fillable = [
        'user_id',
        'job_listing_id',
        'score',
        'explanation',
        'matched_skills',
        'missing_skills',
        'bonus_factors',
        'ai_provider',
        'ai_model',
        'scored_at',
    ];

    protected function casts(): array
    {
        return [
            'matched_skills' => 'array',
            'missing_skills' => 'array',
            'bonus_factors' => 'array',
            'scored_at' => 'datetime',
            'score' => 'integer',
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

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeMinScore(Builder $query, int $minScore): Builder
    {
        return $query->where('score', '>=', $minScore);
    }

    public function scopeHighMatches(Builder $query): Builder
    {
        return $query->where('score', '>=', 80);
    }
}
