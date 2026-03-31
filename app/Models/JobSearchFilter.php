<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobSearchFilter extends Model
{
    public const JSEARCH = 'jsearch';

    public const REMOTEOK = 'remoteok';

    public const REMOTIVE = 'remotive';

    public const ADZUNA = 'adzuna';

    public const ROZEE = 'rozee';

    public const MUSTAKBIL = 'mustakbil';

    public const ALL_PLATFORMS = [
        self::JSEARCH => 'JSearch (Indeed/Glassdoor/LinkedIn)',
        self::REMOTEOK => 'RemoteOK',
        self::REMOTIVE => 'Remotive',
        self::ADZUNA => 'Adzuna',
        self::ROZEE => 'Rozee.pk',
        self::MUSTAKBIL => 'Mustakbil.com',
    ];

    protected $fillable = [
        'user_id',
        'preferred_titles',
        'preferred_tech',
        'location_type',
        'location_value',
        'min_salary',
        'salary_currency',
        'experience_level',
        'enabled_platforms',
    ];

    protected function casts(): array
    {
        return [
            'preferred_titles' => 'array',
            'preferred_tech' => 'array',
            'enabled_platforms' => 'array',
            'min_salary' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
