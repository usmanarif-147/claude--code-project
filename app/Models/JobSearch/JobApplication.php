<?php

namespace App\Models\JobSearch;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplication extends Model
{
    public const STATUS_SAVED = 'saved';

    public const STATUS_APPLIED = 'applied';

    public const STATUS_INTERVIEW = 'interview';

    public const STATUS_OFFER = 'offer';

    public const STATUS_REJECTED = 'rejected';

    public const ALL_STATUSES = [
        self::STATUS_SAVED => 'Saved',
        self::STATUS_APPLIED => 'Applied',
        self::STATUS_INTERVIEW => 'Interview',
        self::STATUS_OFFER => 'Offer',
        self::STATUS_REJECTED => 'Rejected',
    ];

    protected $fillable = [
        'job_listing_id',
        'company',
        'position',
        'status',
        'applied_date',
        'notes',
        'salary_offered',
        'url',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'applied_date' => 'date',
            'sort_order' => 'integer',
        ];
    }

    public function jobListing(): BelongsTo
    {
        return $this->belongsTo(JobListing::class);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }
}
