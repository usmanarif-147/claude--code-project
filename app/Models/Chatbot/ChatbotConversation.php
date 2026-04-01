<?php

namespace App\Models\Chatbot;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatbotConversation extends Model
{
    protected $fillable = [
        'visitor_uuid',
        'visitor_ip',
        'visitor_user_agent',
        'title',
        'message_count',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'message_count' => 'integer',
        ];
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatbotMessage::class);
    }
}
