# Phase 1 Cleanup & Phase 2 Rename — Improvement Spec

**Module:** Tasks → Project Management
**Type:** REMOVE + RENAME
**Mode:** dev (destructive migrations allowed, data loss acceptable)
**Generated:** 2026-04-05

---

## 1. UPDATE OVERVIEW

### What is being changed
The "Tasks" module is being restructured in two phases:

**Phase 1 — Remove Unused Features:** Remove Daily Planner, Categories, Recurring Tasks, Quick Capture, AI Prioritization, and Task Import. This includes dropping the `tasks`, `task_categories`, and `recurring_tasks` database tables, removing all associated Livewire components, views, routes, services, models, and console commands. The `category_id` foreign key on `project_tasks` must also be dropped since `task_categories` is being removed.

**Phase 2 — Rename "Tasks" to "Project Management":** Rename the module at every code layer — namespaces, directories, routes, URLs, sidebar labels, and breadcrumbs — from "Tasks" to "Project Management." Database table names remain unchanged (they already use `project_*` prefixes).

### Why
The removed features are unused and add maintenance overhead. The remaining features (Project Board, Calendar, Weekly Review) are project-management-oriented, not personal task tracking. Renaming aligns the module name with its actual purpose and prevents confusion as future features (Design Board, Sharing) are added.

---

## 2. CURRENT STATE (BEFORE)

### Models (app/Models/Task/)
| Model | Table | Status |
|-------|-------|--------|
| Task.php | tasks | REMOVE |
| TaskCategory.php | task_categories | REMOVE |
| RecurringTask.php | recurring_tasks | REMOVE |
| WeeklyReview.php | weekly_reviews | KEEP (rename namespace) |
| ProjectBoard.php | project_boards | KEEP (rename namespace) |
| ProjectBoardColumn.php | project_board_columns | KEEP (rename namespace) |
| ProjectTask.php | project_tasks | KEEP (rename namespace, drop category_id) |
| ProjectTaskImage.php | project_task_images | KEEP (rename namespace) |

### Services (app/Services/)
| Service | Status |
|---------|--------|
| TaskService.php | REMOVE |
| TaskCategoryService.php | REMOVE |
| RecurringTaskService.php | REMOVE |
| TaskImportService.php | REMOVE |
| TaskPdfService.php | REMOVE |
| AiTaskPrioritizationService.php | REMOVE |
| AiCategoryIdentificationService.php | REMOVE |
| ProjectBoardService.php | KEEP (update imports) |
| ProjectTaskService.php | KEEP (update imports, remove category eager loads) |
| ProjectBoardExportService.php | KEEP (update imports, remove category references) |
| WeeklyReviewService.php | KEEP (rewrite to use ProjectTask instead of Task) |
| CalendarService.php | KEEP (rewrite to use only ProjectTask, remove Task queries) |

### Livewire Components (app/Livewire/Admin/Tasks/)
| Component | Status |
|-----------|--------|
| DailyPlanner/ | REMOVE entire directory |
| Categories/ | REMOVE entire directory |
| RecurringTasks/ | REMOVE entire directory |
| QuickCapture/ | REMOVE entire directory |
| AiPrioritization/ | REMOVE entire directory |
| ProjectBoard/ProjectBoardIndex.php | KEEP (rename namespace + directory) |
| Calendar/CalendarIndex.php | KEEP (rename namespace + directory) |
| WeeklyReview/WeeklyReviewIndex.php | KEEP (rename namespace + directory) |

### Views (resources/views/livewire/admin/tasks/)
| View | Status |
|------|--------|
| daily-planner/ | REMOVE |
| categories/ | REMOVE |
| recurring-tasks/ | REMOVE |
| quick-capture/ | REMOVE |
| ai-prioritization/ | REMOVE |
| project-board/ | KEEP (move to project-management/) |
| calendar/ | KEEP (move to project-management/) |
| weekly-review/ | KEEP (move to project-management/) |

### PDF Views (resources/views/tasks/pdf/)
| View | Status |
|------|--------|
| task-list.blade.php | REMOVE |
| project-board.blade.php | KEEP (rename path to project-management/pdf/) |

### Routes (routes/admin/tasks/)
| Route File | Status |
|------------|--------|
| daily-planner.php | REMOVE |
| categories.php | REMOVE |
| recurring-tasks.php | REMOVE |
| ai-prioritization.php | REMOVE |
| pdf-download.php | REMOVE |
| project-board.php | KEEP (move to project-management/, update route names) |
| calendar.php | KEEP (move to project-management/, update route names) |
| weekly-review.php | KEEP (move to project-management/, update route names) |
| project-board-export.php | KEEP (move to project-management/, update route names) |

### Controllers
| Controller | Status |
|------------|--------|
| TaskPdfController.php | REMOVE |
| ProjectBoardExportController.php | KEEP (no changes needed) |

### Console
| Item | Status |
|------|--------|
| app/Console/Commands/GenerateRecurringTasks.php | REMOVE |
| routes/console.php schedule entry | REMOVE the line |

### Database Seeders
| Seeder | Status |
|--------|--------|
| database/seeders/TasksSeeder.php | REMOVE |
| database/seeders/DatabaseSeeder.php | MODIFY (remove TasksSeeder call) |

---

## 3. TARGET STATE (AFTER)

### Directory Structure
```
app/Models/ProjectManagement/
├── WeeklyReview.php          (namespace: App\Models\ProjectManagement)
├── ProjectBoard.php
├── ProjectBoardColumn.php
├── ProjectTask.php           (no category_id, no category relationship)
└── ProjectTaskImage.php

app/Services/
├── ProjectBoardService.php       (updated imports)
├── ProjectTaskService.php        (updated imports, no category)
├── ProjectBoardExportService.php (updated imports, no category)
├── WeeklyReviewService.php       (rewritten: uses ProjectTask)
└── CalendarService.php           (rewritten: uses only ProjectTask)

app/Livewire/Admin/ProjectManagement/
├── ProjectBoard/ProjectBoardIndex.php
├── Calendar/CalendarIndex.php
└── WeeklyReview/WeeklyReviewIndex.php

resources/views/livewire/admin/project-management/
├── project-board/index.blade.php
├── calendar/index.blade.php
└── weekly-review/index.blade.php

resources/views/project-management/pdf/
└── project-board.blade.php

routes/admin/project-management/
├── project-board.php
├── calendar.php
├── weekly-review.php
└── project-board-export.php
```

### Route Names (After)
- `admin.project-management.project-board.index`
- `admin.project-management.project-board.export`
- `admin.project-management.calendar.index`
- `admin.project-management.weekly-review.index`

### URLs (After)
- `/admin/project-management/project-board`
- `/admin/project-management/project-board/export/{format}/{boardId}`
- `/admin/project-management/calendar`
- `/admin/project-management/weekly-review`

### Sidebar (After)
```
Project Management (parent, collapsible)
├── Project Board
├── Calendar
└── Weekly Review
```

### Database Tables (Unchanged)
- `project_boards` — no changes
- `project_board_columns` — no changes
- `project_tasks` — `category_id` column and FK dropped
- `project_task_images` — no changes
- `weekly_reviews` — no changes

### Tables Dropped
- `tasks`
- `task_categories`
- `recurring_tasks`

---

## 4. MIGRATION PATH (BEFORE → AFTER)

### Database Migration (single migration file)

**Migration name:** `2026_04_05_000001_cleanup_tasks_module_phase1.php`

```
1. Drop FK `project_tasks.category_id` → task_categories
2. Drop column `project_tasks.category_id`
3. Drop table `recurring_tasks`
4. Drop table `tasks` (has FK to task_categories, drop it first or drop FK first)
5. Drop table `task_categories`
```

**Important order:** Drop FKs referencing `task_categories` before dropping the table. Both `tasks.category_id` and `project_tasks.category_id` reference `task_categories`.

### Phase 1 File Removal Order
1. Remove console schedule entry (routes/console.php)
2. Remove console command (GenerateRecurringTasks.php)
3. Remove services (7 files)
4. Remove Livewire components (5 directories)
5. Remove views (5 directories + task-list PDF)
6. Remove routes (5 files)
7. Remove controller (TaskPdfController.php)
8. Remove models (Task.php, TaskCategory.php, RecurringTask.php)
9. Remove seeder (TasksSeeder.php)
10. Remove QuickCapture from admin layout
11. Run migration to drop tables/columns

### Phase 2 Rename Order
1. Create new directories first
2. Move and update model files (namespace change)
3. Move and update Livewire component files (namespace change)
4. Move view files
5. Move route files and update route names/URLs
6. Move PDF view
7. Update all service imports
8. Update admin layout sidebar
9. Update cross-module references
10. Update all blade view breadcrumbs and route references
11. Clear all caches

---

## 5. FILES TO MODIFY

### 5.1 Models (namespace change + category removal)

**app/Models/Task/ProjectTask.php → app/Models/ProjectManagement/ProjectTask.php**
- Change namespace from `App\Models\Task` to `App\Models\ProjectManagement`
- Remove `category_id` from `$fillable`
- Remove `category()` relationship method
- Remove `use App\Models\Task\TaskCategory` (if present, it references itself via same namespace)

**app/Models/Task/ProjectBoard.php → app/Models/ProjectManagement/ProjectBoard.php**
- Change namespace from `App\Models\Task` to `App\Models\ProjectManagement`

**app/Models/Task/ProjectBoardColumn.php → app/Models/ProjectManagement/ProjectBoardColumn.php**
- Change namespace from `App\Models\Task` to `App\Models\ProjectManagement`

**app/Models/Task/ProjectTaskImage.php → app/Models/ProjectManagement/ProjectTaskImage.php**
- Change namespace from `App\Models\Task` to `App\Models\ProjectManagement`

**app/Models/Task/WeeklyReview.php → app/Models/ProjectManagement/WeeklyReview.php**
- Change namespace from `App\Models\Task` to `App\Models\ProjectManagement`

### 5.2 Services (update imports, remove category references)

**app/Services/ProjectBoardService.php**
- Change all `use App\Models\Task\*` to `use App\Models\ProjectManagement\*`
- In `getBoard()`: remove `'columns.tasks.category'` from eager load (line 28)

**app/Services/ProjectTaskService.php**
- Change all `use App\Models\Task\*` to `use App\Models\ProjectManagement\*`
- In `getTasksForDate()` (line 116): remove `'category'` from `with()` clause
- In `getTasksForDateRange()` (line 128): remove `'category'` from `with()` clause

**app/Services/ProjectBoardExportService.php**
- Change `use App\Models\Task\ProjectBoard` to `use App\Models\ProjectManagement\ProjectBoard`
- In `exportCsv()`: remove `Category` column header and `$task->category?->name` value from CSV output (lines 55, 64)
- In `exportMarkdown()`: remove category detail from markdown output (lines 117-119)
- In `exportPdf()`: update view path from `'tasks.pdf.project-board'` to `'project-management.pdf.project-board'` (line 37)
- In `getBoardWithTasks()`: remove `'columns.tasks.category'` from eager load (line 159)

**app/Services/WeeklyReviewService.php** — MAJOR REWRITE
- Remove `use App\Models\Task\Task` (line 5)
- Remove `use App\Models\Task\TaskCategory` (line 6)
- Change `use App\Models\Task\WeeklyReview` to `use App\Models\ProjectManagement\WeeklyReview`
- Rewrite `computeWeekStats()` (line 66): query `ProjectTask` with `target_date` instead of `Task` with `due_date`. Use `completed_at IS NOT NULL` instead of `status = 'completed'`
- Rewrite `computeCategoryBreakdown()` (line 83): since categories are removed, this should return an empty array or be rewritten to group by board instead. Simplest approach: return `[]` (the WeeklyReview model stores `category_breakdown` as JSON, and existing reviews keep their data, but new reviews get empty breakdown)
- Rewrite `getIncompleteTasks()` (line 121): query `ProjectTask` with `pending()` scope and `target_date` instead of `Task` with `due_date`. Remove `.with('category')`
- Add `use App\Models\ProjectManagement\ProjectTask` import

**app/Services/CalendarService.php** — MAJOR REWRITE
- Remove `use App\Models\Task\Task` (line 5)
- Remove `use App\Models\Task\TaskCategory` (line 6)
- Change all references from `App\Models\Task\ProjectTask` to `App\Models\ProjectManagement\ProjectTask`
- Rewrite `getTasksForMonth()`: remove all `Task` (personal task) queries. Only query `ProjectTask` by `target_date`. Remove the conditional `Schema::hasTable` check — project_tasks is the only source now
- Rewrite `getTasksForWeek()`: same as above
- Rewrite `getTasksForDate()`: return only project tasks (remove `personal` key from return, or change structure). The return should now be `['personal' => collect(), 'project' => $projectTasks]` or simplified to just project tasks
- Rewrite `getCalendarStats()`: query only `ProjectTask`. Use `completed_at IS NOT NULL` for completed status, `target_date < today AND completed_at IS NULL` for overdue
- Remove `getCategories()` method entirely (line 162)
- Remove `use Illuminate\Support\Facades\Schema` (no longer needed)

### 5.3 Livewire Components (namespace + directory change)

**app/Livewire/Admin/Tasks/ProjectBoard/ProjectBoardIndex.php → app/Livewire/Admin/ProjectManagement/ProjectBoard/ProjectBoardIndex.php**
- Change namespace from `App\Livewire\Admin\Tasks\ProjectBoard` to `App\Livewire\Admin\ProjectManagement\ProjectBoard`
- Change all `use App\Models\Task\*` to `use App\Models\ProjectManagement\*`
- Remove `use App\Models\Task\TaskCategory` (line 9)
- In `render()`: remove `$categories = TaskCategory::query()->ordered()->get()` (line 409)
- In `render()`: remove `'categories' => $categories` from view data
- In `render()`: remove category filter query constraint (lines 399-401)
- In `render()`: remove `'columns.tasks.category'` from eager load (line 405)
- Update view path from `'livewire.admin.tasks.project-board.index'` to `'livewire.admin.project-management.project-board.index'` (line 411)
- Remove `$categoryFilter` property and related logic
- Remove `$taskCategoryId` property or keep for compatibility but remove validation rule `'taskCategoryId' => 'nullable|exists:task_categories,id'` (line 426)
- Remove `category_id` from task create/update data arrays (lines 271, 295)

**app/Livewire/Admin/Tasks/Calendar/CalendarIndex.php → app/Livewire/Admin/ProjectManagement/Calendar/CalendarIndex.php**
- Change namespace from `App\Livewire\Admin\Tasks\Calendar` to `App\Livewire\Admin\ProjectManagement\Calendar`
- Remove `use App\Models\Task\Task` (line 6)
- Remove `use App\Services\TaskService` (line 8)
- Remove `toggleTaskComplete()` method entirely (lines 130-145) — this toggles personal Task completion, which no longer exists
- Rewrite `goToPlanner()` method: remove or redirect to project board instead: `route('admin.project-management.project-board.index')`
- In `openDayModal()`: update `selectedDayTasks` structure — remove `personal` key mapping that references `$task->category`. Only keep project tasks
- In `render()`: remove `$categories` variable and its passage to view. Remove `'categories' => $categories`
- Update view path from `'livewire.admin.tasks.calendar.index'` to `'livewire.admin.project-management.calendar.index'`

**app/Livewire/Admin/Tasks/WeeklyReview/WeeklyReviewIndex.php → app/Livewire/Admin/ProjectManagement/WeeklyReview/WeeklyReviewIndex.php**
- Change namespace from `App\Livewire\Admin\Tasks\WeeklyReview` to `App\Livewire\Admin\ProjectManagement\WeeklyReview`
- Update view path from `'livewire.admin.tasks.weekly-review.index'` to `'livewire.admin.project-management.weekly-review.index'`

### 5.4 Views (move + update references)

**resources/views/livewire/admin/tasks/project-board/index.blade.php → resources/views/livewire/admin/project-management/project-board/index.blade.php**
- Update breadcrumb: change "Tasks" link from `route('admin.tasks.planner.index')` to `route('admin.project-management.project-board.index')` and label to "Project Management"
- Update export route references: `admin.tasks.project-board.export` → `admin.project-management.project-board.export` (3 occurrences at lines 99, 107, 115)
- Remove category filter dropdown (the `<select wire:model.live="categoryFilter"` block)
- Remove category display on task cards (`@if ($task->category)` block)
- Remove category select in task create/edit modal (`@foreach ($categories as $category)` in the form)

**resources/views/livewire/admin/tasks/calendar/index.blade.php → resources/views/livewire/admin/project-management/calendar/index.blade.php**
- Update breadcrumb: change "Tasks" to "Project Management"
- Remove category color indicators on calendar cells (lines referencing `$task->category`)
- Remove categories legend section (`@foreach($categories as $category)` block)
- Remove "Personal Tasks" section from day modal — only show project tasks
- Remove `toggleTaskComplete` button (this called Task toggle)
- Remove `goToPlanner` button or change to link to project board
- Remove category badge from day modal task display

**resources/views/livewire/admin/tasks/weekly-review/index.blade.php → resources/views/livewire/admin/project-management/weekly-review/index.blade.php**
- Update breadcrumb: change "Tasks" to "Project Management"
- Category breakdown section should handle empty array gracefully (or be removed if categories are the only source)

**resources/views/tasks/pdf/project-board.blade.php → resources/views/project-management/pdf/project-board.blade.php**
- Remove any category references in the PDF template

### 5.5 Route Files (move + update names/URLs)

**routes/admin/tasks/project-board.php → routes/admin/project-management/project-board.php**
- Change `use App\Livewire\Admin\Tasks\ProjectBoard\ProjectBoardIndex` to `use App\Livewire\Admin\ProjectManagement\ProjectBoard\ProjectBoardIndex`
- Change URL from `/tasks/project-board` to `/project-management/project-board`
- Change route name from `admin.tasks.project-board.index` to `admin.project-management.project-board.index`

**routes/admin/tasks/calendar.php → routes/admin/project-management/calendar.php**
- Change `use App\Livewire\Admin\Tasks\Calendar\CalendarIndex` to `use App\Livewire\Admin\ProjectManagement\Calendar\CalendarIndex`
- Change URL from `/tasks/calendar` to `/project-management/calendar`
- Change route name from `admin.tasks.calendar.index` to `admin.project-management.calendar.index`

**routes/admin/tasks/weekly-review.php → routes/admin/project-management/weekly-review.php**
- Change `use App\Livewire\Admin\Tasks\WeeklyReview\WeeklyReviewIndex` to `use App\Livewire\Admin\ProjectManagement\WeeklyReview\WeeklyReviewIndex`
- Change URL from `/tasks/weekly-review` to `/project-management/weekly-review`
- Change route name from `admin.tasks.weekly-review.index` to `admin.project-management.weekly-review.index`

**routes/admin/tasks/project-board-export.php → routes/admin/project-management/project-board-export.php**
- Change URL from `/tasks/project-board/export/{format}/{boardId}` to `/project-management/project-board/export/{format}/{boardId}`
- Change route name from `admin.tasks.project-board.export` to `admin.project-management.project-board.export`

### 5.6 Admin Layout Sidebar

**resources/views/components/layouts/admin.blade.php**
- Lines 193-275: Replace entire Tasks collapsible section
  - Change `$tasksActive = request()->routeIs('admin.tasks.*')` to `$pmActive = request()->routeIs('admin.project-management.*')`
  - Change label from "Tasks" to "Project Management"
  - Change Alpine variable from `tasksOpen` to `pmOpen`
  - Remove links for: Daily Planner, Categories, Recurring Tasks, AI Prioritization
  - Keep and update links for: Project Board, Calendar, Weekly Review (update route names)
  - Update `routeIs()` checks from `admin.tasks.*` to `admin.project-management.*`
- Line 774: Remove `<livewire:admin.tasks.quick-capture.quick-capture />`

### 5.7 Cross-Module Files

**app/Livewire/Admin/Home/DailyBriefing/DailyBriefingIndex.php**
- Remove `use App\Models\Task\Task` (line 6)
- Remove `use App\Services\TaskService` (line 8)
- Remove `completeTask()` method entirely (lines 46-56) — it uses Task model + TaskService
- Keep `$todayTasks` property but source from ProjectTask instead (via updated DailyBriefingService)
- Or simplify: remove todayTasks entirely if the briefing should no longer show tasks (decision needed — recommend keeping it but sourcing from ProjectTask)

**app/Services/DailyBriefingService.php**
- Remove `use App\Models\Task\Task` (line 8)
- Remove `protected TaskService $taskService` from constructor (line 15)
- Rewrite `getQuickStats()`: change `Task::query()` to `ProjectTask::query()`, use `completed_at IS NOT NULL` and `whereBetween('completed_at', ...)` instead of `completed()` scope on Task
- Rewrite `getTodayTasks()`: use `ProjectTask::query()->forUser($userId)->forDate(Carbon::today())->with(['board', 'column'])->take(5)->get()` instead of calling TaskService
- Add `use App\Models\ProjectManagement\ProjectTask`

**resources/views/livewire/admin/home/daily-briefing/index.blade.php**
- Line 93: Change "View All" link from `route('admin.tasks.planner.index')` to `route('admin.project-management.project-board.index')`
- Line 134: Change "Go to Daily Planner" link to "Go to Project Board" with route `admin.project-management.project-board.index`
- Update task display: remove `$task->category` references (lines 110-112)
- Update task checkbox/completion: remove `wire:click="completeTask"` or adapt for ProjectTask
- Update `$task->status === 'completed'` checks to use `$task->completed_at !== null`

**app/Services/AiChatService.php**
- Lines 202-223: Rewrite the "Tasks context" section in `gatherDashboardContext()`
  - Change `Schema::hasTable('tasks')` to `Schema::hasTable('project_tasks')`
  - Change `\App\Models\Task\Task::class` to `\App\Models\ProjectManagement\ProjectTask::class`
  - Use `completed_at IS NULL` instead of `where('status', 'pending')`
  - Use `target_date` instead of `due_date`
  - Update the context label from "TASKS" to "PROJECT TASKS" (optional but clearer)

**app/Services/DatabaseManagementService.php**
- Line 144: No change needed — `storage_path('app/public/project-tasks')` path is still valid (table name unchanged)

**database/seeders/DatabaseSeeder.php**
- Line 236: Remove `$this->call(TasksSeeder::class);`

**routes/console.php**
- Line 11: Remove `Schedule::command('tasks:generate-recurring')->dailyAt('00:05');`

---

## 6. FILES TO CREATE

### 6.1 Database Migration
- `database/migrations/2026_04_05_000001_cleanup_tasks_module_and_drop_unused_tables.php`
  - `up()`: Drop FK on `project_tasks.category_id`, drop column `category_id` from `project_tasks`, drop tables `recurring_tasks`, `tasks`, `task_categories` (in that order — tasks before task_categories due to FK)
  - `down()`: Not needed (dev mode, irreversible)

### 6.2 New Directories
- `app/Models/ProjectManagement/` (moved from app/Models/Task/)
- `app/Livewire/Admin/ProjectManagement/` (moved from app/Livewire/Admin/Tasks/)
- `app/Livewire/Admin/ProjectManagement/ProjectBoard/`
- `app/Livewire/Admin/ProjectManagement/Calendar/`
- `app/Livewire/Admin/ProjectManagement/WeeklyReview/`
- `resources/views/livewire/admin/project-management/` (moved from tasks/)
- `resources/views/livewire/admin/project-management/project-board/`
- `resources/views/livewire/admin/project-management/calendar/`
- `resources/views/livewire/admin/project-management/weekly-review/`
- `resources/views/project-management/pdf/` (moved from resources/views/tasks/pdf/)
- `routes/admin/project-management/` (moved from routes/admin/tasks/)

---

## 7. FILES TO DELETE

### Models
- `app/Models/Task/Task.php`
- `app/Models/Task/TaskCategory.php`
- `app/Models/Task/RecurringTask.php`
- The entire `app/Models/Task/` directory (after moving kept models out)

### Services
- `app/Services/TaskService.php`
- `app/Services/TaskCategoryService.php`
- `app/Services/RecurringTaskService.php`
- `app/Services/TaskImportService.php`
- `app/Services/TaskPdfService.php`
- `app/Services/AiTaskPrioritizationService.php`
- `app/Services/AiCategoryIdentificationService.php`

### Livewire Components
- `app/Livewire/Admin/Tasks/DailyPlanner/` (entire directory)
- `app/Livewire/Admin/Tasks/Categories/` (entire directory)
- `app/Livewire/Admin/Tasks/RecurringTasks/` (entire directory)
- `app/Livewire/Admin/Tasks/QuickCapture/` (entire directory)
- `app/Livewire/Admin/Tasks/AiPrioritization/` (entire directory)
- The entire `app/Livewire/Admin/Tasks/` directory (after moving kept components out)

### Views
- `resources/views/livewire/admin/tasks/daily-planner/` (entire directory)
- `resources/views/livewire/admin/tasks/categories/` (entire directory)
- `resources/views/livewire/admin/tasks/recurring-tasks/` (entire directory)
- `resources/views/livewire/admin/tasks/quick-capture/` (entire directory)
- `resources/views/livewire/admin/tasks/ai-prioritization/` (entire directory)
- `resources/views/tasks/pdf/task-list.blade.php`
- The entire `resources/views/livewire/admin/tasks/` directory (after moving kept views out)
- The entire `resources/views/tasks/` directory (after moving kept PDF view out)

### Routes
- `routes/admin/tasks/daily-planner.php`
- `routes/admin/tasks/categories.php`
- `routes/admin/tasks/recurring-tasks.php`
- `routes/admin/tasks/ai-prioritization.php`
- `routes/admin/tasks/pdf-download.php`
- The entire `routes/admin/tasks/` directory (after moving kept routes out)

### Controllers
- `app/Http/Controllers/TaskPdfController.php`

### Console
- `app/Console/Commands/GenerateRecurringTasks.php`

### Seeders
- `database/seeders/TasksSeeder.php`

---

## 8. CROSS-MODULE IMPACT

### 8.1 DailyBriefing (app/Livewire/Admin/Home/DailyBriefing/)
**Impact:** Currently imports `Task` model and `TaskService` to display today's tasks and allow completion toggling.
**Action:**
- Remove `Task` model import and `TaskService` dependency
- Source today's tasks from `ProjectTask` instead (tasks with `target_date = today`)
- Remove `completeTask()` method (or rewrite to toggle `completed_at` on `ProjectTask`)
- Update DailyBriefingService to query `ProjectTask` for quick stats and today's tasks
- Update the blade view to remove `$task->category` references and `admin.tasks.planner.index` links

### 8.2 Admin Layout (resources/views/components/layouts/admin.blade.php)
**Impact:** Sidebar has full "Tasks" section with 7 links. QuickCapture component embedded at line 774.
**Action:**
- Replace "Tasks" sidebar section with "Project Management" section containing only 3 links
- Remove `<livewire:admin.tasks.quick-capture.quick-capture />` at line 774
- Update all `routeIs()` checks

### 8.3 AiChatService (app/Services/AiChatService.php)
**Impact:** Queries `tasks` table in `gatherDashboardContext()` for AI context.
**Action:**
- Update to query `project_tasks` table via `ProjectTask` model
- Use `target_date` instead of `due_date`, `completed_at IS NULL` instead of `status = 'pending'`

### 8.4 Console (routes/console.php)
**Impact:** Schedules `tasks:generate-recurring` command daily at 00:05.
**Action:**
- Remove the schedule entry

### 8.5 DatabaseSeeder (database/seeders/DatabaseSeeder.php)
**Impact:** Calls `TasksSeeder` which seeds tasks and categories.
**Action:**
- Remove `$this->call(TasksSeeder::class)` line

### 8.6 DatabaseManagementService (app/Services/DatabaseManagementService.php)
**Impact:** References `storage_path('app/public/project-tasks')` for file cleanup.
**Action:** No change needed — the storage path is still valid.

---

## 9. EDGE CASES & RISKS

### 9.1 Livewire Auto-Discovery After Folder Rename
**Risk:** Livewire discovers components by scanning `app/Livewire/`. After renaming `Tasks/` to `ProjectManagement/`, component tag names change from `admin.tasks.*` to `admin.project-management.*`.
**Mitigation:** Since the only inline component reference is QuickCapture (being removed), and all other components are route-based, this is safe. The admin layout sidebar uses route links, not Livewire component tags.

### 9.2 Route Caching
**Risk:** Laravel caches routes. After rename, cached routes will point to old namespaces.
**Mitigation:** Run `docker compose exec app php artisan route:clear` after all changes.

### 9.3 View Caching
**Risk:** Compiled Blade views in `storage/framework/views/` may reference old paths.
**Mitigation:** Run `docker compose exec app php artisan view:clear` after all changes.

### 9.4 Livewire Component Caching
**Risk:** Livewire may cache component manifests.
**Mitigation:** Run `docker compose exec app php artisan cache:clear` after all changes.

### 9.5 Route Auto-Loading
**Risk:** Routes are auto-loaded via `glob('routes/admin/*/*.php')` in `bootstrap/app.php`. After renaming `routes/admin/tasks/` to `routes/admin/project-management/`, the glob pattern will auto-discover the new directory. However, if both old and new directories exist during migration, routes may conflict.
**Mitigation:** Delete old route directory completely before (or immediately after) creating the new one. Never have both active simultaneously.

### 9.6 WeeklyReviewService Rewrite — Data Compatibility
**Risk:** Existing `weekly_reviews` rows have `category_breakdown` JSON data referencing categories. New reviews will have empty category breakdown.
**Mitigation:** This is acceptable. Historical data is preserved as-is. The view should handle empty `category_breakdown` gracefully (already an array, just empty).

### 9.7 CalendarService Rewrite — Personal vs Project Split
**Risk:** The calendar currently shows both "personal" tasks (from `tasks` table) and "project" tasks. After removing `tasks` table, only project tasks remain. The `getTasksForDate()` return format changes.
**Mitigation:** The calendar view and CalendarIndex component both reference `selectedDayTasks['personal']` and `selectedDayTasks['project']`. Either:
  - (a) Return empty `personal` and populate `project` only (minimal view changes), or
  - (b) Flatten to a single list (requires more view changes).
  Recommend option (a) for simplicity. Update the calendar blade to remove the "Personal Tasks" section header but keep the structure working.

### 9.8 DailyBriefing `completeTask` Method
**Risk:** The `completeTask()` method uses `TaskService::toggleComplete()` which is being removed.
**Mitigation:** Either remove task completion from the briefing entirely, or rewrite it to toggle `completed_at` on `ProjectTask` directly. Recommend removing it — the briefing is a summary view, not an action view.

### 9.9 Broken Test References
**Risk:** If any tests reference old route names, namespaces, or models, they will fail.
**Mitigation:** Search test files for `admin.tasks.`, `App\Models\Task\`, and `TaskService` references. Update or remove affected tests.

### 9.10 Storage Path for Project Task Images
**Risk:** Existing images stored at `storage/app/public/project-tasks/{task_id}/` — no change needed since table name is unchanged.
**Mitigation:** None needed.

---

## 10. IMPLEMENTATION ORDER

Execute these steps sequentially. Each step should be verified before proceeding.

### Step 1: Create the Database Migration
```
docker compose exec app php artisan make:migration cleanup_tasks_module_and_drop_unused_tables
```
Write the migration to:
1. Drop FK constraint on `project_tasks.category_id`
2. Drop `category_id` column from `project_tasks`
3. Drop `recurring_tasks` table
4. Drop `tasks` table
5. Drop `task_categories` table

Run the migration:
```
docker compose exec app php artisan migrate
```

### Step 2: Remove Unused Files (Phase 1 Cleanup)
Delete in this order:
1. `routes/console.php` — remove the schedule line (edit, not delete the file)
2. `app/Console/Commands/GenerateRecurringTasks.php`
3. Services: `TaskService.php`, `TaskCategoryService.php`, `RecurringTaskService.php`, `TaskImportService.php`, `TaskPdfService.php`, `AiTaskPrioritizationService.php`, `AiCategoryIdentificationService.php`
4. Controller: `app/Http/Controllers/TaskPdfController.php`
5. Models: `app/Models/Task/Task.php`, `app/Models/Task/TaskCategory.php`, `app/Models/Task/RecurringTask.php`
6. Livewire dirs: `DailyPlanner/`, `Categories/`, `RecurringTasks/`, `QuickCapture/`, `AiPrioritization/`
7. View dirs: `daily-planner/`, `categories/`, `recurring-tasks/`, `quick-capture/`, `ai-prioritization/`
8. Route files: `daily-planner.php`, `categories.php`, `recurring-tasks.php`, `ai-prioritization.php`, `pdf-download.php`
9. PDF view: `resources/views/tasks/pdf/task-list.blade.php`
10. Seeder: `database/seeders/TasksSeeder.php`

### Step 3: Update Cross-Module Files (Phase 1 fixes)
1. `database/seeders/DatabaseSeeder.php` — remove `$this->call(TasksSeeder::class)`
2. `routes/console.php` — remove schedule entry
3. `resources/views/components/layouts/admin.blade.php` — remove QuickCapture line (774), remove Daily Planner/Categories/Recurring Tasks/AI Prioritization sidebar links

### Step 4: Create New Directory Structure (Phase 2 Rename)
```
mkdir -p app/Models/ProjectManagement
mkdir -p app/Livewire/Admin/ProjectManagement/ProjectBoard
mkdir -p app/Livewire/Admin/ProjectManagement/Calendar
mkdir -p app/Livewire/Admin/ProjectManagement/WeeklyReview
mkdir -p resources/views/livewire/admin/project-management/project-board
mkdir -p resources/views/livewire/admin/project-management/calendar
mkdir -p resources/views/livewire/admin/project-management/weekly-review
mkdir -p resources/views/project-management/pdf
mkdir -p routes/admin/project-management
```

### Step 5: Move and Update Models
Move each model from `app/Models/Task/` to `app/Models/ProjectManagement/`:
- `ProjectBoard.php` — update namespace
- `ProjectBoardColumn.php` — update namespace
- `ProjectTask.php` — update namespace, remove `category_id` from fillable, remove `category()` relationship
- `ProjectTaskImage.php` — update namespace
- `WeeklyReview.php` — update namespace

Delete `app/Models/Task/` directory.

### Step 6: Move and Update Livewire Components
Move from `app/Livewire/Admin/Tasks/` to `app/Livewire/Admin/ProjectManagement/`:
- `ProjectBoard/ProjectBoardIndex.php` — update namespace, imports, view path, remove category logic
- `Calendar/CalendarIndex.php` — update namespace, imports, view path, remove Task/TaskService usage
- `WeeklyReview/WeeklyReviewIndex.php` — update namespace, imports, view path

Delete `app/Livewire/Admin/Tasks/` directory.

### Step 7: Move and Update Views
Move from `resources/views/livewire/admin/tasks/` to `resources/views/livewire/admin/project-management/`:
- `project-board/index.blade.php` — update breadcrumbs, route references, remove category UI
- `calendar/index.blade.php` — update breadcrumbs, remove category UI, remove personal tasks section
- `weekly-review/index.blade.php` — update breadcrumbs

Move `resources/views/tasks/pdf/project-board.blade.php` to `resources/views/project-management/pdf/project-board.blade.php`.

Delete `resources/views/livewire/admin/tasks/` and `resources/views/tasks/` directories.

### Step 8: Move and Update Route Files
Move from `routes/admin/tasks/` to `routes/admin/project-management/`:
- `project-board.php` — update import, URL, route name
- `calendar.php` — update import, URL, route name
- `weekly-review.php` — update import, URL, route name
- `project-board-export.php` — update URL, route name

Delete `routes/admin/tasks/` directory.

### Step 9: Update Services
1. `ProjectBoardService.php` — update model imports
2. `ProjectTaskService.php` — update model imports, remove category eager loads
3. `ProjectBoardExportService.php` — update model imports, PDF view path, remove category references
4. `WeeklyReviewService.php` — rewrite to use ProjectTask instead of Task, update model imports
5. `CalendarService.php` — rewrite to use only ProjectTask, remove Task queries and category method

### Step 10: Update Admin Layout Sidebar
`resources/views/components/layouts/admin.blade.php`:
- Rewrite the Tasks collapsible section as "Project Management"
- Only include Project Board, Calendar, Weekly Review links
- Update all route references to `admin.project-management.*`

### Step 11: Update Cross-Module References
1. `app/Livewire/Admin/Home/DailyBriefing/DailyBriefingIndex.php` — remove Task/TaskService, update for ProjectTask
2. `app/Services/DailyBriefingService.php` — rewrite task queries to use ProjectTask
3. `resources/views/livewire/admin/home/daily-briefing/index.blade.php` — update route links, remove category references
4. `app/Services/AiChatService.php` — update gatherDashboardContext() to query project_tasks

### Step 12: Clear All Caches
```
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
```

### Step 13: Verify
```
docker compose exec app php artisan route:list --path=project-management
docker compose exec app php artisan test
```

Search for any remaining references:
```
docker compose exec app grep -r "admin\.tasks\." --include="*.php" --include="*.blade.php" app/ resources/ routes/
docker compose exec app grep -r "App\\\\Models\\\\Task\\\\" --include="*.php" app/ routes/ database/
docker compose exec app grep -r "TaskService" --include="*.php" app/
docker compose exec app grep -r "TaskCategory" --include="*.php" app/
```

### Step 14: Run Pint
```
docker compose exec app ./vendor/bin/pint
```

---

## SUMMARY OF COUNTS

| Action | Count |
|--------|-------|
| Files to DELETE | ~30+ (across models, services, components, views, routes, commands, seeders) |
| Files to MOVE + MODIFY | 18 (models, components, views, routes, services) |
| Files to MODIFY only (cross-module) | 7 (DailyBriefing component + service + view, AiChatService, admin layout, DatabaseSeeder, console.php) |
| Files to CREATE | 1 (migration file) |
| Directories to CREATE | 11 |
| Directories to DELETE | ~15 (old Task-based directories) |
| Database tables to DROP | 3 (tasks, task_categories, recurring_tasks) |
| Database columns to DROP | 1 (project_tasks.category_id) |
