<?php

namespace App\Services;

use App\Models\ApiKey;
use App\Models\ProjectManagement\ProjectBoard;
use App\Models\ProjectManagement\ProjectTask;
use App\Models\ProjectManagement\WeeklyReview;
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
        $boardColumnBreakdown = $this->computeBoardColumnBreakdown($userId, $weekStart, $weekEnd);

        $review = WeeklyReview::create([
            'user_id' => $userId,
            'week_start' => $weekStart->toDateString(),
            'week_end' => $weekEnd->toDateString(),
            'total_planned' => $stats['total_planned'],
            'total_completed' => $stats['total_completed'],
            'total_carried_over' => $stats['total_carried_over'],
            'category_breakdown' => $boardColumnBreakdown,
        ]);

        if ($stats['total_planned'] > 0) {
            $incompleteTasks = $this->getIncompleteTasks($userId, $weekStart, $weekEnd);
            $previousReview = $this->getPreviousWeekReview($userId, $weekStart);
            $comparison = $this->computeWeekComparison($review, $previousReview);

            try {
                $summary = $this->generateAiSummary($review, $incompleteTasks->toArray(), $boardColumnBreakdown, $comparison);
                $focusAreas = $this->generateAiFocusAreas($review, $incompleteTasks->toArray(), $boardColumnBreakdown);

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

    public function computeWeekStats(int $userId, Carbon $weekStart, Carbon $weekEnd, ?int $boardId = null): array
    {
        $query = ProjectTask::query()
            ->forUser($userId)
            ->forDateRange($weekStart, $weekEnd);

        if ($boardId) {
            $query->forBoard($boardId);
        }

        $totalPlanned = $query->count();
        $totalCompleted = (clone $query)->completed()->count();
        $totalCarriedOver = $totalPlanned - $totalCompleted;

        return [
            'total_planned' => $totalPlanned,
            'total_completed' => $totalCompleted,
            'total_carried_over' => $totalCarriedOver,
        ];
    }

    public function computeBoardColumnBreakdown(int $userId, Carbon $weekStart, Carbon $weekEnd, ?int $boardId = null): array
    {
        $query = ProjectTask::query()
            ->forUser($userId)
            ->forDateRange($weekStart, $weekEnd)
            ->with(['board', 'column']);

        if ($boardId) {
            $query->forBoard($boardId);
        }

        $tasks = $query->get();

        $boardGroups = $tasks->groupBy('board_id');

        $breakdown = [];
        foreach ($boardGroups as $bId => $boardTasks) {
            $board = $boardTasks->first()->board;
            $columnGroups = $boardTasks->groupBy('column_id');

            $columns = [];
            foreach ($columnGroups as $columnTasks) {
                $column = $columnTasks->first()->column;
                $columns[] = [
                    'column_name' => $column?->name ?? 'Unknown',
                    'color' => $column?->color ?? '#7c3aed',
                    'planned' => $columnTasks->count(),
                    'completed' => $columnTasks->whereNotNull('completed_at')->count(),
                ];
            }

            $breakdown[] = [
                'board_id' => $bId,
                'board_name' => $board?->name ?? 'Unknown Board',
                'columns' => $columns,
                'total_planned' => $boardTasks->count(),
                'total_completed' => $boardTasks->whereNotNull('completed_at')->count(),
            ];
        }

        return $breakdown;
    }

    public function getIncompleteTasks(int $userId, Carbon $weekStart, Carbon $weekEnd, ?int $boardId = null): Collection
    {
        $query = ProjectTask::query()
            ->forUser($userId)
            ->forDateRange($weekStart, $weekEnd)
            ->pending()
            ->with(['board', 'column'])
            ->byPriority();

        if ($boardId) {
            $query->forBoard($boardId);
        }

        return $query->get();
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

    public function getBoards(int $userId): Collection
    {
        return ProjectBoard::query()->forUser($userId)->ordered()->get();
    }

    public function computePerBoardAnalytics(int $userId, Carbon $weekStart, Carbon $weekEnd): array
    {
        $boards = ProjectBoard::query()->forUser($userId)->ordered()->get();
        $analytics = [];

        foreach ($boards as $board) {
            $query = ProjectTask::query()
                ->forUser($userId)
                ->forBoard($board->id)
                ->forDateRange($weekStart, $weekEnd);

            $total = $query->count();
            $completed = (clone $query)->completed()->count();
            $overdue = (clone $query)->pending()
                ->where('target_date', '<', Carbon::today())
                ->count();

            $analytics[] = [
                'board_id' => $board->id,
                'board_name' => $board->name,
                'total' => $total,
                'completed' => $completed,
                'overdue' => $overdue,
                'completion_rate' => $total > 0 ? round(($completed / $total) * 100) : 0,
            ];
        }

        return $analytics;
    }

    public function generateAiSummary(WeeklyReview $review, array $incompleteTasks, array $boardColumnBreakdown, ?array $comparison): ?string
    {
        $apiKey = $this->getAiApiKey($review->user_id);

        if (! $apiKey) {
            return null;
        }

        $prompt = $this->buildSummaryPrompt($review, $incompleteTasks, $boardColumnBreakdown, $comparison);

        return $this->callAiApi($apiKey['key'], $apiKey['provider'], $prompt);
    }

    public function generateAiFocusAreas(WeeklyReview $review, array $incompleteTasks, array $boardColumnBreakdown): ?array
    {
        $apiKey = $this->getAiApiKey($review->user_id);

        if (! $apiKey) {
            return null;
        }

        $prompt = $this->buildFocusAreasPrompt($review, $incompleteTasks, $boardColumnBreakdown);

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
        $boardColumnBreakdown = $this->computeBoardColumnBreakdown($userId, $weekStart, $weekEnd);

        $review = WeeklyReview::updateOrCreate(
            ['user_id' => $userId, 'week_start' => $weekStart->toDateString()],
            [
                'week_end' => $weekEnd->toDateString(),
                'total_planned' => $stats['total_planned'],
                'total_completed' => $stats['total_completed'],
                'total_carried_over' => $stats['total_carried_over'],
                'category_breakdown' => $boardColumnBreakdown,
            ]
        );

        if ($stats['total_planned'] > 0) {
            $incompleteTasks = $this->getIncompleteTasks($userId, $weekStart, $weekEnd);
            $previousReview = $this->getPreviousWeekReview($userId, $weekStart);
            $comparison = $this->computeWeekComparison($review, $previousReview);

            try {
                $summary = $this->generateAiSummary($review, $incompleteTasks->toArray(), $boardColumnBreakdown, $comparison);
                $focusAreas = $this->generateAiFocusAreas($review, $incompleteTasks->toArray(), $boardColumnBreakdown);

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

    private function buildSummaryPrompt(WeeklyReview $review, array $incompleteTasks, array $boardColumnBreakdown, ?array $comparison): string
    {
        $prompt = "You are a productivity coach. Provide a concise 2-3 paragraph weekly summary based on these task statistics.\n\n";
        $prompt .= "Week: {$review->week_start->format('M j')} - {$review->week_end->format('M j, Y')}\n";
        $prompt .= "Tasks Planned: {$review->total_planned}\n";
        $prompt .= "Tasks Completed: {$review->total_completed}\n";
        $prompt .= "Completion Rate: {$review->completion_percentage}%\n";
        $prompt .= "Carried Over: {$review->total_carried_over}\n\n";

        if (! empty($boardColumnBreakdown)) {
            $prompt .= "Board/Column Breakdown:\n";
            foreach ($boardColumnBreakdown as $board) {
                $boardRate = $board['total_planned'] > 0 ? round(($board['total_completed'] / $board['total_planned']) * 100) : 0;
                $prompt .= "- Board \"{$board['board_name']}\": {$board['total_completed']}/{$board['total_planned']} completed ({$boardRate}%)\n";
                foreach ($board['columns'] as $col) {
                    $colRate = $col['planned'] > 0 ? round(($col['completed'] / $col['planned']) * 100) : 0;
                    $prompt .= "  - Column \"{$col['column_name']}\": {$col['completed']}/{$col['planned']} ({$colRate}%)\n";
                }
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

    private function buildFocusAreasPrompt(WeeklyReview $review, array $incompleteTasks, array $boardColumnBreakdown): string
    {
        $prompt = "Based on this week's task data, suggest 3-5 specific, actionable focus areas for next week. Keep each focus area to one sentence.\n\n";
        $prompt .= "Completion Rate: {$review->completion_percentage}%\n";
        $prompt .= "Carried Over: {$review->total_carried_over} tasks\n\n";

        if (! empty($boardColumnBreakdown)) {
            $prompt .= "Boards worked on:\n";
            foreach ($boardColumnBreakdown as $board) {
                $prompt .= "- {$board['board_name']}: {$board['total_completed']}/{$board['total_planned']} completed\n";
                foreach ($board['columns'] as $col) {
                    $prompt .= "  - {$col['column_name']}: {$col['completed']}/{$col['planned']} completed\n";
                }
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
