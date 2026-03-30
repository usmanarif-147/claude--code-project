<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ResumeDownload extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'ip_address',
        'country',
        'referrer',
        'template_used',
        'downloaded_at',
    ];

    protected function casts(): array
    {
        return [
            'downloaded_at' => 'datetime',
        ];
    }

    public function scopeInPeriod(Builder $query, string $period): Builder
    {
        return match ($period) {
            '7d' => $query->where('downloaded_at', '>=', now()->subDays(7)),
            '30d' => $query->where('downloaded_at', '>=', now()->subDays(30)),
            '90d' => $query->where('downloaded_at', '>=', now()->subDays(90)),
            default => $query,
        };
    }
}
