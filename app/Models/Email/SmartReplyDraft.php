<?php

namespace App\Models\Email;

use App\Models\EmailTemplate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmartReplyDraft extends Model
{
    protected $fillable = [
        'email_id',
        'template_id',
        'tone',
        'prompt_context',
        'generated_body',
        'edited_body',
        'status',
        'ai_model_used',
        'generated_at',
        'copied_at',
    ];

    protected function casts(): array
    {
        return [
            'generated_at' => 'datetime',
            'copied_at' => 'datetime',
        ];
    }

    public function email(): BelongsTo
    {
        return $this->belongsTo(Email::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(EmailTemplate::class, 'template_id');
    }

    public function getFinalBodyAttribute(): string
    {
        return $this->edited_body ?? $this->generated_body;
    }
}
