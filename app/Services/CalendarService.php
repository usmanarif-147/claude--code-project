<?php

namespace App\Services;

use App\Models\Task\Task;
use App\Models\Task\TaskCategory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class CalendarService
{
    public function getTasksForMonth(int $userId, int $year, int $month): Collection
    {
        $firstOfMonth = Carbon::create($year, $month, 1);

        // Fetch tasks for the full 42-cell grid range (includes padding days from adjacent months)
        $gridStart = $firstOfMonth->copy()->subDays($firstOfMonth->dayOfWeekIso - 1)->startOfDay();
        $gridEnd = $gridStart->copy()->addDays(41)->endOfDay();

        return Task::query()
            ->forUser($userId)
            ->whereBetween('due_date', [$gridStart, $gridEnd])
            ->with('category')
            ->byPriority()
            ->ordered()
            ->orderBy('created_at')
            ->get()
            ->groupBy(fn ($task) => $task->due_date->format('Y-m-d'));
    }

    public function getTasksForWeek(int $userId, Carbon $weekStart): Collection
    {
        $weekEnd = $weekStart->copy()->addDays(6)->endOfDay();

        return Task::query()
            ->forUser($userId)
            ->whereBetween('due_date', [$weekStart->copy()->startOfDay(), $weekEnd])
            ->with('category')
            ->byPriority()
            ->ordered()
            ->orderBy('created_at')
            ->get()
            ->groupBy(fn ($task) => $task->due_date->format('Y-m-d'));
    }

    public function getCalendarStats(int $userId, Carbon $periodStart, Carbon $periodEnd): array
    {
        $tasks = Task::query()
            ->forUser($userId)
            ->whereBetween('due_date', [$periodStart->copy()->startOfDay(), $periodEnd->copy()->endOfDay()])
            ->get();

        $total = $tasks->count();
        $completed = $tasks->where('status', 'completed')->count();
        $overdue = $tasks->filter(function ($task) {
            return $task->status !== 'completed' && $task->due_date->lt(Carbon::today());
        })->count();

        $busiestDay = null;
        if ($total > 0) {
            $grouped = $tasks->groupBy(fn ($task) => $task->due_date->format('Y-m-d'));
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

    public function getCategories(): Collection
    {
        return TaskCategory::query()->ordered()->get();
    }

    public function buildMonthGrid(int $year, int $month, Collection $tasksByDate): array
    {
        $firstOfMonth = Carbon::create($year, $month, 1);
        $lastOfMonth = $firstOfMonth->copy()->endOfMonth();

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
