<?php

namespace Database\Seeders;

use App\Models\Task\RecurringTask;
use App\Models\Task\Task;
use App\Models\Task\TaskCategory;
use App\Models\Task\WeeklyReview;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TasksSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $today = Carbon::today();

        // ─── Task Categories ─────────────────────────────────────
        $work = TaskCategory::create(['name' => 'Work', 'color' => '#3b82f6', 'sort_order' => 0]);
        $freelance = TaskCategory::create(['name' => 'Freelance', 'color' => '#22c55e', 'sort_order' => 1]);
        $youtube = TaskCategory::create(['name' => 'YouTube', 'color' => '#ef4444', 'sort_order' => 2]);
        $jobSearch = TaskCategory::create(['name' => 'Job Search', 'color' => '#f59e0b', 'sort_order' => 3]);
        $personal = TaskCategory::create(['name' => 'Personal', 'color' => '#7c3aed', 'sort_order' => 4]);
        $learning = TaskCategory::create(['name' => 'Learning', 'color' => '#06b6d4', 'sort_order' => 5]);

        // ─── Tasks — Past Week (for weekly review stats) ─────────
        // 5 days ago
        Task::create(['user_id' => $user->id, 'category_id' => $work->id, 'title' => 'Fix authentication bug in client portal', 'due_date' => $today->copy()->subDays(5), 'priority' => 'high', 'status' => 'completed', 'completed_at' => $today->copy()->subDays(5)->setHour(11), 'sort_order' => 0]);
        Task::create(['user_id' => $user->id, 'category_id' => $work->id, 'title' => 'Write unit tests for payment module', 'due_date' => $today->copy()->subDays(5), 'priority' => 'medium', 'status' => 'completed', 'completed_at' => $today->copy()->subDays(5)->setHour(15), 'sort_order' => 1]);
        Task::create(['user_id' => $user->id, 'category_id' => $jobSearch->id, 'title' => 'Apply to 3 remote Laravel positions', 'due_date' => $today->copy()->subDays(5), 'priority' => 'medium', 'status' => 'completed', 'completed_at' => $today->copy()->subDays(5)->setHour(17), 'sort_order' => 2]);

        // 4 days ago
        Task::create(['user_id' => $user->id, 'category_id' => $freelance->id, 'title' => 'Send proposal for e-commerce project', 'due_date' => $today->copy()->subDays(4), 'priority' => 'high', 'status' => 'completed', 'completed_at' => $today->copy()->subDays(4)->setHour(10), 'sort_order' => 0]);
        Task::create(['user_id' => $user->id, 'category_id' => $learning->id, 'title' => 'Complete Laravel Reverb tutorial', 'due_date' => $today->copy()->subDays(4), 'priority' => 'low', 'status' => 'completed', 'completed_at' => $today->copy()->subDays(4)->setHour(14), 'sort_order' => 1]);
        Task::create(['user_id' => $user->id, 'category_id' => $youtube->id, 'title' => 'Script video: Laravel tips for beginners', 'due_date' => $today->copy()->subDays(4), 'priority' => 'medium', 'status' => 'pending', 'sort_order' => 2]);

        // 3 days ago
        Task::create(['user_id' => $user->id, 'category_id' => $work->id, 'title' => 'Deploy staging environment updates', 'due_date' => $today->copy()->subDays(3), 'priority' => 'urgent', 'status' => 'completed', 'completed_at' => $today->copy()->subDays(3)->setHour(9), 'sort_order' => 0]);
        Task::create(['user_id' => $user->id, 'category_id' => $work->id, 'title' => 'Review pull request from junior dev', 'due_date' => $today->copy()->subDays(3), 'priority' => 'medium', 'status' => 'completed', 'completed_at' => $today->copy()->subDays(3)->setHour(11), 'sort_order' => 1]);
        Task::create(['user_id' => $user->id, 'category_id' => $personal->id, 'title' => 'Renew domain registration', 'due_date' => $today->copy()->subDays(3), 'priority' => 'medium', 'status' => 'completed', 'completed_at' => $today->copy()->subDays(3)->setHour(16), 'sort_order' => 2]);
        Task::create(['user_id' => $user->id, 'category_id' => $jobSearch->id, 'title' => 'Follow up on interview feedback', 'due_date' => $today->copy()->subDays(3), 'priority' => 'high', 'status' => 'pending', 'sort_order' => 3]);

        // 2 days ago
        Task::create(['user_id' => $user->id, 'category_id' => $freelance->id, 'title' => 'Implement dashboard charts for client', 'due_date' => $today->copy()->subDays(2), 'priority' => 'high', 'status' => 'completed', 'completed_at' => $today->copy()->subDays(2)->setHour(13), 'sort_order' => 0]);
        Task::create(['user_id' => $user->id, 'category_id' => $learning->id, 'title' => 'Read chapter on DDD patterns', 'due_date' => $today->copy()->subDays(2), 'priority' => 'low', 'status' => 'pending', 'sort_order' => 1]);
        Task::create(['user_id' => $user->id, 'category_id' => $work->id, 'title' => 'Update API documentation', 'due_date' => $today->copy()->subDays(2), 'priority' => 'medium', 'status' => 'completed', 'completed_at' => $today->copy()->subDays(2)->setHour(16), 'sort_order' => 2]);

        // Yesterday
        Task::create(['user_id' => $user->id, 'category_id' => $work->id, 'title' => 'Standup meeting preparation', 'due_date' => $today->copy()->subDay(), 'priority' => 'medium', 'status' => 'completed', 'completed_at' => $today->copy()->subDay()->setHour(9), 'sort_order' => 0]);
        Task::create(['user_id' => $user->id, 'category_id' => $freelance->id, 'title' => 'Fix responsive layout issues on mobile', 'due_date' => $today->copy()->subDay(), 'priority' => 'high', 'status' => 'completed', 'completed_at' => $today->copy()->subDay()->setHour(14), 'sort_order' => 1]);
        Task::create(['user_id' => $user->id, 'category_id' => $youtube->id, 'title' => 'Edit and upload Livewire tutorial video', 'due_date' => $today->copy()->subDay(), 'priority' => 'medium', 'status' => 'pending', 'sort_order' => 2]);
        Task::create(['user_id' => $user->id, 'category_id' => $jobSearch->id, 'title' => 'Customize resume for startup role', 'due_date' => $today->copy()->subDay(), 'priority' => 'high', 'status' => 'completed', 'completed_at' => $today->copy()->subDay()->setHour(17), 'sort_order' => 3]);

        // ─── Tasks — Today (for daily planner + AI prioritization) ──
        Task::create(['user_id' => $user->id, 'category_id' => $work->id, 'title' => 'Refactor user notification system', 'description' => 'Replace the legacy notification handler with Laravel notifications. Update all 3 notification channels.', 'due_date' => $today, 'priority' => 'urgent', 'status' => 'in_progress', 'sort_order' => 0]);
        Task::create(['user_id' => $user->id, 'category_id' => $work->id, 'title' => 'Code review for API v2 endpoints', 'due_date' => $today, 'priority' => 'high', 'status' => 'pending', 'sort_order' => 1]);
        Task::create(['user_id' => $user->id, 'category_id' => $freelance->id, 'title' => 'Send weekly progress report to client', 'due_date' => $today, 'priority' => 'high', 'status' => 'completed', 'completed_at' => $today->copy()->setHour(9), 'sort_order' => 2]);
        Task::create(['user_id' => $user->id, 'category_id' => $jobSearch->id, 'title' => 'Apply to 3 new positions on LinkedIn', 'due_date' => $today, 'priority' => 'medium', 'status' => 'pending', 'sort_order' => 3]);
        Task::create(['user_id' => $user->id, 'category_id' => $youtube->id, 'title' => 'Plan next video: Docker for Laravel devs', 'description' => 'Outline the script, list key points, and prepare code examples for the demo.', 'due_date' => $today, 'priority' => 'medium', 'status' => 'pending', 'sort_order' => 4]);
        Task::create(['user_id' => $user->id, 'category_id' => $learning->id, 'title' => 'Practice system design: URL shortener', 'due_date' => $today, 'priority' => 'low', 'status' => 'pending', 'sort_order' => 5]);
        Task::create(['user_id' => $user->id, 'category_id' => $personal->id, 'title' => 'Update portfolio website with new projects', 'due_date' => $today, 'priority' => 'medium', 'status' => 'pending', 'sort_order' => 6]);

        // ─── Tasks — Future (for calendar view) ──────────────────
        Task::create(['user_id' => $user->id, 'category_id' => $work->id, 'title' => 'Prepare sprint planning presentation', 'due_date' => $today->copy()->addDay(), 'priority' => 'high', 'status' => 'pending', 'sort_order' => 0]);
        Task::create(['user_id' => $user->id, 'category_id' => $freelance->id, 'title' => 'Deploy client project to production', 'due_date' => $today->copy()->addDay(), 'priority' => 'urgent', 'status' => 'pending', 'sort_order' => 1]);

        Task::create(['user_id' => $user->id, 'category_id' => $youtube->id, 'title' => 'Record Docker tutorial video', 'due_date' => $today->copy()->addDays(2), 'priority' => 'medium', 'status' => 'pending', 'sort_order' => 0]);
        Task::create(['user_id' => $user->id, 'category_id' => $learning->id, 'title' => 'Complete AWS certification module 3', 'due_date' => $today->copy()->addDays(3), 'priority' => 'medium', 'status' => 'pending', 'sort_order' => 0]);

        Task::create(['user_id' => $user->id, 'category_id' => $personal->id, 'title' => 'Weekly grocery shopping', 'due_date' => $today->copy()->addDays(4), 'priority' => 'low', 'status' => 'pending', 'sort_order' => 0]);
        Task::create(['user_id' => $user->id, 'category_id' => $jobSearch->id, 'title' => 'Prepare for technical interview at TechCorp', 'description' => 'Review data structures, practice coding problems, and prepare STAR method answers.', 'due_date' => $today->copy()->addDays(5), 'priority' => 'urgent', 'status' => 'pending', 'sort_order' => 0]);

        // ─── Recurring Tasks ─────────────────────────────────────
        RecurringTask::create(['user_id' => $user->id, 'category_id' => $jobSearch->id, 'title' => 'Apply to 3 remote positions', 'description' => 'Search LinkedIn, Indeed, and We Work Remotely for new Laravel/PHP roles.', 'frequency' => 'daily', 'priority' => 'medium', 'is_active' => true, 'last_generated_at' => $today]);
        RecurringTask::create(['user_id' => $user->id, 'category_id' => $youtube->id, 'title' => 'Record YouTube video', 'description' => 'Film, edit, and upload the weekly coding tutorial.', 'frequency' => 'weekly', 'day_of_week' => 6, 'priority' => 'high', 'is_active' => true, 'last_generated_at' => $today->copy()->subWeek()]);
        RecurringTask::create(['user_id' => $user->id, 'category_id' => $freelance->id, 'title' => 'Send freelance invoices', 'description' => 'Generate and send invoices for all active freelance projects.', 'frequency' => 'monthly', 'day_of_month' => 1, 'priority' => 'high', 'is_active' => true, 'last_generated_at' => $today->copy()->startOfMonth()]);
        RecurringTask::create(['user_id' => $user->id, 'category_id' => $work->id, 'title' => 'Review weekly goals', 'description' => 'Check progress on weekly OKRs and adjust priorities.', 'frequency' => 'weekly', 'day_of_week' => 1, 'priority' => 'medium', 'is_active' => true, 'last_generated_at' => $today->copy()->startOfWeek()]);
        RecurringTask::create(['user_id' => $user->id, 'category_id' => $personal->id, 'title' => 'Update portfolio with latest work', 'description' => 'Add new projects, update descriptions, and refresh screenshots.', 'frequency' => 'monthly', 'day_of_month' => 15, 'priority' => 'low', 'is_active' => false]);

        // ─── Weekly Reviews (past 2 weeks) ───────────────────────
        $lastWeekStart = $today->copy()->subWeek()->startOfWeek(Carbon::MONDAY);
        WeeklyReview::create([
            'user_id' => $user->id,
            'week_start' => $lastWeekStart,
            'week_end' => $lastWeekStart->copy()->endOfWeek(Carbon::SUNDAY),
            'total_planned' => 18,
            'total_completed' => 14,
            'total_carried_over' => 4,
            'category_breakdown' => [
                ['name' => 'Work', 'color' => '#3b82f6', 'planned' => 6, 'completed' => 5],
                ['name' => 'Freelance', 'color' => '#22c55e', 'planned' => 4, 'completed' => 3],
                ['name' => 'YouTube', 'color' => '#ef4444', 'planned' => 2, 'completed' => 1],
                ['name' => 'Job Search', 'color' => '#f59e0b', 'planned' => 3, 'completed' => 3],
                ['name' => 'Personal', 'color' => '#7c3aed', 'planned' => 1, 'completed' => 1],
                ['name' => 'Learning', 'color' => '#06b6d4', 'planned' => 2, 'completed' => 1],
            ],
        ]);

        $twoWeeksAgoStart = $today->copy()->subWeeks(2)->startOfWeek(Carbon::MONDAY);
        WeeklyReview::create([
            'user_id' => $user->id,
            'week_start' => $twoWeeksAgoStart,
            'week_end' => $twoWeeksAgoStart->copy()->endOfWeek(Carbon::SUNDAY),
            'total_planned' => 15,
            'total_completed' => 11,
            'total_carried_over' => 4,
            'category_breakdown' => [
                ['name' => 'Work', 'color' => '#3b82f6', 'planned' => 5, 'completed' => 4],
                ['name' => 'Freelance', 'color' => '#22c55e', 'planned' => 3, 'completed' => 2],
                ['name' => 'YouTube', 'color' => '#ef4444', 'planned' => 2, 'completed' => 2],
                ['name' => 'Job Search', 'color' => '#f59e0b', 'planned' => 3, 'completed' => 2],
                ['name' => 'Personal', 'color' => '#7c3aed', 'planned' => 1, 'completed' => 0],
                ['name' => 'Learning', 'color' => '#06b6d4', 'planned' => 1, 'completed' => 1],
            ],
        ]);
    }
}
