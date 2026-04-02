<?php

namespace App\Services;

use App\Models\Goal;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class GoalService
{
    public function getAll(array $filters = []): LengthAwarePaginator
    {
        $query = Goal::query()
            ->orderByRaw("FIELD(status, 'active', 'completed', 'abandoned')")
            ->orderBy('target_date', 'asc');

        if (! empty($filters['search'])) {
            $query->where('title', 'like', '%'.$filters['search'].'%');
        }

        if (! empty($filters['category'])) {
            $query->category($filters['category']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate(10);
    }

    public function getActiveGoals(): Collection
    {
        return Goal::active()->orderBy('target_date', 'asc')->get();
    }

    public function getStats(): array
    {
        $activeCount = Goal::active()->count();
        $completedThisMonth = Goal::completed()
            ->whereMonth('completed_at', now()->month)
            ->whereYear('completed_at', now()->year)
            ->count();
        $averageProgress = (int) Goal::active()->avg('progress');
        $overdueCount = Goal::active()->where('target_date', '<', now()->startOfDay())->count();

        return [
            'active_count' => $activeCount,
            'completed_this_month' => $completedThisMonth,
            'average_progress' => $averageProgress,
            'overdue_count' => $overdueCount,
        ];
    }

    public function create(array $data): Goal
    {
        return Goal::create($data);
    }

    public function update(Goal $goal, array $data): Goal
    {
        $goal->update($data);

        return $goal;
    }

    public function updateProgress(Goal $goal, int $progress): Goal
    {
        $goal->update(['progress' => $progress]);

        return $goal;
    }

    public function markCompleted(Goal $goal): Goal
    {
        $goal->update([
            'status' => 'completed',
            'progress' => 100,
            'completed_at' => now(),
        ]);

        return $goal;
    }

    public function markAbandoned(Goal $goal): Goal
    {
        $goal->update(['status' => 'abandoned']);

        return $goal;
    }

    public function reopen(Goal $goal): Goal
    {
        $goal->update([
            'status' => 'active',
            'completed_at' => null,
        ]);

        return $goal;
    }

    public function delete(Goal $goal): void
    {
        $goal->delete();
    }
}
