# Calendar Day-Click Modal Fix — Improvement Spec

## 1. UPDATE OVERVIEW

**Problem:** Clicking a date in the Calendar view redirects the user to `admin/tasks/planner`. This is disruptive — the user loses their place in the calendar and must navigate back.

**Solution:** Replace the redirect with an inline modal dialog that shows all tasks for the clicked date, separated into Personal Tasks and Project Tasks sections. The modal supports toggling task completion and provides an escape hatch link to the Daily Planner.

**Scope:** 3 files modified, 0 files created.

---

## 2. CURRENT STATE (BEFORE)

### CalendarIndex.php — `navigateToDay()` method (line 82-85)

```php
public function navigateToDay(string $date): void
{
    $this->redirect(route('admin.tasks.planner.index', ['selectedDate' => $date]), navigate: true);
}
```

This method is called from the view and causes a full page redirect away from the calendar.

### Calendar View — Month grid click binding (line 127)

```blade
<div wire:click="navigateToDay('{{ $day['date'] }}')"
```

### Calendar View — Week grid click binding (line 189)

```blade
<div wire:click="navigateToDay('{{ $day['date'] }}')"
```

Both grids call `navigateToDay()` which redirects to the Daily Planner page.

### CalendarService.php — No `getTasksForDate()` method

The service has `getTasksForMonth()` and `getTasksForWeek()` but no method to fetch tasks for a single date. The existing methods only query the `tasks` table — no awareness of project tasks.

---

## 3. TARGET STATE (AFTER)

### Behavior

1. User clicks any date cell in the month or week calendar grid.
2. A modal dialog appears on the same page (no navigation).
3. The modal header shows the formatted date (e.g., "Friday, Apr 3") and total task count.
4. The modal body shows two sections:
   - **Personal Tasks** — from the `tasks` table (shown only if count > 0)
   - **Project Tasks** — from the `project_tasks` table (shown only if count > 0)
5. Personal tasks have a checkbox to toggle completion status inline.
6. Project tasks are display-only (completion is managed on the project board).
7. If no tasks exist for the date, an empty state message is shown.
8. The modal footer has an "Open in Daily Planner" link that navigates to the planner with the selected date.
9. The modal can be closed via: close button, backdrop click, or Escape key.

### Data Flow

```
User clicks date cell
  -> wire:click="openDayModal('2026-04-03')"
  -> CalendarIndex::openDayModal()
    -> CalendarService::getTasksForDate(userId, '2026-04-03')
      -> Query Task where due_date = date
      -> Query ProjectTask where target_date = date (guarded by Schema::hasTable)
      -> Return ['personal' => Collection, 'project' => Collection]
    -> Set $selectedDayTasks, $selectedDayDate, $showDayModal = true
  -> Modal renders with task data
```

---

## 4. MIGRATION PATH

No database migrations required. This is a pure UI/logic change.

The `project_tasks` table is being created by a separate spec. All queries against it MUST be guarded with `Schema::hasTable('project_tasks')` so the calendar works whether or not that migration has run.

---

## 5. FILES TO MODIFY

### File 1: `app/Services/CalendarService.php`

#### 5.1.1 — Add import at top of file

**After line 5** (`use App\Models\Task\TaskCategory;`), add:

```php
use App\Models\Task\ProjectTask;
use Illuminate\Support\Facades\Schema;
```

> Note: `ProjectTask` does not exist yet. The import will resolve once the project-tasks spec is implemented. The `Schema::hasTable` guard prevents runtime errors.

#### 5.1.2 — Add new method `getTasksForDate()`

**After the `getTasksForWeek()` method (after line 43)**, add:

```php
public function getTasksForDate(int $userId, string $date): array
{
    $carbonDate = Carbon::parse($date);

    $personalTasks = Task::query()
        ->forUser($userId)
        ->forDate($carbonDate)
        ->with('category')
        ->byPriority()
        ->ordered()
        ->orderBy('created_at')
        ->get();

    $projectTasks = collect();

    if (Schema::hasTable('project_tasks')) {
        $projectTasks = ProjectTask::query()
            ->forUser($userId)
            ->whereDate('target_date', $carbonDate)
            ->with(['board', 'column'])
            ->ordered()
            ->get();
    }

    return [
        'personal' => $personalTasks,
        'project' => $projectTasks,
    ];
}
```

#### 5.1.3 — Modify `getTasksForMonth()` to include project tasks

**Replace the entire `getTasksForMonth()` method (lines 12-29)** with:

```php
public function getTasksForMonth(int $userId, int $year, int $month): Collection
{
    $firstOfMonth = Carbon::create($year, $month, 1);

    // Fetch tasks for the full 42-cell grid range (includes padding days from adjacent months)
    $gridStart = $firstOfMonth->copy()->subDays($firstOfMonth->dayOfWeekIso - 1)->startOfDay();
    $gridEnd = $gridStart->copy()->addDays(41)->endOfDay();

    $personalTasks = Task::query()
        ->forUser($userId)
        ->whereBetween('due_date', [$gridStart, $gridEnd])
        ->with('category')
        ->byPriority()
        ->ordered()
        ->orderBy('created_at')
        ->get()
        ->groupBy(fn ($task) => $task->due_date->format('Y-m-d'));

    if (Schema::hasTable('project_tasks')) {
        $projectTasks = ProjectTask::query()
            ->forUser($userId)
            ->whereBetween('target_date', [$gridStart, $gridEnd])
            ->get()
            ->groupBy(fn ($task) => $task->target_date->format('Y-m-d'));

        // Merge project tasks into personal tasks collection, keyed by date
        foreach ($projectTasks as $date => $tasks) {
            $existing = $personalTasks->get($date, collect());
            $personalTasks[$date] = $existing->concat($tasks);
        }
    }

    return $personalTasks;
}
```

#### 5.1.4 — Modify `getTasksForWeek()` to include project tasks

**Replace the entire `getTasksForWeek()` method (lines 31-43)** with:

```php
public function getTasksForWeek(int $userId, Carbon $weekStart): Collection
{
    $weekEnd = $weekStart->copy()->addDays(6)->endOfDay();

    $personalTasks = Task::query()
        ->forUser($userId)
        ->whereBetween('due_date', [$weekStart->copy()->startOfDay(), $weekEnd])
        ->with('category')
        ->byPriority()
        ->ordered()
        ->orderBy('created_at')
        ->get()
        ->groupBy(fn ($task) => $task->due_date->format('Y-m-d'));

    if (Schema::hasTable('project_tasks')) {
        $projectTasks = ProjectTask::query()
            ->forUser($userId)
            ->whereBetween('target_date', [$weekStart->copy()->startOfDay(), $weekEnd])
            ->get()
            ->groupBy(fn ($task) => $task->target_date->format('Y-m-d'));

        foreach ($projectTasks as $date => $tasks) {
            $existing = $personalTasks->get($date, collect());
            $personalTasks[$date] = $existing->concat($tasks);
        }
    }

    return $personalTasks;
}
```

#### 5.1.5 — Modify `getCalendarStats()` to include project task counts

**Replace the entire `getCalendarStats()` method (lines 46-77)** with:

```php
public function getCalendarStats(int $userId, Carbon $periodStart, Carbon $periodEnd): array
{
    $tasks = Task::query()
        ->forUser($userId)
        ->whereBetween('due_date', [$periodStart->copy()->startOfDay(), $periodEnd->copy()->endOfDay()])
        ->get();

    $total = $tasks->count();
    $completed = $tasks->where('status', 'completed')->count();
    $overdue = $tasks->filter(function ($task) {
        return $task->status !== 'completed' && $task->due_date->lt(Carbon::today());
    })->count();

    // Include project tasks in stats if table exists
    if (Schema::hasTable('project_tasks')) {
        $projectTasks = ProjectTask::query()
            ->forUser($userId)
            ->whereBetween('target_date', [$periodStart->copy()->startOfDay(), $periodEnd->copy()->endOfDay()])
            ->get();

        $total += $projectTasks->count();
        $completed += $projectTasks->where('status', 'done')->count();
        $overdue += $projectTasks->filter(function ($task) {
            return $task->status !== 'done' && $task->target_date->lt(Carbon::today());
        })->count();

        // Merge for busiest day calculation
        $tasks = $tasks->concat($projectTasks);
    }

    $busiestDay = null;
    if ($total > 0) {
        $grouped = $tasks->groupBy(function ($task) {
            $dateField = $task->due_date ?? $task->target_date;
            return $dateField->format('Y-m-d');
        });
        $maxCount = 0;
        foreach ($grouped->sortKeys() as $date => $dateTasks) {
            if ($dateTasks->count() > $maxCount) {
                $maxCount = $dateTasks->count();
                $busiestDay = $date;
            }
        }
    }

    return [
        'total' => $total,
        'completed' => $completed,
        'overdue' => $overdue,
        'busiestDay' => $busiestDay,
    ];
}
```

---

### File 2: `app/Livewire/Admin/Tasks/Calendar/CalendarIndex.php`

#### 5.2.1 — Add imports

**After line 3** (`use App\Services\CalendarService;`), add:

```php
use App\Services\TaskService;
use App\Models\Task\Task;
```

#### 5.2.2 — Add new properties

**After line 25** (`public string $currentWeekStart = '';`), add:

```php
public bool $showDayModal = false;

public string $selectedDayDate = '';

public array $selectedDayTasks = ['personal' => [], 'project' => []];
```

#### 5.2.3 — Remove `navigateToDay()` method

**Delete lines 82-85:**

```php
public function navigateToDay(string $date): void
{
    $this->redirect(route('admin.tasks.planner.index', ['selectedDate' => $date]), navigate: true);
}
```

#### 5.2.4 — Add new methods in place of `navigateToDay()`

**Where `navigateToDay()` was (after `goToToday()` method)**, add:

```php
public function openDayModal(string $date): void
{
    $this->selectedDayDate = $date;

    $service = app(CalendarService::class);
    $result = $service->getTasksForDate(auth()->id(), $date);

    $this->selectedDayTasks = [
        'personal' => $result['personal']->map(function ($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'status' => $task->status,
                'priority' => $task->priority,
                'category_name' => $task->category?->name,
                'category_color' => $task->category?->color,
            ];
        })->toArray(),
        'project' => $result['project']->map(function ($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'status' => $task->status,
                'priority' => $task->priority ?? 'medium',
                'board_name' => $task->board?->name ?? 'Unknown Board',
                'column_name' => $task->column?->name ?? 'Unknown',
            ];
        })->toArray(),
    ];

    $this->showDayModal = true;
}

public function closeDayModal(): void
{
    $this->showDayModal = false;
    $this->selectedDayDate = '';
    $this->selectedDayTasks = ['personal' => [], 'project' => []];
}

public function toggleTaskComplete(int $taskId): void
{
    $task = Task::where('id', $taskId)
        ->where('user_id', auth()->id())
        ->first();

    if (! $task) {
        return;
    }

    $service = app(TaskService::class);
    $service->toggleComplete($task);

    // Refresh modal data
    $this->openDayModal($this->selectedDayDate);
}

public function goToPlanner(): void
{
    $this->redirect(
        route('admin.tasks.planner.index', ['selectedDate' => $this->selectedDayDate]),
        navigate: true
    );
}
```

---

### File 3: `resources/views/livewire/admin/tasks/calendar/index.blade.php`

#### 5.3.1 — Replace month grid wire:click (line 127)

**Find:**
```blade
<div wire:click="navigateToDay('{{ $day['date'] }}')"
```
(inside the `@foreach($calendarDays as $day)` loop in the monthly grid, ~line 127)

**Replace with:**
```blade
<div wire:click="openDayModal('{{ $day['date'] }}')"
```

#### 5.3.2 — Replace week grid wire:click (line 189)

**Find:**
```blade
<div wire:click="navigateToDay('{{ $day['date'] }}')"
```
(inside the `@foreach($calendarDays as $day)` loop for column bodies in the weekly grid, ~line 189)

**Replace with:**
```blade
<div wire:click="openDayModal('{{ $day['date'] }}')"
```

#### 5.3.3 — Add modal at end of view

**Before the closing `</div>` on the last line (line 222)**, add the following modal markup:

```blade
{{-- 8. DAY TASKS MODAL --}}
@if($showDayModal)
    <div
        x-data="{ open: @entangle('showDayModal') }"
        x-show="open"
        x-cloak
        x-on:keydown.escape.window="open = false"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
    >
        {{-- Backdrop --}}
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            x-on:click="open = false"
            class="absolute inset-0 bg-dark-950/80 backdrop-blur-sm"
        ></div>

        {{-- Modal Panel --}}
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-4"
            class="relative w-full max-w-lg bg-dark-800 border border-dark-700 rounded-xl shadow-2xl shadow-black/50 overflow-hidden"
        >
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-dark-700">
                <div>
                    <h3 class="text-base font-mono font-semibold text-white uppercase tracking-wider">
                        {{ \Carbon\Carbon::parse($selectedDayDate)->format('l, M j') }}
                    </h3>
                    @php
                        $totalCount = count($selectedDayTasks['personal']) + count($selectedDayTasks['project']);
                    @endphp
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $totalCount }} {{ Str::plural('task', $totalCount) }}
                    </p>
                </div>
                <button wire:click="closeDayModal"
                        class="text-gray-500 hover:text-gray-300 transition-colors p-1 rounded-lg hover:bg-dark-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="px-6 py-4 max-h-96 overflow-y-auto">
                @if(count($selectedDayTasks['personal']) === 0 && count($selectedDayTasks['project']) === 0)
                    {{-- Empty State --}}
                    <div class="flex flex-col items-center justify-center py-10 text-center">
                        <div class="w-12 h-12 rounded-full bg-dark-700 flex items-center justify-center mb-3">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <p class="text-sm text-gray-500">No tasks for this day</p>
                        <p class="text-xs text-gray-600 mt-1">Enjoy the free time!</p>
                    </div>
                @endif

                {{-- Personal Tasks Section --}}
                @if(count($selectedDayTasks['personal']) > 0)
                    <div class="mb-5">
                        <h4 class="text-xs font-mono font-medium text-gray-500 uppercase tracking-widest mb-3">
                            Personal Tasks
                            <span class="text-gray-600 ml-1">({{ count($selectedDayTasks['personal']) }})</span>
                        </h4>
                        <div class="space-y-1.5">
                            @foreach($selectedDayTasks['personal'] as $task)
                                <div class="flex items-center gap-3 group px-3 py-2.5 rounded-lg hover:bg-dark-700/50 transition-colors">
                                    {{-- Checkbox --}}
                                    <button wire:click="toggleTaskComplete({{ $task['id'] }})"
                                            wire:loading.attr="disabled"
                                            class="shrink-0 w-5 h-5 rounded-md border-2 flex items-center justify-center transition-all duration-200
                                                {{ $task['status'] === 'completed'
                                                    ? 'bg-emerald-500 border-emerald-500'
                                                    : 'border-dark-600 hover:border-primary group-hover:border-dark-500' }}">
                                        @if($task['status'] === 'completed')
                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        @endif
                                    </button>

                                    {{-- Task Info --}}
                                    <div class="flex-1 min-w-0">
                                        <span class="text-sm block truncate {{ $task['status'] === 'completed' ? 'line-through text-gray-500' : 'text-gray-300' }}">
                                            {{ $task['title'] }}
                                        </span>
                                    </div>

                                    {{-- Badges --}}
                                    <div class="flex items-center gap-1.5 shrink-0">
                                        {{-- Priority Badge --}}
                                        @switch($task['priority'])
                                            @case('urgent')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-500/10 text-red-400">Urgent</span>
                                                @break
                                            @case('high')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400">High</span>
                                                @break
                                            @case('medium')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-500/10 text-blue-400">Med</span>
                                                @break
                                            @case('low')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-500/10 text-gray-400">Low</span>
                                                @break
                                        @endswitch

                                        {{-- Category Badge --}}
                                        @if($task['category_name'])
                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                                                  style="background-color: {{ $task['category_color'] ?? '#6b7280' }}1a; color: {{ $task['category_color'] ?? '#6b7280' }}">
                                                {{ $task['category_name'] }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Project Tasks Section --}}
                @if(count($selectedDayTasks['project']) > 0)
                    <div>
                        <h4 class="text-xs font-mono font-medium text-gray-500 uppercase tracking-widest mb-3">
                            Project Tasks
                            <span class="text-gray-600 ml-1">({{ count($selectedDayTasks['project']) }})</span>
                        </h4>
                        <div class="space-y-1.5">
                            @foreach($selectedDayTasks['project'] as $task)
                                <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-dark-700/50 transition-colors">
                                    {{-- Project icon --}}
                                    <div class="shrink-0 w-5 h-5 rounded-md bg-primary/10 flex items-center justify-center">
                                        <svg class="w-3 h-3 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7"/>
                                        </svg>
                                    </div>

                                    {{-- Task Info --}}
                                    <div class="flex-1 min-w-0">
                                        <span class="text-sm block truncate {{ $task['status'] === 'done' ? 'line-through text-gray-500' : 'text-gray-300' }}">
                                            {{ $task['title'] }}
                                        </span>
                                    </div>

                                    {{-- Badges --}}
                                    <div class="flex items-center gap-1.5 shrink-0">
                                        {{-- Priority Badge --}}
                                        @switch($task['priority'])
                                            @case('urgent')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-500/10 text-red-400">Urgent</span>
                                                @break
                                            @case('high')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400">High</span>
                                                @break
                                            @case('medium')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-500/10 text-blue-400">Med</span>
                                                @break
                                            @case('low')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-500/10 text-gray-400">Low</span>
                                                @break
                                        @endswitch

                                        {{-- Board Badge --}}
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary-light">
                                            {{ $task['board_name'] }}
                                        </span>

                                        {{-- Column Badge --}}
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-dark-700 text-gray-400">
                                            {{ $task['column_name'] }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-between px-6 py-4 border-t border-dark-700 bg-dark-800">
                <button wire:click="closeDayModal"
                        class="text-sm text-gray-500 hover:text-gray-300 transition-colors">
                    Close
                </button>
                <button wire:click="goToPlanner"
                        class="inline-flex items-center gap-2 text-sm font-medium text-primary-light hover:text-white transition-colors">
                    Open in Daily Planner
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
@endif
```

---

## 6. FILES TO CREATE

None. All changes are modifications to existing files.

---

## 7. CROSS-MODULE IMPACT

**None.** The calendar is a self-contained view within the Tasks module. The changes are:

- CalendarService gains a new public method (`getTasksForDate`) — no other code calls it yet.
- CalendarIndex loses `navigateToDay()` — only called from its own view.
- The view changes are scoped to the calendar page only.

**Cross-spec dependency:** The ProjectTask model and `project_tasks` table are being created by a separate spec. All references are guarded by `Schema::hasTable('project_tasks')` so this spec can be implemented and merged independently, in any order.

---

## 8. EDGE CASES

### Empty Day (no tasks)
The modal shows a centered empty state with an icon, "No tasks for this day" message, and a subtle sub-message. The "Open in Daily Planner" link remains available so the user can navigate there to add tasks.

### Many Tasks (20+ on a single day)
The modal body has `max-h-96 overflow-y-auto` so it scrolls vertically. Tasks remain readable and interactive within the scroll area. The header and footer stay pinned.

### `project_tasks` Table Not Yet Created
Every query against `ProjectTask` is wrapped in `Schema::hasTable('project_tasks')`. If the table does not exist:
- `getTasksForDate()` returns an empty collection for the `project` key.
- `getTasksForMonth()` and `getTasksForWeek()` return only personal tasks (current behavior).
- `getCalendarStats()` only counts personal tasks (current behavior).
- The "Project Tasks" section in the modal is hidden (its `@if(count(...) > 0)` guard triggers).

### `ProjectTask` Model Class Not Yet Created
The `use App\Models\Task\ProjectTask;` import at the top of CalendarService will cause a compile error if the class file does not exist. **Implementation note:** If implementing this spec before the project-tasks spec, wrap the import in a conditional or defer implementation of the ProjectTask queries until that model exists. Alternatively, use `class_exists('App\Models\Task\ProjectTask')` as the guard instead of `Schema::hasTable`.

**Recommended safe pattern:**

```php
if (Schema::hasTable('project_tasks') && class_exists(\App\Models\Task\ProjectTask::class)) {
    // query ProjectTask
}
```

This removes the need for the `use` import entirely (use fully-qualified class name inline).

### Rapid Clicking / Loading State
The `wire:click` on the checkbox has `wire:loading.attr="disabled"` to prevent double-toggling. The modal re-opens with refreshed data after each toggle via `openDayModal($this->selectedDayDate)`.

### Date Format Consistency
The `selectedDayDate` property stores dates as `Y-m-d` strings (e.g., `2026-04-03`), consistent with the `$day['date']` values from `buildMonthGrid()` and `buildWeekGrid()`.

---

## 9. IMPLEMENTATION ORDER

1. **CalendarService.php** — Add imports, add `getTasksForDate()`, modify `getTasksForMonth()`, `getTasksForWeek()`, `getCalendarStats()`.
2. **CalendarIndex.php** — Add imports, add properties, remove `navigateToDay()`, add `openDayModal()`, `closeDayModal()`, `toggleTaskComplete()`, `goToPlanner()`.
3. **Calendar view (index.blade.php)** — Replace two `wire:click="navigateToDay(..."` bindings with `wire:click="openDayModal(..."`, add modal markup before closing `</div>`.
4. **Manual test** — Click a date with tasks, verify modal appears. Toggle a task, verify it updates. Click "Open in Daily Planner", verify redirect. Click a date with no tasks, verify empty state. Press Escape, verify modal closes.
