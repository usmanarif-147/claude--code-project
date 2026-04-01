# Daily Task Planner — Spec

Side: **ADMIN**

---

## 1. MODULE OVERVIEW

The Daily Task Planner is the core task management view within the Tasks module group. It provides a focused, single-page interface for managing today's tasks: viewing them in a prioritized list, adding new tasks inline, marking them complete with one click, moving incomplete tasks to tomorrow, and tracking daily progress via a completion bar.

### Features
- View today's tasks in a prioritized, sortable list (urgent > high > medium > low)
- Add a new task quickly via an inline form (no separate page navigation)
- Mark tasks as complete/incomplete with one click (toggles status + sets completed_at)
- Move all incomplete tasks to tomorrow with one action
- Progress bar showing daily completion (e.g., "4 of 7 tasks done")
- Filter tasks by status (all / pending / in_progress / completed)
- Filter tasks by priority (all / urgent / high / medium / low)
- Filter tasks by category (from TaskCategory model)

### Admin Features
- Full CRUD on tasks (create inline, edit inline or via modal, delete with confirmation)
- Bulk action: move incomplete tasks to tomorrow
- Visual priority indicators (color-coded badges)
- Real-time progress tracking

---

## 2. DATABASE SCHEMA

```
Table: tasks
Columns:
  - id (bigint, primary key, auto increment)
  - user_id (bigint, unsigned, required, FK -> users.id)
  - category_id (bigint, unsigned, nullable, FK -> task_categories.id)
  - title (string 255, required)
  - description (text, nullable)
  - due_date (date, required)
  - priority (enum: 'low','medium','high','urgent', required, default: 'medium')
  - status (enum: 'pending','in_progress','completed', required, default: 'pending')
  - completed_at (timestamp, nullable)
  - sort_order (integer, unsigned, default: 0)
  - created_at, updated_at (timestamps)

Indexes:
  - index on user_id
  - index on category_id
  - index on due_date
  - index on (user_id, due_date) — composite for daily queries
  - index on priority
  - index on status

Foreign keys:
  - user_id references users(id) on delete cascade
  - category_id references task_categories(id) on delete set null
```

> Note: The `task_categories` table is created by the task-categories feature (separate spec). This spec assumes that migration runs first or that `category_id` is nullable to allow independent deployment.

---

## 3. FILE MAP

```
MIGRATIONS:
  - database/migrations/YYYY_MM_DD_XXXXXX_create_tasks_table.php

MODELS:
  - app/Models/Task/Task.php
    - fillable: title, description, category_id, due_date, priority, status, completed_at, sort_order, user_id
    - relationships:
      - user(): belongsTo(User::class)
      - category(): belongsTo(TaskCategory::class, 'category_id')
    - casts:
      - due_date -> date
      - completed_at -> datetime
      - sort_order -> integer
    - scopes:
      - scopeForUser(Builder $query, int $userId): filters by user_id
      - scopeForDate(Builder $query, Carbon $date): filters by due_date
      - scopeForToday(Builder $query): filters by due_date = today
      - scopeOrdered(Builder $query): orders by sort_order asc
      - scopeByPriority(Builder $query): orders by priority (urgent first: urgent > high > medium > low)
      - scopePending(Builder $query): where status != 'completed'
      - scopeCompleted(Builder $query): where status = 'completed'

SERVICES:
  - app/Services/TaskService.php
    - create(array $data): Task — creates a new task, auto-assigns auth user_id
    - update(Task $task, array $data): Task — updates task fields
    - delete(Task $task): void — deletes a task
    - toggleComplete(Task $task): Task — toggles between completed/pending, sets/clears completed_at
    - markComplete(Task $task): Task — sets status=completed, completed_at=now
    - markPending(Task $task): Task — sets status=pending, completed_at=null
    - moveIncompleteTo(int $userId, Carbon $fromDate, Carbon $toDate): int — moves all non-completed tasks from fromDate to toDate, returns count moved
    - getTasksForDate(int $userId, Carbon $date): Collection — returns tasks for a specific date, ordered by priority then sort_order
    - getCompletionStats(int $userId, Carbon $date): array — returns ['total' => int, 'completed' => int, 'percentage' => int]

--- ADMIN FILES ---

LIVEWIRE COMPONENTS:
  - app/Livewire/Admin/Tasks/DailyPlanner/DailyPlannerIndex.php
    - public properties (see Component Contracts below)
    - methods (see Component Contracts below)

VIEWS:
  - resources/views/livewire/admin/tasks/daily-planner/index.blade.php
    - Main daily planner view: progress bar, inline add form, task list, filters

ROUTES (admin):
  - routes/admin/tasks/daily-planner.php
    - GET /admin/tasks/planner -> DailyPlannerIndex -> admin.tasks.planner.index
```

---

## 4. COMPONENT CONTRACTS

### Admin Components

```
Component: App\Livewire\Admin\Tasks\DailyPlanner\DailyPlannerIndex
Namespace: App\Livewire\Admin\Tasks\DailyPlanner
Layout: #[Layout('components.layouts.admin')]

Properties:
  - $selectedDate (string, Y-m-d format, default: today) #[Url] — the date being viewed
  - $statusFilter (string, default: 'all') #[Url] — filter: all / pending / in_progress / completed
  - $priorityFilter (string, default: 'all') #[Url] — filter: all / urgent / high / medium / low
  - $categoryFilter (string, default: 'all') #[Url] — filter by category_id or 'all'
  - $newTaskTitle (string, default: '') — inline form: task title
  - $newTaskPriority (string, default: 'medium') — inline form: priority select
  - $newTaskCategoryId (string, default: '') — inline form: category select
  - $editingTaskId (int|null, default: null) — ID of task currently being edited inline
  - $editTitle (string, default: '') — inline edit: title
  - $editPriority (string, default: 'medium') — inline edit: priority
  - $editCategoryId (string, default: '') — inline edit: category

Methods:
  - addTask(TaskService $service)
    Input: reads $newTaskTitle, $newTaskPriority, $newTaskCategoryId
    Does: validates title required|string|max:255, creates task with due_date=$selectedDate, user_id=auth, resets inline form fields
    Output: flash success message

  - toggleComplete(TaskService $service, int $taskId)
    Input: task ID
    Does: finds task, calls service toggleComplete, no redirect
    Output: task status toggles in-place

  - startEditing(int $taskId)
    Input: task ID
    Does: sets $editingTaskId, populates $editTitle, $editPriority, $editCategoryId from task
    Output: inline edit form appears for that task row

  - saveEdit(TaskService $service)
    Input: reads $editingTaskId, $editTitle, $editPriority, $editCategoryId
    Does: validates title required|string|max:255, updates task, resets editing state
    Output: flash success message

  - cancelEdit()
    Input: none
    Does: resets $editingTaskId and edit fields to defaults
    Output: inline edit form closes

  - deleteTask(TaskService $service, int $taskId)
    Input: task ID
    Does: finds task, calls service delete
    Output: flash success message

  - moveIncompleteToTomorrow(TaskService $service)
    Input: none
    Does: calls service moveIncompleteTo(auth id, selectedDate, selectedDate + 1 day)
    Output: flash success message with count moved

  - goToDate(string $date)
    Input: date string (Y-m-d)
    Does: sets $selectedDate to given date
    Output: task list refreshes for new date

  - goToToday()
    Input: none
    Does: sets $selectedDate to today
    Output: task list refreshes for today

  - goToPreviousDay()
    Input: none
    Does: sets $selectedDate to selectedDate - 1 day
    Output: task list refreshes

  - goToNextDay()
    Input: none
    Does: sets $selectedDate to selectedDate + 1 day
    Output: task list refreshes

  - render()
    Does: queries tasks for selectedDate + auth user, applies filters, gets completion stats, gets categories list
    Output: returns view with $tasks, $stats, $categories
```

---

## 5. VIEW BLUEPRINTS

### Admin View

```
View: resources/views/livewire/admin/tasks/daily-planner/index.blade.php
Layout: components.layouts.admin
Side: ADMIN
Page title: "Daily Planner"

Design rules (from CLAUDE.md admin side):
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:

1. BREADCRUMB
   Dashboard > Tasks > Daily Planner

2. PAGE HEADER
   - Left: title "Daily Planner" with subtitle showing the selected date formatted (e.g., "Tuesday, April 1, 2026")
   - Right: "Move Incomplete to Tomorrow" button (secondary style, only shown when there are incomplete tasks)

3. DATE NAVIGATION BAR (card)
   - Left arrow button (previous day)
   - Date display (selected date, formatted) with "Today" button to jump back
   - Right arrow button (next day)
   - Styled as a compact card row: bg-dark-800 border border-dark-700 rounded-xl

4. PROGRESS SECTION (card)
   - Stat row: "X of Y tasks completed" text
   - Full-width progress bar: bg-dark-700 track, bg-gradient-to-r from-primary to-fuchsia-500 fill
   - Percentage label on the right
   - When all tasks done: show success color (emerald) and congratulatory text

5. FILTER BAR (card)
   - Status filter: select (all / pending / in_progress / completed)
   - Priority filter: select (all / urgent / high / medium / low)
   - Category filter: select (all / each category name)
   - Inline within a single row, same pattern as design system filter bar

6. INLINE ADD TASK FORM (card)
   - Single row with: text input (title), priority select, category select, "Add" button
   - All in one horizontal line within a card
   - Input uses standard design system input styles
   - Button is primary style with plus icon
   - Validation error shows below input if title is empty

7. TASK LIST (card)
   - Each task is a row within the card
   - Row layout:
     - Checkbox circle (click to toggle complete): completed tasks show filled emerald circle with checkmark
     - Task title: normal weight for pending, line-through + text-gray-500 for completed
     - Priority badge: color-coded (urgent=red, high=amber, medium=blue, low=gray)
     - Category badge (if assigned): subtle colored badge
     - Action buttons: edit (pencil icon), delete (trash icon with wire:confirm)
   - Completed tasks appear at the bottom of the list (or visually muted)
   - When a task row is being edited ($editingTaskId matches): show inline edit fields instead of display
   - Rows have hover:bg-dark-700/30 transition

8. INLINE EDIT ROW (replaces task row when editing)
   - Text input (title), priority select, category select
   - Save button (primary), Cancel button (secondary)

9. EMPTY STATE
   - When no tasks for selected date
   - Icon, "No tasks for this day" message
   - "Add your first task" prompt pointing to the inline form above

Priority badge colors:
  - urgent: bg-red-500/10 text-red-400
  - high: bg-amber-500/10 text-amber-400
  - medium: bg-blue-500/10 text-blue-400
  - low: bg-gray-500/10 text-gray-400

Status badge colors:
  - pending: bg-amber-500/10 text-amber-400
  - in_progress: bg-blue-500/10 text-blue-400
  - completed: bg-emerald-500/10 text-emerald-400
```

---

## 6. VALIDATION RULES

```
Inline Add Task Form:
  - newTaskTitle: required|string|max:255
  - newTaskPriority: required|in:low,medium,high,urgent
  - newTaskCategoryId: nullable|exists:task_categories,id

Inline Edit Task Form:
  - editTitle: required|string|max:255
  - editPriority: required|in:low,medium,high,urgent
  - editCategoryId: nullable|exists:task_categories,id

Full Task (service-level, for future use):
  - title: required|string|max:255
  - description: nullable|string|max:5000
  - category_id: nullable|exists:task_categories,id
  - due_date: required|date
  - priority: required|in:low,medium,high,urgent
  - status: required|in:pending,in_progress,completed
  - sort_order: integer|min:0
```

---

## 7. EDGE CASES & BUSINESS RULES

1. **Task ownership**: All queries MUST filter by `user_id = auth()->id()`. A user can only see/edit/delete their own tasks.

2. **Toggle complete logic**:
   - If status is `pending` or `in_progress` -> set to `completed`, set `completed_at = now()`
   - If status is `completed` -> set to `pending`, set `completed_at = null`

3. **Move incomplete to tomorrow**:
   - Only moves tasks where `status != 'completed'` AND `due_date = selectedDate`
   - Sets `due_date` to `selectedDate + 1 day`
   - Returns count of moved tasks for the flash message
   - If no incomplete tasks exist, flash an info message "No incomplete tasks to move"

4. **Category deletion (from task-categories feature)**:
   - When a TaskCategory is deleted, `category_id` on tasks becomes NULL (on delete set null)
   - Tasks without a category still display normally, just without a category badge

5. **Priority sort order**:
   - Tasks ordered by: priority weight (urgent=0, high=1, medium=2, low=3), then sort_order ASC, then created_at ASC
   - Use a CASE expression or model accessor for priority ordering

6. **Completed tasks display**:
   - Completed tasks appear below pending/in_progress tasks in the list
   - They show with line-through text and muted styling
   - They can be toggled back to pending

7. **Date navigation**:
   - No restriction on past/future dates — user can view any date
   - "Today" button always available to jump back to current date
   - Selected date persists in URL via #[Url] so page refreshes maintain state

8. **Empty state**:
   - When no tasks exist for the selected date, show empty state with encouraging message
   - The inline add form is always visible above the task list (even when empty)

9. **Inline edit conflicts**:
   - Only one task can be in edit mode at a time
   - Starting to edit a new task auto-cancels the previous edit
   - Pressing Escape or clicking Cancel reverts without saving

10. **Progress bar edge cases**:
    - 0 tasks: hide progress bar or show "No tasks yet" state
    - All completed: progress bar at 100% with emerald/success color
    - Percentage is integer (floor), e.g., 2 of 3 = 66%

---

## 8. IMPLEMENTATION ORDER

```
1. database/migrations/YYYY_MM_DD_XXXXXX_create_tasks_table.php
2. app/Models/Task/Task.php
3. app/Services/TaskService.php
4. routes/admin/tasks/daily-planner.php
5. app/Livewire/Admin/Tasks/DailyPlanner/DailyPlannerIndex.php
6. resources/views/livewire/admin/tasks/daily-planner/index.blade.php
7. Update sidebar navigation in components/layouts/admin.blade.php (add Tasks module group with Daily Planner link)
```

> **Dependency note**: The `task_categories` table/model must exist before `category_id` foreign key works. If implementing this feature before task-categories, either:
> (a) Create the task_categories migration as part of that feature first, OR
> (b) Make the foreign key constraint conditional / remove it temporarily and add it via a later migration.
> The recommended approach is to implement task-categories first.
