<?php

namespace App\Services;

use App\Models\Chatbot\ChatbotConversation;
use App\Models\Chatbot\ChatbotMessage;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ChatbotService
{
    public function getOrCreateConversation(string $visitorUuid, ?string $ip, ?string $userAgent): ChatbotConversation
    {
        $conversation = ChatbotConversation::where('visitor_uuid', $visitorUuid)
            ->latest('last_message_at')
            ->first();

        if (! $conversation) {
            $conversation = ChatbotConversation::create([
                'visitor_uuid' => $visitorUuid,
                'visitor_ip' => $ip,
                'visitor_user_agent' => $userAgent,
            ]);
        }

        return $conversation;
    }

    public function addMessage(
        ChatbotConversation $conversation,
        string $role,
        string $content,
        ?int $tokens = null,
        ?string $provider = null
    ): ChatbotMessage {
        $message = $conversation->messages()->create([
            'role' => $role,
            'content' => $content,
            'tokens_used' => $tokens,
            'ai_provider' => $provider,
        ]);

        $updateData = [
            'message_count' => $conversation->messages()->count(),
            'last_message_at' => now(),
        ];

        if ($role === 'user' && ! $conversation->title) {
            $updateData['title'] = $this->generateTitle($content);
        }

        $conversation->update($updateData);

        return $message;
    }

    public function getConversations(?string $search, int $perPage): LengthAwarePaginator
    {
        return ChatbotConversation::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('visitor_uuid', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('last_message_at')
            ->paginate($perPage);
    }

    public function getConversationWithMessages(int $conversationId): ChatbotConversation
    {
        return ChatbotConversation::with(['messages' => function ($query) {
            $query->orderBy('created_at', 'asc');
        }])->findOrFail($conversationId);
    }

    public function generateTitle(string $firstMessage): string
    {
        $title = strip_tags($firstMessage);

        if (mb_strlen($title) > 100) {
            return mb_substr($title, 0, 100).'...';
        }

        return $title;
    }
}
