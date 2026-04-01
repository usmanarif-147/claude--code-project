<?php

namespace App\Models\JobSearch;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoverLetter extends Model
{
    public const PROVIDER_CLAUDE = 'claude';

    public const PROVIDER_OPENAI = 'openai';

    protected $fillable = [
        'user_id',
        'job_listing_id',
        'job_title',
        'company_name',
        'job_description_snippet',
        'content',
        'ai_provider',
        'ai_model',
        'prompt_tokens',
        'completion_tokens',
        'is_edited',
    ];

    protected function casts(): array
    {
        return [
            'is_edited' => 'boolean',
            'prompt_tokens' => 'integer',
            'completion_tokens' => 'integer',
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

    public function scopeForJob(Builder $query, int $jobListingId): Builder
    {
        return $query->where('job_listing_id', $jobListingId);
    }
}
