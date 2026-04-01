# Video Ideas Tracker — Spec

Side: ADMIN

---

## 1. MODULE OVERVIEW

The Video Ideas Tracker lets the admin capture video ideas as they come, assign priority and status, and move them into the content calendar when ready to schedule. It serves as the ideation inbox for the YouTube module.

**Features:**
- Add a video idea with title and short description
- Mark priority (high, medium, low)
- Mark status (idea, scripting, recording, editing, published)
- Edit or delete ideas
- Move ideas to content calendar when ready (creates a content_calendar_items record and links back via foreign key)
- Filter and search ideas by status and priority

**Admin features:**
- CRUD for video ideas (list, create, edit, delete)
- Inline status and priority updates from the index page
- "Move to Content Calendar" action that creates a content calendar item and stores the link
- Visual indicators showing which ideas are already scheduled

---

## 2. DATABASE SCHEMA

```
Table: video_ideas
Columns:
  - id (bigint, primary key, auto increment)
  - title (string 255, required) — video idea title
  - description (text, nullable) — short description of the idea
  - priority (string 20, required, default: 'medium') — enum: high, medium, low
  - status (string 20, required, default: 'idea') — enum: idea, scripting, recording, editing, published
  - content_calendar_item_id (bigint unsigned, nullable) — FK to content_calendar_items table; set when idea is moved to calendar
  - created_at, updated_at (timestamps)

Indexes:
  - index on status
  - index on priority
  - index on content_calendar_item_id

Foreign keys:
  - content_calendar_item_id references content_calendar_items(id) on delete set null
```

> **Note:** The `content_calendar_items` table is defined by the Content Calendar feature spec. This feature depends on that table existing. The foreign key uses `SET NULL` on delete so that if a calendar item is removed, the idea remains but becomes unscheduled.

---

## 3. FILE MAP

```
MIGRATIONS:
  - database/migrations/2026_04_01_600001_create_video_ideas_table.php

MODELS:
  - app/Models/VideoIdea.php
    - Single model (no related models in this feature), so no subfolder
    - fillable: title, description, priority, status, content_calendar_item_id
    - relationships:
      - contentCalendarItem(): belongsTo(ContentCalendarItem::class)
    - casts:
      - (none needed beyond default — priority and status are plain strings)

SERVICES:
  - app/Services/VideoIdeaService.php
    - list(filters): LengthAwarePaginator — paginated list with optional search, status, priority filters
    - store(data): VideoIdea — create a new video idea
    - update(videoIdea, data): VideoIdea — update an existing idea
    - delete(videoIdea): void — delete a video idea (unlinks from calendar item if any)
    - moveToContentCalendar(videoIdea): VideoIdea — creates a content_calendar_items record (type: 'video', title from idea, planned_date: null) and sets content_calendar_item_id on the idea

--- ADMIN FILES ---

LIVEWIRE COMPONENTS:
  - app/Livewire/Admin/Youtube/VideoIdeas/VideoIdeaIndex.php
    - public properties: $search, $filterStatus, $filterPriority
    - methods:
      - mount() — initialize filters
      - delete(id) — delete a video idea via service
      - moveToCalendar(id) — move idea to content calendar via service
    - traits: WithPagination
    - uses: #[Layout('components.layouts.admin')]

  - app/Livewire/Admin/Youtube/VideoIdeas/VideoIdeaForm.php
    - public properties: $videoIdeaId, $title, $description, $priority, $status
    - methods:
      - mount(videoIdea?) — load existing idea for edit or set defaults for create
      - save() — validate and store/update via service, flash message, redirect to index
    - uses: #[Layout('components.layouts.admin')]

VIEWS:
  - resources/views/livewire/admin/youtube/video-ideas/index.blade.php
    - List page: filter bar (search + status + priority dropdowns), table of ideas, pagination
  - resources/views/livewire/admin/youtube/video-ideas/form.blade.php
    - Create/edit form for a video idea

ROUTES (admin):
  - routes/admin/youtube/video-ideas.php
    - GET  /admin/youtube/video-ideas                        → VideoIdeaIndex  → admin.youtube.video-ideas.index
    - GET  /admin/youtube/video-ideas/create                 → VideoIdeaForm   → admin.youtube.video-ideas.create
    - GET  /admin/youtube/video-ideas/{videoIdea}/edit        → VideoIdeaForm   → admin.youtube.video-ideas.edit
```

---

## 4. COMPONENT CONTRACTS

### VideoIdeaIndex

```
Component: App\Livewire\Admin\Youtube\VideoIdeas\VideoIdeaIndex
Namespace: App\Livewire\Admin\Youtube\VideoIdeas

Properties:
  - $search (string, '') — search query, bound with #[Url]
  - $filterStatus (string, '') — status filter, bound with #[Url]
  - $filterPriority (string, '') — priority filter, bound with #[Url]

Methods:
  - delete(int $id)
    Input: video idea ID
    Does: calls VideoIdeaService::delete(), flashes success message
    Output: stays on index page, list refreshes

  - moveToCalendar(int $id)
    Input: video idea ID
    Does: calls VideoIdeaService::moveToContentCalendar(), flashes success message
    Output: stays on index page, idea now shows "Scheduled" badge

Computed/Render:
  - render() calls VideoIdeaService::list() with search, filterStatus, filterPriority
  - passes paginated $videoIdeas to view
```

### VideoIdeaForm

```
Component: App\Livewire\Admin\Youtube\VideoIdeas\VideoIdeaForm
Namespace: App\Livewire\Admin\Youtube\VideoIdeas

Properties:
  - $videoIdeaId (int|null) — null for create, set for edit
  - $title (string, '')
  - $description (string, '')
  - $priority (string, 'medium')
  - $status (string, 'idea')

Methods:
  - mount(?VideoIdea $videoIdea)
    Input: optional VideoIdea model (route model binding)
    Does: if editing, populates all properties from the model
    Output: sets component state

  - save()
    Input: component properties
    Does:
      1. Validates all fields
      2. If $videoIdeaId: calls VideoIdeaService::update()
      3. Else: calls VideoIdeaService::store()
      4. Flashes success message
    Output: redirect to admin.youtube.video-ideas.index

Validation Rules:
  - title: required|string|max:255
  - description: nullable|string|max:2000
  - priority: required|in:high,medium,low
  - status: required|in:idea,scripting,recording,editing,published
```

---

## 5. VIEW BLUEPRINTS

### Index View

```
View: resources/views/livewire/admin/youtube/video-ideas/index.blade.php
Layout: components.layouts.admin
Side: ADMIN
Page title: "Video Ideas"

Design rules:
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:
  - Breadcrumb: Dashboard > YouTube > Video Ideas
  - Page header: "Video Ideas" title + "Add Idea" button (links to create route)
  - Filter bar card: search input + status dropdown (All, Idea, Scripting, Recording, Editing, Published) + priority dropdown (All, High, Medium, Low)
  - Table card:
    Columns: Title (with description preview below), Priority (badge), Status (badge), Scheduled (shows calendar icon + date if linked, dash if not), Actions
    - Priority badges:
      - high: bg-red-500/10 text-red-400
      - medium: bg-amber-500/10 text-amber-400
      - low: bg-emerald-500/10 text-emerald-400
    - Status badges:
      - idea: bg-gray-500/10 text-gray-400
      - scripting: bg-blue-500/10 text-blue-400
      - recording: bg-amber-500/10 text-amber-400
      - editing: bg-fuchsia-500/10 text-fuchsia-400
      - published: bg-emerald-500/10 text-emerald-400
    - Actions: edit icon button, "Move to Calendar" icon button (only if not already scheduled), delete icon button
  - Empty state: "No video ideas yet" with "Add First Idea" button
  - Pagination footer with record count
```

### Form View

```
View: resources/views/livewire/admin/youtube/video-ideas/form.blade.php
Layout: components.layouts.admin
Side: ADMIN
Page title: "Create Video Idea" / "Edit Video Idea"

Design rules:
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:
  - Breadcrumb: Dashboard > YouTube > Video Ideas > Create/Edit
  - Page header: dynamic title + Back button (links to index)
  - 2/3 + 1/3 grid layout:
    - Left (2/3): "Idea Details" card
      - Title input (text, required)
      - Description textarea (4 rows, optional)
    - Right (1/3):
      - "Settings" card:
        - Priority select (high, medium, low)
        - Status select (idea, scripting, recording, editing, published)
      - "Actions" card:
        - Save button (primary, with loading state)
        - Cancel button (secondary, links to index)
```

---

## 6. VALIDATION RULES

```
Form: VideoIdeaForm (create & edit)
  - title: required|string|max:255
  - description: nullable|string|max:2000
  - priority: required|in:high,medium,low
  - status: required|in:idea,scripting,recording,editing,published
```

---

## 7. EDGE CASES & BUSINESS RULES

- **Delete with calendar link:** When deleting a video idea that has a `content_calendar_item_id`, the idea is deleted but the calendar item remains (the calendar item is independent content). The foreign key is on the video_ideas side, so no cascade issue.
- **Move to calendar (already scheduled):** If `content_calendar_item_id` is already set, the "Move to Calendar" action should be hidden/disabled. The service method should check and throw an exception or return early if already scheduled.
- **Move to calendar creates a calendar item:** The service creates a `content_calendar_items` record with `type = 'video'`, `title` copied from the idea, and `planned_date = null` (user will set the date in the content calendar). The returned item's ID is stored in `video_ideas.content_calendar_item_id`.
- **Status "published" vs calendar:** An idea can be marked "published" independently of the calendar. These are separate workflows — status tracks production progress, calendar tracks scheduling.
- **Sort order:** Default sort is `created_at desc` (newest ideas first). Could also sort by priority (high first).
- **No soft deletes:** Video ideas use hard deletes. They are lightweight records.
- **Unique constraints:** None — multiple ideas can have the same title.
- **content_calendar_items table dependency:** The migration must run after the content_calendar_items migration. Use a timestamp that comes after the content calendar migration.

---

## 8. IMPLEMENTATION ORDER

```
1. Migration: database/migrations/2026_04_01_600001_create_video_ideas_table.php
   (depends on content_calendar_items table existing)
2. Model: app/Models/VideoIdea.php
3. Service: app/Services/VideoIdeaService.php
4. Routes: routes/admin/youtube/video-ideas.php
5. Livewire component: app/Livewire/Admin/Youtube/VideoIdeas/VideoIdeaIndex.php
6. Livewire component: app/Livewire/Admin/Youtube/VideoIdeas/VideoIdeaForm.php
7. View: resources/views/livewire/admin/youtube/video-ideas/index.blade.php
8. View: resources/views/livewire/admin/youtube/video-ideas/form.blade.php
9. Sidebar: Add "YouTube" collapsible group to admin layout with "Video Ideas" link
```
