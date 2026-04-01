# Application Tracker — Spec

Side: ADMIN

---

## 1. MODULE OVERVIEW

The Application Tracker is a kanban-style board within the Job Search module group that lets the authenticated user track job applications through their lifecycle. Applications flow through five status columns — Saved, Applied, Interview, Offer, Rejected — and can be dragged between columns to update their status. Each application card stores the company, position, URL, applied date, salary offered, and free-form notes.

### Features
- Kanban board with five columns: Saved, Applied, Interview, Offer, Rejected
- Drag-and-drop cards between columns to change status (Alpine.js + Livewire)
- Per-column application count badges
- Create/edit application form (modal or dedicated page)
- Optional link to an existing `JobListing` record (nullable FK)
- Delete application with confirmation
- Filter/search applications by company or position

### Admin Features
- View all applications in kanban layout
- Create new application (manual entry or linked to a job listing)
- Edit application details (company, position, status, dates, notes, salary, URL)
- Drag card to new column to update status
- Delete application

---

## 2. DATABASE SCHEMA

```
Table: job_applications
Columns:
  - id (bigint, primary key, auto increment)
  - job_listing_id (bigint, unsigned, nullable — FK to job_listings.id)
  - company (string 255, required)
  - position (string 255, required)
  - status (string 50, required, default: 'saved') — enum: saved, applied, interview, offer, rejected
  - applied_date (date, nullable — date application was submitted)
  - notes (text, nullable — free-form notes, interview dates, etc.)
  - salary_offered (string 100, nullable — e.g. "$120k-$150k" or "95000")
  - url (string 2048, nullable — link to job posting)
  - sort_order (integer, unsigned, default: 0 — position within its column)
  - created_at, updated_at (timestamps)

Indexes:
  - index on status (for column grouping queries)
  - index on job_listing_id (for FK lookups)
  - index on company (for search)

Foreign Keys:
  - job_listing_id → job_listings.id ON DELETE SET NULL
```

---

## 3. FILE MAP

```
MIGRATIONS:
  - database/migrations/YYYY_MM_DD_XXXXXX_create_job_applications_table.php

MODELS:
  - app/Models/JobSearch/JobApplication.php
    - fillable: job_listing_id, company, position, status, applied_date, notes, salary_offered, url, sort_order
    - relationships:
      - jobListing(): BelongsTo → App\Models\JobSearch\JobListing (nullable)
    - casts:
      - applied_date: 'date'
      - sort_order: 'integer'
    - constants: STATUS_SAVED, STATUS_APPLIED, STATUS_INTERVIEW, STATUS_OFFER, STATUS_REJECTED
    - scope: scopeByStatus($query, $status)

SERVICES:
  - app/Services/ApplicationTrackerService.php
    - getApplicationsGroupedByStatus(): array — returns ['saved' => Collection, 'applied' => Collection, ...] sorted by sort_order
    - getStatusCounts(): array — returns ['saved' => int, 'applied' => int, ...]
    - createApplication(array $data): JobApplication — creates a new application
    - updateApplication(JobApplication $application, array $data): JobApplication — updates an application
    - deleteApplication(JobApplication $application): void — deletes an application
    - updateStatus(JobApplication $application, string $newStatus, int $newSortOrder): void — changes status and sort_order (drag-drop)
    - reorderColumn(string $status, array $orderedIds): void — reorders cards within a single column

--- ADMIN FILES ---

LIVEWIRE COMPONENTS:
  - app/Livewire/Admin/JobSearch/ApplicationTracker/ApplicationTrackerIndex.php
    - Kanban board page — loads all applications grouped by status
    - public properties: search, applications (grouped), statusCounts
    - methods: mount(), updateCardStatus(), deleteApplication(), getApplicationsProperty()

  - app/Livewire/Admin/JobSearch/ApplicationTracker/ApplicationTrackerForm.php
    - Create/edit form page for a single application
    - public properties: applicationId, job_listing_id, company, position, status, applied_date, notes, salary_offered, url
    - methods: mount($application = null), save(), loadApplication()

VIEWS:
  - resources/views/livewire/admin/job-search/application-tracker/index.blade.php
    - Kanban board with five columns, drag-drop via Alpine.js
  - resources/views/livewire/admin/job-search/application-tracker/form.blade.php
    - Full-width form for creating/editing an application

ROUTES (admin):
  - routes/admin/job-search/application-tracker.php
    - GET  /admin/job-search/applications           → ApplicationTrackerIndex  → admin.job-search.applications.index
    - GET  /admin/job-search/applications/create     → ApplicationTrackerForm   → admin.job-search.applications.create
    - GET  /admin/job-search/applications/{jobApplication}/edit → ApplicationTrackerForm → admin.job-search.applications.edit
```

---

## 4. COMPONENT CONTRACTS

### ApplicationTrackerIndex

```
Component: App\Livewire\Admin\JobSearch\ApplicationTracker\ApplicationTrackerIndex
Namespace: App\Livewire\Admin\JobSearch\ApplicationTracker
Layout: #[Layout('components.layouts.admin')]

Properties:
  - $search (string, '') — #[Url] search filter for company/position
  - $statusCounts (array) — count per column, computed on load and after updates

Computed/Derived:
  - applications(): array — grouped by status, each group sorted by sort_order

Methods:
  - mount()
    Input: none
    Does: loads applications grouped by status via ApplicationTrackerService
    Output: sets properties

  - updateCardStatus(int $applicationId, string $newStatus, int $newSortOrder)
    Input: application ID, target status column, new sort position
    Does: calls ApplicationTrackerService::updateStatus(), refreshes board
    Output: updates kanban board in-place

  - reorderColumn(string $status, array $orderedIds)
    Input: column status key, array of application IDs in new order
    Does: calls ApplicationTrackerService::reorderColumn()
    Output: persists new sort order

  - deleteApplication(int $applicationId)
    Input: application ID
    Does: calls ApplicationTrackerService::deleteApplication()
    Output: flash success, refreshes board
```

### ApplicationTrackerForm

```
Component: App\Livewire\Admin\JobSearch\ApplicationTracker\ApplicationTrackerForm
Namespace: App\Livewire\Admin\JobSearch\ApplicationTracker
Layout: #[Layout('components.layouts.admin')]

Properties:
  - $applicationId (int|null) — null for create, set for edit
  - $job_listing_id (int|null) — optional FK
  - $company (string, '') — required
  - $position (string, '') — required
  - $status (string, 'saved') — required, one of the 5 statuses
  - $applied_date (string|null) — date string
  - $notes (string, '') — optional
  - $salary_offered (string, '') — optional
  - $url (string, '') — optional

Methods:
  - mount($jobApplication = null)
    Input: optional JobApplication model (route model binding)
    Does: if editing, populates all properties from the model
    Output: sets properties

  - save()
    Input: none (reads from properties)
    Does: validates, calls ApplicationTrackerService create or update
    Output: flash success, redirect to admin.job-search.applications.index

Validation Rules:
  - company: required|string|max:255
  - position: required|string|max:255
  - status: required|string|in:saved,applied,interview,offer,rejected
  - applied_date: nullable|date
  - notes: nullable|string|max:5000
  - salary_offered: nullable|string|max:100
  - url: nullable|url|max:2048
  - job_listing_id: nullable|exists:job_listings,id
```

---

## 5. VIEW BLUEPRINTS

### Index View (Kanban Board)

```
View: resources/views/livewire/admin/job-search/application-tracker/index.blade.php
Layout: components.layouts.admin
Side: ADMIN
Page title: "Applications"

Design rules:
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:
  - Breadcrumb: Dashboard > Job Search > Applications
  - Page header: title "Applications" + "Add Application" button (links to create route)
  - Stat cards row (5 cards): one per status column showing count
    - Saved: bg-blue-500/10, text-blue-400
    - Applied: bg-primary/10, text-primary-light
    - Interview: bg-amber-500/10, text-amber-400
    - Offer: bg-emerald-500/10, text-emerald-400
    - Rejected: bg-red-500/10, text-red-400
  - Search bar: filter applications by company or position name
  - Kanban board: horizontal scroll container with 5 columns
    - Each column: bg-dark-800 rounded-xl border border-dark-700, min-width ~280px
    - Column header: status name (font-mono uppercase) + count badge
    - Column body: vertical stack of application cards, droppable zone
    - Application card: bg-dark-700 rounded-lg p-4, shows company (bold), position, applied_date, salary badge if present
    - Card actions: edit link (icon), delete button (icon)
    - Empty column: muted text "No applications"
  - Alpine.js drag-and-drop:
    - Uses native HTML5 drag/drop or SortableJS via Alpine plugin
    - On drop: calls Livewire updateCardStatus(id, newStatus, newIndex)
    - Visual feedback: ghost card, drop zone highlight
```

### Form View (Create/Edit)

```
View: resources/views/livewire/admin/job-search/application-tracker/form.blade.php
Layout: components.layouts.admin
Side: ADMIN
Page title: "Create Application" / "Edit Application"

Design rules:
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:
  - Breadcrumb: Dashboard > Job Search > Applications > Create/Edit
  - Page header: dynamic title + Back button to index
  - Form layout: full-width, xl:grid-cols-3 (main 2/3, sidebar 1/3)
  - Main content (xl:col-span-2):
    - "Job Details" section card:
      - company (text input, required)
      - position (text input, required)
      - url (url input, optional)
      - salary_offered (text input, optional)
    - "Notes" section card:
      - notes (textarea, 6 rows, optional)
  - Sidebar (xl:col-span-1):
    - "Status & Dates" section card:
      - status (select dropdown: Saved, Applied, Interview, Offer, Rejected)
      - applied_date (date input, optional)
      - job_listing_id (select dropdown of existing job listings, optional — shows "None" + list)
    - "Actions" section card:
      - Save button (primary, with loading state)
      - Cancel button (secondary, links back to index)
```

---

## 6. VALIDATION RULES

```
Form: ApplicationTrackerForm (create & edit)
  - company: required|string|max:255
  - position: required|string|max:255
  - status: required|string|in:saved,applied,interview,offer,rejected
  - applied_date: nullable|date
  - notes: nullable|string|max:5000
  - salary_offered: nullable|string|max:100
  - url: nullable|url|max:2048
  - job_listing_id: nullable|exists:job_listings,id

Kanban drag-drop (inline validation in updateCardStatus):
  - applicationId: required|exists:job_applications,id
  - newStatus: required|string|in:saved,applied,interview,offer,rejected
  - newSortOrder: required|integer|min:0
```

---

## 7. EDGE CASES & BUSINESS RULES

- **Delete behavior:** Deleting a job application is a hard delete (no soft delete). Uses `wire:confirm` for confirmation.
- **Job listing FK:** `job_listing_id` is nullable. If the linked `JobListing` is deleted, the FK is SET NULL — the application remains with company/position intact.
- **No job_listings table yet:** The migration should use a nullable FK. If the `job_listings` table does not exist at migration time, the FK constraint should be wrapped in a `Schema::hasTable('job_listings')` check, or the FK should be added in the Job Feed migration instead. Safest approach: define the column as `unsignedBigInteger()->nullable()` without a formal FK constraint initially, and add the constraint in the Job Feed migration.
- **Duplicate applications:** No unique constraint on company+position — a user may apply to the same company for the same role multiple times.
- **Sort order:** When dragging a card into a column, all cards in the target column get their `sort_order` recalculated (0, 1, 2, ...). Cards within a column are always displayed in `sort_order ASC` order.
- **Status default:** New applications default to `saved` status.
- **Applied date auto-fill:** When status changes from `saved` to `applied` (either via form or drag), if `applied_date` is null, auto-set it to today.
- **Search:** Filters across all columns simultaneously — matching cards remain visible, non-matching cards are hidden but columns still show.
- **Empty board:** When no applications exist at all, show a centered empty state with an illustration/icon and a CTA to create the first application.
- **URL validation:** Must be a valid URL format when provided (nullable|url).
- **Salary field:** Stored as a string to allow flexible formats ("$120k", "90000-110000", "Competitive").

---

## 8. IMPLEMENTATION ORDER

```
1. database/migrations/YYYY_MM_DD_XXXXXX_create_job_applications_table.php
2. app/Models/JobSearch/JobApplication.php
3. app/Services/ApplicationTrackerService.php
4. routes/admin/job-search/application-tracker.php
5. app/Livewire/Admin/JobSearch/ApplicationTracker/ApplicationTrackerIndex.php
6. app/Livewire/Admin/JobSearch/ApplicationTracker/ApplicationTrackerForm.php
7. resources/views/livewire/admin/job-search/application-tracker/index.blade.php
8. resources/views/livewire/admin/job-search/application-tracker/form.blade.php
9. Update sidebar in resources/views/components/layouts/admin.blade.php — add "Applications" link under Job Search group
```
