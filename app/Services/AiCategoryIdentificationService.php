<?php

namespace App\Services;

use App\Models\ApiKey;
use App\Models\Task\TaskCategory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiCategoryIdentificationService
{
    /**
     * Identify the best category for a single task using AI.
     *
     * @param  int  $userId  The authenticated user's ID (to fetch their API key)
     * @param  string  $taskTitle  The task's title text
     * @param  string  $description  Optional task description for better context
     * @return ?TaskCategory The matched or newly created TaskCategory, or null on failure
     */
    public function identifyCategory(int $userId, string $taskTitle, string $description = ''): ?TaskCategory
    {
        $result = $this->identifyCategoriesBatch($userId, [
            ['title' => $taskTitle, 'description' => $description],
        ]);

        return $result[0] ?? null;
    }

    /**
     * Identify categories for multiple tasks in a single AI call (batch).
     *
     * @param  int  $userId  The authenticated user's ID
     * @param  array  $tasks  Array of ['title' => string, 'description' => string]
     * @return array Array of ?TaskCategory (same order as input), null for any task that could not be categorized
     */
    public function identifyCategoriesBatch(int $userId, array $tasks): array
    {
        if (empty($tasks)) {
            return [];
        }

        $provider = $this->getConfiguredProvider($userId);

        if ($provider === null) {
            Log::info('AI Category Identification: No API key configured for user '.$userId);

            return array_fill(0, count($tasks), null);
        }

        $existingCategories = TaskCategory::query()->ordered()->pluck('name')->toArray();
        $prompt = $this->buildPrompt($tasks, $existingCategories);

        $apiKey = ApiKey::query()
            ->forUser($userId)
            ->forProvider($provider === 'claude' ? ApiKey::PROVIDER_CLAUDE : ApiKey::PROVIDER_OPENAI)
            ->connected()
            ->first();

        try {
            $responseText = $provider === 'claude'
                ? $this->callClaudeApi($apiKey->key_value, $prompt)
                : $this->callOpenAiApi($apiKey->key_value, $prompt);

            $categoryNames = $this->parseAiResponse($responseText, count($tasks));

            $result = [];
            foreach ($categoryNames as $name) {
                if ($name === null || trim($name) === '') {
                    $result[] = null;
                } else {
                    $result[] = $this->findOrCreateCategory(trim($name));
                }
            }

            return $result;
        } catch (\Throwable $e) {
            Log::warning('AI Category Identification failed: '.$e->getMessage());

            return array_fill(0, count($tasks), null);
        }
    }

    /**
     * Find an existing category by name (case-insensitive) or create a new one.
     */
    public function findOrCreateCategory(string $categoryName): TaskCategory
    {
        $category = TaskCategory::query()
            ->whereRaw('LOWER(name) = ?', [strtolower($categoryName)])
            ->first();

        if ($category) {
            return $category;
        }

        return TaskCategory::create([
            'name' => ucfirst($categoryName),
            'color' => $this->generateCategoryColor(),
            'sort_order' => (TaskCategory::max('sort_order') ?? 0) + 1,
        ]);
    }

    /**
     * Check which AI provider the user has configured.
     * Prefers Claude, falls back to OpenAI. Returns null if neither is available.
     */
    public function getConfiguredProvider(int $userId): ?string
    {
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

    /**
     * Build the AI prompt for category identification.
     *
     * @param  array  $tasks  Array of ['title' => string, 'description' => string]
     * @param  array  $existingCategories  Array of existing category name strings
     */
    public function buildPrompt(array $tasks, array $existingCategories): string
    {
        $taskList = '';
        foreach ($tasks as $index => $task) {
            $desc = ! empty($task['description']) ? " | Description: {$task['description']}" : '';
            $taskList .= ($index + 1).". Title: {$task['title']}{$desc}\n";
        }

        $categoryList = ! empty($existingCategories)
            ? "Existing categories (prefer these when appropriate):\n".implode(', ', $existingCategories)."\n\n"
            : "No existing categories yet. Create appropriate category names.\n\n";

        $count = count($tasks);

        return <<<PROMPT
You are a task organization assistant. Your job is to assign the best category to each task.

{$categoryList}Here are the tasks to categorize:
{$taskList}
Rules:
- Use an existing category if it fits well (match by meaning, not just exact name)
- If no existing category fits, suggest a new short category name (1-3 words, capitalize first letter)
- Category names should be general enough to group related tasks (e.g., "Development", "Design", "Health", "Finance", "Meetings", "Personal", "Shopping", "Errands")
- Do NOT create overly specific categories (e.g., "Buy Groceries" is too specific -- use "Shopping" instead)
- Every task MUST get a category -- never leave one blank

Respond with ONLY a valid JSON array of exactly {$count} strings, one category name per task, in the same order as the input. No markdown, no code fences, no explanation.

Example response for 3 tasks:
["Development", "Health", "Finance"]
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

    /**
     * Parse the AI response into an array of category name strings.
     *
     * @param  string  $responseText  Raw AI response text
     * @param  int  $expectedCount  How many category names we expect
     * @return array Array of ?string category names
     */
    public function parseAiResponse(string $responseText, int $expectedCount): array
    {
        // Strip markdown code fences if present
        $responseText = preg_replace('/^```(?:json)?\s*/m', '', $responseText);
        $responseText = preg_replace('/```\s*$/m', '', $responseText);
        $responseText = trim($responseText);

        $parsed = json_decode($responseText, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($parsed)) {
            throw new \RuntimeException('AI returned an unexpected response format.');
        }

        // Ensure we have the right number of results
        $result = [];
        for ($i = 0; $i < $expectedCount; $i++) {
            $result[] = isset($parsed[$i]) && is_string($parsed[$i]) ? $parsed[$i] : null;
        }

        return $result;
    }

    private function generateCategoryColor(): string
    {
        $colors = [
            '#7c3aed', '#3b82f6', '#22c55e', '#f59e0b', '#ef4444',
            '#ec4899', '#8b5cf6', '#06b6d4', '#14b8a6', '#f97316',
        ];

        return $colors[array_rand($colors)];
    }
}
