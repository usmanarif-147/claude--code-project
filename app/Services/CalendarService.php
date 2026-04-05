<?php

namespace App\Services;

use App\Models\ProjectManagement\ProjectBoard;
use App\Models\ProjectManagement\ProjectTask;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class CalendarService
{
    public function getTasksForMonth(int $userId, int $year, int $month, ?int $boardId = null): Collection
    {
        $firstOfMonth = Carbon::create($year, $month, 1);

        // Fetch tasks for the full 42-cell grid range (includes padding days from adjacent months)
        $gridStart = $firstOfMonth->copy()->subDays($firstOfMonth->dayOfWeekIso - 1)->startOfDay();
        $gridEnd = $gridStart->copy()->addDays(41)->endOfDay();

        $query = ProjectTask::query()
            ->forUser($userId)
            ->whereBetween('target_date', [$gridStart, $gridEnd])
            ->with(['board', 'column'])
            ->ordered();

        if ($boardId) {
            $query->forBoard($boardId);
        }

        return $query->get()
            ->groupBy(fn ($task) => $task->target_date->format('Y-m-d'));
    }

    public function getTasksForWeek(int $userId, Carbon $weekStart, ?int $boardId = null): Collection
    {
        $weekEnd = $weekStart->copy()->addDays(6)->endOfDay();

        $query = ProjectTask::query()
            ->forUser($userId)
            ->whereBetween('target_date', [$weekStart->copy()->startOfDay(), $weekEnd])
            ->with(['board', 'column'])
            ->ordered();

        if ($boardId) {
            $query->forBoard($boardId);
        }

        return $query->get()
            ->groupBy(fn ($task) => $task->target_date->format('Y-m-d'));
    }

    public function getTasksForDate(int $userId, string $date, ?int $boardId = null): array
    {
        $carbonDate = Carbon::parse($date);

        $query = ProjectTask::query()
            ->forUser($userId)
            ->whereDate('target_date', $carbonDate)
            ->with(['board', 'column'])
            ->ordered();

        if ($boardId) {
            $query->forBoard($boardId);
        }

        $projectTasks = $query->get();

        return [
            'personal' => collect(),
            'project' => $projectTasks,
        ];
    }

    public function getCalendarStats(int $userId, Carbon $periodStart, Carbon $periodEnd, ?int $boardId = null): array
    {
        $query = ProjectTask::query()
            ->forUser($userId)
            ->whereBetween('target_date', [$periodStart->copy()->startOfDay(), $periodEnd->copy()->endOfDay()]);

        if ($boardId) {
            $query->forBoard($boardId);
        }

        $tasks = $query->get();

        $total = $tasks->count();
        $completed = $tasks->whereNotNull('completed_at')->count();
        $overdue = $tasks->filter(function ($task) {
            return $task->completed_at === null && $task->target_date->lt(Carbon::today());
        })->count();

        $busiestDay = null;
        if ($total > 0) {
            $grouped = $tasks->groupBy(function ($task) {
                return $task->target_date->format('Y-m-d');
            });
            $maxCount = 0;
            foreach ($grouped->sortKeys() as $date => $dateTasks) {
                if ($dateTasks->count() > $maxCount) {
                    $maxCount = $dateTasks->count();
                    $busiestDay = $date;
                }
            }
        }

        return [
            'total' => $total,
            'completed' => $completed,
            'overdue' => $overdue,
            'busiestDay' => $busiestDay,
        ];
    }

    public function getBoards(int $userId): Collection
    {
        return ProjectBoard::query()->forUser($userId)->ordered()->get();
    }

    public function buildMonthGrid(int $year, int $month, Collection $tasksByDate): array
    {
        $firstOfMonth = Carbon::create($year, $month, 1);

        // ISO weekday: Monday = 1, Sunday = 7
        $startDayOfWeek = $firstOfMonth->dayOfWeekIso;

        // Pad days from previous month
        $gridStart = $firstOfMonth->copy()->subDays($startDayOfWeek - 1);

        $days = [];
        $current = $gridStart->copy();

        // Always 42 cells (6 rows x 7 cols)
        for ($i = 0; $i < 42; $i++) {
            $dateKey = $current->format('Y-m-d');
            $days[] = [
                'date' => $dateKey,
                'day' => $current->day,
                'isCurrentMonth' => $current->month === $month && $current->year === $year,
                'isToday' => $current->isToday(),
                'tasks' => $tasksByDate->get($dateKey, collect()),
            ];
            $current->addDay();
        }

        return $days;
    }

    public function buildWeekGrid(Carbon $weekStart, Collection $tasksByDate): array
    {
        $days = [];
        $current = $weekStart->copy();

        for ($i = 0; $i < 7; $i++) {
            $dateKey = $current->format('Y-m-d');
            $days[] = [
                'date' => $dateKey,
                'day' => $current->day,
                'dayName' => $current->format('D'),
                'isToday' => $current->isToday(),
                'tasks' => $tasksByDate->get($dateKey, collect()),
            ];
            $current->addDay();
        }

        return $days;
    }
}
