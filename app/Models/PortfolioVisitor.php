<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PortfolioVisitor extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'ip_address',
        'country',
        'city',
        'page_visited',
        'referrer',
        'user_agent',
        'device_type',
        'visited_at',
    ];

    protected function casts(): array
    {
        return [
            'visited_at' => 'datetime',
        ];
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('visited_at', today());
    }

    public function scopeThisWeek(Builder $query): Builder
    {
        return $query->where('visited_at', '>=', now()->startOfWeek());
    }

    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->where('visited_at', '>=', now()->startOfMonth());
    }

    public function scopeInPeriod(Builder $query, string $period): Builder
    {
        return match ($period) {
            '7d' => $query->where('visited_at', '>=', now()->subDays(7)),
            '30d' => $query->where('visited_at', '>=', now()->subDays(30)),
            '90d' => $query->where('visited_at', '>=', now()->subDays(90)),
            default => $query,
        };
    }
}
