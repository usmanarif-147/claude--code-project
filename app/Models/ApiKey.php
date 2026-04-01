<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiKey extends Model
{
    public const PROVIDER_GMAIL = 'gmail';

    public const PROVIDER_CLAUDE = 'claude';

    public const PROVIDER_OPENAI = 'openai';

    public const PROVIDER_JSEARCH = 'jsearch';

    public const PROVIDER_ADZUNA = 'adzuna';

    public const PROVIDER_SERPAPI = 'serpapi';

    public const PROVIDER_YOUTUBE = 'youtube';

    public const PROVIDER_GEMINI = 'gemini';

    public const PROVIDER_GROQ = 'groq';

    public const ALL_PROVIDERS = [
        self::PROVIDER_GMAIL,
        self::PROVIDER_CLAUDE,
        self::PROVIDER_OPENAI,
        self::PROVIDER_JSEARCH,
        self::PROVIDER_ADZUNA,
        self::PROVIDER_SERPAPI,
        self::PROVIDER_YOUTUBE,
        self::PROVIDER_GEMINI,
        self::PROVIDER_GROQ,
    ];

    protected $fillable = [
        'user_id',
        'provider',
        'key_value',
        'extra_data',
        'is_connected',
        'test_status',
        'last_tested_at',
    ];

    protected $hidden = [
        'key_value',
        'extra_data',
    ];

    protected function casts(): array
    {
        return [
            'key_value' => 'encrypted',
            'extra_data' => 'encrypted:array',
            'is_connected' => 'boolean',
            'last_tested_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForProvider(Builder $query, string $provider): Builder
    {
        return $query->where('provider', $provider);
    }

    public function scopeConnected(Builder $query): Builder
    {
        return $query->where('is_connected', true);
    }
}
