# Phase 4 (Multi-Project Enhancements) & Phase 5 (UUID Sharing) — Improvement Spec

**Module:** Project Management (renamed from Tasks)
**Type:** IMPROVE existing functionality + ADD sharing
**Mode:** dev
**Generated:** 2026-04-05

---

## 1. UPDATE OVERVIEW

**Phase 4 — Multi-Project Calendar, Weekly Review, and Analytics**
- Calendar: remove personal task queries entirely, show only project tasks from all boards, add board filter dropdown
- Weekly Review: rewrite stats computation to use `project_tasks` table instead of `tasks` table, replace category breakdown with board/column breakdown, add board filter, update AI prompts
- Analytics: integrated into the Weekly Review page as enhanced stats (no separate analytics page)

**Phase 5 — UUID-Based Read-Only Sharing Links**
- Add `share_token` (UUID) and `is_shared` (boolean) columns to `project_boards` table
- Generate shareable public URL per board: `GET /shared/project/{token}`
- Public read-only page showing tasks grouped by column, using the public layout (`components.layouts.app`)
- No authentication required; no password protection (future enhancement if needed)

---

## 2. CURRENT STATE (BEFORE)

### Calendar (CalendarService.php + CalendarIndex.php)
- `getTasksForMonth()` queries `tasks` table (personal tasks via `Task::query()->forUser()`) AND `project_tasks` table, merging both by date
- `getTasksForWeek()` follows the same dual-query pattern
- `getTasksForDate()` returns `['personal' => ..., 'project' => ...]` array structure
- `getCalendarStats()` computes total/completed/overdue from `tasks` table first, then adds project task counts
- `getCategories()` returns `TaskCategory` list for the legend
- CalendarIndex blade renders personal tasks and project tasks as separate sections in the day modal
- CalendarIndex component references `Task` model and `TaskService` for `toggleTaskComplete()`
- `goToPlanner()` method links to Daily Planner route (being removed in Phase 1)
- **Pre-existing bug:** CalendarService calls `$projectTasks->where('status', 'done')` but ProjectTask has no `status` column — it uses `completed_at` (datetime). Project task completion stats are currently broken

### Weekly Review (WeeklyReviewService.php + WeeklyReviewIndex.php)
- `computeWeekStats()` queries `tasks` table only (`Task::query()->forUser()->whereBetween('due_date', ...)`)
- `computeCategoryBreakdown()` queries `tasks` table, groups by `category_id`, joins with `TaskCategory`
- `getIncompleteTasks()` queries `tasks` table with `->pending()->with('category')`
- AI summary prompt includes "Category Breakdown" section built from `$categoryBreakdown`
- WeeklyReview model stores `category_breakdown` as JSON array
- Blade shows "Category Breakdown" card with colored progress bars per category
- Empty state message says "Tasks added via the Daily Planner will appear in weekly reviews"
- Uses Claude/OpenAI for AI generation (via `getAiApiKey()`)

### ProjectBoard Model
- Has `fillable`: `user_id`, `name`, `description`, `sort_order`
- No sharing-related columns

### ProjectTask Model
- Has `completed_at` (datetime) to track completion — NOT a `status` string column
- Has `target_date` (date) instead of `due_date`
- Has `category_id` FK to `task_categories` (being removed in Phase 1)

---

## 3. TARGET STATE (AFTER)

### Calendar
- Shows ONLY project tasks from `project_tasks` table — all personal task queries removed
- New `$boardFilter` property (URL-bound): `'all'` (default) or a specific `board_id`
- Board filter dropdown in the toolbar showing all user's boards
- Stats computed exclusively from `project_tasks`
- Completion determined by `completed_at IS NOT NULL` (fixing the pre-existing bug)
- Day modal shows only project tasks section (personal tasks section removed)
- `toggleTaskComplete` removed (project tasks managed via Kanban drag, not checkbox)
- `goToPlanner()` removed (Daily Planner gone after Phase 1)
- Category legend removed, replaced with board color legend
- `getCategories()` method removed from CalendarService

### Weekly Review
- Stats computed from `project_tasks` table exclusively
- `computeCategoryBreakdown()` replaced with `computeBoardColumnBreakdown()` — groups tasks by board name and column name
- Board filter: `$boardFilter` property, `'all'` or specific `board_id`
- Incomplete tasks query from `project_tasks` (pending scope = `whereNull('completed_at')`)
- AI summary prompt rewritten: replaces "Category Breakdown" with "Board/Column Breakdown"
- WeeklyReview model: `category_breakdown` JSON column repurposed to store board/column breakdown
- Blade: "Category Breakdown" becomes "Board & Column Breakdown" with board-grouped progress bars
- Enhanced stats section: per-board completion rates, overdue counts per board (analytics folded in)
- Empty state message updated to reference Project Board

### Sharing
- `project_boards` table gets: `share_token` (uuid, nullable, unique) and `is_shared` (boolean, default false)
- ProjectBoard model: add to fillable, add casts, add `getShareUrlAttribute()` accessor
- Toggle sharing from ProjectBoardIndex: button generates UUID when enabling, clears token when disabling
- Public route: `GET /shared/project/{token}` — no auth middleware
- Public controller: `SharedProjectController@show` (plain controller, not Livewire)
- Public blade view: read-only task list grouped by column, using `components.layouts.app` layout

---

## 4. DATABASE CHANGES

### Migration: `add_sharing_columns_to_project_boards_table`

```
up():
  - Add column: share_token (uuid, nullable, unique) after sort_order
  - Add column: is_shared (boolean, default false) after share_token

down():
  - Drop columns: share_token, is_shared
```

No other table changes needed. The `category_breakdown` JSON column in `weekly_reviews` is repurposed in-place.

---

## 5. FILES TO MODIFY

| File | Changes |
|------|---------|
| `app/Services/CalendarService.php` | Remove all `Task` model imports and queries. Remove `Schema::hasTable` guards. Query only `ProjectTask`. Add `?int $boardId = null` parameter to all public methods. Remove `getCategories()`. Fix completion check to use `completed_at`. Add `getBoards()` method. |
| `app/Livewire/Admin/ProjectManagement/Calendar/CalendarIndex.php` | Add `#[Url] public string $boardFilter = 'all'` property. Remove `Task` and `TaskService` imports. Remove `toggleTaskComplete()` and `goToPlanner()`. Rewrite `openDayModal()` for project-tasks-only. Pass `$boardFilter` to service. Add `$boards` to render. |
| Calendar blade view | Add board filter dropdown. Remove personal tasks section from day modal. Remove category legend. Remove "Open in Daily Planner" button. Update breadcrumb to "Project Management". |
| `app/Services/WeeklyReviewService.php` | Replace `Task` queries with `ProjectTask` queries in all methods. Replace `computeCategoryBreakdown()` with `computeBoardColumnBreakdown()`. Add `?int $boardId = null` parameter. Update AI prompts to use board/column data. Add `computePerBoardAnalytics()`. Add `getBoards()`. |
| `app/Livewire/Admin/ProjectManagement/WeeklyReview/WeeklyReviewIndex.php` | Add `#[Url] public string $boardFilter = 'all'` property. Pass filter to service. Add `$boards` to render. |
| Weekly Review blade view | Replace "Category Breakdown" with "Board & Column Breakdown". Update incomplete tasks display (board badge, target_date). Add per-board analytics section. Update breadcrumb and empty state text. |
| `app/Models/ProjectManagement/ProjectBoard.php` | Add `share_token` and `is_shared` to `$fillable`. Add casts: `'is_shared' => 'boolean'`. Add `getShareUrlAttribute()` accessor. Add `scopeShared()` scope. |
| `app/Livewire/Admin/ProjectManagement/ProjectBoard/ProjectBoardIndex.php` | Add `toggleSharing(int $boardId)` method. Add `copyShareLink(int $boardId)` method (sets clipboard via dispatch). |
| Project Board blade view | Add sharing toggle button and share link display in board header. |

---

## 6. FILES TO CREATE

| File | Purpose |
|------|---------|
| `database/migrations/YYYY_add_sharing_columns_to_project_boards_table.php` | Add `share_token` and `is_shared` to `project_boards` |
| `app/Http/Controllers/SharedProjectController.php` | Public controller with `show(string $token)`. Fetches board by share_token where is_shared=true, loads columns and tasks, returns view. Returns 404 if invalid/disabled token. |
| `resources/views/shared/project.blade.php` | Public read-only view. Uses `components.layouts.app` layout. Shows board name, tasks grouped by column. Each task: title, priority badge, completion indicator, target date. Minimal design, `rounded-2xl` cards, `accent` color alias per public side rules. |
| `routes/shared.php` or add to `routes/web.php` | `Route::get('/shared/project/{token}', [SharedProjectController::class, 'show'])->name('shared.project.show');` — no auth middleware. |

---

## 7. SERVICE METHODS — Key Changes

### CalendarService

- `getTasksForMonth(int $userId, int $year, int $month, ?int $boardId = null): Collection` — query only ProjectTask by target_date, add board filter
- `getTasksForWeek(int $userId, Carbon $weekStart, ?int $boardId = null): Collection` — same pattern
- `getTasksForDate(int $userId, string $date, ?int $boardId = null): Collection` — return flat collection (not personal/project split)
- `getCalendarStats(int $userId, Carbon $periodStart, Carbon $periodEnd, ?int $boardId = null): array` — use `completed_at IS NOT NULL` for completed, fix overdue logic
- `getBoards(int $userId): Collection` — new method, returns user's boards for dropdown
- REMOVE: `getCategories()`

### WeeklyReviewService

- `computeWeekStats(...)` — query ProjectTask with target_date, use completed_at
- `computeBoardColumnBreakdown(int $userId, Carbon $weekStart, Carbon $weekEnd, ?int $boardId = null): array` — replaces computeCategoryBreakdown(), groups by board then column, returns `[{board_id, board_name, columns: [{column_name, color, planned, completed}], total_planned, total_completed}]`
- `getIncompleteTasks(...)` — query ProjectTask, pending scope, with board/column eager loads
- `computePerBoardAnalytics(int $userId, Carbon $weekStart, Carbon $weekEnd): array` — per-board: total, completed, overdue, completion rate
- AI prompts: replace "Category Breakdown" with "Board/Column Breakdown"
- Board filter computed on-the-fly — do NOT store per-board review records (one record per user per week, filtered at query time)

---

## 8. IMPLEMENTATION ORDER

1. **Database migration** — add share_token and is_shared to project_boards
2. **CalendarService rewrite** — remove Task queries, add board filter, fix completion logic
3. **CalendarIndex component update** — add boardFilter property, remove removed-feature references
4. **Calendar blade update** — board filter dropdown, remove personal tasks UI
5. **WeeklyReviewService rewrite** — switch to ProjectTask, add board/column breakdown
6. **WeeklyReviewIndex component update** — add boardFilter, add boards to render
7. **Weekly Review blade update** — board breakdown UI, per-board analytics section
8. **ProjectBoard model update** — add sharing columns to fillable/casts, accessor, scope
9. **ProjectBoardIndex sharing toggle** — add toggleSharing() and copyShareLink() methods
10. **Project Board blade sharing UI** — toggle button, share link display
11. **SharedProjectController** — public controller with show() method
12. **Shared project blade view** — read-only public page
13. **Public route** — add shared project route
14. **Testing and cleanup** — verify all views, run pint

---

## 9. EDGE CASES & RISKS

### Pre-existing Bug: ProjectTask status field
CalendarService currently calls `$projectTasks->where('status', 'done')` but ProjectTask has no `status` column. Phase 4 fixes this by using `whereNotNull('completed_at')`.

### WeeklyReview AI prompts reference category_breakdown
Both AI prompts iterate `$categoryBreakdown` with keys like `category_name`. Must be rewritten to use board/column data shape.

### Old weekly_reviews records
Existing records have `category_breakdown` in the old shape. New records use board/column shape. Blade should handle both gracefully — old records just show empty in the new section (acceptable since old task data is removed anyway).

### Board filter and WeeklyReview record storage
Filtered stats are computed on-the-fly. Only the "all boards" review is persisted as the stored record. This avoids creating N review records per week.

### Shared link security
UUID v4 tokens have 122 bits of randomness — practically impossible to guess. No rate limiting on the shared route for now. Acceptable for read-only view-only access.

### Empty states
- Calendar with no boards: show "Create your first project board" CTA
- Calendar with boards but no tasks: show stats as all zeros
- Weekly Review with no project tasks: show empty state referencing Project Board
- Shared link for board with no tasks: show board name and "No tasks yet" message
- Invalid/disabled share token: return 404

### Phase 2 namespace dependency
This spec uses ProjectManagement paths (post Phase 2 rename). Phase 2 must be complete before implementing Phase 4/5.
