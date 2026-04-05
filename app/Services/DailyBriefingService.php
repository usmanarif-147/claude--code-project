<?php

namespace App\Services;

use App\Models\Email\RecruiterAlert;
use App\Models\Goal;
use App\Models\JobSearch\JobListing;
use App\Models\ProjectManagement\ProjectTask;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DailyBriefingService
{
    public function __construct(
        protected EmailInboxService $emailInboxService,
        protected ApplicationStatsService $applicationStatsService,
        protected GoalService $goalService,
        protected ExpenseService $expenseService,
    ) {}

    public function getGreeting(): string
    {
        $hour = now()->hour;
        $userName = auth()->user()->name;
        $firstName = explode(' ', $userName)[0];

        $greeting = match (true) {
            $hour >= 5 && $hour < 12 => 'Good morning',
            $hour >= 12 && $hour < 17 => 'Good afternoon',
            default => 'Good evening',
        };

        return "{$greeting}, {$firstName}";
    }

    public function getQuickStats(int $userId): array
    {
        $startOfWeek = now()->startOfWeek();

        $tasksCompletedThisWeek = ProjectTask::query()
            ->forUser($userId)
            ->completed()
            ->whereBetween('completed_at', [$startOfWeek, now()])
            ->count();

        $emailStats = $this->emailInboxService->getRecentStats();

        $newJobMatches = JobListing::query()
            ->forUser($userId)
            ->visible()
            ->where('fetched_at', '>=', now()->subDay())
            ->count();

        $goalStats = $this->goalService->getStats();

        $monthExpenses = $this->expenseService->getMonthTotal(now()->year, now()->month);

        return [
            'tasks_completed_this_week' => $tasksCompletedThisWeek,
            'unread_emails' => $emailStats['unread'],
            'new_job_matches' => $newJobMatches,
            'active_goals_progress' => $goalStats['average_progress'],
            'month_expenses' => $monthExpenses,
        ];
    }

    public function getTodayTasks(int $userId): Collection
    {
        return ProjectTask::query()
            ->forUser($userId)
            ->whereDate('target_date', Carbon::today())
            ->with(['board', 'column'])
            ->pending()
            ->byPriority()
            ->take(5)
            ->get();
    }

    public function getNewJobMatches(int $userId): Collection
    {
        return JobListing::query()
            ->forUser($userId)
            ->visible()
            ->where('fetched_at', '>=', now()->subDay())
            ->latest('fetched_at')
            ->take(5)
            ->get();
    }

    public function getEmailSummary(): array
    {
        return $this->emailInboxService->getRecentStats();
    }

    public function getPendingRecruiterAlerts(): Collection
    {
        return RecruiterAlert::query()
            ->unread()
            ->undismissed()
            ->with('email')
            ->latest()
            ->take(5)
            ->get();
    }

    public function getActiveGoals(): Collection
    {
        return Goal::query()
            ->where('status', 'active')
            ->orderBy('target_date')
            ->take(5)
            ->get();
    }

    public function getJobSearchStats(): array
    {
        return $this->applicationStatsService->getSummaryStats('7d');
    }
}
