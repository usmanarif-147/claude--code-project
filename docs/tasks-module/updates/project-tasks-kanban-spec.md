# Project Tasks Kanban Board — Improvement Spec

## 1. UPDATE OVERVIEW

Add a Kanban-style "Project Board" feature to the existing Tasks module. Project tasks are completely separate from personal tasks (Daily Planner). Each board has customizable columns, and tasks can be dragged between columns using SortableJS. This is a single-page Livewire component with inline modals for board CRUD, column management, and task CRUD including image uploads.

**Key difference from personal tasks:**
- Personal tasks (Daily Planner) use a flat list with date-based navigation and status field (pending/in_progress/completed)
- Project tasks use a Kanban board with multiple boards, columns, drag-drop positioning, image attachments, and tags

**Scope:** New feature only — no modifications to existing personal task logic. The Calendar view will later query both task types (handled by a separate calendar spec).

---

## 2. CURRENT STATE (BEFORE)

### Existing Tasks Module Structure

**Models (app/Models/Task/):**
- `Task.php` — personal tasks with fields: user_id, category_id, title, description, due_date, priority, status, completed_at, sort_order
- `TaskCategory.php` — shared categories with name, color, sort_order
- `RecurringTask.php` — repeating task templates
- `WeeklyReview.php` — weekly summary snapshots

**Services (app/Services/):**
- `TaskService.php` — CRUD, toggleComplete, moveIncompleteTo, getTasksForDate, getCompletionStats
- `TaskCategoryService.php` — category CRUD
- `RecurringTaskService.php` — recurring task management
- `WeeklyReviewService.php` — weekly review generation
- `CalendarService.php` — calendar data aggregation
- `AiTaskPrioritizationService.php` — AI-based task ordering

**Livewire Components (app/Livewire/Admin/Tasks/):**
- `DailyPlanner/DailyPlannerIndex.php`
- `Categories/TaskCategoryIndex.php`, `TaskCategoryForm.php`
- `Calendar/CalendarIndex.php`
- `RecurringTasks/RecurringTaskIndex.php`, `RecurringTaskForm.php`
- `WeeklyReview/WeeklyReviewIndex.php`
- `AiPrioritization/AiPrioritizationIndex.php`
- `QuickCapture/QuickCapture.php`

**Routes (routes/admin/tasks/):**
- `daily-planner.php`, `categories.php`, `calendar.php`, `recurring-tasks.php`, `weekly-review.php`, `ai-prioritization.php`

**Sidebar order:** Daily Planner, Categories, Recurring Tasks, Calendar, AI Prioritization, Weekly Review

**No existing project board/kanban tables, models, services, or components exist.** This is a greenfield addition to the Tasks module.

---

## 3. TARGET STATE (AFTER)

### 3.1 Database Schema (4 new tables)

#### `project_boards`
| Column | Type | Constraints |
|---|---|---|
| id | bigint unsigned | PK, auto-increment |
| user_id | bigint unsigned | FK -> users.id, CASCADE on delete |
| name | varchar(255) | NOT NULL |
| description | text | NULLABLE |
| sort_order | unsigned integer | DEFAULT 0 |
| created_at | timestamp | NULLABLE |
| updated_at | timestamp | NULLABLE |

Indexes: `project_boards_user_id_foreign` on user_id

#### `project_board_columns`
| Column | Type | Constraints |
|---|---|---|
| id | bigint unsigned | PK, auto-increment |
| board_id | bigint unsigned | FK -> project_boards.id, CASCADE on delete |
| name | varchar(255) | NOT NULL |
| color | varchar(7) | NULLABLE (hex color, e.g. "#7c3aed") |
| sort_order | unsigned integer | DEFAULT 0 |
| is_completed_column | boolean | DEFAULT false |
| created_at | timestamp | NULLABLE |
| updated_at | timestamp | NULLABLE |

Indexes: `project_board_columns_board_id_foreign` on board_id

#### `project_tasks`
| Column | Type | Constraints |
|---|---|---|
| id | bigint unsigned | PK, auto-increment |
| board_id | bigint unsigned | FK -> project_boards.id, CASCADE on delete |
| column_id | bigint unsigned | FK -> project_board_columns.id, CASCADE on delete |
| category_id | bigint unsigned | NULLABLE, FK -> task_categories.id, SET NULL on delete |
| user_id | bigint unsigned | FK -> users.id, CASCADE on delete |
| title | varchar(255) | NOT NULL |
| description | text | NULLABLE |
| priority | varchar(20) | DEFAULT 'medium' |
| target_date | date | NULLABLE |
| tags | json | NULLABLE |
| position | unsigned integer | DEFAULT 0 |
| completed_at | timestamp | NULLABLE |
| created_at | timestamp | NULLABLE |
| updated_at | timestamp | NULLABLE |

Indexes: `project_tasks_board_id_foreign`, `project_tasks_column_id_foreign`, `project_tasks_category_id_foreign`, `project_tasks_user_id_foreign`

#### `project_task_images`
| Column | Type | Constraints |
|---|---|---|
| id | bigint unsigned | PK, auto-increment |
| project_task_id | bigint unsigned | FK -> project_tasks.id, CASCADE on delete |
| image_path | varchar(255) | NOT NULL |
| sort_order | unsigned integer | DEFAULT 0 |
| created_at | timestamp | NULLABLE |
| updated_at | timestamp | NULLABLE |

Indexes: `project_task_images_project_task_id_foreign` on project_task_id

### 3.2 Models (all in app/Models/Task/)

Since the Task/ subfolder already has 2+ models (Task, TaskCategory, RecurringTask, WeeklyReview), all new models go in the same `app/Models/Task/` directory.

#### `ProjectBoard.php`
```php
namespace App\Models\Task;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectBoard extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    // --- Relationships ---

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function columns(): HasMany
    {
        return $this->hasMany(ProjectBoardColumn::class, 'board_id')->orderBy('sort_order');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class, 'board_id');
    }

    // --- Scopes ---

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
```

#### `ProjectBoardColumn.php`
```php
namespace App\Models\Task;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectBoardColumn extends Model
{
    protected $fillable = [
        'board_id',
        'name',
        'color',
        'sort_order',
        'is_completed_column',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_completed_column' => 'boolean',
        ];
    }

    // --- Relationships ---

    public function board(): BelongsTo
    {
        return $this->belongsTo(ProjectBoard::class, 'board_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class, 'column_id')->orderBy('position');
    }

    // --- Scopes ---

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }
}
```

#### `ProjectTask.php`
```php
namespace App\Models\Task;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class ProjectTask extends Model
{
    protected $fillable = [
        'board_id',
        'column_id',
        'category_id',
        'user_id',
        'title',
        'description',
        'priority',
        'target_date',
        'tags',
        'position',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'target_date' => 'date',
            'tags' => 'array',
            'position' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    // --- Relationships ---

    public function board(): BelongsTo
    {
        return $this->belongsTo(ProjectBoard::class, 'board_id');
    }

    public function column(): BelongsTo
    {
        return $this->belongsTo(ProjectBoardColumn::class, 'column_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TaskCategory::class, 'category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProjectTaskImage::class)->orderBy('sort_order');
    }

    // --- Scopes ---

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForBoard(Builder $query, int $boardId): Builder
    {
        return $query->where('board_id', $boardId);
    }

    public function scopeForColumn(Builder $query, int $columnId): Builder
    {
        return $query->where('column_id', $columnId);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position');
    }

    public function scopeByPriority(Builder $query): Builder
    {
        return $query->orderByRaw("CASE priority WHEN 'urgent' THEN 0 WHEN 'high' THEN 1 WHEN 'medium' THEN 2 WHEN 'low' THEN 3 END");
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereNotNull('completed_at');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('completed_at');
    }

    public function scopeForDate(Builder $query, Carbon $date): Builder
    {
        return $query->whereDate('target_date', $date);
    }

    public function scopeForDateRange(Builder $query, Carbon $start, Carbon $end): Builder
    {
        return $query->whereBetween('target_date', [$start->startOfDay(), $end->endOfDay()]);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }
}
```

#### `ProjectTaskImage.php`
```php
namespace App\Models\Task;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTaskImage extends Model
{
    protected $fillable = [
        'project_task_id',
        'image_path',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'project_task_id');
    }
}
```

### 3.3 Services (app/Services/)

#### `ProjectBoardService.php`

```php
namespace App\Services;

use App\Models\Task\ProjectBoard;
use App\Models\Task\ProjectBoardColumn;
use Illuminate\Support\Collection;

class ProjectBoardService
{
    /**
     * Get all boards for a user, ordered by sort_order.
     */
    public function getBoards(int $userId): Collection;

    /**
     * Get a single board with columns and tasks eager-loaded.
     * Eager loads: columns.tasks.category, columns.tasks.images
     */
    public function getBoard(int $boardId): ProjectBoard;

    /**
     * Create a board and 5 default columns:
     *   1. New (color: #8b5cf6, sort_order: 0)
     *   2. Todo (color: #f59e0b, sort_order: 1)
     *   3. On Going (color: #3b82f6, sort_order: 2)
     *   4. In Review (color: #f97316, sort_order: 3)
     *   5. Completed (color: #22c55e, sort_order: 4, is_completed_column: true)
     */
    public function createBoard(array $data): ProjectBoard;

    /**
     * Update board name/description.
     */
    public function updateBoard(ProjectBoard $board, array $data): ProjectBoard;

    /**
     * Delete a board (cascades to columns and tasks).
     */
    public function deleteBoard(ProjectBoard $board): void;

    /**
     * Add a custom column to a board.
     * Sets sort_order to max(existing) + 1.
     */
    public function addColumn(int $boardId, array $data): ProjectBoardColumn;

    /**
     * Update column name, color, or is_completed_column flag.
     */
    public function updateColumn(ProjectBoardColumn $column, array $data): ProjectBoardColumn;

    /**
     * Delete a column. Fails if column has tasks (must move tasks first).
     * Returns false if column has tasks, true on successful delete.
     */
    public function deleteColumn(ProjectBoardColumn $column): bool;

    /**
     * Reorder columns by accepting an array of [column_id => sort_order].
     */
    public function reorderColumns(array $columnOrder): void;
}
```

#### `ProjectTaskService.php`

```php
namespace App\Services;

use App\Models\Task\ProjectTask;
use App\Models\Task\ProjectTaskImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ProjectTaskService
{
    /**
     * Create a project task.
     * Sets position to max(position in target column) + 1.
     */
    public function create(array $data): ProjectTask;

    /**
     * Update a project task. Does NOT change column/position.
     */
    public function update(ProjectTask $task, array $data): ProjectTask;

    /**
     * Delete a project task and its images from storage.
     */
    public function delete(ProjectTask $task): void;

    /**
     * Move a task to a target column at a given position (within the same board).
     * - Updates column_id and position
     * - Reorders other tasks in the old and new columns
     * - If target column is_completed_column, sets completed_at = now()
     * - If moving OUT of a completed column, clears completed_at
     */
    public function moveToColumn(int $taskId, int $targetColumnId, int $position): ProjectTask;

    /**
     * Move a task to another board entirely.
     * Places the task in the first column (lowest sort_order) of the target board.
     * Updates board_id, column_id, sets position to end of target column.
     * Handles completed_at based on target column's is_completed_column flag.
     */
    public function moveToBoard(int $taskId, int $targetBoardId): ProjectTask;

    /**
     * Reorder tasks within a single column.
     * Accepts array of [task_id => position].
     */
    public function reorderInColumn(int $columnId, array $taskOrder): void;

    /**
     * Get project tasks for a specific date (by target_date).
     * Used by Calendar view.
     */
    public function getTasksForDate(int $userId, Carbon $date): Collection;

    /**
     * Get project tasks for a date range (by target_date).
     * Used by Calendar month view.
     */
    public function getTasksForDateRange(int $userId, Carbon $start, Carbon $end): Collection;

    /**
     * Upload images for a task. Stores in 'project-tasks/{task_id}/' directory.
     * @param UploadedFile[] $files
     * @return ProjectTaskImage[]
     */
    public function uploadImages(ProjectTask $task, array $files): array;

    /**
     * Delete a single image from storage and database.
     */
    public function deleteImage(ProjectTaskImage $image): void;
}
```

### 3.4 Livewire Component

**File:** `app/Livewire/Admin/Tasks/ProjectBoard/ProjectBoardIndex.php`

```php
namespace App\Livewire\Admin\Tasks\ProjectBoard;

use App\Models\Task\ProjectBoard;
use App\Models\Task\ProjectBoardColumn;
use App\Models\Task\ProjectTask;
use App\Models\Task\TaskCategory;
use App\Services\ProjectBoardService;
use App\Services\ProjectTaskService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
class ProjectBoardIndex extends Component
{
    use WithFileUploads;

    // --- Board Selection ---
    #[Url]
    public ?int $selectedBoardId = null;

    // --- Search & Filter ---
    public string $search = '';
    public string $priorityFilter = 'all';
    public string $categoryFilter = 'all';

    // --- New Board Modal ---
    public bool $showNewBoardModal = false;
    public string $newBoardName = '';
    public string $newBoardDescription = '';

    // --- Task Modal ---
    public bool $showTaskModal = false;
    public ?int $editingTaskId = null;
    public ?int $taskColumnId = null;       // which column the "Add Task" was clicked in
    public string $taskTitle = '';
    public string $taskDescription = '';
    public string $taskPriority = 'medium';
    public ?string $taskTargetDate = null;
    public ?string $taskCategoryId = '';
    public array $taskTags = [];
    public string $tagInput = '';
    public array $taskImages = [];          // Livewire TemporaryUploadedFile[]
    public array $existingImages = [];      // for edit mode — [{id, image_path}]

    // --- Column Management ---
    public bool $showColumnModal = false;
    public string $newColumnName = '';
    public string $newColumnColor = '#7c3aed';

    // --- Methods ---

    public function mount(): void;
        // If no selectedBoardId, select the first board for the user
        // Load board data

    public function selectBoard(int $boardId): void;
        // Set selectedBoardId, reset search/filters

    // -- Board CRUD --
    public function openNewBoardModal(): void;
    public function createBoard(ProjectBoardService $service): void;
        // Validate: newBoardName required|string|max:255
        // Create board, select it, close modal, flash success
    public function deleteBoard(ProjectBoardService $service, int $boardId): void;
        // Confirm via wire:confirm, delete, select next board or null, flash

    // -- Column CRUD --
    public function openColumnModal(): void;
    public function addColumn(ProjectBoardService $service): void;
        // Validate: newColumnName required|string|max:100, newColumnColor nullable|string|max:7
        // Add column, close modal, flash success
    public function deleteColumn(ProjectBoardService $service, int $columnId): void;
        // Confirm via wire:confirm, attempt delete, flash error if has tasks

    // -- Task CRUD --
    public function openTaskModal(?int $columnId = null, ?int $taskId = null): void;
        // If taskId: load task data into form fields (edit mode)
        // If columnId only: set taskColumnId (create mode)
    public function createTask(ProjectTaskService $service): void;
        // Validate task fields, create, upload images, close modal, flash
    public function updateTask(ProjectTaskService $service): void;
        // Validate task fields, update, handle new images, close modal, flash
    public function deleteTask(ProjectTaskService $service, int $taskId): void;
        // Confirm via wire:confirm, delete, flash
    public function closeTaskModal(): void;
        // Reset all task form fields

    // -- Drag-Drop --
    public function moveTask(ProjectTaskService $service, int $taskId, int $columnId, int $position): void;
        // Called from Alpine/SortableJS via $wire.moveTask()
        // Calls service->moveToColumn()
        // Works for both intra-column reorder AND cross-column moves within same board

    // -- Cross-Board Move --
    public function moveTaskToBoard(ProjectTaskService $service, int $taskId, int $targetBoardId): void;
        // Move a task to the first column of another board (adjacent board navigation)
        // Called via UI buttons (← →) on each task card for moving to prev/next board
        // Service finds the first (lowest sort_order) column of targetBoardId
        // Updates task's board_id and column_id, sets position to end of target column
        // If target column is_completed_column, sets completed_at; otherwise clears it

    // -- Tags --
    public function addTag(): void;
        // Push tagInput to taskTags array, clear tagInput
    public function removeTag(int $index): void;
        // Remove tag at index from taskTags

    // -- Images --
    public function removeExistingImage(ProjectTaskService $service, int $imageId): void;
        // Delete image via service, remove from existingImages array

    public function render(): \Illuminate\Contracts\View\View;
        // Load boards for user
        // If selectedBoardId: load board with columns.tasks.category, columns.tasks.images
        // Apply search filter to tasks if $search is not empty
        // Apply priority/category filters
        // Load categories for the task form dropdown
        // Return view with: boards, selectedBoard, categories
}
```

### 3.5 View

**File:** `resources/views/livewire/admin/tasks/project-board/index.blade.php`

**Layout structure (matching Xintra screenshots):**

```
[Breadcrumb: Dashboard > Tasks > Project Board]

[Page Header Row]
  Left: "PROJECT BOARD" h1 (font-mono uppercase tracking-wider)
  Right: (empty or board count)

[Control Bar — bg-dark-800 rounded-xl p-4 mb-6]
  Left:
    - Board selector dropdown (select element, wire:model.live="selectedBoardId")
    - Search input (wire:model.live.debounce.300ms="search")
  Right:
    - Priority filter dropdown
    - Category filter dropdown
    - "+ New Board" button (opens showNewBoardModal)

[Kanban Container — horizontal scroll, full viewport height]
  <div class="flex gap-5 overflow-x-auto pb-4" style="height: calc(100vh - 280px);">
    @foreach($selectedBoard->columns as $column)
      [Column — w-80 shrink-0 bg-dark-800 border border-dark-700 rounded-xl flex flex-col max-h-full]
        [Column Header — px-4 py-3 border-b border-dark-700 shrink-0]
          Left: colored dot (column color) + column name (font-mono uppercase tracking-wider text-sm)
          Right: task count badge + delete column icon button
        [Task List — flex-1 overflow-y-auto p-3 space-y-3 min-h-0]
          ** IMPORTANT: overflow-y-auto + min-h-0 ensures column scrolls when tasks exceed screen height **
          Alpine x-data with SortableJS initialization
          @foreach($column->tasks as $task)
            [Task Card — bg-dark-700 border border-dark-600 rounded-lg p-3 cursor-grab]
              - Title (text-sm font-medium text-white)
              - Description snippet (text-xs text-gray-500 line-clamp-2)
              - Bottom row: priority badge, tags, target_date
              - Click card title/body → opens task modal in edit mode
              - Move-to-board buttons: if user has multiple boards, show small ← → arrow buttons
                wire:click="moveTaskToBoard({{ $task->id }}, {{ $prevBoardId }})" (← prev board)
                wire:click="moveTaskToBoard({{ $task->id }}, {{ $nextBoardId }})" (→ next board)
                Tooltip shows target board name. Hide arrow if no prev/next board exists.
          @endforeach
        [Add Task Button — px-3 py-2 border-t border-dark-700 shrink-0]
          wire:click="openTaskModal({{ $column->id }})"
          Text: "+ Add Task" (text-sm text-gray-500 hover:text-primary-light)
    @endforeach

    [Add Column Button — w-80 shrink-0 bg-dark-800/50 border border-dashed border-dark-600 rounded-xl]
      wire:click="openColumnModal"
      Center: "+" icon + "Add Column" text
  </div>

[Empty State — when no boards exist]
  Standard empty state card with "Create your first project board" CTA

[New Board Modal — Livewire-controlled via $showNewBoardModal]
  Title: "Add Board"
  Fields:
    - Task Board Title (text input, wire:model="newBoardName") — required
    - Description (textarea, wire:model="newBoardDescription") — optional
  Buttons: Cancel, "Add Board" (primary)

[Add Column Modal — Livewire-controlled via $showColumnModal]
  Title: "Add Column"
  Fields:
    - Column Name (text input, wire:model="newColumnName") — required
    - Color (color input, wire:model="newColumnColor") — optional
  Buttons: Cancel, "Add Column" (primary)

[Task Modal — Livewire-controlled via $showTaskModal]
  Title: "Add Task" or "Edit Task" (based on editingTaskId)
  Width: max-w-lg
  Fields (matching screenshot 3):
    Row 1: Task Name (text, wire:model="taskTitle") | Task ID (readonly, shown in edit mode only)
    Row 2: Task Description (textarea, wire:model="taskDescription") — full width
    Row 3: Task Images (drag-drop file upload zone, wire:model="taskImages") — full width
      - Shows existing images in edit mode with delete buttons
      - Supports multiple file uploads
    Row 4: Target Date (date input, wire:model="taskTargetDate") | Tags (tag input with wire:model="tagInput", add/remove)
    Row 5: Priority (select: low/medium/high/urgent, wire:model="taskPriority") | Category (select from TaskCategory, wire:model="taskCategoryId")
  Buttons: Cancel, "Add Task" / "Update Task" (primary)
```

**Priority badges (reuse from DailyPlanner pattern):**
- urgent: `bg-red-500/10 text-red-400`
- high: `bg-amber-500/10 text-amber-400`
- medium: `bg-blue-500/10 text-blue-400`
- low: `bg-emerald-500/10 text-emerald-400`

**SortableJS integration (via @push('scripts')):**
```blade
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
<script>
    document.addEventListener('livewire:navigated', () => {
        initSortable();
    });

    document.addEventListener('livewire:morph', () => {
        // Re-init after Livewire DOM updates
        setTimeout(() => initSortable(), 100);
    });

    function initSortable() {
        document.querySelectorAll('[data-sortable-column]').forEach(el => {
            if (el._sortable) el._sortable.destroy();
            el._sortable = new Sortable(el, {
                group: 'kanban',
                animation: 150,
                ghostClass: 'opacity-30',
                dragClass: 'rotate-2',
                handle: '[data-task-card]',
                onEnd: function (evt) {
                    const taskId = parseInt(evt.item.dataset.taskId);
                    const targetColumnId = parseInt(evt.to.dataset.sortableColumn);
                    const newPosition = evt.newIndex;
                    Livewire.find(evt.item.closest('[wire\\:id]').getAttribute('wire:id'))
                        .call('moveTask', taskId, targetColumnId, newPosition);
                }
            });
        });
    }
</script>
@endpush
```

### 3.6 Route

**File:** `routes/admin/tasks/project-board.php`

```php
<?php

use App\Livewire\Admin\Tasks\ProjectBoard\ProjectBoardIndex;
use Illuminate\Support\Facades\Route;

Route::get('/tasks/project-board', ProjectBoardIndex::class)
    ->name('admin.tasks.project-board.index');
```

This file is auto-discovered by `glob('routes/admin/*/*.php')` in `bootstrap/app.php` — no registration needed.

### 3.7 Sidebar

Add "Project Board" link in the Tasks sidebar group in `resources/views/components/layouts/admin.blade.php`.

**Position:** After "Recurring Tasks", before "Calendar" — matching the requirement.

```blade
<a href="{{ route('admin.tasks.project-board.index') }}"
   class="flex items-center gap-3 pl-10 pr-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.tasks.project-board.*') ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white hover:bg-dark-700' }} transition-colors">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
    </svg>
    Project Board
</a>
```

---

## 4. MIGRATION PATH

### Migration 1: `create_project_boards_table`
```php
Schema::create('project_boards', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->text('description')->nullable();
    $table->unsignedInteger('sort_order')->default(0);
    $table->timestamps();
});
```

### Migration 2: `create_project_board_columns_table`
```php
Schema::create('project_board_columns', function (Blueprint $table) {
    $table->id();
    $table->foreignId('board_id')->constrained('project_boards')->cascadeOnDelete();
    $table->string('name');
    $table->string('color', 7)->nullable();
    $table->unsignedInteger('sort_order')->default(0);
    $table->boolean('is_completed_column')->default(false);
    $table->timestamps();
});
```

### Migration 3: `create_project_tasks_table`
```php
Schema::create('project_tasks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('board_id')->constrained('project_boards')->cascadeOnDelete();
    $table->foreignId('column_id')->constrained('project_board_columns')->cascadeOnDelete();
    $table->foreignId('category_id')->nullable()->constrained('task_categories')->nullOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('title');
    $table->text('description')->nullable();
    $table->string('priority', 20)->default('medium');
    $table->date('target_date')->nullable();
    $table->json('tags')->nullable();
    $table->unsignedInteger('position')->default(0);
    $table->timestamp('completed_at')->nullable();
    $table->timestamps();
});
```

### Migration 4: `create_project_task_images_table`
```php
Schema::create('project_task_images', function (Blueprint $table) {
    $table->id();
    $table->foreignId('project_task_id')->constrained('project_tasks')->cascadeOnDelete();
    $table->string('image_path');
    $table->unsignedInteger('sort_order')->default(0);
    $table->timestamps();
});
```

**Order of execution:** Migrations must run in exact sequence (1 through 4) because of foreign key dependencies.

---

## 5. FILES TO CREATE

### 5.1 Migrations (4 files)
| File | Location |
|---|---|
| `YYYY_MM_DD_000001_create_project_boards_table.php` | `database/migrations/` |
| `YYYY_MM_DD_000002_create_project_board_columns_table.php` | `database/migrations/` |
| `YYYY_MM_DD_000003_create_project_tasks_table.php` | `database/migrations/` |
| `YYYY_MM_DD_000004_create_project_task_images_table.php` | `database/migrations/` |

Contents: See Section 4 above for exact schema.

### 5.2 Models (4 files)
| File | Location |
|---|---|
| `ProjectBoard.php` | `app/Models/Task/` |
| `ProjectBoardColumn.php` | `app/Models/Task/` |
| `ProjectTask.php` | `app/Models/Task/` |
| `ProjectTaskImage.php` | `app/Models/Task/` |

Contents: See Section 3.2 above for exact code.

### 5.3 Services (2 files)
| File | Location |
|---|---|
| `ProjectBoardService.php` | `app/Services/` |
| `ProjectTaskService.php` | `app/Services/` |

Contents: See Section 3.3 above for exact method signatures and behavior.

### 5.4 Livewire Component (1 file)
| File | Location |
|---|---|
| `ProjectBoardIndex.php` | `app/Livewire/Admin/Tasks/ProjectBoard/` |

Contents: See Section 3.4 above for exact properties, methods, and behavior.

### 5.5 View (1 file)
| File | Location |
|---|---|
| `index.blade.php` | `resources/views/livewire/admin/tasks/project-board/` |

Contents: See Section 3.5 above for exact layout structure.

### 5.6 Route (1 file)
| File | Location |
|---|---|
| `project-board.php` | `routes/admin/tasks/` |

Contents: See Section 3.6 above for exact route definition.

**Total new files: 13**

---

## 6. FILES TO MODIFY

### 6.1 `resources/views/components/layouts/admin.blade.php`

**What to change:** Add "Project Board" sidebar link inside the Tasks collapsible menu.

**Where:** After the "Recurring Tasks" link, before the "Calendar" link.

**Exact content to insert:** See Section 3.7 above.

**No other files require modification.** The route auto-discovery, model autoloading, and service injection all work automatically.

---

## 7. CROSS-MODULE IMPACT

### Calendar View (future spec handles this)
- `CalendarService` or `CalendarIndex` will need to query `ProjectTask::forUser()->forDateRange()` in addition to `Task::forUser()->forDate()`
- When a user clicks a date in the calendar, show both personal tasks and project tasks in separate sections
- Show project tasks only if at least 1 exists for that date; same for personal tasks
- **This is NOT part of this spec** — the calendar spec will handle integration

### Daily Briefing
- `DailyBriefingService` currently aggregates personal task stats
- Project tasks are separate and do NOT appear in the Daily Briefing until explicitly added in a future update
- **No changes needed now**

### Quick Capture
- Quick Capture creates personal tasks only
- Project tasks are always created within a board context (specific column)
- **No changes needed now**

### AI Prioritization
- AI Prioritization currently works on personal tasks
- Could be extended to project tasks later but **no changes needed now**

### Weekly Review
- Weekly Review summarizes personal task completion
- Could include project task metrics later but **no changes needed now**

### TaskCategory (shared)
- Project tasks share `task_categories` with personal tasks via `category_id` FK
- This is intentional — categories like "Work", "Personal", "Learning" apply to both
- The `nullOnDelete` FK ensures deleting a category does not delete project tasks

---

## 8. VALIDATION RULES

### Board Creation/Update
```php
'newBoardName' => 'required|string|max:255',
'newBoardDescription' => 'nullable|string|max:1000',
```

### Column Creation
```php
'newColumnName' => 'required|string|max:100',
'newColumnColor' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
```

### Task Creation/Update
```php
'taskTitle' => 'required|string|max:255',
'taskDescription' => 'nullable|string|max:5000',
'taskPriority' => 'required|in:low,medium,high,urgent',
'taskTargetDate' => 'nullable|date',
'taskCategoryId' => 'nullable|exists:task_categories,id',
'taskTags' => 'nullable|array|max:10',
'taskTags.*' => 'string|max:50',
'taskImages' => 'nullable|array|max:5',
'taskImages.*' => 'image|max:2048',  // 2MB per image
```

### Move Task (called via JS, validated in PHP)
```php
'taskId' => 'required|integer|exists:project_tasks,id',
'columnId' => 'required|integer|exists:project_board_columns,id',
'position' => 'required|integer|min:0',
```

---

## 9. EDGE CASES & RISKS

### 9.1 SortableJS + Livewire Morph Conflict
**Risk:** Livewire re-renders the DOM after each request, which destroys SortableJS instances.
**Mitigation:**
- Listen for `livewire:morph` event and re-initialize SortableJS on all column containers after a short delay (100ms)
- Use `wire:ignore` on the task list container to prevent Livewire from morphing the drag area during moves
- For `moveTask()`, use `$this->skipRender()` to avoid full re-render — instead dispatch a browser event that Alpine handles to update the DOM optimistically
- Alternative: use `wire:ignore.self` on each column's task list container and handle all DOM updates via Alpine.js, only syncing state to Livewire

### 9.2 Race Conditions on Drag-Drop
**Risk:** Rapid drag-drop operations could cause position conflicts.
**Mitigation:**
- Use database transactions in `moveToColumn()` and `reorderInColumn()`
- Re-calculate all positions in the affected columns using sequential integers (0, 1, 2, ...)
- The server is the source of truth — after moveTask completes, Livewire re-renders the correct state

### 9.3 Image Upload Storage
**Risk:** Images stored in wrong location or orphaned files on task deletion.
**Mitigation:**
- Store in `storage/app/public/project-tasks/{task_id}/` directory
- On task deletion, `ProjectTaskService::delete()` must delete the entire `project-tasks/{task_id}/` directory from storage
- Use `Storage::disk('public')->deleteDirectory("project-tasks/{$task->id}")` in the delete method
- Livewire `WithFileUploads` stores temporary files automatically; they are moved to permanent storage on task save

### 9.4 Deleting a Column with Tasks
**Risk:** Data loss if column deleted while it has tasks.
**Mitigation:**
- `ProjectBoardService::deleteColumn()` checks `$column->tasks()->count()` first
- If tasks exist, return false and flash error "Move or delete all tasks in this column before removing it."
- UI: disable the delete button on columns that have tasks, or show a warning tooltip

### 9.5 Completed Column Logic
**Risk:** Multiple columns marked as `is_completed_column` could cause confusion.
**Mitigation:**
- When creating default columns, only "Completed" gets `is_completed_column = true`
- When adding a custom column, `is_completed_column` defaults to false
- The `moveToColumn()` method checks the target column's `is_completed_column` flag to set/clear `completed_at`
- Moving from a completed column to a non-completed column clears `completed_at`

### 9.6 Board with No Columns
**Risk:** If all columns are deleted, no tasks can exist.
**Mitigation:**
- Default columns are created with the board (5 columns)
- Prevent deleting the last column — `deleteColumn()` checks `$board->columns()->count() > 1`

### 9.7 Large Number of Tasks Per Column
**Risk:** Performance degradation if a column has hundreds of tasks; UI overflow.
**Mitigation:**
- Each column uses `overflow-y-auto` with `min-h-0` inside a flex-col container. The kanban container height is `calc(100vh - 280px)` so columns fill the viewport and scroll independently.
- The column header and "Add Task" footer use `shrink-0` so only the task list area scrolls.
- Server-side: eager load is fine for typical boards (5 columns x 50 tasks = 250 tasks max per board).
- If needed later, add lazy loading / "View More" pagination per column.

### 9.8 Cross-Board Task Movement
**Risk:** Moving tasks between boards could cause orphaned references or position conflicts.
**Mitigation:**
- `moveToBoard()` is a single transaction: updates `board_id`, `column_id` (first column of target board), and `position` (appended to end).
- Handles `completed_at` logic based on target column's `is_completed_column` flag.
- UI shows ← → arrow buttons on each task card only when the user has 2+ boards. Prev/next board determined by `sort_order`.
- Arrow hidden when no prev/next board exists (first board has no ←, last board has no →).

### 9.9 Tags as JSON
**Risk:** JSON column cannot be indexed for search.
**Mitigation:**
- Tags are low-cardinality and searched client-side (Alpine.js filter) or via MySQL JSON functions
- For the initial implementation, search only covers title/description, not tags
- Tags are displayed as colored pills on task cards

---

## 10. IMPLEMENTATION ORDER

Execute these steps in exact sequence. Each step must be completed and verified before proceeding to the next.

### Step 1: Migrations
1. Create `create_project_boards_table` migration
2. Create `create_project_board_columns_table` migration
3. Create `create_project_tasks_table` migration
4. Create `create_project_task_images_table` migration
5. Run: `docker compose exec app php artisan migrate`

### Step 2: Models
1. Create `app/Models/Task/ProjectBoard.php`
2. Create `app/Models/Task/ProjectBoardColumn.php`
3. Create `app/Models/Task/ProjectTask.php`
4. Create `app/Models/Task/ProjectTaskImage.php`

### Step 3: Services
1. Create `app/Services/ProjectBoardService.php` (board + column logic)
2. Create `app/Services/ProjectTaskService.php` (task + image logic)

### Step 4: Route
1. Create `routes/admin/tasks/project-board.php`

### Step 5: Livewire Component
1. Create directory `app/Livewire/Admin/Tasks/ProjectBoard/`
2. Create `app/Livewire/Admin/Tasks/ProjectBoard/ProjectBoardIndex.php`

### Step 6: View
1. Create directory `resources/views/livewire/admin/tasks/project-board/`
2. Create `resources/views/livewire/admin/tasks/project-board/index.blade.php`

### Step 7: Sidebar
1. Edit `resources/views/components/layouts/admin.blade.php`
2. Add "Project Board" link in Tasks menu, after "Recurring Tasks" and before "Calendar"

### Step 8: Verify
1. Run `docker compose exec app php artisan route:list --name=admin.tasks.project-board` to confirm route registered
2. Run `docker compose exec app ./vendor/bin/pint` to format all new files
3. Navigate to `/admin/tasks/project-board` in browser
4. Test: create a board (verify 5 default columns appear)
5. Test: add tasks to different columns
6. Test: drag a task between columns (verify SortableJS works)
7. Test: move a task to Completed column (verify completed_at is set)
8. Test: image upload on task creation
9. Test: delete a task (verify images cleaned from storage)
10. Test: delete an empty column (verify success)
11. Test: attempt to delete a column with tasks (verify error message)
12. Test: search and filter on the board
