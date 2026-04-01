# Quick Capture — Spec

Side: ADMIN

---

## 1. MODULE OVERVIEW

Quick Capture provides a floating action button (FAB) visible on every admin page that opens a small modal/popup for rapidly adding tasks without leaving the current page. The user types a task title, optionally selects a category and due date, and hits Enter to save. The task defaults to today's date and appears in the Daily Task Planner.

### Features
- Floating "+" button fixed to the bottom-right corner of every admin page
- Clicking the button (or pressing a keyboard shortcut) opens a compact popup form
- Title field auto-focused — type and press Enter to save immediately
- Optional category dropdown (from TaskCategory model) and due date picker
- Task defaults to today's date when no due date is selected
- Success feedback (brief flash/animation) after saving, then form resets for the next task
- Fully responsive — works well on mobile
- Keyboard shortcut (Ctrl+K or similar) to open/close the popup

### Admin Features
- Create a task from any admin page without navigating away
- Select a TaskCategory for the new task
- Set a due date (defaults to today)
- Form resets after save so multiple tasks can be added in quick succession

---

## 2. DATABASE SCHEMA

No new tables or migrations needed. This feature reuses the Task and TaskCategory models/tables from the Daily Task Planner and Task Categories features.

### Referenced Tables (created by other features — NOT created here)

```
Table: tasks
Columns:
  - id (bigint, primary key, auto increment)
  - title (string, required, max 255)
  - task_category_id (bigint, nullable, foreign key -> task_categories.id)
  - due_date (date, required, defaults to today in application logic)
  - is_completed (boolean, default false)
  - sort_order (integer, default 0)
  - created_at, updated_at (timestamps)

Table: task_categories
Columns:
  - id (bigint, primary key, auto increment)
  - name (string, required, max 100)
  - color (string, required, max 7 — hex color code)
  - sort_order (integer, default 0)
  - created_at, updated_at (timestamps)
```

---

## 3. FILE MAP

```
MIGRATIONS:
  - None — uses existing tasks and task_categories tables

MODELS:
  - None — uses existing Task and TaskCategory models (from daily-task-planner and task-categories features)
    - App\Models\Task\Task
    - App\Models\Task\TaskCategory

SERVICES:
  - None — uses existing app/Services/TaskService.php (from daily-task-planner feature)
    - Relevant method: create(array $data): Task — creates a new task
```

### ADMIN FILES

```
LIVEWIRE COMPONENTS:
  - app/Livewire/Admin/Tasks/QuickCapture/QuickCapture.php
    - Public properties:
      - $title (string, default '')
      - $taskCategoryId (int|null, default null)
      - $dueDate (string, default today's date)
    - Methods:
      - mount() — sets $dueDate to today
      - save() — validates input, calls TaskService::create(), resets form, dispatches browser event
      - resetForm() — clears title and category, resets dueDate to today
      - render() — returns view with categories list

  - resources/views/livewire/admin/tasks/quick-capture/quick-capture.blade.php
    - Floating action button (FAB) + popup modal for quick task creation

ROUTES:
  - None — this is an embedded component in the admin layout, not a routed page
```

### LAYOUT MODIFICATION

```
MODIFIED FILE:
  - resources/views/components/layouts/admin.blade.php
    - Add <livewire:admin.tasks.quick-capture.quick-capture /> before the closing </body> tag
    - Only rendered for authenticated users (already guaranteed by admin layout)
```

---

## 4. COMPONENT CONTRACTS

### Admin Components

```
Component: App\Livewire\Admin\Tasks\QuickCapture\QuickCapture
Namespace: App\Livewire\Admin\Tasks\QuickCapture

Properties:
  - $title (string, default '') — task title input
  - $taskCategoryId (?int, default null) — selected category ID
  - $dueDate (string) — due date, defaults to today (Carbon::today()->toDateString())

Methods:
  - mount()
    Input: none
    Does: sets $dueDate to today's date string (Y-m-d)
    Output: none

  - save(TaskService $service)
    Input: TaskService injected by Livewire
    Does:
      1. Calls $this->validate()
      2. Calls $service->create([
           'title' => $this->title,
           'task_category_id' => $this->taskCategoryId,
           'due_date' => $this->dueDate,
           'is_completed' => false,
           'sort_order' => 0,
         ])
      3. Calls $this->resetForm()
      4. Dispatches 'task-created' browser event (for success animation)
    Output: none (stays on same page — no redirect)

  - resetForm()
    Input: none
    Does: resets $title to '', $taskCategoryId to null, $dueDate to today
    Output: none

  - render()
    Input: none
    Does: fetches TaskCategory::orderBy('sort_order')->get() and passes to view
    Output: view('livewire.admin.tasks.quick-capture.quick-capture', ['categories' => $categories])

Validation Rules:
  - title: required|string|max:255
  - taskCategoryId: nullable|exists:task_categories,id
  - dueDate: required|date|after_or_equal:today
```

---

## 5. VIEW BLUEPRINTS

### Admin View

```
View: resources/views/livewire/admin/tasks/quick-capture/quick-capture.blade.php
Layout: embedded in components.layouts.admin (not a standalone page)
Side: ADMIN

Design rules (from CLAUDE.md admin side):
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Structure:
  This is a floating component with two parts:

  1. FLOATING ACTION BUTTON (FAB)
     - Fixed position: bottom-6 right-6 (bottom-20 right-4 on mobile to avoid overlap with nav)
     - Circular button: w-14 h-14 rounded-full
     - Style: bg-primary hover:bg-primary-hover text-white shadow-lg shadow-primary/30
     - Icon: "+" SVG (plus icon), w-6 h-6
     - Hover: scale-110 transition
     - Alpine.js x-data controls open/close state
     - Click toggles popup visibility
     - Keyboard shortcut: listen for Ctrl+Shift+K to toggle

  2. POPUP PANEL (shown above the FAB when open)
     - Position: fixed bottom-24 right-6 (above the FAB)
     - Width: w-80 (320px) — compact but usable
     - Style: bg-dark-800 border border-dark-700 rounded-xl shadow-2xl shadow-black/50
     - Alpine.js transition: scale + opacity animation on open/close
     - Click outside closes the popup (x-on:click.outside)
     - Escape key closes the popup

     Sections inside the popup:
     a. HEADER
        - Text: "Quick Add Task" — text-sm font-mono font-semibold text-white uppercase tracking-wider
        - Close button (X icon) on the right

     b. TITLE INPUT (primary field)
        - Auto-focused when popup opens (x-init with $nextTick -> focus)
        - Placeholder: "What needs to be done?"
        - Style: standard admin input (bg-dark-700 border border-dark-600 rounded-lg)
        - wire:model="title"
        - wire:keydown.enter="save" — pressing Enter submits the task
        - Validation error shown below

     c. OPTIONAL FIELDS ROW (compact layout — side by side)
        - Category select (left): small dropdown, placeholder "Category"
          - wire:model="taskCategoryId"
          - Options from TaskCategory list, each showing category name
          - Optional colored dot next to each category name
        - Due date input (right): date picker
          - wire:model="dueDate"
          - Defaults to today

     d. FOOTER / ACTIONS
        - "Add Task" primary button (small): bg-primary hover:bg-primary-hover text-white rounded-lg px-4 py-2 text-sm
        - Loading state with spinner while saving (wire:loading)
        - Hint text: "Press Enter to save" — text-xs text-gray-500

  3. SUCCESS FEEDBACK
     - After save, show a brief checkmark animation or green flash on the FAB
     - Alpine.js x-on:task-created.window triggers a brief success state
     - FAB briefly changes to green (bg-emerald-500) with a checkmark icon, then reverts after 1.5s
```

---

## 6. VALIDATION RULES

```
Form: Quick Capture
  - title: required|string|max:255
  - taskCategoryId: nullable|exists:task_categories,id
  - dueDate: required|date|after_or_equal:today
```

---

## 7. EDGE CASES & BUSINESS RULES

- **Default due date**: When no due date is explicitly set, the task defaults to today's date. The mount() method initializes $dueDate to Carbon::today()->toDateString().
- **Empty title on Enter**: If the user presses Enter with an empty title, validation fires and shows the error message. The popup stays open.
- **Rapid submission**: After a successful save, the form resets immediately. The user can type another task right away without closing/reopening the popup. This supports "brain dump" mode.
- **No categories exist yet**: If no TaskCategory records exist, the category dropdown is hidden entirely (not shown as an empty select). The task is created with task_category_id = null.
- **Past dates blocked**: The dueDate validation uses after_or_equal:today to prevent creating tasks in the past.
- **Sort order**: New tasks created via quick capture get sort_order = 0, placing them at the top of the day's list (the Daily Planner can reorder later).
- **No delete/edit**: Quick Capture only creates tasks. Editing and deleting happen in the Daily Task Planner.
- **Component isolation**: The QuickCapture component is embedded in the admin layout but is a fully independent Livewire component. It does not share state with the current page's component.
- **Mobile usability**: On small screens, the FAB stays visible but the popup width adapts (w-72 on mobile vs w-80 on desktop). The popup should not overflow the viewport.
- **Sidebar overlap**: The FAB is positioned at bottom-right. On desktop with the sidebar open (lg:ml-64), the FAB sits inside the main content area, not behind the sidebar.
- **Page navigation**: When the user navigates to a different admin page via wire:navigate, the QuickCapture component persists because it is in the layout. The popup state (open/closed) resets on navigation since Alpine state is re-initialized.

---

## 8. IMPLEMENTATION ORDER

```
Prerequisites: Task and TaskCategory models, tasks and task_categories migrations,
               and TaskService must exist first (from daily-task-planner and
               task-categories features). If they do not exist yet, implement
               those features before this one.

1. Livewire component — app/Livewire/Admin/Tasks/QuickCapture/QuickCapture.php
2. Blade view — resources/views/livewire/admin/tasks/quick-capture/quick-capture.blade.php
3. Layout modification — add <livewire:admin.tasks.quick-capture.quick-capture /> to
   resources/views/components/layouts/admin.blade.php (before closing </body> tag,
   after @livewireScripts)
```

No migrations, models, services, or routes are created by this feature.
