# Saved Searches — Spec

Side: **ADMIN**

---

## 1. MODULE OVERVIEW

Saved Searches lets the user create named, reusable job search filter presets. Unlike the global `job_search_filters` table (which stores one set of default preferences per user), each saved search is an individual named configuration with its own filter criteria that can be independently enabled or disabled. Multiple saved searches can be active simultaneously and will each be used during automated daily job fetching.

### Features
- Create a saved search with a descriptive name and a set of filters (titles, tech stack, location type, location value, salary, currency, experience level, platforms)
- Edit an existing saved search's name and filter criteria
- Delete a saved search
- Enable or disable a saved search without deleting it (toggle `is_active`)
- View all saved searches in a list with status badges, filter summary, and quick toggle
- Multiple saved searches can be active at the same time

### Admin Features
- Full CRUD on saved searches (create, read, update, delete)
- Inline toggle to activate/deactivate a saved search from the index page
- Filter the list by active/inactive status
- Search by name

---

## 2. DATABASE SCHEMA

```
Table: saved_searches
Columns:
  - id                BIGINT, primary key, auto increment
  - user_id           BIGINT UNSIGNED, NOT NULL, FK -> users.id ON DELETE CASCADE
  - name              VARCHAR(255), NOT NULL (e.g., "Remote Laravel International")
  - preferred_titles  JSON, NULLABLE (array of job title keywords, e.g., ["Laravel Developer", "PHP Engineer"])
  - preferred_tech    JSON, NULLABLE (array of tech keywords, e.g., ["Laravel", "Vue.js"])
  - location_type     VARCHAR(30), NULLABLE (remote, onsite, hybrid)
  - location_value    VARCHAR(255), NULLABLE (city/country, e.g., "Lahore", "Pakistan")
  - min_salary        UNSIGNED INTEGER, NULLABLE
  - salary_currency   VARCHAR(3), DEFAULT 'USD'
  - experience_level  VARCHAR(20), NULLABLE (junior, mid, senior, lead)
  - enabled_platforms JSON, NULLABLE (array of platform keys from JobSearchFilter::ALL_PLATFORMS)
  - is_active         BOOLEAN, DEFAULT true
  - created_at        TIMESTAMP
  - updated_at        TIMESTAMP

Indexes:
  - INDEX(user_id)
  - INDEX(user_id, is_active)

Foreign keys:
  - user_id references users(id) ON DELETE CASCADE
```

---

## 3. FILE MAP

```
MIGRATIONS:
  - database/migrations/xxxx_xx_xx_xxxxxx_create_saved_searches_table.php

MODELS:
  - app/Models/JobSearch/SavedSearch.php  (in JobSearch/ subfolder — module will have 2+ models as it grows)
    - fillable: user_id, name, preferred_titles, preferred_tech, location_type, location_value, min_salary, salary_currency, experience_level, enabled_platforms, is_active
    - casts: preferred_titles -> array, preferred_tech -> array, enabled_platforms -> array, min_salary -> integer, is_active -> boolean
    - relationships: belongsTo(User)
    - scopes: scopeActive($query), scopeForUser($query, $userId)

SERVICES:
  - app/Services/SavedSearchService.php
    - list(int $userId, ?string $search, ?string $status): LengthAwarePaginator — paginated list of saved searches for user, optionally filtered by name search and active/inactive status, ordered by created_at desc, 10 per page
    - create(int $userId, array $data): SavedSearch — creates a new saved search for the user
    - update(SavedSearch $savedSearch, array $data): SavedSearch — updates name and filter fields
    - delete(SavedSearch $savedSearch): void — hard deletes the saved search
    - toggleActive(SavedSearch $savedSearch): SavedSearch — flips is_active and returns updated model
    - getActiveForUser(int $userId): Collection — returns all active saved searches for user (used by future job fetching)

--- ADMIN FILES ---

LIVEWIRE COMPONENTS:
  - app/Livewire/Admin/JobSearch/SavedSearches/SavedSearchIndex.php
    - public properties: $search (string, URL-bound), $filterStatus (string, URL-bound)
    - methods: mount(), toggleActive(int $id), delete(int $id), render()
  - app/Livewire/Admin/JobSearch/SavedSearches/SavedSearchForm.php
    - public properties: $savedSearchId, $name, $preferred_titles, $preferred_tech, $location_type, $location_value, $min_salary, $salary_currency, $experience_level, $enabled_platforms, $is_active
    - methods: mount(?int $savedSearch), save(), render()

  - resources/views/livewire/admin/job-search/saved-searches/index.blade.php
    - List page with search/filter bar, table of saved searches, toggle and actions
  - resources/views/livewire/admin/job-search/saved-searches/form.blade.php
    - Create/edit form for a saved search

ROUTES (admin):
  - routes/admin/job-search/saved-searches.php
    - GET  /admin/job-search/saved-searches              -> SavedSearchIndex  -> admin.job-search.saved-searches.index
    - GET  /admin/job-search/saved-searches/create        -> SavedSearchForm   -> admin.job-search.saved-searches.create
    - GET  /admin/job-search/saved-searches/{savedSearch}/edit -> SavedSearchForm -> admin.job-search.saved-searches.edit
```

---

## 4. COMPONENT CONTRACTS

### Component: App\Livewire\Admin\JobSearch\SavedSearches\SavedSearchIndex

```
Namespace: App\Livewire\Admin\JobSearch\SavedSearches
Layout: components.layouts.admin

Properties:
  - $search (string, default '') — text search on saved search name, URL-bound via #[Url]
  - $filterStatus (string, default '') — filter by 'active', 'inactive', or '' (all), URL-bound via #[Url]

Methods:
  - toggleActive(int $id)
    Input: saved search ID
    Does:
      1. Finds the SavedSearch by ID (scoped to auth user)
      2. Calls SavedSearchService::toggleActive()
    Output: flash success message ("Search activated" or "Search deactivated")

  - delete(int $id)
    Input: saved search ID
    Does:
      1. Finds the SavedSearch by ID (scoped to auth user)
      2. Calls SavedSearchService::delete()
    Output: flash success message

  - render()
    Does:
      1. Calls SavedSearchService::list(auth()->id(), $this->search, $this->filterStatus)
      2. Returns view with paginated saved searches
```

### Component: App\Livewire\Admin\JobSearch\SavedSearches\SavedSearchForm

```
Namespace: App\Livewire\Admin\JobSearch\SavedSearches
Layout: components.layouts.admin

Properties:
  - $savedSearchId (int|null) — null for create, set for edit
  - $name (string) — saved search name
  - $preferred_titles (array, default []) — list of title keywords
  - $preferred_tech (array, default []) — list of tech keywords
  - $location_type (string, default '') — remote/onsite/hybrid
  - $location_value (string, default '') — city or country
  - $min_salary (int|null, default null) — minimum salary threshold
  - $salary_currency (string, default 'USD') — salary currency code
  - $experience_level (string, default '') — junior/mid/senior/lead
  - $enabled_platforms (array, default []) — selected platform keys
  - $is_active (bool, default true) — whether this search is active
  - $titleInput (string, default '') — temp input for adding a title tag
  - $techInput (string, default '') — temp input for adding a tech tag

Methods:
  - mount(?int $savedSearch = null)
    Input: optional saved search ID (route model binding by ID)
    Does:
      1. If $savedSearch provided, loads the SavedSearch model (scoped to auth user)
      2. Populates all properties from the model
      3. If not provided, keeps defaults (create mode)
    Output: sets component state

  - addTitle()
    Input: reads $titleInput
    Does: adds trimmed value to $preferred_titles array if not empty and not duplicate, clears $titleInput
    Output: updates $preferred_titles

  - removeTitle(int $index)
    Input: array index
    Does: removes title at given index, re-indexes array
    Output: updates $preferred_titles

  - addTech()
    Input: reads $techInput
    Does: adds trimmed value to $preferred_tech array if not empty and not duplicate, clears $techInput
    Output: updates $preferred_tech

  - removeTech(int $index)
    Input: array index
    Does: removes tech at given index, re-indexes array
    Output: updates $preferred_tech

  - save()
    Input: reads all public properties
    Does:
      1. Validates all fields
      2. Builds data array from validated properties
      3. If $savedSearchId: calls SavedSearchService::update()
      4. If no $savedSearchId: calls SavedSearchService::create()
    Output: flash success, redirect to admin.job-search.saved-searches.index

  - render()
    Does: returns view with platform options from JobSearchFilter::ALL_PLATFORMS

Validation Rules:
  - name: required|string|max:255
  - preferred_titles: nullable|array|max:20
  - preferred_titles.*: string|max:100
  - preferred_tech: nullable|array|max:20
  - preferred_tech.*: string|max:100
  - location_type: nullable|string|in:remote,onsite,hybrid
  - location_value: nullable|string|max:255
  - min_salary: nullable|integer|min:0|max:999999
  - salary_currency: required|string|size:3
  - experience_level: nullable|string|in:junior,mid,senior,lead
  - enabled_platforms: nullable|array
  - enabled_platforms.*: string|in:jsearch,remoteok,remotive,adzuna,rozee,mustakbil
  - is_active: boolean
```

---

## 5. VIEW BLUEPRINTS

### View: resources/views/livewire/admin/job-search/saved-searches/index.blade.php

```
Layout: components.layouts.admin
Side: ADMIN
Page title: "Saved Searches"

Design rules (from CLAUDE.md admin side):
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:

  1. Breadcrumb
     Dashboard > Job Search > Saved Searches

  2. Page Header
     - Title: "Saved Searches"
     - Subtitle: "Create and manage reusable job search filter presets."
     - "Add Search" button (bg-primary hover:bg-primary-hover text-white rounded-lg) linking to create route

  3. Filter Bar (bg-dark-800 border border-dark-700 rounded-xl p-4 mb-5)
     - Search input (wire:model.live.debounce.300ms="search", placeholder "Search by name...")
     - Status select (wire:model.live="filterStatus": All, Active, Inactive)

  4. Table (bg-dark-800 border border-dark-700 rounded-xl overflow-hidden)
     Columns:
       - Name: saved search name (text-sm font-medium text-white) with filter summary below (text-xs text-gray-500 showing location_type, tech count, platform count)
       - Filters: compact summary badges — location type badge, title count, tech count
       - Platforms: count of enabled platforms shown as badge (e.g., "3 platforms")
       - Status: toggle switch (inline, wire:click="toggleActive(id)")
         - Active: bg-emerald-500/10 text-emerald-400 "Active" with green dot
         - Inactive: bg-gray-500/10 text-gray-400 "Inactive" with gray dot
       - Actions: edit icon button + delete icon button (standard icon action buttons from design system)

     Each row: hover:bg-dark-700/30 transition-colors

  5. Pagination (standard Livewire pagination below table)

  6. Empty State (when no saved searches exist)
     - Icon: magnifying glass or bookmark icon in bg-primary/10 rounded-xl
     - Title: "No saved searches yet" (font-mono uppercase tracking-wider)
     - Subtitle: "Create your first saved search to start automating job discovery."
     - CTA button: "Create Saved Search" linking to create route

  7. Flash Messages
     - Standard session flash at top of page
```

### View: resources/views/livewire/admin/job-search/saved-searches/form.blade.php

```
Layout: components.layouts.admin
Side: ADMIN
Page title: "Create Saved Search" / "Edit Saved Search"

Design rules (from CLAUDE.md admin side):
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:

  1. Breadcrumb
     Dashboard > Job Search > Saved Searches > Create / Edit

  2. Page Header
     - Title: dynamic "Create Saved Search" or "Edit Saved Search" based on $savedSearchId
     - Subtitle: dynamic "Fill in the details..." or "Update the details..."
     - Back button linking to index route

  3. Form Layout: grid grid-cols-1 xl:grid-cols-3 gap-6

     Main content (xl:col-span-2 space-y-6):

       Section Card: "Basic Information"
         - Name input (required, text, full width) — "e.g., Remote Laravel International"

       Section Card: "Job Title Keywords"
         - Tag input for preferred_titles using Alpine.js
         - Text input with "Add" button; on enter/click adds tag to array
         - Tags displayed as removable badges (bg-primary/10 text-primary-light rounded-full px-3 py-1 text-sm)
         - Each tag has an X button to remove (wire:click="removeTitle(index)")
         - Helper text: "Add job titles you're looking for (e.g., Laravel Developer, PHP Engineer)"

       Section Card: "Tech Stack"
         - Same tag input pattern as titles, for preferred_tech
         - Helper text: "Add technologies to match (e.g., Laravel, Vue.js, React)"

       Section Card: "Location & Salary"
         - Two-column grid:
           - Location type select (Remote, On-site, Hybrid) — nullable
           - Location value text input (placeholder "City or country...") — nullable
           - Minimum salary number input — nullable
           - Salary currency select (USD, PKR, EUR, GBP) — defaults to USD

       Section Card: "Experience Level"
         - Single select: Junior, Mid, Senior, Lead — nullable

       Section Card: "Platforms"
         - Checkbox grid (2 columns) for each platform from JobSearchFilter::ALL_PLATFORMS
         - Each checkbox: wire:model="enabled_platforms" with platform key as value
         - Platform label + description (e.g., "JSearch (Indeed/Glassdoor/LinkedIn)")

     Sidebar (space-y-6):

       Status Card: "Status"
         - Toggle switch for is_active
         - Label: "Active" / "Inactive"
         - Description: "Active searches run during daily job fetching."

       Submit Card:
         - "Save" primary button (full width, with loading state)
         - "Cancel" secondary button (full width, links to index)

  4. Flash Messages
     - Standard session flash at top of page
```

---

## 6. VALIDATION RULES

```
Form: Save Saved Search (create and edit share same rules)
  - name: required|string|max:255
  - preferred_titles: nullable|array|max:20
  - preferred_titles.*: string|max:100
  - preferred_tech: nullable|array|max:20
  - preferred_tech.*: string|max:100
  - location_type: nullable|string|in:remote,onsite,hybrid
  - location_value: nullable|string|max:255
  - min_salary: nullable|integer|min:0|max:999999
  - salary_currency: required|string|size:3
  - experience_level: nullable|string|in:junior,mid,senior,lead
  - enabled_platforms: nullable|array
  - enabled_platforms.*: string|in:jsearch,remoteok,remotive,adzuna,rozee,mustakbil
  - is_active: boolean
```

---

## 7. EDGE CASES & BUSINESS RULES

- **No unique constraint on name**: A user can have multiple saved searches with the same name (though UI should discourage it, it is not blocked at the DB level).
- **Relationship to JobSearchFilter**: Saved searches are independent from the global `job_search_filters` record. The global filter stores default preferences; saved searches are individual named presets. They share the same column structure for filter fields but are separate tables with no foreign key between them.
- **Platform keys**: The `enabled_platforms` array values must match the keys defined in `JobSearchFilter::ALL_PLATFORMS`. Reuse the constant from the existing model rather than duplicating it.
- **Delete behavior**: Hard delete. No soft deletes. No cascade to other tables (saved_searches is a leaf table currently).
- **Auth scoping**: All queries are scoped to the authenticated user. A user can only see/manage their own saved searches.
- **Toggle from index**: The `toggleActive` action on the index page flips `is_active` without a full page reload (Livewire re-render). Flash message confirms the new state.
- **Empty filters**: A saved search can have all filter fields null/empty except `name` and `salary_currency`. This represents a very broad search with no specific filters.
- **Array fields (titles, tech, platforms)**: Stored as JSON. Empty arrays should be stored as `null` (service normalizes `[]` to `null` before saving).
- **Max 20 items per array field**: Both `preferred_titles` and `preferred_tech` are limited to 20 entries to prevent abuse.
- **Pagination**: 10 items per page on the index list.
- **Sort order**: Newest first (`created_at desc`) on the index page.
- **Sidebar placement**: The "Saved Searches" link goes under the "Job Search" parent group in the sidebar, alongside "Job Feed" and any future job search features.

---

## 8. IMPLEMENTATION ORDER

```
1. Migration: database/migrations/xxxx_xx_xx_xxxxxx_create_saved_searches_table.php
2. Model: app/Models/JobSearch/SavedSearch.php
3. Service: app/Services/SavedSearchService.php
4. Route: routes/admin/job-search/saved-searches.php
5. Livewire component: app/Livewire/Admin/JobSearch/SavedSearches/SavedSearchIndex.php
6. Livewire component: app/Livewire/Admin/JobSearch/SavedSearches/SavedSearchForm.php
7. View: resources/views/livewire/admin/job-search/saved-searches/index.blade.php
8. View: resources/views/livewire/admin/job-search/saved-searches/form.blade.php
9. Sidebar: add "Saved Searches" link under "Job Search" group in admin layout
```
