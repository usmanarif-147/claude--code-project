<?php

namespace App\Services;

use App\Models\Task\Task;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class TaskService
{
    public function create(array $data, bool $aiCategorize = false, ?int $aiUserId = null): Task
    {
        $task = Task::create($data);

        // If AI categorization is requested and no category was provided
        if ($aiCategorize && empty($data['category_id']) && $aiUserId !== null) {
            try {
                $aiService = app(AiCategoryIdentificationService::class);
                $category = $aiService->identifyCategory($aiUserId, $data['title'], $data['description'] ?? '');

                if ($category !== null) {
                    $task->update(['category_id' => $category->id]);
                    $task->refresh();
                }
            } catch (\Throwable $e) {
                // AI failure is non-fatal -- task is already created without category
                Log::warning('AI categorization failed during task create: '.$e->getMessage());
            }
        }

        return $task;
    }

    public function update(Task $task, array $data): Task
    {
        $task->update($data);

        return $task;
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }

    public function toggleComplete(Task $task): Task
    {
        if ($task->status === 'completed') {
            return $this->markPending($task);
        }

        return $this->markComplete($task);
    }

    public function markComplete(Task $task): Task
    {
        $task->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return $task;
    }

    public function markPending(Task $task): Task
    {
        $task->update([
            'status' => 'pending',
            'completed_at' => null,
        ]);

        return $task;
    }

    public function moveIncompleteTo(int $userId, Carbon $fromDate, Carbon $toDate): int
    {
        return Task::query()
            ->forUser($userId)
            ->forDate($fromDate)
            ->pending()
            ->update(['due_date' => $toDate]);
    }

    public function getTasksForDate(int $userId, Carbon $date): Collection
    {
        return Task::query()
            ->forUser($userId)
            ->forDate($date)
            ->byPriority()
            ->ordered()
            ->orderBy('created_at')
            ->get();
    }

    public function getCompletionStats(int $userId, Carbon $date): array
    {
        $tasks = Task::query()
            ->forUser($userId)
            ->forDate($date);

        $total = $tasks->count();
        $completed = (clone $tasks)->completed()->count();
        $percentage = $total > 0 ? (int) floor(($completed / $total) * 100) : 0;

        return [
            'total' => $total,
            'completed' => $completed,
            'percentage' => $percentage,
        ];
    }
}
