# Daily Briefing — Spec

Side: ADMIN

---

## 1. MODULE OVERVIEW

The Daily Briefing is a read-only aggregation dashboard that serves as the first page you see after login. It pulls data from existing modules (Tasks, Email, Job Search, Personal) and presents a morning summary so you can start your day without checking multiple places.

Features:
- Time-based greeting with today's date
- 5 quick stat cards: tasks completed this week, unread emails, new job matches, active goals progress, monthly expenses
- Today's top priority tasks (max 5) with one-click complete
- New job matches since yesterday
- Unread email summary
- Pending recruiter alerts with dismiss action
- Active goals with progress bars

Admin features:
- View aggregated data from Tasks, Email, Job Search, Personal modules
- Complete a task directly from the briefing (completeTask action)
- Dismiss a recruiter alert directly from the briefing (dismissAlert action)

---

## 2. DATABASE SCHEMA

No new tables. This feature is an aggregation dashboard that queries existing tables only:

- `tasks` — today's tasks, weekly completion count
- `emails` — unread/important email stats
- `job_listings` — new matches since yesterday
- `recruiter_alerts` — pending unread alerts
- `goals` — active goals with progress
- `expenses` — current month total
- `job_applications` — weekly application count

---

## 3. FILE MAP

```
MIGRATIONS:
  (none — no new tables)

MODELS:
  (none — reuses existing models)

SERVICES:
  - app/Services/DailyBriefingService.php
    - getGreeting(): string — returns "Good morning/afternoon/evening, Usman" based on current hour
    - getQuickStats(int $userId): array — returns associative array with:
        - tasks_completed_this_week (int) — Task::forUser()->completed()->whereBetween('completed_at', [startOfWeek, now()])->count()
        - unread_emails (int) — from EmailInboxService::getRecentStats()['unread']
        - new_job_matches (int) — JobListing::forUser()->visible()->where('fetched_at', '>=', now()->subDay())->count()
        - active_goals_progress (float) — from GoalService::getStats()['average_progress']
        - month_expenses (float) — from ExpenseService::getMonthTotal(currentYear, currentMonth)
    - getTodayTasks(int $userId): Collection — delegates to TaskService::getTasksForDate() for today, limited to top 5 by priority
    - getNewJobMatches(int $userId): Collection — JobListing::forUser()->visible()->where('fetched_at', '>=', now()->subDay())->latest('fetched_at')->take(5)->get()
    - getEmailSummary(): array — delegates to EmailInboxService::getRecentStats(), returns ['total', 'unread', 'important', 'today']
    - getPendingRecruiterAlerts(): Collection — RecruiterAlert::unread()->undismissed()->with('email')->latest()->take(5)->get()
    - getActiveGoals(): Collection — Goal::where('status', 'active')->orderBy('target_date')->take(5)->get()
    - getJobSearchStats(): array — delegates to ApplicationStatsService::getSummaryStats('7d'), returns ['applied_this_week', etc.]

--- ADMIN FILES ---

LIVEWIRE COMPONENTS:
  - app/Livewire/Admin/Home/DailyBriefing/DailyBriefingIndex.php
    - public properties: $greeting, $quickStats, $todayTasks, $newJobMatches, $emailSummary, $pendingAlerts, $activeGoals, $jobSearchStats
    - mount(): loads all data via DailyBriefingService
    - completeTask(int $taskId): toggles task complete via TaskService::toggleComplete(), then refreshes todayTasks
    - dismissAlert(int $alertId): marks RecruiterAlert as dismissed, refreshes pendingAlerts

  - resources/views/livewire/admin/home/daily-briefing/index.blade.php
    - Full briefing dashboard view (see View Blueprints section)

ROUTES (admin):
  - routes/admin/home/daily-briefing.php
    - GET /admin/home/daily-briefing → DailyBriefingIndex → admin.home.daily-briefing

FILES TO MODIFY:
  - routes/web.php line 21: change redirect from admin.dashboard → admin.home.daily-briefing
    - Before: Route::get('/', fn () => redirect()->route('admin.dashboard'));
    - After:  Route::get('/', fn () => redirect()->route('admin.home.daily-briefing'));
  - resources/views/components/layouts/admin.blade.php: add "Home" collapsible sidebar group above existing groups
    - Home group with "Daily Briefing" as first (and initially only) child link
    - Icon: home/house icon
    - Active detection: request()->routeIs('admin.home.*')
```

---

## 4. COMPONENT CONTRACTS

### Admin Components

```
Component: App\Livewire\Admin\Home\DailyBriefing\DailyBriefingIndex
Namespace: App\Livewire\Admin\Home\DailyBriefing
Layout: #[Layout('components.layouts.admin')]

Properties:
  - $greeting (string) — time-based greeting message
  - $quickStats (array) — keys: tasks_completed_this_week, unread_emails, new_job_matches, active_goals_progress, month_expenses
  - $todayTasks (Collection) — up to 5 tasks for today, ordered by priority
  - $newJobMatches (Collection) — up to 5 job listings fetched since yesterday
  - $emailSummary (array) — keys: total, unread, important, today
  - $pendingAlerts (Collection) — up to 5 unread undismissed recruiter alerts with email relationship
  - $activeGoals (Collection) — up to 5 active goals with progress
  - $jobSearchStats (array) — keys: applied_this_week, etc.

Methods:
  - mount()
    Input: none (uses auth()->id())
    Does:
      1. Calls DailyBriefingService::getGreeting() → sets $greeting
      2. Calls DailyBriefingService::getQuickStats(userId) → sets $quickStats
      3. Calls DailyBriefingService::getTodayTasks(userId) → sets $todayTasks
      4. Calls DailyBriefingService::getNewJobMatches(userId) → sets $newJobMatches
      5. Calls DailyBriefingService::getEmailSummary() → sets $emailSummary
      6. Calls DailyBriefingService::getPendingRecruiterAlerts() → sets $pendingAlerts
      7. Calls DailyBriefingService::getActiveGoals() → sets $activeGoals
      8. Calls DailyBriefingService::getJobSearchStats() → sets $jobSearchStats
    Output: none (sets properties)

  - completeTask(int $taskId)
    Input: task ID
    Does:
      1. Finds Task by ID, verifies belongs to current user
      2. Calls TaskService::toggleComplete($task)
      3. Refreshes $todayTasks via DailyBriefingService::getTodayTasks()
      4. Refreshes $quickStats via DailyBriefingService::getQuickStats()
      5. Flash success message
    Output: updates properties in-place

  - dismissAlert(int $alertId)
    Input: recruiter alert ID
    Does:
      1. Finds RecruiterAlert by ID
      2. Sets dismissed_at = now() and saves
      3. Refreshes $pendingAlerts via DailyBriefingService::getPendingRecruiterAlerts()
      4. Flash success message
    Output: updates $pendingAlerts in-place

Validation Rules:
  (none — this is a read-only dashboard with action buttons, no form inputs)
```

---

## 5. VIEW BLUEPRINTS

### Admin View

```
View: resources/views/livewire/admin/home/daily-briefing/index.blade.php
Layout: components.layouts.admin
Side: ADMIN
Page title: "Daily Briefing"

Design rules (from CLAUDE.md admin side):
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider
  - Stat cards: vary icon bg colors (never all the same)
  - Staggered fade-in animations on page load
  - Breadcrumb: Dashboard > Home > Daily Briefing

Sections:

1. Breadcrumb
   - Dashboard > Home > Daily Briefing (current page highlighted)

2. Page Header
   - Left: greeting text (e.g. "Good morning, Usman") as h1 with font-mono uppercase tracking-wider
   - Subtitle: today's date formatted as "Friday, April 3, 2026"
   - No action button (read-only dashboard)

3. Quick Stats Row (5 cards in grid: grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5 mb-8)
   Card 1: Tasks Done This Week
     - Icon: checkmark circle, bg-emerald-500/10 text-emerald-400
     - Value: $quickStats['tasks_completed_this_week']
     - Label: "Tasks this week"
   Card 2: Unread Emails
     - Icon: envelope, bg-blue-500/10 text-blue-400
     - Value: $quickStats['unread_emails']
     - Label: "Unread emails"
   Card 3: New Job Matches
     - Icon: briefcase, bg-amber-500/10 text-amber-400
     - Value: $quickStats['new_job_matches']
     - Label: "New matches (24h)"
   Card 4: Goals Progress
     - Icon: target/flag, bg-primary/10 text-primary-light
     - Value: $quickStats['active_goals_progress'] . '%'
     - Label: "Avg goal progress"
   Card 5: Monthly Expenses
     - Icon: dollar/currency, bg-fuchsia-500/10 text-fuchsia-400
     - Value: '$' . number_format($quickStats['month_expenses'])
     - Label: "Spent this month"

4. Two-Column Layout (grid grid-cols-1 lg:grid-cols-2 gap-6)

   LEFT COLUMN (space-y-6):

   4a. Today's Tasks Card
     - Card header: "Today's Tasks" with "View All" link to admin.tasks.planner.index
     - List of up to 5 tasks, each row showing:
       - Checkbox button (wire:click="completeTask(taskId)") — filled if completed, empty if pending
       - Task title (text-white if pending, text-gray-500 line-through if completed)
       - Priority badge: high=red, medium=amber, low=emerald
       - Category name as small muted text
     - Empty state: "No tasks for today. Enjoy your free time!" with link to Daily Planner

   4b. New Job Matches Card
     - Card header: "New Job Matches" with "View All" link to admin.job-search.feed.index
     - List of up to 5 job listings, each row showing:
       - Job title (text-white, font-medium)
       - Company name (text-gray-400, text-sm)
       - Platform badge (e.g. "LinkedIn", "Indeed") — bg-blue-500/10 text-blue-400
       - Posted/fetched time as relative (e.g. "2h ago")
     - Empty state: "No new job matches in the last 24 hours."

   RIGHT COLUMN (space-y-6):

   4c. Email Summary Card
     - Card header: "Email Overview" with "View Inbox" link to admin.email.inbox.index
     - 2x2 mini-stat grid inside the card:
       - Total emails today: $emailSummary['today']
       - Unread: $emailSummary['unread']
       - Important: $emailSummary['important']
       - Total: $emailSummary['total']
     - Each mini-stat: icon + value + label, compact layout
     - Empty state: "No email data available. Connect your inbox."

   4d. Recruiter Alerts Card
     - Card header: "Recruiter Alerts" with "View All" link to admin.email.recruiter-alerts.index
     - List of up to 5 alerts, each row showing:
       - Sender name/email from related email (text-white)
       - Subject snippet (text-gray-400, truncated)
       - Received time as relative
       - Dismiss button (wire:click="dismissAlert(alertId)") — small X icon, hover:text-red-400
     - Empty state: "No pending recruiter alerts. Check back later!"

   4e. Active Goals Card
     - Card header: "Active Goals" with "View All" link to admin.personal.goals-tracker.index
     - List of up to 5 goals, each showing:
       - Goal title (text-white)
       - Target date (text-gray-500, text-xs)
       - Progress bar: bg-dark-700 track, bg-gradient-to-r from-primary to-fuchsia-500 fill
       - Progress percentage text (text-xs text-primary-light)
     - Empty state: "No active goals. Set some targets to track your progress!"
```

---

## 6. VALIDATION RULES

No forms — this is a read-only dashboard. The only user actions are:
- `completeTask(taskId)` — no validation needed, task existence and ownership checked in service
- `dismissAlert(alertId)` — no validation needed, alert existence checked before update

---

## 7. EDGE CASES & BUSINESS RULES

1. **Module data unavailability**: If a module has no data (e.g. no tasks, no emails synced, no job listings), show the appropriate empty state for that section. Never throw errors for empty collections.

2. **Greeting logic**: Based on the user's current server time:
   - 5:00 AM - 11:59 AM → "Good morning"
   - 12:00 PM - 4:59 PM → "Good afternoon"
   - 5:00 PM - 4:59 AM → "Good evening"
   - User name comes from auth()->user()->name (first name only if possible)

3. **Task completion**: When a task is marked complete from the briefing, it uses the same TaskService::toggleComplete() used by the Daily Planner. If the task is already completed, clicking again uncompletes it (toggle behavior).

4. **Recruiter alert dismissal**: Sets `dismissed_at` timestamp. The alert still exists in the database but is filtered out from the briefing query via the `undismissed()` scope.

5. **Job matches "since yesterday"**: Uses `fetched_at >= now()->subDay()` (24-hour rolling window), not calendar day boundary.

6. **Expense month total**: Always uses current year and current month. Shows 0.00 if no expenses recorded.

7. **Goals progress**: Shows average progress across all active goals. If no active goals, show 0%.

8. **Redirect change**: The `/admin` route currently redirects to `admin.dashboard`. After this feature, it redirects to `admin.home.daily-briefing` instead. The old Dashboard component and route remain accessible at `/admin/dashboard` — they are not removed.

9. **Sidebar ordering**: The "Home" group should appear FIRST in the sidebar, above Portfolio, Tasks, etc. Daily Briefing is the only item inside Home for now.

10. **Data freshness**: All data is loaded on mount (page load). No polling or auto-refresh. User can manually refresh the page.

---

## 8. IMPLEMENTATION ORDER

```
1. app/Services/DailyBriefingService.php
   - Implement all aggregation methods
   - Inject TaskService, EmailInboxService, ApplicationStatsService, GoalService, ExpenseService

2. routes/admin/home/daily-briefing.php
   - Single GET route with auth middleware

3. app/Livewire/Admin/Home/DailyBriefing/DailyBriefingIndex.php
   - Mount loads all data via DailyBriefingService
   - completeTask and dismissAlert actions

4. resources/views/livewire/admin/home/daily-briefing/index.blade.php
   - Full dashboard view with all sections per View Blueprints

5. resources/views/components/layouts/admin.blade.php (MODIFY)
   - Add "Home" collapsible sidebar group with "Daily Briefing" link
   - Place it as the first group in the sidebar navigation

6. routes/web.php (MODIFY)
   - Line 21: change redirect from admin.dashboard → admin.home.daily-briefing
```
