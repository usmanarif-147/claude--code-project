<?php

namespace App\Models\Email;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Email extends Model
{
    protected $fillable = [
        'gmail_id',
        'thread_id',
        'from_email',
        'from_name',
        'to_email',
        'subject',
        'snippet',
        'body_preview',
        'received_at',
        'is_read',
        'is_starred',
        'is_important',
        'labels',
        'category',
        'category_id',
        'ai_summary',
        'gmail_link',
        'raw_payload',
    ];

    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
            'is_read' => 'boolean',
            'is_starred' => 'boolean',
            'is_important' => 'boolean',
            'labels' => 'array',
            'raw_payload' => 'array',
        ];
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }

    public function scopeImportant(Builder $query): Builder
    {
        return $query->where('is_important', true);
    }

    public function scopeStarred(Builder $query): Builder
    {
        return $query->where('is_starred', true);
    }

    public function scopeCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeReceivedBetween(Builder $query, $start, $end): Builder
    {
        return $query->whereBetween('received_at', [$start, $end]);
    }

    public function emailCategory(): BelongsTo
    {
        return $this->belongsTo(EmailCategory::class, 'category_id');
    }

    public function recruiterAlert(): HasOne
    {
        return $this->hasOne(RecruiterAlert::class);
    }
}
