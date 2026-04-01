# Weekly Review Summary — Spec

Side: **ADMIN**

---

## 1. MODULE OVERVIEW

The Weekly Review Summary provides a read-only analytics dashboard within the Tasks module that aggregates task completion data for a given week and optionally generates AI-powered insights. It shows what was accomplished, what was missed, which categories received the most attention, and how the current week compares to the previous one. When an AI API key (Claude or OpenAI) is configured, the service generates focus-area suggestions for the following week.

### Features
- View total tasks completed vs total tasks planned for a selected week
- List tasks that were not completed (carried over or still pending)
- Show category breakdown: which categories got the most tasks completed
- Compare current week stats with the previous week (trend indicators)
- AI-generated insights: focus areas for next week, productivity observations
- Week selector to navigate and view past weeks' reviews
- Cache/store generated reviews so AI is not re-called on every page load
- Graceful degradation: show all stats without AI insights when no API key is configured

### Admin Features
- View weekly review dashboard with stat cards, category breakdown, and AI summary
- Navigate between weeks using a week picker
- Manually trigger AI summary regeneration for the current week
- View incomplete tasks list with priority and category context

---

## 2. DATABASE SCHEMA

```
Table: weekly_reviews
Columns:
  - id                  (bigint, primary key, auto increment)
  - user_id             (bigint, unsigned, required, FK -> users.id)
  - week_start          (date, required) — Monday of the review week
  - week_end            (date, required) — Sunday of the review week
  - total_planned       (integer, unsigned, default 0) — total tasks that existed for the week
  - total_completed     (integer, unsigned, default 0) — tasks marked completed during the week
  - total_carried_over  (integer, unsigned, default 0) — tasks not completed that were moved forward
  - category_breakdown  (json, nullable) — [{category_id, category_name, color, planned, completed}]
  - ai_summary          (text, nullable) — AI-generated weekly summary text
  - ai_focus_areas      (json, nullable) — ["Focus area 1", "Focus area 2", ...]
  - ai_generated_at     (timestamp, nullable) — when AI summary was last generated
  - created_at          (timestamp)
  - updated_at          (timestamp)

Indexes:
  - unique index on (user_id, week_start) — one review per user per week
  - index on user_id
  - index on week_start

Foreign keys:
  - user_id references users(id) on delete cascade
```

> Note: This table caches computed stats + AI output so they are not recalculated on every page load. Stats are recomputed when the user visits a week or triggers a regeneration. The `weekly_reviews` table lives alongside the existing `tasks` and `task_categories` tables.

---

## 3. FILE MAP

```
MIGRATIONS:
  - database/migrations/YYYY_MM_DD_XXXXXX_create_weekly_reviews_table.php

MODELS:
  - app/Models/Task/WeeklyReview.php
    - namespace: App\Models\Task
    - fillable: user_id, week_start, week_end, total_planned, total_completed,
                total_carried_over, category_breakdown, ai_summary, ai_focus_areas, ai_generated_at
    - casts:
        - week_start -> date
        - week_end -> date
        - category_breakdown -> array
        - ai_focus_areas -> array
        - ai_generated_at -> datetime
        - total_planned -> integer
        - total_completed -> integer
        - total_carried_over -> integer
    - relationships:
        - user(): belongsTo(User::class)
    - scopes:
        - scopeForUser(Builder $query, int $userId): filters by user_id
        - scopeForWeek(Builder $query, Carbon $weekStart): filters by week_start
    - accessors:
        - getCompletionPercentageAttribute(): int — returns (total_completed / total_planned) * 100, or 0 if no tasks
        - getHasAiSummaryAttribute(): bool — returns ai_summary !== null

SERVICES:
  - app/Services/WeeklyReviewService.php
    - getOrCreateReview(int $userId, Carbon $weekStart): WeeklyReview
        — finds existing review for user+week or creates a new one, computes stats from tasks table
    - computeWeekStats(int $userId, Carbon $weekStart, Carbon $weekEnd): array
        — queries tasks table to compute total_planned, total_completed, total_carried_over
    - computeCategoryBreakdown(int $userId, Carbon $weekStart, Carbon $weekEnd): array
        — groups task stats by category, returns [{category_id, category_name, color, planned, completed}]
    - getIncompleteTasks(int $userId, Carbon $weekStart, Carbon $weekEnd): Collection
        — returns tasks with status != completed for the week, with category eager-loaded
    - getPreviousWeekReview(int $userId, Carbon $currentWeekStart): ?WeeklyReview
        — returns the review for the week before, or null if none exists
    - computeWeekComparison(WeeklyReview $current, ?WeeklyReview $previous): array
        — returns ['completion_trend' => int, 'planned_trend' => int, 'carried_over_trend' => int]
          each trend is a signed integer showing change from previous week (e.g., +12, -3)
    - generateAiSummary(WeeklyReview $review, array $incompleteTasks, array $categoryBreakdown, ?array $comparison): ?string
        — calls AI API (Claude or OpenAI) using key from api_keys table
        — builds prompt with week stats, category data, incomplete tasks, comparison
        — returns generated summary text, or null if no API key configured
    - generateAiFocusAreas(WeeklyReview $review, array $incompleteTasks, array $categoryBreakdown): ?array
        — calls AI API to suggest 3-5 focus areas for next week
        — returns array of strings, or null if no API key configured
    - getAiApiKey(int $userId): ?string
        — checks api_keys table for a connected Claude or OpenAI key for the user
        — prefers Claude, falls back to OpenAI
        — returns decrypted key value, or null if none configured
    - refreshReview(int $userId, Carbon $weekStart): WeeklyReview
        — recomputes all stats from tasks table and regenerates AI summary
        — updates the existing weekly_reviews row

--- ADMIN FILES ---

LIVEWIRE COMPONENTS:
  - app/Livewire/Admin/Tasks/WeeklyReview/WeeklyReviewIndex.php
    - public properties (see Component Contracts below)
    - methods (see Component Contracts below)

VIEWS:
  - resources/views/livewire/admin/tasks/weekly-review/index.blade.php
    - Weekly review dashboard: stat cards, category chart, incomplete tasks, AI insights

ROUTES (admin):
  - routes/admin/tasks/weekly-review.php
    - GET /admin/tasks/weekly-review -> WeeklyReviewIndex -> admin.tasks.weekly-review.index
```

---

## 4. COMPONENT CONTRACTS

### Admin Components

```
Component: App\Livewire\Admin\Tasks\WeeklyReview\WeeklyReviewIndex
Namespace: App\Livewire\Admin\Tasks\WeeklyReview
Layout:    #[Layout('components.layouts.admin')]

Properties:
  - $weekStart (string, Y-m-d format, default: Monday of current week) #[Url]
    — the Monday date of the selected review week
  - $isGenerating (bool, default: false)
    — true while AI summary is being generated (loading state)

Methods:
  - mount()
    Input: none
    Does: if $weekStart is empty, sets it to the Monday of the current ISO week

  - previousWeek()
    Input: none
    Does: sets $weekStart to $weekStart - 7 days
    Output: view refreshes for previous week

  - nextWeek()
    Input: none
    Does: sets $weekStart to $weekStart + 7 days (capped at current week's Monday)
    Output: view refreshes for next week

  - goToCurrentWeek()
    Input: none
    Does: sets $weekStart to Monday of current week
    Output: view refreshes for current week

  - regenerateSummary(WeeklyReviewService $service)
    Input: none
    Does:
      1. Sets $isGenerating = true
      2. Calls $service->refreshReview(auth()->id(), Carbon::parse($weekStart))
      3. Sets $isGenerating = false
      4. Flashes success message ("Weekly review regenerated") or error if AI fails
    Output: review data refreshes with new AI summary

  - render(WeeklyReviewService $service)
    Input: none
    Does:
      1. Parses $weekStart as Carbon, computes $weekEnd (Sunday)
      2. Calls $service->getOrCreateReview(auth()->id(), $weekStart)
      3. Calls $service->getIncompleteTasks(auth()->id(), $weekStart, $weekEnd)
      4. Calls $service->getPreviousWeekReview(auth()->id(), $weekStart)
      5. Calls $service->computeWeekComparison($review, $previousReview)
      6. Checks if AI API key is available via $service->getAiApiKey(auth()->id())
    Output: returns view with $review, $incompleteTasks, $comparison, $hasApiKey
```

---

## 5. VIEW BLUEPRINTS

### Admin View

```
View: resources/views/livewire/admin/tasks/weekly-review/index.blade.php
Layout: components.layouts.admin
Side: ADMIN
Page title: "Weekly Review"

Design rules (from CLAUDE.md admin side):
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:

1. BREADCRUMB
   Dashboard > Tasks > Weekly Review

2. PAGE HEADER
   - Left: title "Weekly Review" with subtitle showing the week range
     (e.g., "Mar 30 - Apr 5, 2026")
   - Right: "Regenerate Summary" button (secondary style with refresh icon,
     only shown when AI API key is configured, wire:loading state while generating)

3. WEEK NAVIGATION BAR (card)
   - Left arrow button (previous week)
   - Week display: "Week of {{ weekStart formatted }}" with date range below
   - "This Week" button to jump to current week
   - Right arrow button (next week, disabled if already on current week)
   - Styled as compact card row: bg-dark-800 border border-dark-700 rounded-xl

4. STAT CARDS ROW (4 columns grid)
   - Card 1: "Tasks Planned" — total_planned value, icon bg-blue-500/10,
     trend badge showing change from previous week (e.g., "+5" or "-2")
   - Card 2: "Tasks Completed" — total_completed value, icon bg-emerald-500/10,
     trend badge showing change from previous week
   - Card 3: "Completion Rate" — completion_percentage with % suffix, icon bg-primary/10,
     trend badge showing percentage point change
   - Card 4: "Carried Over" — total_carried_over value, icon bg-amber-500/10,
     trend badge (lower is better, so negative change = green, positive = red)

   Trend badge colors:
     - positive improvement: bg-emerald-500/10 text-emerald-400
     - negative change: bg-red-500/10 text-red-400
     - no change: bg-gray-500/10 text-gray-400
     - "Carried Over" card inverts: fewer carried over = green, more = red

5. CATEGORY BREAKDOWN (card)
   - Card heading: "Category Breakdown"
   - For each category with tasks during the week:
     - Row: color swatch circle, category name, "X of Y completed" text
     - Progress bar: bg-dark-700 track, colored fill using the category's hex color
     - Percentage label on the right
   - Categories sorted by total planned (descending — most active first)
   - If no categories have tasks: "No categorized tasks this week"

6. INCOMPLETE TASKS LIST (card)
   - Card heading: "Incomplete Tasks" with count badge
   - Table or list of tasks not completed during the week:
     Columns/fields per row:
       - Task title (text-white)
       - Priority badge (color-coded: urgent=red, high=amber, medium=blue, low=gray)
       - Category badge (if assigned, using category color)
       - Due date (text-gray-500)
   - If no incomplete tasks: success state "All tasks completed this week!" with
     emerald icon and encouraging text
   - Limit display to 20 items, with note "and X more..." if exceeds

7. AI INSIGHTS SECTION (card, conditional)
   - Only shown when AI API key is configured ($hasApiKey = true)
   - Card has gradient accent border: border-primary/30 (premium feel)
   - Card heading: "AI Insights" with sparkle/brain icon
   - Sub-section 1: "Weekly Summary" — rendered AI summary text (ai_summary field),
     displayed as formatted paragraphs in text-gray-300
   - Sub-section 2: "Focus Areas for Next Week" — ai_focus_areas rendered as
     a numbered list, each item with a target/arrow icon and text-gray-300
   - If AI has not been generated yet (ai_summary is null): show a CTA button
     "Generate AI Insights" (primary style) that calls regenerateSummary
   - Timestamp: "Generated {{ ai_generated_at->diffForHumans() }}" in text-gray-500

8. NO API KEY NOTICE (conditional, replaces section 7 when no key)
   - Subtle info card: bg-dark-800 border border-dark-700
   - Info icon (blue), text: "Connect a Claude or OpenAI API key in Settings to
     enable AI-powered weekly insights."
   - Link to settings/API keys page if it exists

9. WEEK COMPARISON CARD (card)
   - Card heading: "vs Previous Week"
   - Side-by-side or row comparison:
     - "This Week: X completed of Y planned (Z%)"
     - "Last Week: X completed of Y planned (Z%)"
   - Visual: two horizontal bars comparing completion rates
   - If no previous week data: "No data for previous week"

10. EMPTY STATE (when no tasks exist for the selected week at all)
    - Icon, "No tasks were planned for this week" message
    - Suggestion text: "Tasks added via the Daily Planner will appear in weekly reviews."
```

---

## 6. VALIDATION RULES

This feature has no user-editable forms. All data is computed from the tasks table. The only user interaction is navigation (week selection) and triggering AI generation.

```
Week navigation (implicit):
  - weekStart: must be a valid date string in Y-m-d format, must be a Monday
    (enforced in mount/setter — if not a Monday, snap to the Monday of that week)

No form validation rules needed.
```

---

## 7. EDGE CASES & BUSINESS RULES

1. **Task ownership**: All queries MUST filter by `user_id = auth()->id()`. A user can only see their own weekly review data.

2. **Week boundaries**: A "week" is defined as Monday 00:00:00 through Sunday 23:59:59 (ISO week). Tasks are included if their `due_date` falls within this range. The `week_start` column always stores the Monday date.

3. **Stats computation**:
   - `total_planned` = count of all tasks with `due_date` between week_start and week_end for the user
   - `total_completed` = count of tasks where `status = 'completed'` within that date range
   - `total_carried_over` = count of tasks where `status != 'completed'` within that date range
   - These are recomputed from the tasks table each time `getOrCreateReview` or `refreshReview` is called, then cached in the weekly_reviews row

4. **Category breakdown**:
   - Tasks with `category_id = null` are grouped under "Uncategorized" with a default gray color (#6b7280)
   - Category names and colors are read from the task_categories table at computation time and stored in the JSON column (so they survive if the category is later renamed/deleted)

5. **AI summary generation**:
   - Only attempted if `getAiApiKey()` returns a non-null value
   - Prefers Claude API key (provider = 'claude'), falls back to OpenAI (provider = 'openai')
   - API key must have `is_connected = true` in the api_keys table
   - If API call fails (network error, rate limit, invalid key), catch the exception, flash an error message, and leave ai_summary as null — never crash the page
   - AI summary is stored in the weekly_reviews row and only regenerated on explicit user action ("Regenerate Summary" button) or first visit to a week

6. **First visit to a week**:
   - `getOrCreateReview` checks if a row exists for (user_id, week_start)
   - If not, creates one, computes stats, and attempts AI generation
   - If yes, returns the existing row (stats are not recomputed automatically — user must click "Regenerate" to refresh)

7. **Future weeks**:
   - The week selector does not allow navigating beyond the current week's Monday
   - `nextWeek()` is capped: if $weekStart + 7 days > current Monday, do nothing

8. **Week with zero tasks**:
   - total_planned = 0, total_completed = 0, completion_percentage = 0
   - Show empty state instead of stat cards with all zeros
   - AI summary is not generated for weeks with zero tasks

9. **Previous week comparison**:
   - If no previous week review exists (first week of using the app), show "No previous week data" instead of trend badges
   - Trend values are computed as: current_value - previous_value
   - For "Carried Over" card, trend display is inverted: fewer carried over = positive (green), more = negative (red)

10. **No soft deletes**: WeeklyReview rows use hard delete if ever removed. They are essentially a cache and can be regenerated.

11. **Concurrent access**: Use `updateOrCreate` with the unique constraint on (user_id, week_start) to avoid duplicate rows.

12. **AI prompt construction**: The prompt sent to the AI should include:
    - Total planned, completed, and carried over counts
    - Category breakdown with completion rates
    - List of incomplete task titles (truncated to 20 items max to stay within token limits)
    - Previous week comparison data (if available)
    - Instruction to provide a concise 2-3 paragraph summary and 3-5 actionable focus areas

---

## 8. IMPLEMENTATION ORDER

```
1. database/migrations/YYYY_MM_DD_XXXXXX_create_weekly_reviews_table.php
2. app/Models/Task/WeeklyReview.php
3. app/Services/WeeklyReviewService.php
4. routes/admin/tasks/weekly-review.php
5. app/Livewire/Admin/Tasks/WeeklyReview/WeeklyReviewIndex.php
6. resources/views/livewire/admin/tasks/weekly-review/index.blade.php
7. Update sidebar navigation in components/layouts/admin.blade.php
   (add "Weekly Review" link under Tasks module group)
```

> **Dependency note**: This feature depends on:
> - `task_categories` table and `TaskCategory` model (from task-categories spec)
> - `tasks` table and `Task` model (from daily-task-planner spec)
> - `api_keys` table and `ApiKey` model (already exists)
>
> Both the task-categories and daily-task-planner features must be implemented first. The weekly review has no meaning without task data to aggregate.
