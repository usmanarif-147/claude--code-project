<?php

namespace App\Models\Email;

use Illuminate\Database\Eloquent\Model;

class RecruiterAlertSetting extends Model
{
    protected $fillable = [
        'is_enabled',
        'alert_on_recruiter',
        'alert_on_hiring_manager',
        'alert_on_freelance_client',
        'min_confidence_score',
        'browser_notification',
        'email_forward',
        'forward_email',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'alert_on_recruiter' => 'boolean',
            'alert_on_hiring_manager' => 'boolean',
            'alert_on_freelance_client' => 'boolean',
            'min_confidence_score' => 'integer',
            'browser_notification' => 'boolean',
            'email_forward' => 'boolean',
        ];
    }

    public static function getSettings(): self
    {
        return self::firstOrCreate([], [
            'is_enabled' => true,
            'alert_on_recruiter' => true,
            'alert_on_hiring_manager' => true,
            'alert_on_freelance_client' => true,
            'min_confidence_score' => 70,
            'browser_notification' => false,
            'email_forward' => false,
            'forward_email' => null,
        ]);
    }
}
