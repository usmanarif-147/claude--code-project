# Task Categories — Spec

Side: **ADMIN**

---

## 1. MODULE OVERVIEW

Task Categories is the foundation feature of the Tasks module. It lets the admin organize tasks into named, color-coded groups (e.g., Work, Freelance, YouTube, Job Search, Personal) so tasks can be filtered and visually distinguished. Every other Tasks feature depends on this model.

### Features
- List all categories in a sortable table with color preview, name, and task count
- Create a new category with name, color, and sort order
- Edit an existing category
- Delete a category (only if no tasks are assigned to it)
- Seed five default categories: Work, Freelance, YouTube, Job Search, Personal

### Admin features
- Full CRUD on task categories
- Search/filter categories by name
- Reorder categories via sort_order field

---

## 2. DATABASE SCHEMA

```
Table: task_categories
Columns:
  - id          (bigint, primary key, auto increment)
  - name        (string 100, required, unique)
  - color       (string 7, required, default '#7c3aed') — hex color code e.g. #ef4444
  - sort_order  (integer, required, default 0)
  - created_at  (timestamp)
  - updated_at  (timestamp)

Indexes:
  - unique index on `name`
  - index on `sort_order`
```

---

## 3. FILE MAP

```
MIGRATIONS:
  - database/migrations/xxxx_xx_xx_xxxxxx_create_task_categories_table.php

MODELS:
  - app/Models/Task/TaskCategory.php
    - namespace: App\Models\Task
    - fillable: name, color, sort_order
    - casts: sort_order → integer
    - relationships:
        - tasks(): hasMany(Task::class) — for future use, added when Task model exists
    - scopes:
        - scopeOrdered(Builder): orderBy('sort_order')
        - scopeSearch(Builder, string): where name like %term%

SERVICES:
  - app/Services/TaskCategoryService.php
    - getAll(): Collection — returns all categories ordered by sort_order
    - create(array $data): TaskCategory — creates a new category
    - update(TaskCategory $category, array $data): TaskCategory — updates the category
    - delete(TaskCategory $category): void — deletes the category (throws if tasks assigned)

--- ADMIN FILES ---

LIVEWIRE COMPONENTS:
  - app/Livewire/Admin/Tasks/Categories/TaskCategoryIndex.php
    - public properties: search (string, #[Url]), sort_order filter
    - methods: delete(int $id), render()
  - app/Livewire/Admin/Tasks/Categories/TaskCategoryForm.php
    - public properties: taskCategory (?TaskCategory), name (string), color (string), sort_order (int)
    - methods: mount(?TaskCategory), save(), render()

VIEWS:
  - resources/views/livewire/admin/tasks/categories/index.blade.php
    - Table listing all categories with color swatch, name, sort order, task count, actions
  - resources/views/livewire/admin/tasks/categories/form.blade.php
    - Form with name input, color picker, sort order input

ROUTES (admin):
  - routes/admin/tasks/categories.php
    - GET  /categories           → TaskCategoryIndex  → admin.tasks.categories.index
    - GET  /categories/create    → TaskCategoryForm   → admin.tasks.categories.create
    - GET  /categories/{taskCategory}/edit → TaskCategoryForm → admin.tasks.categories.edit
```

---

## 4. COMPONENT CONTRACTS

### TaskCategoryIndex

```
Component: App\Livewire\Admin\Tasks\Categories\TaskCategoryIndex
Namespace: App\Livewire\Admin\Tasks\Categories
Layout:    components.layouts.admin

Properties:
  - $search (string, default '') — #[Url], filters categories by name

Methods:
  - updatingSearch()
    Input: none
    Does: resets pagination to page 1
    Output: none

  - delete(TaskCategoryService $service, int $id)
    Input: category ID
    Does:
      1. Finds category by ID (findOrFail)
      2. Calls $service->delete($category)
      3. If tasks are assigned, catches exception and flashes error
    Output: flash success or error message

  - render()
    Input: none
    Does:
      1. Builds query: TaskCategory::query()->ordered()
      2. Applies search filter if $search is not empty
      3. Paginates results (10 per page)
    Output: returns view with 'categories' collection
```

### TaskCategoryForm

```
Component: App\Livewire\Admin\Tasks\Categories\TaskCategoryForm
Namespace: App\Livewire\Admin\Tasks\Categories
Layout:    components.layouts.admin

Properties:
  - $taskCategory (?TaskCategory, default null)
  - $name (string, default '')
  - $color (string, default '#7c3aed')
  - $sort_order (int, default 0)

Methods:
  - mount(?TaskCategory $taskCategory = null)
    Input: optional TaskCategory model (injected via route model binding on edit)
    Does: if category exists, populates all properties from the model
    Output: none

  - save(TaskCategoryService $service)
    Input: none
    Does:
      1. Validates all fields
      2. If $taskCategory exists → $service->update()
      3. Else → $service->create()
      4. Flashes success message
    Output: redirects to admin.tasks.categories.index (navigate: true)

  - render()
    Output: returns form view
```

---

## 5. VIEW BLUEPRINTS

### Index View

```
View: resources/views/livewire/admin/tasks/categories/index.blade.php
Layout: components.layouts.admin
Side: ADMIN
Page title: "Task Categories"

Design rules:
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:
  - Breadcrumb: Dashboard > Tasks > Categories
  - Page header: "Task Categories" title + "Add Category" button (links to create route)
  - Filter bar: search input (wire:model.live.debounce.300ms)
  - Table card:
    Columns:
      1. Color — small circle swatch showing the hex color
      2. Name — text-white font-medium
      3. Sort Order — text-gray-400
      4. Actions — edit link + delete button with wire:confirm
  - Empty state: "No categories found. Create one." with link to create route
  - Pagination: shown if hasPages()
```

### Form View

```
View: resources/views/livewire/admin/tasks/categories/form.blade.php
Layout: components.layouts.admin
Side: ADMIN
Page title: "Create Category" or "Edit Category" (conditional on $taskCategory)

Design rules:
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:
  - Breadcrumb: Dashboard > Tasks > Categories > Create/Edit
  - Page header: dynamic title + "Back" button (links to index route)
  - Form card — "Category Details" section header:
    Fields (full-width, single column — simple form):
      1. Name — text input, required, placeholder "e.g. Work"
      2. Color — color input (type="color") with hex preview text, default #7c3aed
      3. Sort Order — number input, min 0, default 0
  - Submit card:
    - Save button (primary, with loading state)
    - Cancel button (secondary, links back to index)
```

---

## 6. VALIDATION RULES

```
Form: TaskCategoryForm
  - name:       required|string|max:100|unique:task_categories,name,{id}
  - color:      required|string|regex:/^#[0-9A-Fa-f]{6}$/
  - sort_order: required|integer|min:0
```

Note: The unique rule on `name` must exclude the current record ID on edit. Use `Rule::unique('task_categories', 'name')->ignore($this->taskCategory?->id)`.

---

## 7. EDGE CASES & BUSINESS RULES

- **Delete protection**: A category cannot be deleted if tasks are assigned to it. The service must check `$category->tasks()->count()` before deleting. If tasks exist, throw an exception caught by the component, which flashes an error: "Cannot delete category with assigned tasks."
  - Note: Until the Task model exists, deletion will always succeed (no tasks can reference it yet). The guard should still be coded now so it is ready.
- **Unique name**: Category names are unique (case-insensitive at DB level). Validation enforces `unique:task_categories,name` with ignore on edit.
- **Color format**: Must be a valid 7-character hex string (#RRGGBB). Validated via regex.
- **Sort order**: Categories are always displayed ordered by `sort_order` ascending, then `name` ascending as tiebreaker.
- **Default categories**: A seeder or migration should insert the five defaults: Work (#3b82f6 blue), Freelance (#22c55e green), YouTube (#ef4444 red), Job Search (#f59e0b amber), Personal (#7c3aed purple). These can be deleted/edited by the admin — they are not protected.
- **No soft deletes**: Categories use hard delete. Simple model, no audit trail needed.
- **No public side**: This feature is admin-only. No public display.

---

## 8. IMPLEMENTATION ORDER

```
1. Migration — create_task_categories_table
2. Model — app/Models/Task/TaskCategory.php
3. Service — app/Services/TaskCategoryService.php
4. Routes — routes/admin/tasks/categories.php
5. Livewire component — TaskCategoryIndex.php
6. Livewire component — TaskCategoryForm.php
7. Admin view — index.blade.php
8. Admin view — form.blade.php
9. Sidebar — add "Tasks" parent group with "Categories" link in admin layout
10. Seeder (optional) — seed five default categories
```
