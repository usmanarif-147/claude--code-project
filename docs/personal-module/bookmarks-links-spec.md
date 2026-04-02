# Bookmarks / Links — Spec

Side: **ADMIN**

---

## 1. Module Overview

Save useful links, articles, and resources in one place. A personal collection of categorized bookmarks with search and management capabilities.

**Features:**
- Save a link with title, URL, and category
- Default categories seeded: Learning, Tools, Articles, Job Boards, Other
- Add custom categories on the fly
- Search through saved links by title, URL, or category
- Delete outdated links

**Admin features (what admin can do):**
- Create bookmarks with title, URL, optional description, and category
- Browse all bookmarks in a searchable, filterable list
- Filter bookmarks by category
- Delete bookmarks
- Manage bookmark categories (add custom ones beyond defaults)

---

## 2. Database Schema

```
Table: bookmark_categories
Columns:
  - id (bigint, primary key, auto increment)
  - name (string 100, required, unique) — category display name
  - slug (string 100, required, unique) — URL-safe slug
  - is_default (boolean, default false) — true for seeded categories, prevents deletion
  - sort_order (integer, default 0) — display ordering
  - created_at, updated_at (timestamps)

Indexes:
  - unique(slug)
  - unique(name)
  - index(sort_order)
```

```
Table: bookmarks
Columns:
  - id (bigint, primary key, auto increment)
  - title (string 255, required) — link display title
  - url (string 2048, required) — the actual URL
  - description (text, nullable) — optional short note about the link
  - bookmark_category_id (bigint unsigned, required) — FK to bookmark_categories
  - created_at, updated_at (timestamps)

Indexes:
  - index(bookmark_category_id)
  - index(created_at) — for default sort (newest first)

Foreign keys:
  - bookmark_category_id → bookmark_categories.id (restrict on delete — cannot delete category with bookmarks)
```

**Default seed data for bookmark_categories:**

| name | slug | is_default |
|------|------|------------|
| Learning | learning | true |
| Tools | tools | true |
| Articles | articles | true |
| Job Boards | job-boards | true |
| Other | other | true |

---

## 3. File Map

```
MIGRATIONS:
  - database/migrations/YYYY_MM_DD_000001_create_bookmark_categories_table.php
  - database/migrations/YYYY_MM_DD_000002_create_bookmarks_table.php

SEEDERS:
  - database/seeders/BookmarkCategorySeeder.php
    - Seeds default categories: Learning, Tools, Articles, Job Boards, Other

MODELS:
  - app/Models/Bookmark/Bookmark.php
    - fillable: title, url, description, bookmark_category_id
    - relationships: belongsTo(BookmarkCategory)
    - casts: none special
  - app/Models/Bookmark/BookmarkCategory.php
    - fillable: name, slug, is_default, sort_order
    - relationships: hasMany(Bookmark)
    - casts: is_default → boolean

SERVICES:
  - app/Services/BookmarkService.php
    - getBookmarks(search, categoryId, perPage): LengthAwarePaginator — paginated list with optional search/filter
    - createBookmark(data): Bookmark — validate and store a new bookmark
    - deleteBookmark(bookmarkId): void — delete a bookmark by ID
    - getCategories(): Collection — all categories ordered by sort_order
    - createCategory(name): BookmarkCategory — create custom category, generate slug
    - deleteCategory(categoryId): void — delete category if not default and has no bookmarks
    - getCategoryCounts(): Collection — categories with bookmark counts for filter display

--- ADMIN FILES ---

LIVEWIRE COMPONENTS:
  - app/Livewire/Admin/Personal/Bookmarks/BookmarkIndex.php
    - Index page: list, search, filter, delete bookmarks + inline add form
  - resources/views/livewire/admin/personal/bookmarks/index.blade.php
    - Main bookmarks page with search bar, category filter, bookmark list, add form

ROUTES (admin):
  - routes/admin/personal/bookmarks.php
    - GET /admin/personal/bookmarks → BookmarkIndex → admin.personal.bookmarks.index
```

---

## 4. Component Contracts

### Admin Components

```
Component: App\Livewire\Admin\Personal\Bookmarks\BookmarkIndex
Namespace:  App\Livewire\Admin\Personal\Bookmarks
Layout: components.layouts.admin
Traits: WithPagination

Properties:
  - $search (string, #[Url]) — search query for title/URL filtering
  - $filterCategory (string, #[Url]) — selected category ID for filtering, empty = all
  - $title (string) — new bookmark title input
  - $url (string) — new bookmark URL input
  - $description (string) — new bookmark description input (optional)
  - $bookmark_category_id (string) — selected category for new bookmark
  - $newCategoryName (string) — input for adding a custom category
  - $showAddForm (bool, default false) — toggles the add bookmark form visibility
  - $showCategoryModal (bool, default false) — toggles add category modal

Methods:
  - mount()
    Input: none
    Does: nothing special, properties initialized via defaults
    Output: none

  - save()
    Input: reads $title, $url, $description, $bookmark_category_id
    Does: validates inputs, calls BookmarkService::createBookmark(), resets form fields, flashes success
    Output: flash message "Bookmark saved successfully."

  - delete(bookmarkId)
    Input: bookmark ID
    Does: calls BookmarkService::deleteBookmark(), flashes success
    Output: flash message "Bookmark deleted."

  - addCategory()
    Input: reads $newCategoryName
    Does: validates name, calls BookmarkService::createCategory(), resets input, flashes success
    Output: flash message "Category added." Closes modal.

  - deleteCategory(categoryId)
    Input: category ID
    Does: calls BookmarkService::deleteCategory(), flashes success or error if blocked
    Output: flash message "Category deleted." or error "Cannot delete category with bookmarks." / "Cannot delete default category."

  - updatedSearch()
    Input: none
    Does: resets pagination
    Output: none

  - updatedFilterCategory()
    Input: none
    Does: resets pagination
    Output: none

Computed / Render:
  - render() fetches:
    - BookmarkService::getBookmarks($search, $filterCategory, 15)
    - BookmarkService::getCategories()
    - BookmarkService::getCategoryCounts()
  - Passes bookmarks, categories, categoryCounts to view

Validation Rules (save):
  - title: required|string|max:255
  - url: required|url|max:2048
  - description: nullable|string|max:500
  - bookmark_category_id: required|exists:bookmark_categories,id

Validation Rules (addCategory):
  - newCategoryName: required|string|max:100|unique:bookmark_categories,name
```

---

## 5. View Blueprints

### Admin Views

```
View: resources/views/livewire/admin/personal/bookmarks/index.blade.php
Layout: components.layouts.admin
Side: ADMIN
Page title: "Bookmarks"

Design rules (from CLAUDE.md admin side):
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:

  1. Breadcrumb
     - Dashboard > Personal > Bookmarks (current)

  2. Page Header
     - Title: "BOOKMARKS" (h1, font-mono uppercase tracking-wider)
     - Subtitle: "Save and organize useful links."
     - Right side: "Add Bookmark" button (toggles $showAddForm) + "Manage Categories" button (toggles $showCategoryModal)

  3. Add Bookmark Form (collapsible, shown when $showAddForm is true)
     - Card with bg-dark-800 border border-dark-700 rounded-xl
     - Fields in 2-column grid:
       - Title (text input, required)
       - URL (text input, required)
       - Category (select dropdown from categories)
       - Description (textarea, optional, spans full width)
     - Footer: Save button (primary) + Cancel button (secondary, hides form)

  4. Filter Bar
     - Search input with magnifying glass icon (wire:model.live.debounce.300ms="search")
     - Category filter dropdown (wire:model.live="filterCategory") — "All Categories" + each category with count

  5. Bookmarks List (card-based, not table — better for link display)
     - Each bookmark rendered as a row inside a card:
       - Left: favicon placeholder (2-letter initials in bg-primary/10 rounded-lg)
       - Middle:
         - Title (text-sm font-medium text-white) — clickable, opens URL in new tab
         - URL displayed below title (text-xs text-gray-500, truncated)
         - Description if present (text-xs text-gray-400, max 1 line)
       - Right side:
         - Category badge (rounded-full, colored by category)
         - Time ago (text-xs text-gray-500)
         - Delete icon button (trash, hover red)
     - Rows separated by divide-y divide-dark-700/50

  6. Empty State (when no bookmarks)
     - Centered icon (link/bookmark icon), "No bookmarks yet" heading, "Save your first link to get started." subtext
     - "Add Bookmark" button

  7. Pagination
     - Standard Livewire pagination below the list

  8. Category Management Modal (Alpine.js x-show, triggered by $showCategoryModal)
     - Overlay with bg-dark-800 rounded-xl card
     - Title: "MANAGE CATEGORIES" (font-mono uppercase tracking-wider)
     - List of existing categories:
       - Name + bookmark count + "Default" badge if is_default
       - Delete button (only for non-default categories with 0 bookmarks)
     - Add new category input + "Add" button at bottom
     - Close button
```

---

## 6. Validation Rules

```
Form: Add Bookmark (BookmarkIndex::save)
  - title: required|string|max:255
  - url: required|url|max:2048
  - description: nullable|string|max:500
  - bookmark_category_id: required|exists:bookmark_categories,id

Form: Add Category (BookmarkIndex::addCategory)
  - newCategoryName: required|string|max:100|unique:bookmark_categories,name
```

---

## 7. Edge Cases & Business Rules

- **Default categories cannot be deleted** — check `is_default` flag; flash error if attempted
- **Categories with bookmarks cannot be deleted** — check bookmark count > 0; flash error "Cannot delete a category that has bookmarks. Move or delete the bookmarks first."
- **Duplicate category names** — unique constraint on `name` column; validation catches this
- **URL validation** — must be a valid URL format (required|url); max 2048 chars to handle long URLs
- **Delete cascade** — bookmarks table has `restrict` on `bookmark_category_id` FK; deleting a category with bookmarks is blocked at DB level as safety net
- **Search** — searches across `title` and `url` columns using LIKE %query%
- **Sort order** — bookmarks displayed newest first (created_at desc); categories ordered by sort_order then name
- **Category slug generation** — auto-generated from name using `Str::slug()`; must be unique
- **No edit for bookmarks** — per the plan, only save and delete are required (no update/edit form)
- **Pagination** — 15 bookmarks per page
- **Empty category filter** — shows all bookmarks across all categories

---

## 8. Implementation Order

```
1. database/migrations/YYYY_MM_DD_000001_create_bookmark_categories_table.php
2. database/migrations/YYYY_MM_DD_000002_create_bookmarks_table.php
3. database/seeders/BookmarkCategorySeeder.php
4. app/Models/Bookmark/BookmarkCategory.php
5. app/Models/Bookmark/Bookmark.php
6. app/Services/BookmarkService.php
7. routes/admin/personal/bookmarks.php
8. app/Livewire/Admin/Personal/Bookmarks/BookmarkIndex.php
9. resources/views/livewire/admin/personal/bookmarks/index.blade.php
10. Add sidebar link: Personal group > Bookmarks
```
