<?php

namespace App\Models\Email;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailCategoryCorrection extends Model
{
    protected $fillable = [
        'email_id',
        'from_category_id',
        'to_category_id',
        'corrected_at',
    ];

    protected function casts(): array
    {
        return [
            'corrected_at' => 'datetime',
        ];
    }

    public function email(): BelongsTo
    {
        return $this->belongsTo(Email::class);
    }

    public function fromCategory(): BelongsTo
    {
        return $this->belongsTo(EmailCategory::class, 'from_category_id');
    }

    public function toCategory(): BelongsTo
    {
        return $this->belongsTo(EmailCategory::class, 'to_category_id');
    }
}
