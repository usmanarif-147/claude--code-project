<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailTemplate extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'category',
        'subject',
        'body',
        'is_favorite',
        'sort_order',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'is_favorite' => 'boolean',
            'last_used_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
