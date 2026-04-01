<?php

namespace App\Models\JobSearch;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JobListing extends Model
{
    public const PLATFORM_JSEARCH = 'jsearch';

    public const PLATFORM_REMOTEOK = 'remoteok';

    public const PLATFORM_REMOTIVE = 'remotive';

    public const PLATFORM_ADZUNA = 'adzuna';

    public const PLATFORM_ROZEE = 'rozee';

    public const PLATFORM_MUSTAKBIL = 'mustakbil';

    public const STATUS_INTERESTED = 'interested';

    public const STATUS_NOT_RELEVANT = 'not_relevant';

    public const ALL_PLATFORMS = [
        self::PLATFORM_JSEARCH => 'JSearch (Indeed/Glassdoor/LinkedIn)',
        self::PLATFORM_REMOTEOK => 'RemoteOK',
        self::PLATFORM_REMOTIVE => 'Remotive',
        self::PLATFORM_ADZUNA => 'Adzuna',
        self::PLATFORM_ROZEE => 'Rozee.pk',
        self::PLATFORM_MUSTAKBIL => 'Mustakbil.com',
    ];

    protected $fillable = [
        'user_id',
        'external_id',
        'source_platform',
        'title',
        'company_name',
        'company_logo_url',
        'description',
        'location',
        'location_type',
        'country',
        'salary_min',
        'salary_max',
        'salary_currency',
        'salary_text',
        'tech_stack',
        'job_url',
        'posted_at',
        'fetched_at',
        'user_status',
        'is_hidden',
        'duplicate_group_id',
        'is_duplicate_primary',
    ];

    protected function casts(): array
    {
        return [
            'tech_stack' => 'array',
            'posted_at' => 'datetime',
            'fetched_at' => 'datetime',
            'is_hidden' => 'boolean',
            'is_duplicate_primary' => 'boolean',
            'salary_min' => 'integer',
            'salary_max' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function matchScore(): HasOne
    {
        return $this->hasOne(JobMatchScore::class);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('job_listings.user_id', $userId);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('job_listings.is_hidden', false)->where('job_listings.is_duplicate_primary', true);
    }

    public function scopeByPlatform(Builder $query, string $platform): Builder
    {
        return $query->where('source_platform', $platform);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('user_status', $status);
    }

    public function scopeByLocationType(Builder $query, string $type): Builder
    {
        return $query->where('location_type', $type);
    }

    public function scopeByCountry(Builder $query, string $country): Builder
    {
        return $query->where('country', $country);
    }
}
