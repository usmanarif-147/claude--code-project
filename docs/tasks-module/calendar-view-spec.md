# Calendar View — Spec

Side: **ADMIN**

---

## 1. MODULE OVERVIEW

The Calendar View is a read-and-navigate feature within the Tasks module group. It presents existing tasks on a monthly or weekly calendar grid, color-coded by category, so the admin can visually plan ahead, spot busy or free days, and quickly navigate to add or view tasks for any given day. It reuses the existing Task and TaskCategory models and TaskService — no new database tables or services are needed.

### Features
- Monthly calendar grid showing task counts and color-coded dots per day
- Weekly calendar grid showing individual task titles with more detail per day
- Toggle between month and week view modes
- Navigate forward/backward by month or week, plus a "Today" button to jump to current period
- Click on a day cell to navigate to the Daily Planner for that date
- Color-coded task indicators using each TaskCategory's hex color
- Stat summary cards: total tasks this period, completed count, overdue count, busiest day

### Admin Features
- View-only calendar display (tasks are created/edited via Daily Planner, not inline on calendar)
- Click-through to Daily Planner for any date
- Visual density indicators (how many tasks per day)
- Category color legend

---

## 2. DATABASE SCHEMA

No new tables or migrations required. This feature reads from the existing `tasks` and `task_categories` tables defined in the daily-task-planner and task-categories specs.

### Existing Tables Used

```
Table: tasks
Relevant columns:
  - id, user_id, category_id, title, due_date, priority, status, completed_at

Table: task_categories
Relevant columns:
  - id, name, color
```

---

## 3. FILE MAP

```
MIGRATIONS:
  (none — no new tables)

MODELS:
  (none — uses existing app/Models/Task/Task.php and app/Models/Task/TaskCategory.php)

SERVICES:
  (none new — uses existing app/Services/TaskService.php)

  Additional methods needed on TaskService:
    - getTasksForMonth(int $userId, int $year, int $month): Collection
        Returns all tasks for the given user in the given month, eager-loads category relationship.
        Keyed/grouped by due_date (Y-m-d string) for easy calendar cell lookup.
    - getTasksForWeek(int $userId, Carbon $weekStart): Collection
        Returns all tasks for the given user in the 7-day window starting at $weekStart,
        eager-loads category relationship. Grouped by due_date.
    - getCalendarStats(int $userId, Carbon $periodStart, Carbon $periodEnd): array
        Returns ['total' => int, 'completed' => int, 'overdue' => int, 'busiestDay' => string|null]
        Overdue = status != completed AND due_date < today.
        Busiest day = the date with the most tasks in the period.

--- ADMIN FILES ---

LIVEWIRE COMPONENTS:
  - app/Livewire/Admin/Tasks/Calendar/CalendarIndex.php
    - public properties (see Component Contracts)
    - methods (see Component Contracts)

VIEWS:
  - resources/views/livewire/admin/tasks/calendar/index.blade.php
    - Full calendar page: stat cards, month/week toggle, calendar grid, category legend

ROUTES (admin):
  - routes/admin/tasks/calendar.php
    - GET /admin/tasks/calendar → CalendarIndex → admin.tasks.calendar.index
```

---

## 4. COMPONENT CONTRACTS

### Admin Components

```
Component: App\Livewire\Admin\Tasks\Calendar\CalendarIndex
Namespace: App\Livewire\Admin\Tasks\Calendar
Layout: #[Layout('components.layouts.admin')]

Properties:
  - $viewMode (string, default: 'month') #[Url] — 'month' or 'week'
  - $currentYear (int, default: current year) #[Url] — year being viewed
  - $currentMonth (int, default: current month) #[Url] — month being viewed (1-12)
  - $currentWeekStart (string, Y-m-d, default: start of current week) #[Url] — Monday of the week being viewed (only used in week mode)

Methods:
  - mount()
    Input: none
    Does: initializes $currentYear, $currentMonth to today's year/month; sets $currentWeekStart to the Monday of the current week
    Output: none

  - setViewMode(string $mode)
    Input: 'month' or 'week'
    Does: validates mode is one of the two allowed values, sets $viewMode
    Output: calendar re-renders in selected mode

  - previousPeriod()
    Input: none
    Does:
      If month mode: decrements month (wraps year if going below January)
      If week mode: subtracts 7 days from $currentWeekStart
    Output: calendar re-renders for new period

  - nextPeriod()
    Input: none
    Does:
      If month mode: increments month (wraps year if going above December)
      If week mode: adds 7 days to $currentWeekStart
    Output: calendar re-renders for new period

  - goToToday()
    Input: none
    Does: resets $currentYear/$currentMonth to today; resets $currentWeekStart to Monday of current week
    Output: calendar re-renders for current period

  - navigateToDay(string $date)
    Input: date string (Y-m-d)
    Does: redirects to Daily Planner route with the selected date as a query parameter
    Output: redirect to admin.tasks.planner.index with ?selectedDate=$date (navigate: true)

  - render(TaskService $service)
    Does:
      1. Gets auth user ID
      2. Based on $viewMode:
         - month: calls $service->getTasksForMonth(userId, year, month)
         - week: calls $service->getTasksForWeek(userId, Carbon::parse(weekStart))
      3. Computes period start/end dates for stats
      4. Calls $service->getCalendarStats(userId, periodStart, periodEnd)
      5. Fetches all TaskCategory records for the legend
      6. Builds calendar grid data (array of day objects with date, tasks, isToday, isCurrentMonth flags)
    Output: returns view with $calendarDays, $stats, $categories, $periodLabel

Validation Rules:
  (none — this is a read-only view, no form inputs)
```

---

## 5. VIEW BLUEPRINTS

### Admin View

```
View: resources/views/livewire/admin/tasks/calendar/index.blade.php
Layout: components.layouts.admin
Side: ADMIN
Page title: "Calendar"

Design rules (from CLAUDE.md admin side):
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:

1. BREADCRUMB
   Dashboard > Tasks > Calendar

2. PAGE HEADER
   - Left: title "Calendar" with subtitle showing the current period (e.g., "April 2026" or "Mar 30 – Apr 5, 2026")
   - Right: "Today" button (secondary style, navigates to current period)

3. STAT CARDS ROW (4-column grid, same pattern as design system section 5)
   - Card 1: "Total Tasks" — count of tasks in current period — icon bg-primary/10 text-primary-light
   - Card 2: "Completed" — count of completed tasks — icon bg-emerald-500/10 text-emerald-400
   - Card 3: "Overdue" — count of overdue tasks (due_date < today AND not completed) — icon bg-red-500/10 text-red-400
   - Card 4: "Busiest Day" — the date with most tasks, formatted as "Mon, Apr 5" — icon bg-amber-500/10 text-amber-400

4. CALENDAR TOOLBAR (card)
   - Left: Previous period button (left arrow icon)
   - Center: period label ("April 2026" or "Mar 30 – Apr 5, 2026")
   - Right group: Next period button (right arrow icon), view mode toggle (Month | Week segmented control)
   - View mode toggle: two buttons in a bg-dark-700 rounded-lg pill, active button gets bg-primary text-white
   - Styled as a compact card: bg-dark-800 border border-dark-700 rounded-xl px-4 py-3

5. MONTHLY CALENDAR GRID (shown when $viewMode === 'month')
   - Card wrapper: bg-dark-800 border border-dark-700 rounded-xl overflow-hidden
   - Day-of-week header row: Mon, Tue, Wed, Thu, Fri, Sat, Sun
     - Style: text-xs font-mono font-medium text-gray-500 uppercase tracking-wider, bg-dark-700, py-3, text-center
   - 6 rows x 7 columns grid of day cells
   - Each day cell:
     - Min height: h-24 (enough for date number + up to 3 task dots)
     - Top-left: day number (text-sm)
       - Current month days: text-gray-300
       - Other month days (padding): text-gray-600
       - Today: bg-primary text-white w-7 h-7 rounded-full flex items-center justify-center
     - Below date number: up to 3 small color-coded dots/pills showing task titles (truncated)
       - Each dot: 6x6 rounded-full circle with the task category color (or gray if no category)
       - If >3 tasks: show "+N more" in text-xs text-gray-500
     - Hover state: bg-dark-700/30 transition-colors cursor-pointer
     - Click: calls navigateToDay(date) via wire:click
     - Border: border-b border-r border-dark-700/50 (grid lines)

6. WEEKLY CALENDAR GRID (shown when $viewMode === 'week')
   - Card wrapper: bg-dark-800 border border-dark-700 rounded-xl overflow-hidden
   - 7 column layout (Mon through Sun), each column is one day
   - Column header: day name + date number (e.g., "Mon 30")
     - Style: text-xs font-mono font-medium text-gray-500 uppercase tracking-wider, bg-dark-700 py-3 text-center
     - Today column header: text-primary-light with subtle bg-primary/5
   - Each column body: vertical stack of task items, min-height: h-64
   - Each task item in column:
     - Small card: rounded-lg px-2 py-1.5 mb-1 text-xs cursor-pointer
     - Left border: 3px solid with category color (or primary if no category)
     - Background: category color at 10% opacity (e.g., bg-[color]/10)
     - Shows: task title (truncated, 1 line), priority dot
     - Completed tasks: line-through text, opacity-50
     - Click: calls navigateToDay(date) via wire:click
   - Empty column: shows subtle dashed border area with "No tasks" in text-xs text-gray-600
   - Columns separated by border-r border-dark-700/50

7. CATEGORY LEGEND (below the calendar grid)
   - Horizontal row of category indicators
   - Each: colored circle (w-3 h-3 rounded-full) + category name (text-xs text-gray-400)
   - Plus a gray circle for "Uncategorized"
   - Style: flex flex-wrap items-center gap-4 mt-4 px-2

Alpine.js interactions:
  - Entire calendar grid rendering is server-driven via Livewire (not client-side JS calendar library)
  - Alpine used for:
    - Tooltip on hover over day cells showing task count summary (x-data, @mouseenter/@mouseleave)
    - Smooth transitions when switching view modes (x-show with x-transition)
    - Page load stagger animations on stat cards (per design system section 15)
```

---

## 6. VALIDATION RULES

No form inputs on this page. This is a read-only calendar view. All task CRUD happens via the Daily Planner.

The only inputs validated are navigation parameters:
```
  - viewMode: in:month,week (enforced in setViewMode method, not form validation)
  - currentYear: integer, reasonable range (e.g., 2020-2099)
  - currentMonth: integer, 1-12
  - currentWeekStart: valid date string (Y-m-d format)
```

These are validated programmatically in the component methods, not via Livewire #[Validate] rules.

---

## 7. EDGE CASES & BUSINESS RULES

1. **Task ownership**: All queries MUST filter by `user_id = auth()->id()`. A user can only see their own tasks on the calendar.

2. **Month grid padding days**: The monthly grid always shows 6 rows (42 cells). Days from the previous and next month are shown with muted styling. These padding day cells are still clickable — clicking navigates to the Daily Planner for that date (and auto-switches the calendar period if needed).

3. **Today highlight**: Today's date always receives a distinctive visual indicator (purple circle behind the day number) regardless of which month/week is being viewed. If today is not in the current view period, no special highlight is shown.

4. **Empty days**: Days with no tasks show an empty cell (month view) or a subtle "No tasks" placeholder (week view). They are still clickable to navigate to the Daily Planner.

5. **Overdue calculation**: A task is overdue when `status != 'completed'` AND `due_date < Carbon::today()`. The overdue stat only counts tasks within the currently viewed period.

6. **Busiest day**: If multiple days tie for the most tasks, show the earliest date. If no tasks exist in the period, show "—" or "None" instead of a date.

7. **Category colors**: Task dots/indicators use the category's `color` hex value directly (via inline style). Tasks with no category (null category_id) use a default gray (#6b7280).

8. **Week start day**: Weeks start on Monday (ISO standard). The `$currentWeekStart` always points to a Monday.

9. **View mode persistence**: The `$viewMode`, `$currentYear`, `$currentMonth`, and `$currentWeekStart` are all #[Url] properties so the view survives page refreshes and browser back/forward navigation.

10. **Large task counts**: In month view, only show up to 3 task indicator dots per day cell. If more tasks exist, show a "+N more" label. In week view, show all tasks (they stack vertically and the column scrolls if needed with overflow-y-auto).

11. **No drag-and-drop**: The plan mentions drag to reschedule, but this spec intentionally omits it for the first implementation. Rescheduling is done via the Daily Planner. Drag-and-drop can be added as a future enhancement.

12. **Navigation to Daily Planner**: Clicking any day cell redirects to the Daily Planner route (`admin.tasks.planner.index`) with the clicked date passed as a `selectedDate` query parameter. This leverages the existing DailyPlannerIndex component's `$selectedDate` #[Url] property.

13. **Period label format**:
    - Month view: "April 2026" (month name + year)
    - Week view: "Mar 30 – Apr 5, 2026" (start – end of week, with year)

14. **No public side**: This feature is admin-only. No public display.

---

## 8. IMPLEMENTATION ORDER

```
1. app/Services/TaskService.php — add getTasksForMonth(), getTasksForWeek(), getCalendarStats() methods
2. routes/admin/tasks/calendar.php — single GET route
3. app/Livewire/Admin/Tasks/Calendar/CalendarIndex.php — component with all properties and methods
4. resources/views/livewire/admin/tasks/calendar/index.blade.php — full calendar view
5. Update sidebar navigation in components/layouts/admin.blade.php — add "Calendar" link under Tasks module group
```

> **Dependency note**: This feature depends on:
> - `task_categories` table and `TaskCategory` model (from task-categories spec)
> - `tasks` table and `Task` model (from daily-task-planner spec)
> - `TaskService` class (from daily-task-planner spec)
> - `DailyPlannerIndex` component (for click-through navigation)
>
> All four must be implemented before this feature. The recommended implementation sequence is: task-categories -> daily-task-planner -> calendar-view.
