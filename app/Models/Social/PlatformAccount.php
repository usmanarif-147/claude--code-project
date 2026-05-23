<?php

namespace App\Models\Social;

use Illuminate\Database\Eloquent\Model;

class PlatformAccount extends Model
{
    protected $fillable = [
        'platform',
        'account_label',
        'remote_account_id',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'scope',
        'last_health_check_at',
    ];

    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'token_expires_at' => 'datetime',
            'last_health_check_at' => 'datetime',
        ];
    }
}
