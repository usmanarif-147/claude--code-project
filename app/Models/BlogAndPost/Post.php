<?php

namespace App\Models\BlogAndPost;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    public const TYPE_TEXT = 'text';

    public const TYPE_IMAGE = 'image';

    public const TYPE_VIDEO = 'video';

    public const TYPE_ARTICLE = 'article';

    protected $fillable = [
        'title',
        'type',
        'caption',
        'hashtags',
        'meta',
        'status',
        'scheduled_at',
        'linkedin_post_id',
        'linkedin_post_url',
        'linkedin_error',
        'linkedin_attempts',
        'linkedin_last_attempted_at',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
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

    public function scopePublishing(Builder $query): Builder
    {
        return $query->where('status', 'publishing');
    }

    public function scopePosted(Builder $query): Builder
    {
        return $query->where('status', 'posted');
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_TEXT => 'Text',
            self::TYPE_IMAGE => 'Text + Image',
            self::TYPE_VIDEO => 'Text + Video',
            self::TYPE_ARTICLE => 'Article share',
            default => ucfirst((string) $this->type),
        };
    }
}
