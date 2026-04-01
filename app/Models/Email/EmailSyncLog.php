<?php

namespace App\Models\Email;

use Illuminate\Database\Eloquent\Model;

class EmailSyncLog extends Model
{
    protected $fillable = [
        'synced_at',
        'emails_fetched',
        'emails_skipped',
        'status',
        'error_message',
        'duration_ms',
    ];

    protected function casts(): array
    {
        return [
            'synced_at' => 'datetime',
        ];
    }
}
