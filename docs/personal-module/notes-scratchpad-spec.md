# Notes / Scratchpad — Spec

Side: **ADMIN**

---

## 1. Module Overview

A quick-capture scratchpad for writing down anything -- random thoughts, meeting notes, ideas, reminders. Notes have a title and content, can be searched, pinned to the top for quick access, and deleted when no longer needed.

**Features:**
- Create a note with title and content
- Simple text editor (plain textarea, no rich text)
- Search through notes by title or content
- Pin important notes to the top of the list
- Delete old notes

**Admin features (what admin can do):**
- Create notes with a title and content body
- Browse all notes in a searchable list (pinned notes always appear first)
- Search notes by title or content
- Pin/unpin notes to keep important ones at the top
- Edit existing notes
- Delete notes

---

## 2. Database Schema

```
Table: notes
Columns:
  - id (bigint, primary key, auto increment)
  - title (string 255, required) — note headline
  - content (text, nullable) — note body text
  - is_pinned (boolean, default false) — pinned notes float to the top
  - created_at, updated_at (timestamps)

Indexes:
  - index(is_pinned, updated_at) — for default sort: pinned first, then newest
  - fulltext(title, content) — for search (MySQL fulltext or LIKE fallback)
```

---

## 3. File Map

```
MIGRATIONS:
  - database/migrations/YYYY_MM_DD_000001_create_notes_table.php

MODELS:
  - app/Models/Note.php (single model — no subfolder needed)
    - fillable: title, content, is_pinned
    - casts: is_pinned → boolean
    - no relationships

SERVICES:
  - app/Services/NoteService.php
    - getFilteredNotes(search: ?string, perPage: int = 15): LengthAwarePaginator
      — returns notes ordered by is_pinned DESC, updated_at DESC; filters by title/content LIKE when search is provided
    - createNote(data: array): Note
      — creates a new note from validated data
    - updateNote(note: Note, data: array): Note
      — updates an existing note
    - deleteNote(note: Note): void
      — permanently deletes the note
    - togglePin(note: Note): Note
      — flips is_pinned and saves; returns updated note

--- ADMIN FILES ---

LIVEWIRE COMPONENTS:
  - app/Livewire/Admin/Personal/NotesScratchpad/NotesScratchpadIndex.php
    - list page with search, pin toggle, delete
  - app/Livewire/Admin/Personal/NotesScratchpad/NotesScratchpadForm.php
    - create/edit form

VIEWS:
  - resources/views/livewire/admin/personal/notes-scratchpad/index.blade.php
    - searchable note list with pin badges and actions
  - resources/views/livewire/admin/personal/notes-scratchpad/form.blade.php
    - create/edit form with title input and content textarea

ROUTES:
  - routes/admin/personal/notes-scratchpad.php
    - GET /admin/personal/notes-scratchpad → NotesScratchpadIndex → admin.personal.notes-scratchpad.index
    - GET /admin/personal/notes-scratchpad/create → NotesScratchpadForm → admin.personal.notes-scratchpad.create
    - GET /admin/personal/notes-scratchpad/{note}/edit → NotesScratchpadForm → admin.personal.notes-scratchpad.edit
```

---

## 4. Component Contracts

### NotesScratchpadIndex

```
Component: App\Livewire\Admin\Personal\NotesScratchpad\NotesScratchpadIndex
Namespace: App\Livewire\Admin\Personal\NotesScratchpad
Layout: components.layouts.admin

Properties:
  - $search (string, #[Url]) — search query, filters notes by title/content

Methods:
  - togglePin(noteId: int)
    Input: note ID
    Does: calls NoteService::togglePin() on the note
    Output: flash success message ("Note pinned." or "Note unpinned.")

  - delete(noteId: int)
    Input: note ID
    Does: calls NoteService::deleteNote()
    Output: flash success message ("Note deleted.")

Computed / Render:
  - Notes list from NoteService::getFilteredNotes($this->search) with pagination
```

### NotesScratchpadForm

```
Component: App\Livewire\Admin\Personal\NotesScratchpad\NotesScratchpadForm
Namespace: App\Livewire\Admin\Personal\NotesScratchpad
Layout: components.layouts.admin

Properties:
  - $noteId (int|null) — null for create, set for edit
  - $title (string) — note title
  - $content (string) — note body

Methods:
  - mount(note: ?Note = null)
    Input: optional Note model (route model binding)
    Does: if editing, populates $noteId, $title, $content from the model

  - save()
    Input: none (reads component properties)
    Does:
      1. Validates title and content
      2. If $noteId → calls NoteService::updateNote()
      3. If not → calls NoteService::createNote()
    Output: flash success, redirect to admin.personal.notes-scratchpad.index

Validation Rules:
  - title: required|string|max:255
  - content: nullable|string|max:50000
```

---

## 5. View Blueprints

### Index View

```
View: resources/views/livewire/admin/personal/notes-scratchpad/index.blade.php
Layout: components.layouts.admin
Side: ADMIN
Page title: "Notes / Scratchpad"

Design rules:
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:
  - Breadcrumb: Dashboard > Personal > Notes / Scratchpad
  - Page header: title "Notes / Scratchpad" + subtitle "Quickly jot down anything." + "New Note" button (links to create route)
  - Flash messages (success/error)
  - Filter bar card: search input with magnifying glass icon, debounced 300ms (wire:model.live.debounce.300ms="search")
  - Notes table inside a card:
    Table columns:
      - Title (text-white font-medium, with pin icon badge if is_pinned)
      - Content preview (text-gray-400, truncated to ~80 chars)
      - Updated (relative time, text-gray-500)
      - Actions (pin/unpin toggle button, edit link, delete button with wire:confirm)
    Pinned notes display an amber pin badge next to title
    Sort: pinned first, then by updated_at descending
  - Empty state: "No notes yet" with "Create First Note" button
  - Pagination footer with record count
```

### Form View

```
View: resources/views/livewire/admin/personal/notes-scratchpad/form.blade.php
Layout: components.layouts.admin
Side: ADMIN
Page title: "Create Note" or "Edit Note"

Design rules:
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:
  - Breadcrumb: Dashboard > Personal > Notes / Scratchpad > Create/Edit
  - Page header: "Create Note" or "Edit Note" + subtitle + Back button to index
  - Flash messages
  - Full-width form layout (2/3 + 1/3 grid):
    Left (2/3) — "Note Details" section card:
      - Title input (text, required, full width)
      - Content textarea (rows="12", full width, placeholder "Write anything...")
    Right (1/3) — "Actions" section card:
      - Save button (primary, full width, with loading state)
      - Cancel button (secondary, full width, links back to index)
```

---

## 6. Validation Rules

```
Form: NotesScratchpadForm (create & edit)
  - title: required|string|max:255
  - content: nullable|string|max:50000
```

---

## 7. Edge Cases & Business Rules

- **Delete:** Hard delete (no soft deletes). Confirmation via `wire:confirm` before deletion.
- **Pin toggle:** Inline action on the index page. No limit on how many notes can be pinned.
- **Sort order:** Pinned notes always appear before unpinned notes. Within each group, sort by `updated_at` descending (most recently updated first).
- **Search:** Case-insensitive LIKE search on both `title` and `content` columns. Empty search returns all notes.
- **Content nullable:** A note can have a title only with no body content.
- **No categories/tags:** This is intentionally simple. No folders, no tags, no color coding.
- **No user_id:** Single-user admin app; no need for user scoping on notes.
- **Updated timestamp:** Editing a note updates `updated_at`, which affects sort position among unpinned notes.
- **Pagination:** 15 notes per page. Pinned notes count toward the page total.

---

## 8. Implementation Order

```
1. Migration: create_notes_table
2. Model: Note
3. Service: NoteService
4. Route file: routes/admin/personal/notes-scratchpad.php
5. Livewire component: NotesScratchpadIndex
6. Livewire component: NotesScratchpadForm
7. View: index.blade.php
8. View: form.blade.php
9. Sidebar: add "Notes / Scratchpad" link under Personal module group
```
