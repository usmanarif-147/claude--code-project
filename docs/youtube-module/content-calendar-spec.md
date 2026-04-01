# Content Calendar — Spec

Side: ADMIN

---

## 1. MODULE OVERVIEW

The Content Calendar lets you plan when to publish videos and blog posts on a visual calendar. It helps you stay consistent by showing your content schedule at a glance, highlighting gaps where no content is planned, and letting you drag items to reschedule.

**Features:**
- Calendar view showing planned publish dates (monthly grid with navigation)
- Add content items with title, type (video/blog), and planned date
- Drag-and-drop to reschedule content to a different date
- Mark content as published when done
- Visual gap detection — weeks with no content planned are highlighted

**Admin features (what the admin can do):**
- View a monthly calendar grid of all planned content
- Create new content items (title, type, description, planned date)
- Edit existing content items
- Drag content items to a new date to reschedule
- Toggle content status between planned/published
- Delete content items
- See at-a-glance which weeks have no content scheduled (gap indicators)

---

## 2. DATABASE SCHEMA

```
Table: content_calendar_items
Columns:
  - id (bigint, primary key, auto increment)
  - title (string 255, required) — content title
  - type (string 20, required) — 'video' or 'blog'
  - description (text, nullable) — optional notes about the content
  - planned_date (date, required) — when this content is planned to be published
  - status (string 20, required, default: 'planned') — 'planned' or 'published'
  - published_at (timestamp, nullable) — actual publish timestamp, set when marked as published
  - color (string 7, nullable) — optional hex color override for calendar display
  - sort_order (integer, required, default: 0) — ordering within the same date
  - created_at, updated_at (timestamps)

Indexes:
  - index on planned_date (frequent calendar range queries)
  - index on status (filtering)
  - index on type (filtering)
  - composite index on (planned_date, sort_order) for ordered display

Foreign keys: none (standalone table, single-user app)
```

---

## 3. FILE MAP

```
MIGRATIONS:
  - database/migrations/2026_04_01_600001_create_content_calendar_items_table.php

MODELS:
  - app/Models/ContentCalendarItem.php (single model — no subfolder needed)
    - fillable: title, type, description, planned_date, status, published_at, color, sort_order
    - casts: planned_date → date, published_at → datetime
    - scopes: scopePlanned(), scopePublished(), scopeInDateRange($start, $end), scopeOfType($type)
    - accessors: is_published (bool), type_label (string)

SERVICES:
  - app/Services/ContentCalendarService.php
    - getItemsForMonth(int $year, int $month): Collection — returns all items for the given month
    - getItemsForDateRange(Carbon $start, Carbon $end): Collection — items in a range
    - createItem(array $data): ContentCalendarItem — create a new content item
    - updateItem(ContentCalendarItem $item, array $data): ContentCalendarItem — update an item
    - deleteItem(ContentCalendarItem $item): void — delete an item
    - rescheduleItem(ContentCalendarItem $item, string $newDate): ContentCalendarItem — move item to new date
    - markAsPublished(ContentCalendarItem $item): ContentCalendarItem — set status to published and published_at
    - markAsPlanned(ContentCalendarItem $item): ContentCalendarItem — revert to planned status
    - getGapWeeks(int $year, int $month): array — returns array of week-start dates that have no content planned
    - getMonthStats(int $year, int $month): array — returns counts: total, planned, published, by type

--- ADMIN FILES ---

LIVEWIRE COMPONENTS:
  - app/Livewire/Admin/Youtube/ContentCalendar/ContentCalendarIndex.php
    - public properties: $year, $month, $items, $gapWeeks, $monthStats, $filterType
    - methods:
      - mount() — initialize to current month
      - loadCalendarData() — fetch items, gaps, stats for current year/month
      - previousMonth() — navigate to previous month
      - nextMonth() — navigate to next month
      - goToToday() — reset to current month
      - reschedule(int $itemId, string $newDate) — move item via drag-and-drop
      - togglePublished(int $itemId) — toggle between planned/published
      - delete(int $itemId) — delete an item with confirmation

  - app/Livewire/Admin/Youtube/ContentCalendar/ContentCalendarForm.php
    - public properties: $itemId, $title, $type, $description, $planned_date, $status, $color
    - methods:
      - mount(?ContentCalendarItem $contentCalendarItem) — load existing or defaults
      - save() — validate and create/update via service, redirect to index

VIEWS:
  - resources/views/livewire/admin/youtube/content-calendar/index.blade.php
    - Monthly calendar grid with content items, gap highlighting, stats cards, and drag-and-drop
  - resources/views/livewire/admin/youtube/content-calendar/form.blade.php
    - Create/edit form for content items

ROUTES (admin):
  - routes/admin/youtube/content-calendar.php
    - GET  /admin/youtube/content-calendar                                    → ContentCalendarIndex → admin.youtube.content-calendar.index
    - GET  /admin/youtube/content-calendar/create                             → ContentCalendarForm  → admin.youtube.content-calendar.create
    - GET  /admin/youtube/content-calendar/{contentCalendarItem}/edit          → ContentCalendarForm  → admin.youtube.content-calendar.edit
```

---

## 4. COMPONENT CONTRACTS

### ContentCalendarIndex

```
Component: App\Livewire\Admin\Youtube\ContentCalendar\ContentCalendarIndex
Namespace: App\Livewire\Admin\Youtube\ContentCalendar

Layout: #[Layout('components.layouts.admin')]

Properties:
  - $year (int) — currently displayed year
  - $month (int) — currently displayed month (1-12)
  - $items (Collection) — content items for the current month
  - $gapWeeks (array) — week-start dates with no content
  - $monthStats (array) — {total, planned, published, videos, blogs}
  - $filterType (string) #[Url] — '' (all), 'video', or 'blog'

Methods:
  - mount()
    Input: none
    Does: sets $year and $month to current date, calls loadCalendarData()
    Output: properties populated

  - loadCalendarData()
    Input: none
    Does: calls ContentCalendarService to get items (filtered by $filterType if set), gap weeks, and month stats for $year/$month
    Output: updates $items, $gapWeeks, $monthStats

  - previousMonth()
    Input: none
    Does: decrements month (wraps year), calls loadCalendarData()
    Output: calendar updates

  - nextMonth()
    Input: none
    Does: increments month (wraps year), calls loadCalendarData()
    Output: calendar updates

  - goToToday()
    Input: none
    Does: resets $year/$month to current, calls loadCalendarData()
    Output: calendar updates

  - reschedule(int $itemId, string $newDate)
    Input: item ID and new date string (Y-m-d)
    Does: validates date, calls ContentCalendarService::rescheduleItem(), calls loadCalendarData()
    Output: flash success message, calendar refreshes

  - togglePublished(int $itemId)
    Input: item ID
    Does: finds item, calls markAsPublished or markAsPlanned depending on current status
    Output: flash success message, calendar refreshes

  - delete(int $itemId)
    Input: item ID
    Does: finds item, calls ContentCalendarService::deleteItem()
    Output: flash success message, calendar refreshes

  - updatedFilterType()
    Input: none (triggered by Livewire property change)
    Does: calls loadCalendarData()
    Output: calendar re-filtered
```

### ContentCalendarForm

```
Component: App\Livewire\Admin\Youtube\ContentCalendar\ContentCalendarForm
Namespace: App\Livewire\Admin\Youtube\ContentCalendar

Layout: #[Layout('components.layouts.admin')]

Properties:
  - $itemId (int|null) — null for create, set for edit
  - $title (string) — content title
  - $type (string) — 'video' or 'blog'
  - $description (string) — optional notes
  - $planned_date (string) — Y-m-d format
  - $status (string) — 'planned' or 'published'
  - $color (string|null) — optional hex color

Methods:
  - mount(?ContentCalendarItem $contentCalendarItem = null)
    Input: optional model instance (injected via route model binding)
    Does: if editing, populates properties from model; if creating, sets defaults (type='video', status='planned', planned_date=today)
    Output: properties populated

  - save()
    Input: none (uses component properties)
    Does: validates all fields, calls ContentCalendarService::createItem() or updateItem()
    Output: flash success, redirect to admin.youtube.content-calendar.index

Validation Rules:
  - title: required|string|max:255
  - type: required|in:video,blog
  - description: nullable|string|max:2000
  - planned_date: required|date|date_format:Y-m-d
  - status: required|in:planned,published
  - color: nullable|string|regex:/^#[0-9A-Fa-f]{6}$/
```

---

## 5. VIEW BLUEPRINTS

### Index View (Calendar)

```
View: resources/views/livewire/admin/youtube/content-calendar/index.blade.php
Layout: components.layouts.admin
Side: ADMIN
Page title: "Content Calendar"

Design rules (from CLAUDE.md admin side):
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:
  1. Breadcrumb: Dashboard > YouTube > Content Calendar

  2. Page header: "Content Calendar" title + "Add Content" primary button (links to create route)
     Subtitle: "Plan your video and blog publishing schedule."

  3. Stat cards row (4 columns):
     - Total Items (bg-primary/10, text-primary-light icon)
     - Planned (bg-amber-500/10, text-amber-400 icon)
     - Published (bg-emerald-500/10, text-emerald-400 icon)
     - Gap Weeks (bg-red-500/10, text-red-400 icon) — count of weeks with no content

  4. Calendar navigation bar (inside a card):
     - Left: Previous month button (<)
     - Center: "Month YYYY" heading (font-mono uppercase)
     - Right: Next month button (>), Today button
     - Filter: type dropdown (All / Video / Blog)

  5. Calendar grid (main card):
     - 7-column grid, one column per day of week (Mon-Sun)
     - Header row: day names (text-xs font-mono uppercase text-gray-500)
     - Day cells: bg-dark-800, border-dark-700 borders
       - Day number in top-left (text-sm text-gray-400, current day highlighted with primary bg)
       - Content items as small cards within the cell:
         - Video items: bg-primary/10 border-l-2 border-primary text, fuchsia video icon
         - Blog items: bg-blue-500/10 border-l-2 border-blue-400 text, blue document icon
         - Each item shows: title (truncated), type icon, status dot (amber=planned, emerald=published)
         - Click item: navigates to edit page
         - Published items show checkmark overlay
       - Days in gap weeks: subtle red-500/5 background highlight
       - Days outside current month: opacity-30
     - Drag-and-drop: Alpine.js drag events on items, drop zones on day cells
       - On drop: calls $wire.reschedule(itemId, newDate)

  6. Quick actions on each item (hover):
     - Toggle published button (checkmark icon)
     - Edit button (pencil icon, links to edit route)
     - Delete button (trash icon, with wire:confirm)

  7. Gap indicator: below the calendar, a small alert card listing weeks with no content
     - "Weeks with no content: Week of Jan 13, Week of Jan 20" etc.
     - Uses bg-amber-500/10 border border-amber-500/20 rounded-xl styling

  8. Empty state: when no items exist for the month, show centered empty state inside calendar area
     - "No content planned this month" + "Add Content" button
```

### Form View (Create/Edit)

```
View: resources/views/livewire/admin/youtube/content-calendar/form.blade.php
Layout: components.layouts.admin
Side: ADMIN
Page title: "Create Content" or "Edit Content"

Design rules:
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:
  1. Breadcrumb: Dashboard > YouTube > Content Calendar > Create/Edit

  2. Page header: "Create Content" or "Edit Content" + Back button (links to index)
     Subtitle: "Fill in the details..." or "Update the details..."

  3. Form layout: 2/3 + 1/3 grid (xl:grid-cols-3)

     Main content (xl:col-span-2):
       Section card "Content Details":
         - Title: text input, required
         - Description: textarea, optional, 4 rows
         - Type: select dropdown (Video, Blog)

     Sidebar (xl:col-span-1):
       Section card "Schedule":
         - Planned Date: date input, required
         - Status: select (Planned, Published)

       Section card "Appearance" (optional):
         - Color: color picker input (hex), optional, with preview swatch

       Submit card:
         - Save button (primary, with loading state)
         - Cancel button (secondary, links to index)

  4. Flash messages at top (success/error)
```

---

## 6. VALIDATION RULES

```
Form: ContentCalendarForm (create & edit)
  - title: required|string|max:255
  - type: required|in:video,blog
  - description: nullable|string|max:2000
  - planned_date: required|date|date_format:Y-m-d
  - status: required|in:planned,published
  - color: nullable|string|regex:/^#[0-9A-Fa-f]{6}$/

Inline action: reschedule (in ContentCalendarIndex)
  - newDate: required|date|date_format:Y-m-d
```

---

## 7. EDGE CASES & BUSINESS RULES

- **Delete behavior:** Hard delete. No cascading needed (standalone table, no related records).
- **Unique constraints:** None. Multiple items can have the same title and same date.
- **Null handling:** description and color are nullable. published_at is null when status is 'planned', auto-set to now() when marked as published.
- **Sort logic:** Items within the same date are ordered by sort_order ASC, then created_at ASC.
- **Status transitions:**
  - When marking as published: set status='published' and published_at=now().
  - When reverting to planned: set status='planned' and published_at=null.
- **Drag-and-drop reschedule:** Only updates planned_date. Does not change status or any other field.
- **Gap week calculation:** A "gap week" is any ISO week (Mon-Sun) within the displayed month that has zero content items with a planned_date falling in that week. Weeks partially outside the month still count if at least one day is in the month.
- **Month boundaries:** Calendar grid shows leading/trailing days from adjacent months (grayed out). Items on those days are visible but not included in month stats.
- **Filter persistence:** The $filterType property is URL-bound (#[Url]) so filtering survives page refresh.
- **Today highlight:** The current date cell gets a visual indicator (primary-colored day number) regardless of which month is displayed.
- **Color field:** If null, items use default type-based colors (purple for video, blue for blog). If set, the custom color overrides.
- **No public side:** This feature has no public-facing component. All data is admin-only.

---

## 8. IMPLEMENTATION ORDER

```
1. database/migrations/2026_04_01_600001_create_content_calendar_items_table.php
2. app/Models/ContentCalendarItem.php
3. app/Services/ContentCalendarService.php
4. routes/admin/youtube/content-calendar.php
5. app/Livewire/Admin/Youtube/ContentCalendar/ContentCalendarIndex.php
6. app/Livewire/Admin/Youtube/ContentCalendar/ContentCalendarForm.php
7. resources/views/livewire/admin/youtube/content-calendar/index.blade.php
8. resources/views/livewire/admin/youtube/content-calendar/form.blade.php
9. Update sidebar in resources/views/components/layouts/admin.blade.php — add YouTube group with Content Calendar link
```
