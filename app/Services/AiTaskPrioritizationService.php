<?php

namespace App\Services;

use App\Models\ApiKey;
use App\Models\Task\Task;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class AiTaskPrioritizationService
{
    public function getConfiguredProvider(int $userId): ?string
    {
        // Prefer Claude, fallback to OpenAI
        $claudeKey = ApiKey::query()
            ->forUser($userId)
            ->forProvider(ApiKey::PROVIDER_CLAUDE)
            ->connected()
            ->first();

        if ($claudeKey) {
            return 'claude';
        }

        $openaiKey = ApiKey::query()
            ->forUser($userId)
            ->forProvider(ApiKey::PROVIDER_OPENAI)
            ->connected()
            ->first();

        if ($openaiKey) {
            return 'openai';
        }

        return null;
    }

    public function getTodaysTasks(int $userId): Collection
    {
        return Task::query()
            ->forUser($userId)
            ->where(function ($q) {
                $q->whereDate('due_date', '<=', now()->toDateString())
                    ->orWhereNull('due_date');
            })
            ->pending()
            ->with('category')
            ->ordered()
            ->get();
    }

    public function getOverdueTasks(int $userId): Collection
    {
        return Task::query()
            ->forUser($userId)
            ->whereDate('due_date', '<', now()->toDateString())
            ->pending()
            ->with('category')
            ->get();
    }

    public function getCompletedTodayCount(int $userId): int
    {
        return Task::query()
            ->forUser($userId)
            ->forToday()
            ->completed()
            ->count();
    }

    public function prioritize(int $userId): array
    {
        $provider = $this->getConfiguredProvider($userId);

        if (! $provider) {
            throw new \RuntimeException('No AI API key configured.');
        }

        $tasks = $this->getTodaysTasks($userId);

        if ($tasks->isEmpty()) {
            throw new \RuntimeException('No tasks available for prioritization.');
        }

        $overdueTasks = $this->getOverdueTasks($userId);
        $prompt = $this->buildPrompt($tasks, $overdueTasks);

        $apiKey = ApiKey::query()
            ->forUser($userId)
            ->forProvider($provider === 'claude' ? ApiKey::PROVIDER_CLAUDE : ApiKey::PROVIDER_OPENAI)
            ->connected()
            ->first();

        $responseText = $provider === 'claude'
            ? $this->callClaudeApi($apiKey->key_value, $prompt)
            : $this->callOpenAiApi($apiKey->key_value, $prompt);

        return $this->parseAiResponse($responseText);
    }

    public function buildPrompt(Collection $tasks, Collection $overdueTasks): string
    {
        $today = now()->format('l, F j, Y');

        $taskList = $tasks->map(function ($task, $index) {
            $category = $task->category ? $task->category->name : 'Uncategorized';
            $dueDate = $task->due_date ? $task->due_date->format('Y-m-d') : 'No due date';
            $isOverdue = $task->due_date && $task->due_date->lt(now()->startOfDay());
            $overdueText = $isOverdue ? ' [OVERDUE by '.now()->startOfDay()->diffInDays($task->due_date).' days]' : '';

            return sprintf(
                '%d. ID:%d | Title: %s | Description: %s | Category: %s | Priority: %s | Status: %s | Due: %s%s',
                $index + 1,
                $task->id,
                $task->title,
                $task->description ?: 'None',
                $category,
                $task->priority,
                $task->status,
                $dueDate,
                $overdueText
            );
        })->implode("\n");

        return <<<PROMPT
You are a productivity expert and task prioritization assistant. Today is {$today}.

Analyze the following tasks and provide an optimal execution order. Consider:
- Task priority levels (urgent > high > medium > low)
- Due dates (overdue tasks need immediate attention)
- Task categories for context
- Logical grouping of related work

Here are the tasks:
{$taskList}

Respond with ONLY a valid JSON object (no markdown, no code fences) in this exact format:
{
  "prioritized_tasks": [
    {
      "task_id": <integer>,
      "rank": <integer starting from 1>,
      "reasoning": "<brief explanation why this task is at this rank>"
    }
  ],
  "start_with": {
    "task_id": <integer>,
    "reasoning": "<why this task should be done first>"
  },
  "overdue_warnings": [
    {
      "task_id": <integer>,
      "days_overdue": <integer>,
      "warning": "<brief warning message>"
    }
  ],
  "focus_suggestion": "<one sentence overall focus recommendation for today>"
}

Rules:
- Include ALL tasks in prioritized_tasks
- start_with must reference one of the tasks
- Only include tasks that are actually overdue in overdue_warnings
- Keep reasoning concise (under 20 words each)
PROMPT;
    }

    public function callClaudeApi(string $apiKey, string $prompt): string
    {
        $response = Http::timeout(30)
            ->withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => 'claude-sonnet-4-20250514',
                'max_tokens' => 1024,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Failed to reach the AI service. Please try again.');
        }

        $body = $response->json();

        return $body['content'][0]['text'] ?? '';
    }

    public function callOpenAiApi(string $apiKey, string $prompt): string
    {
        $response = Http::timeout(30)
            ->withHeaders([
                'Authorization' => 'Bearer '.$apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'max_tokens' => 1024,
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Failed to reach the AI service. Please try again.');
        }

        $body = $response->json();

        return $body['choices'][0]['message']['content'] ?? '';
    }

    public function parseAiResponse(string $responseText): array
    {
        // Strip markdown code fences if present
        $responseText = preg_replace('/^```(?:json)?\s*/m', '', $responseText);
        $responseText = preg_replace('/```\s*$/m', '', $responseText);
        $responseText = trim($responseText);

        $parsed = json_decode($responseText, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($parsed)) {
            throw new \RuntimeException('AI returned an unexpected response. Please try again.');
        }

        // Validate expected structure
        if (! isset($parsed['prioritized_tasks']) || ! is_array($parsed['prioritized_tasks'])) {
            throw new \RuntimeException('AI returned an unexpected response. Please try again.');
        }

        return [
            'prioritized_tasks' => $parsed['prioritized_tasks'] ?? [],
            'start_with' => $parsed['start_with'] ?? null,
            'overdue_warnings' => $parsed['overdue_warnings'] ?? [],
            'focus_suggestion' => $parsed['focus_suggestion'] ?? '',
        ];
    }

    public function applyOrder(int $userId, array $taskIds): void
    {
        foreach ($taskIds as $index => $taskId) {
            Task::query()
                ->forUser($userId)
                ->where('id', $taskId)
                ->update(['sort_order' => $index + 1]);
        }
    }
}
