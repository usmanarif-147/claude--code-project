<?php

namespace App\Http\Controllers;

use App\Services\AiChatbotService;
use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function sendMessage(
        Request $request,
        ChatbotService $chatbotService,
        AiChatbotService $aiChatbotService
    ): JsonResponse {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'visitor_uuid' => 'required|string|uuid',
        ]);

        $message = strip_tags($validated['message']);

        $conversation = $chatbotService->getOrCreateConversation(
            $validated['visitor_uuid'],
            $request->ip(),
            $request->userAgent()
        );

        $chatbotService->addMessage($conversation, 'user', $message);

        try {
            $result = $aiChatbotService->chat($conversation, $message);

            $chatbotService->addMessage(
                $conversation,
                'assistant',
                $result['content'],
                $result['tokens'],
                $result['provider']
            );

            return response()->json([
                'reply' => $result['content'],
                'conversation_id' => $conversation->id,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Sorry, I am unable to respond right now. Please try again later.',
            ], 500);
        }
    }
}
