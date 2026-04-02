# Goals Tracker — Spec

Side: ADMIN

---

## 1. MODULE OVERVIEW

The Goals Tracker allows setting monthly and yearly goals, tracking their progress as a percentage, and marking them as completed. Goals are categorized (Career, Financial, Learning, Health, Personal) and have a target date, enabling at-a-glance visibility into what is active and how close each goal is to completion.

**Features:**
- Create a goal with title, target date, description, and category
- Track progress as a percentage (0-100%)
- Mark a goal as completed
- Filter goals by category and status
- View all active goals at a glance with progress indicators

**Admin features (what admin can do):**
- CRUD operations on goals
- Update progress percentage on any goal
- Mark goals as completed or reopen them
- Filter and search goals by category, status, and date range

---

## 2. DATABASE SCHEMA

```
Table: goals
Columns:
  - id (bigint, primary key, auto increment)
  - title (string 255, required)
  - description (text, nullable)
  - category (string 50, required) — enum: career, financial, learning, health, personal
  - target_date (date, required)
  - progress (integer, required, default: 0) — 0 to 100
  - status (string 20, required, default: 'active') — enum: active, completed, abandoned
  - completed_at (timestamp, nullable) — set when marked completed
  - created_at, updated_at (timestamps)

Indexes:
  - index on category
  - index on status
  - index on target_date
```

---

## 3. FILE MAP

```
MIGRATIONS:
  - database/migrations/xxxx_xx_xx_xxxxxx_create_goals_table.php

MODELS:
  - app/Models/Goal.php  (single model — no subfolder needed)
    - fillable: title, description, category, target_date, progress, status, completed_at
    - casts: target_date → date, progress → integer, completed_at → datetime
    - relationships: none
    - scopes: active(), completed(), abandoned(), category($category)

SERVICES:
  - app/Services/GoalService.php
    - getAll(filters): LengthAwarePaginator — paginated goals with optional search, category, status filters
    - getActiveGoals(): Collection — all active goals ordered by target_date
    - getStats(): array — counts by status, average progress, overdue count
    - create(data): Goal — create a new goal
    - update(Goal, data): Goal — update goal fields
    - updateProgress(Goal, progress): Goal — update progress %; auto-complete if 100%
    - markCompleted(Goal): Goal — set status=completed, completed_at=now, progress=100
    - markAbandoned(Goal): Goal — set status=abandoned
    - reopen(Goal): Goal — set status=active, completed_at=null
    - delete(Goal): void — permanently delete goal

--- ADMIN FILES ---

LIVEWIRE COMPONENTS:
  - app/Livewire/Admin/Personal/GoalsTracker/GoalIndex.php
    - public properties: search, filterCategory, filterStatus
    - methods: mount(), getGoalsProperty(), delete(id)
  - app/Livewire/Admin/Personal/GoalsTracker/GoalForm.php
    - public properties: goalId, title, description, category, target_date, progress, status
    - methods: mount(goal?), save(), updatedProgress()

VIEWS:
  - resources/views/livewire/admin/personal/goals-tracker/index.blade.php
    - Stat cards row (total active, completed this month, average progress, overdue)
    - Filter bar with search, category dropdown, status dropdown
    - Table of goals with progress bars, category badges, status badges, actions
    - Empty state when no goals
  - resources/views/livewire/admin/personal/goals-tracker/form.blade.php
    - 2/3 + 1/3 layout
    - Left: goal details section (title, description, category, target date)
    - Right: progress section (range slider with visual bar), status display, save/cancel buttons

ROUTES (admin):
  - routes/admin/personal/goals-tracker.php
    - GET  /admin/personal/goals-tracker           → GoalIndex       → admin.personal.goals-tracker.index
    - GET  /admin/personal/goals-tracker/create     → GoalForm        → admin.personal.goals-tracker.create
    - GET  /admin/personal/goals-tracker/{goal}/edit → GoalForm       → admin.personal.goals-tracker.edit
```

---

## 4. COMPONENT CONTRACTS

### GoalIndex

```
Component: App\Livewire\Admin\Personal\GoalsTracker\GoalIndex
Namespace: App\Livewire\Admin\Personal\GoalsTracker
Layout: components.layouts.admin

Properties:
  - $search (string, #[Url]) — search query for title
  - $filterCategory (string, #[Url]) — filter by category (empty = all)
  - $filterStatus (string, #[Url]) — filter by status (empty = all)

Computed:
  - goals — paginated goals from GoalService::getAll(filters), 10 per page
  - stats — array from GoalService::getStats()

Methods:
  - delete(id)
    Input: goal id
    Does: calls GoalService::delete(), flashes success message
    Output: flash 'success' message, re-renders list
```

### GoalForm

```
Component: App\Livewire\Admin\Personal\GoalsTracker\GoalForm
Namespace: App\Livewire\Admin\Personal\GoalsTracker
Layout: components.layouts.admin

Properties:
  - $goalId (int|null) — null for create, set for edit
  - $title (string)
  - $description (string|null)
  - $category (string) — default: 'career'
  - $target_date (string)
  - $progress (int) — default: 0
  - $status (string) — default: 'active'

Methods:
  - mount(goal?)
    Input: optional Goal model (route model binding)
    Does: if editing, populates all properties from model
    Output: properties set

  - save()
    Input: validated form data
    Does: calls GoalService::create() or GoalService::update()
    Output: redirect to admin.personal.goals-tracker.index with flash 'success'

  - markCompleted()
    Input: none (uses $goalId)
    Does: calls GoalService::markCompleted()
    Output: redirect to index with flash 'success'

  - markAbandoned()
    Input: none (uses $goalId)
    Does: calls GoalService::markAbandoned()
    Output: redirect to index with flash 'success'

  - reopen()
    Input: none (uses $goalId)
    Does: calls GoalService::reopen()
    Output: redirect to index with flash 'success'
```

---

## 5. VIEW BLUEPRINTS

### Index View

```
View: resources/views/livewire/admin/personal/goals-tracker/index.blade.php
Layout: components.layouts.admin
Side: ADMIN
Page title: "Goals Tracker"

Design rules (from CLAUDE.md admin side):
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:
  - Breadcrumb: Dashboard > Personal > Goals Tracker
  - Page header: "Goals Tracker" title + "Add Goal" button (links to create route)
  - Stat cards row (4 columns):
    1. Active Goals (emerald icon bg) — count of active goals
    2. Completed This Month (primary icon bg) — goals completed in current month
    3. Average Progress (blue icon bg) — average progress of active goals
    4. Overdue (amber icon bg) — active goals past target_date
  - Filter bar: search input, category dropdown (All, Career, Financial, Learning, Health, Personal), status dropdown (All, Active, Completed, Abandoned)
  - Table columns: Title (with category badge below), Category, Target Date, Progress (gradient progress bar + percentage), Status (badge with dot), Actions (edit + delete icons)
  - Pagination footer with record count
  - Empty state: "No goals found" with "Add First Goal" button
```

### Form View

```
View: resources/views/livewire/admin/personal/goals-tracker/form.blade.php
Layout: components.layouts.admin
Side: ADMIN
Page title: "Create Goal" / "Edit Goal"

Design rules:
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider
  - 2/3 + 1/3 grid layout

Sections:
  - Breadcrumb: Dashboard > Personal > Goals Tracker > Create/Edit
  - Page header: "Create Goal" or "Edit Goal" + Back button

  Left column (2/3):
    - Goal Details card:
      - Title (text input, required)
      - Description (textarea, 4 rows, optional)
      - Category (select: Career, Financial, Learning, Health, Personal)
      - Target Date (date input, required)

  Right column (1/3):
    - Progress card (only visible when editing):
      - Range slider (0-100, step 5) with gradient progress bar visual
      - Current percentage display
    - Status card (only visible when editing):
      - Current status badge
      - Action buttons based on status:
        - If active: "Mark Completed" (emerald) + "Abandon" (amber)
        - If completed: "Reopen" (primary)
        - If abandoned: "Reopen" (primary)
    - Save card:
      - Save button (primary, with loading state)
      - Cancel button (secondary, links back to index)
```

---

## 6. VALIDATION RULES

```
Form: GoalForm (create and edit)
  - title: required|string|max:255
  - description: nullable|string|max:2000
  - category: required|string|in:career,financial,learning,health,personal
  - target_date: required|date
  - progress: required|integer|min:0|max:100
```

---

## 7. EDGE CASES & BUSINESS RULES

- **Delete:** permanent delete (no soft delete). Use wire:confirm before deletion.
- **Progress = 100:** When progress is updated to 100 via the slider, do NOT auto-complete. The user must explicitly click "Mark Completed" to change status. Progress and status are independent.
- **Mark Completed:** Sets progress to 100 and status to 'completed', records completed_at timestamp.
- **Reopen:** Sets status back to 'active', clears completed_at. Progress stays at whatever it was (100 if was completed, or previous value if abandoned).
- **Mark Abandoned:** Sets status to 'abandoned'. Progress is preserved as-is.
- **Overdue:** A goal is overdue if status is 'active' and target_date is in the past. Show overdue goals with an amber/warning visual indicator in the table.
- **Category colors:** Each category gets a distinct badge color:
  - Career: primary/10 text-primary-light
  - Financial: emerald-500/10 text-emerald-400
  - Learning: blue-500/10 text-blue-400
  - Health: fuchsia-500/10 text-fuchsia-400
  - Personal: amber-500/10 text-amber-400
- **Sort order:** Default sort by status (active first), then by target_date ascending (soonest first).
- **Unique constraints:** None. Multiple goals can have the same title.
- **Sidebar:** Add "Personal" as a new collapsible module group in the sidebar, with "Goals Tracker" nested inside it.

---

## 8. IMPLEMENTATION ORDER

```
1. Migration: create_goals_table
2. Model: app/Models/Goal.php
3. Service: app/Services/GoalService.php
4. Route file: routes/admin/personal/goals-tracker.php
5. Livewire component: GoalIndex (app/Livewire/Admin/Personal/GoalsTracker/GoalIndex.php)
6. Livewire component: GoalForm (app/Livewire/Admin/Personal/GoalsTracker/GoalForm.php)
7. View: index.blade.php (resources/views/livewire/admin/personal/goals-tracker/index.blade.php)
8. View: form.blade.php (resources/views/livewire/admin/personal/goals-tracker/form.blade.php)
9. Sidebar: Add "Personal" module group with "Goals Tracker" link to admin layout
```
