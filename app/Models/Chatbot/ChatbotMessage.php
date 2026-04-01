<?php

namespace App\Models\Chatbot;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotMessage extends Model
{
    protected $fillable = [
        'chatbot_conversation_id',
        'role',
        'content',
        'tokens_used',
        'ai_provider',
    ];

    protected function casts(): array
    {
        return [
            'tokens_used' => 'integer',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatbotConversation::class, 'chatbot_conversation_id');
    }
}
