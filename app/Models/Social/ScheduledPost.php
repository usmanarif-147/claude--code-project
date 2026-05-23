<?php

namespace App\Models\Social;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ScheduledPost extends Model
{
    protected $fillable = [
        'title',
        'caption',
        'hashtags',
        'cover_page',
        'content_pages',
        'final_page',
        'template_slug',
        'scheduled_at',
        'status',
        'rendered_pdf_path',
        'linkedin_post_id',
        'linkedin_post_url',
        'linkedin_error',
        'linkedin_attempts',
        'linkedin_last_attempted_at',
    ];

    protected function casts(): array
    {
        return [
            'content_pages' => 'array',
            'scheduled_at' => 'datetime',
            'linkedin_last_attempted_at' => 'datetime',
            'linkedin_attempts' => 'integer',
        ];
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', 'scheduled');
    }

    public function scopePosted(Builder $query): Builder
    {
        return $query->where('status', 'posted');
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }
}
