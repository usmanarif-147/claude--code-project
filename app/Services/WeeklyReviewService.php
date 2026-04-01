<?php

namespace App\Services;

use App\Models\ApiKey;
use App\Models\Task\Task;
use App\Models\Task\TaskCategory;
use App\Models\Task\WeeklyReview;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeeklyReviewService
{
    public function getOrCreateReview(int $userId, Carbon $weekStart): WeeklyReview
    {
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        $review = WeeklyReview::query()
            ->forUser($userId)
            ->forWeek($weekStart)
            ->first();

        if ($review) {
            return $review;
        }

        $stats = $this->computeWeekStats($userId, $weekStart, $weekEnd);
        $categoryBreakdown = $this->computeCategoryBreakdown($userId, $weekStart, $weekEnd);

        $review = WeeklyReview::create([
            'user_id' => $userId,
            'week_start' => $weekStart->toDateString(),
            'week_end' => $weekEnd->toDateString(),
            'total_planned' => $stats['total_planned'],
            'total_completed' => $stats['total_completed'],
            'total_carried_over' => $stats['total_carried_over'],
            'category_breakdown' => $categoryBreakdown,
        ]);

        if ($stats['total_planned'] > 0) {
            $incompleteTasks = $this->getIncompleteTasks($userId, $weekStart, $weekEnd);
            $previousReview = $this->getPreviousWeekReview($userId, $weekStart);
            $comparison = $this->computeWeekComparison($review, $previousReview);

            try {
                $summary = $this->generateAiSummary($review, $incompleteTasks->toArray(), $categoryBreakdown, $comparison);
                $focusAreas = $this->generateAiFocusAreas($review, $incompleteTasks->toArray(), $categoryBreakdown);

                if ($summary || $focusAreas) {
                    $review->update([
                        'ai_summary' => $summary,
                        'ai_focus_areas' => $focusAreas,
                        'ai_generated_at' => now(),
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('Weekly review AI generation failed: '.$e->getMessage());
            }
        }

        return $review;
    }

    public function computeWeekStats(int $userId, Carbon $weekStart, Carbon $weekEnd): array
    {
        $query = Task::query()
            ->forUser($userId)
            ->whereBetween('due_date', [$weekStart->toDateString(), $weekEnd->toDateString()]);

        $totalPlanned = $query->count();
        $totalCompleted = (clone $query)->completed()->count();
        $totalCarriedOver = $totalPlanned - $totalCompleted;

        return [
            'total_planned' => $totalPlanned,
            'total_completed' => $totalCompleted,
            'total_carried_over' => $totalCarriedOver,
        ];
    }

    public function computeCategoryBreakdown(int $userId, Carbon $weekStart, Carbon $weekEnd): array
    {
        $tasks = Task::query()
            ->forUser($userId)
            ->whereBetween('due_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get();

        $categories = TaskCategory::all()->keyBy('id');
        $breakdown = [];

        $grouped = $tasks->groupBy('category_id');

        foreach ($grouped as $categoryId => $categoryTasks) {
            if ($categoryId && $categories->has($categoryId)) {
                $category = $categories->get($categoryId);
                $breakdown[] = [
                    'category_id' => $categoryId,
                    'category_name' => $category->name,
                    'color' => $category->color,
                    'planned' => $categoryTasks->count(),
                    'completed' => $categoryTasks->where('status', 'completed')->count(),
                ];
            } else {
                $breakdown[] = [
                    'category_id' => null,
                    'category_name' => 'Uncategorized',
                    'color' => '#6b7280',
                    'planned' => $categoryTasks->count(),
                    'completed' => $categoryTasks->where('status', 'completed')->count(),
                ];
            }
        }

        usort($breakdown, fn ($a, $b) => $b['planned'] <=> $a['planned']);

        return $breakdown;
    }

    public function getIncompleteTasks(int $userId, Carbon $weekStart, Carbon $weekEnd): Collection
    {
        return Task::query()
            ->forUser($userId)
            ->whereBetween('due_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->pending()
            ->with('category')
            ->byPriority()
            ->get();
    }

    public function getPreviousWeekReview(int $userId, Carbon $currentWeekStart): ?WeeklyReview
    {
        $previousWeekStart = $currentWeekStart->copy()->subWeek();

        return WeeklyReview::query()
            ->forUser($userId)
            ->forWeek($previousWeekStart)
            ->first();
    }

    public function computeWeekComparison(WeeklyReview $current, ?WeeklyReview $previous): array
    {
        if (! $previous) {
            return [
                'completion_trend' => null,
                'planned_trend' => null,
                'carried_over_trend' => null,
            ];
        }

        $currentPercentage = $current->completion_percentage;
        $previousPercentage = $previous->completion_percentage;

        return [
            'completion_trend' => $currentPercentage - $previousPercentage,
            'planned_trend' => $current->total_planned - $previous->total_planned,
            'carried_over_trend' => $current->total_carried_over - $previous->total_carried_over,
        ];
    }

    public function generateAiSummary(WeeklyReview $review, array $incompleteTasks, array $categoryBreakdown, ?array $comparison): ?string
    {
        $apiKey = $this->getAiApiKey($review->user_id);

        if (! $apiKey) {
            return null;
        }

        $prompt = $this->buildSummaryPrompt($review, $incompleteTasks, $categoryBreakdown, $comparison);

        return $this->callAiApi($apiKey['key'], $apiKey['provider'], $prompt);
    }

    public function generateAiFocusAreas(WeeklyReview $review, array $incompleteTasks, array $categoryBreakdown): ?array
    {
        $apiKey = $this->getAiApiKey($review->user_id);

        if (! $apiKey) {
            return null;
        }

        $prompt = $this->buildFocusAreasPrompt($review, $incompleteTasks, $categoryBreakdown);

        $response = $this->callAiApi($apiKey['key'], $apiKey['provider'], $prompt);

        if (! $response) {
            return null;
        }

        $lines = array_filter(
            array_map('trim', explode("\n", $response)),
            fn ($line) => ! empty($line)
        );

        return array_values(array_map(
            fn ($line) => preg_replace('/^\d+[\.\)]\s*/', '', $line),
            $lines
        ));
    }

    public function getAiApiKey(int $userId): ?array
    {
        $claudeKey = ApiKey::query()
            ->forUser($userId)
            ->forProvider(ApiKey::PROVIDER_CLAUDE)
            ->connected()
            ->first();

        if ($claudeKey) {
            return ['key' => $claudeKey->key_value, 'provider' => 'claude'];
        }

        $openaiKey = ApiKey::query()
            ->forUser($userId)
            ->forProvider(ApiKey::PROVIDER_OPENAI)
            ->connected()
            ->first();

        if ($openaiKey) {
            return ['key' => $openaiKey->key_value, 'provider' => 'openai'];
        }

        return null;
    }

    public function refreshReview(int $userId, Carbon $weekStart): WeeklyReview
    {
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        $stats = $this->computeWeekStats($userId, $weekStart, $weekEnd);
        $categoryBreakdown = $this->computeCategoryBreakdown($userId, $weekStart, $weekEnd);

        $review = WeeklyReview::updateOrCreate(
            ['user_id' => $userId, 'week_start' => $weekStart->toDateString()],
            [
                'week_end' => $weekEnd->toDateString(),
                'total_planned' => $stats['total_planned'],
                'total_completed' => $stats['total_completed'],
                'total_carried_over' => $stats['total_carried_over'],
                'category_breakdown' => $categoryBreakdown,
            ]
        );

        if ($stats['total_planned'] > 0) {
            $incompleteTasks = $this->getIncompleteTasks($userId, $weekStart, $weekEnd);
            $previousReview = $this->getPreviousWeekReview($userId, $weekStart);
            $comparison = $this->computeWeekComparison($review, $previousReview);

            try {
                $summary = $this->generateAiSummary($review, $incompleteTasks->toArray(), $categoryBreakdown, $comparison);
                $focusAreas = $this->generateAiFocusAreas($review, $incompleteTasks->toArray(), $categoryBreakdown);

                $review->update([
                    'ai_summary' => $summary,
                    'ai_focus_areas' => $focusAreas,
                    'ai_generated_at' => $summary || $focusAreas ? now() : $review->ai_generated_at,
                ]);
            } catch (\Exception $e) {
                Log::warning('Weekly review AI regeneration failed: '.$e->getMessage());
            }
        }

        return $review->fresh();
    }

    private function buildSummaryPrompt(WeeklyReview $review, array $incompleteTasks, array $categoryBreakdown, ?array $comparison): string
    {
        $prompt = "You are a productivity coach. Provide a concise 2-3 paragraph weekly summary based on these task statistics.\n\n";
        $prompt .= "Week: {$review->week_start->format('M j')} - {$review->week_end->format('M j, Y')}\n";
        $prompt .= "Tasks Planned: {$review->total_planned}\n";
        $prompt .= "Tasks Completed: {$review->total_completed}\n";
        $prompt .= "Completion Rate: {$review->completion_percentage}%\n";
        $prompt .= "Carried Over: {$review->total_carried_over}\n\n";

        if (! empty($categoryBreakdown)) {
            $prompt .= "Category Breakdown:\n";
            foreach ($categoryBreakdown as $cat) {
                $catRate = $cat['planned'] > 0 ? round(($cat['completed'] / $cat['planned']) * 100) : 0;
                $prompt .= "- {$cat['category_name']}: {$cat['completed']}/{$cat['planned']} completed ({$catRate}%)\n";
            }
            $prompt .= "\n";
        }

        if (! empty($incompleteTasks)) {
            $prompt .= "Incomplete Tasks (up to 20):\n";
            foreach (array_slice($incompleteTasks, 0, 20) as $task) {
                $title = is_array($task) ? ($task['title'] ?? 'Untitled') : $task->title;
                $prompt .= "- {$title}\n";
            }
            $prompt .= "\n";
        }

        if ($comparison && $comparison['completion_trend'] !== null) {
            $prompt .= "Compared to Previous Week:\n";
            $prompt .= "- Completion rate change: {$comparison['completion_trend']} percentage points\n";
            $prompt .= "- Tasks planned change: {$comparison['planned_trend']}\n";
            $prompt .= "- Carried over change: {$comparison['carried_over_trend']}\n\n";
        }

        $prompt .= 'Provide a concise, encouraging but honest summary. Focus on patterns, achievements, and areas for improvement. Do not use markdown formatting.';

        return $prompt;
    }

    private function buildFocusAreasPrompt(WeeklyReview $review, array $incompleteTasks, array $categoryBreakdown): string
    {
        $prompt = "Based on this week's task data, suggest 3-5 specific, actionable focus areas for next week. Keep each focus area to one sentence.\n\n";
        $prompt .= "Completion Rate: {$review->completion_percentage}%\n";
        $prompt .= "Carried Over: {$review->total_carried_over} tasks\n\n";

        if (! empty($categoryBreakdown)) {
            $prompt .= "Categories worked on:\n";
            foreach ($categoryBreakdown as $cat) {
                $prompt .= "- {$cat['category_name']}: {$cat['completed']}/{$cat['planned']} completed\n";
            }
            $prompt .= "\n";
        }

        if (! empty($incompleteTasks)) {
            $prompt .= "Incomplete tasks:\n";
            foreach (array_slice($incompleteTasks, 0, 20) as $task) {
                $title = is_array($task) ? ($task['title'] ?? 'Untitled') : $task->title;
                $prompt .= "- {$title}\n";
            }
            $prompt .= "\n";
        }

        $prompt .= 'Return only the numbered list of focus areas (e.g., "1. Focus area here"). No extra text.';

        return $prompt;
    }

    private function callAiApi(string $key, string $provider, string $prompt): ?string
    {
        try {
            if ($provider === 'claude') {
                $response = Http::withHeaders([
                    'x-api-key' => $key,
                    'anthropic-version' => '2023-06-01',
                    'Content-Type' => 'application/json',
                ])->timeout(30)->post('https://api.anthropic.com/v1/messages', [
                    'model' => 'claude-sonnet-4-20250514',
                    'max_tokens' => 1024,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ]);

                if ($response->successful()) {
                    return $response->json('content.0.text');
                }
            } elseif ($provider === 'openai') {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer '.$key,
                    'Content-Type' => 'application/json',
                ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-mini',
                    'max_tokens' => 1024,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ]);

                if ($response->successful()) {
                    return $response->json('choices.0.message.content');
                }
            }
        } catch (\Exception $e) {
            Log::warning("AI API call failed ({$provider}): ".$e->getMessage());
        }

        return null;
    }
}
