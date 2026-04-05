<?php

namespace App\Services;

use App\Models\AiChat\AiChatConversation;
use App\Models\AiChat\AiChatMessage;
use App\Models\ApiKey;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class AiChatService
{
    public function getConfiguredProvider(int $userId): ?array
    {
        // Prefer Gemini, fallback to Groq
        $geminiKey = ApiKey::query()
            ->forUser($userId)
            ->forProvider(ApiKey::PROVIDER_GEMINI)
            ->connected()
            ->first();

        if ($geminiKey) {
            return ['provider' => 'gemini', 'apiKey' => $geminiKey];
        }

        $groqKey = ApiKey::query()
            ->forUser($userId)
            ->forProvider(ApiKey::PROVIDER_GROQ)
            ->connected()
            ->first();

        if ($groqKey) {
            return ['provider' => 'groq', 'apiKey' => $groqKey];
        }

        return null;
    }

    public function getConversations(int $userId): Collection
    {
        return AiChatConversation::query()
            ->forUser($userId)
            ->recent()
            ->with('latestMessage')
            ->get();
    }

    public function searchConversations(int $userId, string $query): Collection
    {
        return AiChatConversation::query()
            ->forUser($userId)
            ->where('title', 'like', '%'.$query.'%')
            ->recent()
            ->with('latestMessage')
            ->get();
    }

    public function createConversation(int $userId, ?string $title = null): AiChatConversation
    {
        return AiChatConversation::create([
            'user_id' => $userId,
            'title' => $title ?? 'New Conversation',
        ]);
    }

    public function renameConversation(AiChatConversation $conversation, string $title): AiChatConversation
    {
        $conversation->update(['title' => $title]);

        return $conversation;
    }

    public function deleteConversation(AiChatConversation $conversation): void
    {
        $conversation->delete();
    }

    public function clearMessages(AiChatConversation $conversation): void
    {
        $conversation->messages()->delete();
        $conversation->update(['last_message_at' => null]);
    }

    public function getMessages(AiChatConversation $conversation, int $limit = 50): Collection
    {
        return $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();
    }

    public function sendMessage(AiChatConversation $conversation, string $userMessage, int $userId): AiChatMessage
    {
        // Save user message
        $conversation->messages()->create([
            'role' => AiChatMessage::ROLE_USER,
            'content' => $userMessage,
        ]);

        $conversation->update(['last_message_at' => now()]);

        // Gather context and build prompt
        $dashboardContext = $this->gatherDashboardContext($userId);
        $recentMessages = $conversation->messages()
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->reverse()
            ->values();

        $prompt = $this->buildPrompt($userMessage, $dashboardContext, $recentMessages);

        // Try AI providers
        $providerConfig = $this->getConfiguredProvider($userId);
        $responseText = '';
        $provider = null;
        $tokensUsed = null;
        $contextSummary = $dashboardContext;

        if ($providerConfig) {
            try {
                $conversationHistory = $this->buildConversationHistory($recentMessages);

                if ($providerConfig['provider'] === 'gemini') {
                    $result = $this->callGeminiApi(
                        $providerConfig['apiKey']->key_value,
                        $prompt,
                        $conversationHistory
                    );
                    $provider = 'gemini';
                } else {
                    $result = $this->callGroqApi(
                        $providerConfig['apiKey']->key_value,
                        $prompt,
                        $conversationHistory
                    );
                    $provider = 'groq';
                }

                $responseText = $this->parseResponse($result);
            } catch (\Throwable $e) {
                // Try fallback provider
                try {
                    $fallbackConfig = $this->getFallbackProvider($userId, $providerConfig['provider']);

                    if ($fallbackConfig) {
                        $conversationHistory = $this->buildConversationHistory($recentMessages);

                        if ($fallbackConfig['provider'] === 'gemini') {
                            $result = $this->callGeminiApi(
                                $fallbackConfig['apiKey']->key_value,
                                $prompt,
                                $conversationHistory
                            );
                            $provider = 'gemini';
                        } else {
                            $result = $this->callGroqApi(
                                $fallbackConfig['apiKey']->key_value,
                                $prompt,
                                $conversationHistory
                            );
                            $provider = 'groq';
                        }

                        $responseText = $this->parseResponse($result);
                    } else {
                        $responseText = "I'm sorry, I couldn't process your request. Please try again.";
                    }
                } catch (\Throwable) {
                    $responseText = "I'm sorry, I couldn't process your request. Please try again.";
                }
            }
        } else {
            $responseText = 'No AI provider is configured. Please go to Settings > API Keys and configure a Gemini or Groq API key.';
        }

        // Save assistant message
        $assistantMessage = $conversation->messages()->create([
            'role' => AiChatMessage::ROLE_ASSISTANT,
            'content' => $responseText,
            'context_summary' => $contextSummary,
            'tokens_used' => $tokensUsed,
            'provider' => $provider,
        ]);

        $conversation->update(['last_message_at' => now()]);

        // Auto-generate title for first message
        if ($conversation->messages()->count() === 2 && $conversation->title === 'New Conversation') {
            $this->autoGenerateTitle($conversation, $userMessage);
        }

        return $assistantMessage;
    }

    public function gatherDashboardContext(int $userId): string
    {
        $context = [];

        // Project tasks context
        try {
            if (Schema::hasTable('project_tasks')) {
                $taskModel = \App\Models\ProjectManagement\ProjectTask::class;
                $pendingCount = $taskModel::query()->where('user_id', $userId)->whereNull('completed_at')->count();
                $overdueCount = $taskModel::query()->where('user_id', $userId)->whereNull('completed_at')
                    ->whereDate('target_date', '<', now()->toDateString())->count();
                $todayCount = $taskModel::query()->where('user_id', $userId)
                    ->whereDate('target_date', now()->toDateString())->count();

                $context[] = "PROJECT TASKS: {$pendingCount} pending tasks, {$overdueCount} overdue, {$todayCount} due today.";

                $recentTasks = $taskModel::query()->where('user_id', $userId)->whereNull('completed_at')
                    ->orderByDesc('created_at')->limit(5)->get(['title', 'priority', 'target_date', 'completed_at']);

                if ($recentTasks->isNotEmpty()) {
                    $taskList = $recentTasks->map(fn ($t) => "- {$t->title} (priority: {$t->priority}, due: ".($t->target_date ?? 'none').')')->implode("\n");
                    $context[] = "Recent pending tasks:\n{$taskList}";
                }
            }
        } catch (\Throwable) {
            $context[] = 'PROJECT TASKS: No task data available.';
        }

        // Emails context
        try {
            if (Schema::hasTable('emails')) {
                $emailModel = \App\Models\Email\Email::class;
                $unreadCount = $emailModel::query()->where('is_read', false)->count();
                $recentEmails = $emailModel::query()->orderByDesc('received_at')->limit(5)->get(['subject', 'sender_name', 'received_at']);

                $context[] = "EMAILS: {$unreadCount} unread emails.";

                if ($recentEmails->isNotEmpty()) {
                    $emailList = $recentEmails->map(fn ($e) => "- From: {$e->sender_name}, Subject: {$e->subject}")->implode("\n");
                    $context[] = "Recent emails:\n{$emailList}";
                }
            }
        } catch (\Throwable) {
            $context[] = 'EMAILS: No email data available.';
        }

        // Job applications context
        try {
            if (Schema::hasTable('job_applications')) {
                $jobModel = \App\Models\JobSearch\JobApplication::class;
                $activeCount = $jobModel::query()->where('user_id', $userId)
                    ->whereNotIn('status', ['rejected', 'withdrawn'])->count();

                $context[] = "JOB APPLICATIONS: {$activeCount} active applications.";

                $recentApps = $jobModel::query()->where('user_id', $userId)
                    ->orderByDesc('created_at')->limit(5)->get(['company_name', 'job_title', 'status']);

                if ($recentApps->isNotEmpty()) {
                    $appList = $recentApps->map(fn ($a) => "- {$a->job_title} at {$a->company_name} (status: {$a->status})")->implode("\n");
                    $context[] = "Recent applications:\n{$appList}";
                }
            }
        } catch (\Throwable) {
            $context[] = 'JOB APPLICATIONS: No job application data available.';
        }

        // Projects context
        try {
            if (Schema::hasTable('projects')) {
                $projectModel = \App\Models\Project\Project::class;
                $projectCount = $projectModel::query()->count();
                $recentProjects = $projectModel::query()->orderByDesc('created_at')->limit(5)->get(['title', 'created_at']);

                $context[] = "PROJECTS: {$projectCount} total projects.";

                if ($recentProjects->isNotEmpty()) {
                    $projList = $recentProjects->map(fn ($p) => "- {$p->title}")->implode("\n");
                    $context[] = "Recent projects:\n{$projList}";
                }
            }
        } catch (\Throwable) {
            $context[] = 'PROJECTS: No project data available.';
        }

        if (empty($context)) {
            return 'No dashboard data available yet.';
        }

        return implode("\n\n", $context);
    }

    public function buildPrompt(string $userMessage, string $dashboardContext, Collection $recentMessages): string
    {
        $today = now()->format('l, F j, Y');

        return <<<PROMPT
You are a helpful personal AI assistant integrated into a portfolio/productivity dashboard. Today is {$today}.

You have access to the following dashboard context about the user's current data:

{$dashboardContext}

Use this context to provide helpful, personalized responses. You can reference the user's tasks, emails, job applications, and projects when relevant. Be concise, friendly, and actionable.

If the user asks about something not in the context, help them to the best of your ability as a general assistant.

Format your responses using markdown when helpful (bold, lists, code blocks, etc.).
PROMPT;
    }

    public function callGeminiApi(string $apiKey, string $prompt, array $conversationHistory): string
    {
        $contents = [];

        // Add system instruction as first user message context
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $prompt]],
        ];
        $contents[] = [
            'role' => 'model',
            'parts' => [['text' => 'Understood. I am ready to assist you with your dashboard data and any questions you have.']],
        ];

        // Add conversation history
        foreach ($conversationHistory as $msg) {
            $role = $msg['role'] === 'user' ? 'user' : 'model';
            $contents[] = [
                'role' => $role,
                'parts' => [['text' => $msg['content']]],
            ];
        }

        $response = Http::timeout(30)
            ->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key='.$apiKey, [
                'contents' => $contents,
            ]);

        if ($response->status() === 429) {
            throw new \RuntimeException('Rate limit reached. Please wait a moment before sending another message.');
        }

        if ($response->failed()) {
            throw new \RuntimeException('Failed to reach the Gemini API. Status: '.$response->status());
        }

        $body = $response->json();

        return $body['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }

    public function callGroqApi(string $apiKey, string $prompt, array $conversationHistory): string
    {
        $messages = [
            [
                'role' => 'system',
                'content' => $prompt,
            ],
        ];

        foreach ($conversationHistory as $msg) {
            $messages[] = [
                'role' => $msg['role'],
                'content' => $msg['content'],
            ];
        }

        $response = Http::timeout(30)
            ->withToken($apiKey)
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => 'llama-3.3-70b-versatile',
                'messages' => $messages,
                'max_tokens' => 2048,
            ]);

        if ($response->status() === 429) {
            throw new \RuntimeException('Rate limit reached. Please wait a moment before sending another message.');
        }

        if ($response->failed()) {
            throw new \RuntimeException('Failed to reach the Groq API. Status: '.$response->status());
        }

        $body = $response->json();

        return $body['choices'][0]['message']['content'] ?? '';
    }

    public function parseResponse(string $responseText): string
    {
        return trim($responseText);
    }

    public function autoGenerateTitle(AiChatConversation $conversation, string $firstMessage): void
    {
        // Simple heuristic: use first 50 chars of the first message as title
        $title = str($firstMessage)->limit(50)->toString();

        if (! empty($title)) {
            $conversation->update(['title' => $title]);
        }
    }

    private function buildConversationHistory(Collection $recentMessages): array
    {
        return $recentMessages->map(fn (AiChatMessage $msg) => [
            'role' => $msg->role,
            'content' => $msg->content,
        ])->toArray();
    }

    private function getFallbackProvider(int $userId, string $currentProvider): ?array
    {
        $fallbackProviderConst = $currentProvider === 'gemini'
            ? ApiKey::PROVIDER_GROQ
            : ApiKey::PROVIDER_GEMINI;

        $key = ApiKey::query()
            ->forUser($userId)
            ->forProvider($fallbackProviderConst)
            ->connected()
            ->first();

        if ($key) {
            return [
                'provider' => $currentProvider === 'gemini' ? 'groq' : 'gemini',
                'apiKey' => $key,
            ];
        }

        return null;
    }
}
