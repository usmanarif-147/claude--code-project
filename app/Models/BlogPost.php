<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BlogPost extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'cover_image',
        'status',
        'published_at',
        'meta_title',
        'meta_description',
        'reading_time_minutes',
        'view_count',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'reading_time_minutes' => 'integer',
            'view_count' => 'integer',
        ];
    }

    public function tags(): HasMany
    {
        return $this->hasMany(BlogPostTag::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function calculateReadingTime(): int
    {
        return (int) ceil(str_word_count(strip_tags($this->content)) / 200);
    }
}
