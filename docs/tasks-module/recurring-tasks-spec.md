# Recurring Tasks — Spec

Side: **ADMIN**

---

## 1. MODULE OVERVIEW

This feature allows the admin to create task templates that automatically generate new Task records on a recurring schedule (daily, weekly, or monthly). Instead of manually creating the same task every day or week, the system creates them automatically via a scheduled Laravel command.

### Features
- Create recurring task templates with title, description, category, frequency, and priority
- Edit existing recurring task templates
- Pause/resume recurring tasks (toggle is_active)
- Delete recurring task templates
- A scheduled Artisan command runs daily to generate Task records from active recurring templates

### Admin Features
- CRUD for recurring task templates
- Filter by frequency (daily/weekly/monthly) and status (active/paused)
- Search by title
- Pause/resume toggle directly from the list view
- View next scheduled generation date per template

### Dependencies
- This feature depends on the **Task** model (`app/Models/Task/Task.php`) and **TaskCategory** model (`app/Models/Task/TaskCategory.php`) existing from the daily-task-planner and task-categories features. If those are not yet implemented, the migration and model references here assume they will exist.

---

## 2. DATABASE SCHEMA

```
Table: recurring_tasks
Columns:
  - id              BIGINT UNSIGNED, primary key, auto increment
  - user_id         BIGINT UNSIGNED, NOT NULL, FK -> users.id ON DELETE CASCADE
  - category_id     BIGINT UNSIGNED, NULLABLE, FK -> task_categories.id ON DELETE SET NULL
  - title           VARCHAR(255), NOT NULL
  - description     TEXT, NULLABLE
  - frequency       VARCHAR(20), NOT NULL (daily, weekly, monthly)
  - day_of_week     TINYINT UNSIGNED, NULLABLE (0=Sunday, 1=Monday ... 6=Saturday; used when frequency=weekly)
  - day_of_month    TINYINT UNSIGNED, NULLABLE (1-31; used when frequency=monthly)
  - priority        VARCHAR(20), NOT NULL, DEFAULT 'medium' (low, medium, high, urgent)
  - is_active       BOOLEAN, NOT NULL, DEFAULT true
  - last_generated_at TIMESTAMP, NULLABLE (tracks when the last task was generated)
  - created_at      TIMESTAMP
  - updated_at      TIMESTAMP

Indexes:
  - INDEX(user_id, is_active) — for the scheduled command query
  - INDEX(frequency) — for filtering by frequency

Foreign keys:
  - user_id references users(id) ON DELETE CASCADE
  - category_id references task_categories(id) ON DELETE SET NULL
```

---

## 3. FILE MAP

```
MIGRATIONS:
  - database/migrations/xxxx_xx_xx_xxxxxx_create_recurring_tasks_table.php

MODELS:
  - app/Models/Task/RecurringTask.php
    - fillable: user_id, category_id, title, description, frequency, day_of_week, day_of_month, priority, is_active, last_generated_at
    - relationships: belongsTo(User), belongsTo(TaskCategory)
    - casts: is_active -> boolean, last_generated_at -> datetime, day_of_week -> integer, day_of_month -> integer
    - scopes: scopeActive($query), scopeForUser($query, $userId), scopeByFrequency($query, $frequency)
    - constants: FREQUENCY_DAILY, FREQUENCY_WEEKLY, FREQUENCY_MONTHLY, FREQUENCIES array; PRIORITY_LOW, PRIORITY_MEDIUM, PRIORITY_HIGH, PRIORITY_URGENT, PRIORITIES array

SERVICES:
  - app/Services/RecurringTaskService.php
    - getFilteredRecurringTasks(int $userId, ?string $search, ?string $frequencyFilter, ?string $statusFilter): query builder — returns filtered paginated query
    - create(array $data): RecurringTask — creates a new recurring task template
    - update(RecurringTask $recurringTask, array $data): RecurringTask — updates an existing template
    - delete(RecurringTask $recurringTask): void — hard deletes the template
    - toggleActive(RecurringTask $recurringTask): RecurringTask — flips is_active and returns updated model
    - generateDueRecurringTasks(): int — finds all active recurring tasks due today, creates Task records for each, updates last_generated_at, returns count of tasks generated
    - isDueToday(RecurringTask $recurringTask): bool — checks if a recurring task should fire today based on frequency, day_of_week, day_of_month, and last_generated_at

--- ADMIN FILES ---

LIVEWIRE COMPONENTS:
  - app/Livewire/Admin/Tasks/RecurringTasks/RecurringTaskIndex.php
    - public properties: $search, $frequencyFilter, $statusFilter
    - methods: updatingSearch(), updatingFrequencyFilter(), updatingStatusFilter(), toggleActive(int $id), delete(int $id), render()
  - app/Livewire/Admin/Tasks/RecurringTasks/RecurringTaskForm.php
    - public properties: $recurringTask, $title, $description, $category_id, $frequency, $day_of_week, $day_of_month, $priority, $is_active
    - methods: mount(?RecurringTask $recurringTask), save(), render()

VIEWS:
  - resources/views/livewire/admin/tasks/recurring-tasks/index.blade.php
    - Lists all recurring task templates with filters, search, and actions
  - resources/views/livewire/admin/tasks/recurring-tasks/form.blade.php
    - Create/edit form for recurring task templates

ROUTES (admin):
  - routes/admin/tasks/recurring-tasks.php
    - GET  /admin/tasks/recurring-tasks              -> RecurringTaskIndex  -> admin.tasks.recurring.index
    - GET  /admin/tasks/recurring-tasks/create       -> RecurringTaskForm   -> admin.tasks.recurring.create
    - GET  /admin/tasks/recurring-tasks/{recurringTask}/edit -> RecurringTaskForm -> admin.tasks.recurring.edit

ARTISAN COMMAND:
  - app/Console/Commands/GenerateRecurringTasks.php
    - signature: tasks:generate-recurring
    - description: "Generate tasks from active recurring task templates"
    - handle(): calls RecurringTaskService::generateDueRecurringTasks(), outputs count
    - Registered in routes/console.php to run daily via Schedule::command('tasks:generate-recurring')->dailyAt('00:05')
```

---

## 4. COMPONENT CONTRACTS

### Component: App\Livewire\Admin\Tasks\RecurringTasks\RecurringTaskIndex

```
Namespace: App\Livewire\Admin\Tasks\RecurringTasks
Layout: components.layouts.admin

Properties:
  - $search (string, #[Url]) — search by title
  - $frequencyFilter (string, #[Url], default 'all') — filter by frequency: all, daily, weekly, monthly
  - $statusFilter (string, #[Url], default 'all') — filter by status: all, active, paused

Methods:
  - updatingSearch()
    Does: resets pagination

  - updatingFrequencyFilter()
    Does: resets pagination

  - updatingStatusFilter()
    Does: resets pagination

  - toggleActive(int $id)
    Input: recurring task ID
    Does: finds RecurringTask by ID, calls RecurringTaskService::toggleActive()
    Output: flash success message ("Recurring task paused." or "Recurring task resumed.")

  - delete(int $id)
    Input: recurring task ID
    Does: finds RecurringTask by ID, calls RecurringTaskService::delete()
    Output: flash success message "Recurring task deleted successfully."

  - render()
    Does: builds query via RecurringTaskService::getFilteredRecurringTasks() scoped to auth user, paginates at 10, passes to view
    Output: view with $recurringTasks (paginated), $categories (for display)
```

### Component: App\Livewire\Admin\Tasks\RecurringTasks\RecurringTaskForm

```
Namespace: App\Livewire\Admin\Tasks\RecurringTasks
Layout: components.layouts.admin

Properties:
  - $recurringTask (?RecurringTask) — null for create, populated for edit
  - $title (string, default '')
  - $description (string, default '')
  - $category_id (string, default '') — empty string for nullable select
  - $frequency (string, default 'daily')
  - $day_of_week (?int, default null)
  - $day_of_month (?int, default null)
  - $priority (string, default 'medium')
  - $is_active (bool, default true)

Methods:
  - mount(?RecurringTask $recurringTask = null)
    Input: optional RecurringTask for edit
    Does: if recurringTask exists, populates all properties from model
    Output: sets component state

  - save()
    Input: validated form data
    Does:
      1. Runs $this->validate()
      2. Merges user_id from auth()->id()
      3. Nullifies day_of_week when frequency is not weekly
      4. Nullifies day_of_month when frequency is not monthly
      5. Calls RecurringTaskService::create() or ::update() based on $recurringTask
    Output: flash success, redirect to admin.tasks.recurring.index

  - render()
    Does: fetches TaskCategory list for dropdown, returns form view
    Output: view with $categories

Validation Rules:
  - title: required|string|max:255
  - description: nullable|string|max:5000
  - category_id: nullable|exists:task_categories,id
  - frequency: required|in:daily,weekly,monthly
  - day_of_week: required_if:frequency,weekly|nullable|integer|min:0|max:6
  - day_of_month: required_if:frequency,monthly|nullable|integer|min:1|max:31
  - priority: required|in:low,medium,high,urgent
  - is_active: boolean
```

---

## 5. VIEW BLUEPRINTS

### View: resources/views/livewire/admin/tasks/recurring-tasks/index.blade.php

```
Layout: components.layouts.admin
Side: ADMIN
Page title: "Recurring Tasks"

Design rules (from CLAUDE.md admin side):
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:

  1. Breadcrumb
     Dashboard > Tasks > Recurring Tasks

  2. Page Header
     - Title: "Recurring Tasks"
     - Subtitle: "Manage task templates that repeat automatically."
     - Add button: "Add Recurring Task" -> route('admin.tasks.recurring.create')

  3. Filters Row (bg-dark-800 border border-dark-700 rounded-xl p-4, flex items-center gap-4)
     - Search input (wire:model.live.debounce.300ms="search", placeholder "Search by title...")
     - Frequency dropdown (wire:model.live="frequencyFilter"): All, Daily, Weekly, Monthly
     - Status dropdown (wire:model.live="statusFilter"): All, Active, Paused

  4. Table (bg-dark-800 border border-dark-700 rounded-xl overflow-hidden)
     Table columns:
       - Title (text-white font-medium) + description preview (text-gray-500 text-xs, truncated)
       - Category (badge with category color, or "—" if none)
       - Frequency (badge: daily=blue, weekly=amber, monthly=purple)
       - Priority (badge: low=gray, medium=blue, high=amber, urgent=red)
       - Status (toggle switch or badge: active=emerald, paused=red)
       - Next Due (text-gray-400 text-sm, computed from frequency and last_generated_at)
       - Actions (three-dot dropdown): Edit, Pause/Resume, Delete (with wire:confirm)

     Row styling: border-b border-dark-700/50, hover:bg-dark-700/30 transition-colors

  5. Pagination (standard Livewire pagination below table)

  6. Empty State (shown when no recurring tasks exist)
     - Icon (calendar-repeat or refresh icon, text-gray-600)
     - "No recurring tasks yet"
     - "Create your first recurring task to automate your workflow."
     - CTA button: "Add Recurring Task"

  7. Flash messages (session flash at top of page, standard pattern)
```

### View: resources/views/livewire/admin/tasks/recurring-tasks/form.blade.php

```
Layout: components.layouts.admin
Side: ADMIN
Page title: "Create Recurring Task" / "Edit Recurring Task"

Design rules (from CLAUDE.md admin side):
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:

  1. Breadcrumb
     Dashboard > Tasks > Recurring Tasks > Create / Edit

  2. Page Header
     - Title: dynamic "Create Recurring Task" or "Edit Recurring Task"
     - Subtitle: dynamic create/edit hint
     - Back button -> route('admin.tasks.recurring.index')

  3. Form Layout: grid grid-cols-1 xl:grid-cols-3 gap-6
     Main content (xl:col-span-2 space-y-6):

       Section Card 1: "Task Details"
         - Title (text input, required, full width span 2 cols)
         - Description (textarea, optional, full width span 2 cols, 4 rows)

       Section Card 2: "Schedule"
         - Frequency (select dropdown: Daily, Weekly, Monthly)
         - Day of Week (select dropdown: Sunday-Saturday, shown only when frequency=weekly, use Alpine x-show)
         - Day of Month (number input 1-31, shown only when frequency=monthly, use Alpine x-show)

     Sidebar (space-y-6):

       Meta Card: "Settings"
         - Category (select dropdown from task_categories, optional, with "No Category" default)
         - Priority (select dropdown: Low, Medium, High, Urgent)
         - Status (toggle switch for is_active, label "Active")

       Submit Card:
         - Save button (bg-primary hover:bg-primary-hover text-white rounded-lg px-5 py-2.5 full width)
           Label: "Create Recurring Task" or "Update Recurring Task"
         - Cancel link -> route('admin.tasks.recurring.index')

  4. Flash messages (session flash at top of page)

  5. Alpine.js interactions:
     - x-data with frequency state to show/hide day_of_week and day_of_month fields
     - Wire:model on frequency syncs with Alpine for reactive show/hide
```

---

## 6. VALIDATION RULES

```
Form: Recurring Task (Create / Edit)
  - title: required|string|max:255
  - description: nullable|string|max:5000
  - category_id: nullable|exists:task_categories,id
  - frequency: required|in:daily,weekly,monthly
  - day_of_week: required_if:frequency,weekly|nullable|integer|min:0|max:6
  - day_of_month: required_if:frequency,monthly|nullable|integer|min:1|max:31
  - priority: required|in:low,medium,high,urgent
  - is_active: boolean
```

---

## 7. EDGE CASES & BUSINESS RULES

- **Delete behavior**: Hard delete. Deleting a recurring task template does NOT delete any Task records that were already generated from it. No soft deletes.
- **Pause/resume**: Toggling is_active to false (paused) prevents the scheduled command from generating new tasks. Existing generated tasks are unaffected. Resuming (is_active=true) means the next daily run will pick it up again.
- **day_of_week cleanup**: When frequency changes away from "weekly", day_of_week is set to null in the save method. Same for day_of_month when frequency changes away from "monthly".
- **day_of_month edge case**: If day_of_month is 31 but the current month has fewer days (e.g., February), the command should skip that month (do not generate on the last day as a fallback). The task simply does not fire that month.
- **last_generated_at**: Updated each time the scheduled command generates a Task from this template. Used to prevent double-generation if the command runs more than once per day.
- **Duplicate prevention**: The generateDueRecurringTasks method checks last_generated_at to ensure a task is not generated twice on the same day. If last_generated_at is today, skip.
- **Category deletion**: If a TaskCategory is deleted, category_id on recurring_tasks is set to NULL (ON DELETE SET NULL). The recurring task continues to function without a category.
- **User scoping**: All queries are scoped to the authenticated user. A user can only see/manage their own recurring tasks.
- **Generated task fields**: When a Task is created from a recurring template, it receives: title, description, category_id, priority, user_id, and the scheduled_date set to today. The task starts with status "pending" (or whatever the Task model default is).
- **Sort order**: Default list sorted by created_at descending (newest first).
- **Frequency badge colors**: daily=blue (bg-blue-500/10 text-blue-400), weekly=amber (bg-amber-500/10 text-amber-400), monthly=purple (bg-primary/10 text-primary-light).
- **Priority badge colors**: low=gray (bg-gray-500/10 text-gray-400), medium=blue (bg-blue-500/10 text-blue-400), high=amber (bg-amber-500/10 text-amber-400), urgent=red (bg-red-500/10 text-red-400).
- **Sidebar**: "Recurring Tasks" link is nested under the "Tasks" parent module group in the sidebar, never at root level.

---

## 8. IMPLEMENTATION ORDER

```
1. Migration: database/migrations/xxxx_xx_xx_xxxxxx_create_recurring_tasks_table.php
2. Model: app/Models/Task/RecurringTask.php
3. Service: app/Services/RecurringTaskService.php
4. Route: routes/admin/tasks/recurring-tasks.php
5. Livewire component: app/Livewire/Admin/Tasks/RecurringTasks/RecurringTaskIndex.php
6. Livewire component: app/Livewire/Admin/Tasks/RecurringTasks/RecurringTaskForm.php
7. View: resources/views/livewire/admin/tasks/recurring-tasks/index.blade.php
8. View: resources/views/livewire/admin/tasks/recurring-tasks/form.blade.php
9. Artisan command: app/Console/Commands/GenerateRecurringTasks.php
10. Schedule registration: routes/console.php (add daily schedule)
11. Sidebar: add "Recurring Tasks" link under Tasks module group in admin layout
```
