<?php

namespace App\Services;

use App\Models\Task\RecurringTask;
use App\Models\Task\Task;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class RecurringTaskService
{
    public function getFilteredRecurringTasks(int $userId, ?string $search, ?string $frequencyFilter, ?string $statusFilter): Builder
    {
        $query = RecurringTask::query()
            ->forUser($userId)
            ->with('category')
            ->orderByDesc('created_at');

        if ($search) {
            $query->where('title', 'like', '%'.$search.'%');
        }

        if ($frequencyFilter && $frequencyFilter !== 'all') {
            $query->byFrequency($frequencyFilter);
        }

        if ($statusFilter && $statusFilter !== 'all') {
            if ($statusFilter === 'active') {
                $query->active();
            } elseif ($statusFilter === 'paused') {
                $query->where('is_active', false);
            }
        }

        return $query;
    }

    public function create(array $data): RecurringTask
    {
        return RecurringTask::create($data);
    }

    public function update(RecurringTask $recurringTask, array $data): RecurringTask
    {
        $recurringTask->update($data);

        return $recurringTask;
    }

    public function delete(RecurringTask $recurringTask): void
    {
        $recurringTask->delete();
    }

    public function toggleActive(RecurringTask $recurringTask): RecurringTask
    {
        $recurringTask->update(['is_active' => ! $recurringTask->is_active]);

        return $recurringTask;
    }

    public function generateDueRecurringTasks(): int
    {
        $count = 0;
        $today = Carbon::today();

        $recurringTasks = RecurringTask::query()
            ->active()
            ->get();

        foreach ($recurringTasks as $recurringTask) {
            if (! $this->isDueToday($recurringTask)) {
                continue;
            }

            Task::create([
                'user_id' => $recurringTask->user_id,
                'category_id' => $recurringTask->category_id,
                'title' => $recurringTask->title,
                'description' => $recurringTask->description,
                'priority' => $recurringTask->priority,
                'scheduled_date' => $today,
            ]);

            $recurringTask->update(['last_generated_at' => now()]);
            $count++;
        }

        return $count;
    }

    public function isDueToday(RecurringTask $recurringTask): bool
    {
        $today = Carbon::today();

        // Prevent double-generation on the same day
        if ($recurringTask->last_generated_at && $recurringTask->last_generated_at->isToday()) {
            return false;
        }

        return match ($recurringTask->frequency) {
            RecurringTask::FREQUENCY_DAILY => true,
            RecurringTask::FREQUENCY_WEEKLY => $recurringTask->day_of_week === (int) $today->dayOfWeek,
            RecurringTask::FREQUENCY_MONTHLY => $recurringTask->day_of_month === $today->day,
            default => false,
        };
    }
}
