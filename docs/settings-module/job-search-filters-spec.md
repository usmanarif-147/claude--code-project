# Job Search Filters — Spec

Side: **ADMIN**

---

## 1. MODULE OVERVIEW

This feature allows the authenticated admin user to configure default job search preferences that the system uses when fetching jobs daily from external platforms. It is a single-page settings form (edit-style, not CRUD index/form) with one row per user.

### Features
- Set preferred job titles as tags (e.g., "Laravel Developer", "Full Stack Engineer")
- Set preferred tech stack as tags (e.g., "Laravel", "Vue.js", "PHP", "AWS")
- Set location preference: remote, Pakistan, specific country, hybrid, or onsite
- Set minimum salary expectation with currency (USD or PKR)
- Set experience level (mid or senior)
- Enable/disable specific job platforms via toggle switches

### Admin Features
- Load existing filters on mount or create a default row for the user
- Edit and save all filter preferences in a single form submission
- Alpine.js tag-input components for job titles and tech stack arrays
- Toggle switches for enabling/disabling each job platform

---

## 2. DATABASE SCHEMA

```
Table: job_search_filters
Columns:
  - id                  (bigint, primary key, auto increment)
  - user_id             (bigint unsigned, foreign key -> users.id, CASCADE on delete, UNIQUE)
  - preferred_titles    (json, nullable) — array of strings, e.g. ["Laravel Developer", "Full Stack Engineer"]
  - preferred_tech      (json, nullable) — array of strings, e.g. ["Laravel", "Vue.js", "PHP"]
  - location_type       (varchar(30), nullable) — one of: remote, pakistan, country, hybrid, onsite
  - location_value      (varchar(255), nullable) — specific city/country when location_type is pakistan or country
  - min_salary          (integer unsigned, nullable) — minimum salary expectation
  - salary_currency     (varchar(3), default 'USD') — USD or PKR
  - experience_level    (varchar(20), nullable) — mid or senior
  - enabled_platforms   (json, nullable) — object: {"jsearch": true, "remoteok": true, "remotive": false, ...}
  - created_at          (timestamp)
  - updated_at          (timestamp)

Indexes:
  - UNIQUE index on user_id (one row per user)

Foreign Keys:
  - user_id REFERENCES users(id) ON DELETE CASCADE
```

---

## 3. FILE MAP

### MIGRATIONS
- `database/migrations/YYYY_MM_DD_XXXXXX_create_job_search_filters_table.php`

### MODELS
- `app/Models/JobSearchFilter.php` (single model, flat in Models/)
  - fillable: user_id, preferred_titles, preferred_tech, location_type, location_value, min_salary, salary_currency, experience_level, enabled_platforms
  - casts: preferred_titles -> array, preferred_tech -> array, enabled_platforms -> array (or object/json), min_salary -> integer
  - relationships: belongsTo(User::class)

### SERVICES
- `app/Services/JobSearchFilterService.php`
  - `getOrCreateForUser(User $user): JobSearchFilter` — finds existing row by user_id or creates a new default row
  - `update(JobSearchFilter $filter, array $data): JobSearchFilter` — validates and updates the filter row

### LIVEWIRE COMPONENTS (ADMIN)
- `app/Livewire/Admin/Settings/JobSearchFilters/JobSearchFiltersEdit.php`
  - Layout: `#[Layout('components.layouts.admin')]`
  - public properties: all form fields mapped from model columns
  - methods: mount(), save(), addTitle(), removeTitle(), addTech(), removeTech()

### VIEWS (ADMIN)
- `resources/views/livewire/admin/settings/job-search-filters/edit.blade.php`
  - Settings form page: 2/3 + 1/3 grid layout
  - Left column: Job Titles card, Tech Stack card, Location card, Compensation card, Experience Level card
  - Right column: Job Platforms card with toggle switches

### ROUTES (ADMIN)
- `routes/admin/settings/job-search-filters.php`
  - `GET /admin/settings/job-search-filters` -> `JobSearchFiltersEdit` -> `admin.settings.job-search-filters`

---

## 4. COMPONENT CONTRACTS

### Component: App\Livewire\Admin\Settings\JobSearchFilters\JobSearchFiltersEdit

```
Namespace: App\Livewire\Admin\Settings\JobSearchFilters

Properties:
  - $filterId (int|null) — ID of the JobSearchFilter record
  - $preferred_titles (array) — list of job title strings
  - $preferred_tech (array) — list of tech stack strings
  - $location_type (string) — one of: remote, pakistan, country, hybrid, onsite
  - $location_value (string) — city/country value (conditional on location_type)
  - $min_salary (int|null) — minimum salary number
  - $salary_currency (string) — 'USD' or 'PKR'
  - $experience_level (string) — 'mid' or 'senior'
  - $platform_jsearch (bool) — toggle for JSearch
  - $platform_remoteok (bool) — toggle for RemoteOK
  - $platform_remotive (bool) — toggle for Remotive
  - $platform_adzuna (bool) — toggle for Adzuna
  - $platform_rozee (bool) — toggle for Rozee.pk
  - $platform_mustakbil (bool) — toggle for Mustakbil.com
  - $newTitle (string) — temporary input for adding a new title tag
  - $newTech (string) — temporary input for adding a new tech tag

Methods:
  - mount()
    Input: none (uses auth()->user())
    Does:
      1. Calls JobSearchFilterService::getOrCreateForUser(auth()->user())
      2. Populates all public properties from the returned model
      3. Unpacks enabled_platforms JSON into individual $platform_* booleans
    Output: properties populated

  - save()
    Input: none (reads from public properties)
    Does:
      1. Runs validation
      2. Packs $platform_* booleans back into enabled_platforms array
      3. Calls JobSearchFilterService::update($filter, $data)
      4. Flashes success message
      5. Redirects to same page with navigate: true
    Output: flash 'success' + redirect

  - addTitle()
    Input: reads $newTitle
    Does: trims and adds to $preferred_titles array if not empty and not duplicate, resets $newTitle
    Output: updated $preferred_titles

  - removeTitle($index)
    Input: array index
    Does: removes the title at given index from $preferred_titles, re-indexes array
    Output: updated $preferred_titles

  - addTech()
    Input: reads $newTech
    Does: trims and adds to $preferred_tech array if not empty and not duplicate, resets $newTech
    Output: updated $preferred_tech

  - removeTech($index)
    Input: array index
    Does: removes the tech at given index from $preferred_tech, re-indexes array
    Output: updated $preferred_tech
```

---

## 5. VIEW BLUEPRINTS

### View: resources/views/livewire/admin/settings/job-search-filters/edit.blade.php

```
Layout: components.layouts.admin
Side: ADMIN
Page title: "Job Search Filters"

Design rules (from CLAUDE.md admin side):
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Breadcrumb:
  Dashboard > Settings > Job Search Filters

Page header:
  - Title: "Job Search Filters" (h1, font-mono uppercase tracking-wider)
  - Subtitle: "Configure your default job search preferences."

Grid: grid grid-cols-1 xl:grid-cols-3 gap-6

LEFT COLUMN (xl:col-span-2, space-y-6):

  Card 1 — Job Titles:
    - Heading: "Preferred Job Titles" (h2, font-mono uppercase tracking-wider)
    - Description: "Add titles you're looking for (e.g., Laravel Developer)"
    - Alpine.js tag-input:
      - Text input bound to $newTitle with @keydown.enter="$wire.addTitle()"
      - Displayed tags as badges with x-remove button
      - Each tag: bg-primary/10 text-primary-light px-3 py-1 rounded-full text-sm
      - Remove button on each tag calls $wire.removeTitle(index)

  Card 2 — Tech Stack:
    - Heading: "Preferred Tech Stack" (h2, font-mono uppercase tracking-wider)
    - Description: "Add technologies you specialize in"
    - Same Alpine.js tag-input pattern as Job Titles
    - Bound to $preferred_tech / $newTech

  Card 3 — Location:
    - Heading: "Location Preference" (h2, font-mono uppercase tracking-wider)
    - Radio group for location_type:
      - Remote (worldwide)
      - Pakistan
      - Specific Country
      - Hybrid
      - Onsite
    - Each radio: styled radio input with label, bg-dark-700 p-3 rounded-lg
    - Conditional text input for location_value:
      - Shows when location_type is "pakistan" (placeholder: "e.g., Lahore, Karachi, Islamabad")
      - Shows when location_type is "country" (placeholder: "e.g., United States, Germany")
      - Hidden for remote, hybrid, onsite
    - Uses Alpine.js x-show for conditional visibility

  Card 4 — Compensation:
    - Heading: "Compensation" (h2, font-mono uppercase tracking-wider)
    - Two-column grid (sm:grid-cols-2):
      - min_salary: number input, placeholder "e.g., 5000"
      - salary_currency: select dropdown with USD, PKR options

  Card 5 — Experience Level:
    - Heading: "Experience Level" (h2, font-mono uppercase tracking-wider)
    - Radio group:
      - Mid-Level
      - Senior
    - Styled same as location radio group

RIGHT COLUMN (space-y-6):

  Card 6 — Job Platforms:
    - Heading: "Job Platforms" (h2, font-mono uppercase tracking-wider)
    - Description: "Enable the platforms to search for jobs."

    - Sub-section label: "International" (text-xs font-mono text-gray-500 uppercase tracking-widest)
    - Toggle switch rows (design-system toggle pattern):
      - JSearch (Indeed/Glassdoor/LinkedIn) — wire:model="platform_jsearch"
      - RemoteOK — wire:model="platform_remoteok"
      - Remotive — wire:model="platform_remotive"
      - Adzuna — wire:model="platform_adzuna"

    - Sub-section label: "Pakistani" (text-xs font-mono text-gray-500 uppercase tracking-widest)
    - Toggle switch rows:
      - Rozee.pk — wire:model="platform_rozee"
      - Mustakbil.com — wire:model="platform_mustakbil"

    - Each toggle row uses the design-system toggle pattern:
      flex items-center justify-between p-4 bg-dark-700 rounded-lg
      Label + description on left, toggle switch on right

SAVE BUTTON (below grid, mt-6, flex justify-end):
  - Primary button with loading state: "Save Filters" / "Saving..."
  - Uses design-system primary button pattern with wire:loading states
```

---

## 6. VALIDATION RULES

```
Form: Job Search Filters (save method)
  - preferred_titles:    nullable|array
  - preferred_titles.*:  string|max:100
  - preferred_tech:      nullable|array
  - preferred_tech.*:    string|max:100
  - location_type:       nullable|string|in:remote,pakistan,country,hybrid,onsite
  - location_value:      nullable|string|max:255|required_if:location_type,pakistan|required_if:location_type,country
  - min_salary:          nullable|integer|min:0|max:9999999
  - salary_currency:     required|string|in:USD,PKR
  - experience_level:    nullable|string|in:mid,senior
  - platform_jsearch:    boolean
  - platform_remoteok:   boolean
  - platform_remotive:   boolean
  - platform_adzuna:     boolean
  - platform_rozee:      boolean
  - platform_mustakbil:  boolean
```

---

## 7. EDGE CASES & BUSINESS RULES

- **One row per user**: The user_id column has a UNIQUE constraint. `getOrCreateForUser()` uses `firstOrCreate` with user_id as the lookup key.
- **Default row on first visit**: If no row exists for the user, `getOrCreateForUser()` creates one with all nullable fields as null, salary_currency defaulting to 'USD', and enabled_platforms as an empty object `{}`.
- **Null handling**: All filter fields are nullable except salary_currency (defaults to 'USD'). Empty arrays for preferred_titles and preferred_tech are stored as `[]` (not null) once the user saves.
- **location_value conditional requirement**: location_value is required only when location_type is 'pakistan' or 'country'. For 'remote', 'hybrid', and 'onsite', location_value is cleared to null on save.
- **Tag deduplication**: addTitle() and addTech() must check for duplicate entries (case-insensitive) before adding.
- **Tag trimming**: Tags are trimmed of whitespace before adding.
- **Platform packing/unpacking**: The component unpacks the enabled_platforms JSON object into individual boolean properties on mount, and packs them back into a single JSON object on save. Default for all platforms is `false`.
- **No delete**: There is no delete action. The row persists and is updated in place.
- **No public side**: This feature has no public-facing view.
- **Cascade delete**: If the user is deleted, the job_search_filters row is deleted via CASCADE.

---

## 8. IMPLEMENTATION ORDER

```
1. Migration: database/migrations/YYYY_MM_DD_XXXXXX_create_job_search_filters_table.php
2. Model: app/Models/JobSearchFilter.php
3. Service: app/Services/JobSearchFilterService.php
4. Route file: routes/admin/settings/job-search-filters.php
5. Livewire component: app/Livewire/Admin/Settings/JobSearchFilters/JobSearchFiltersEdit.php
6. View: resources/views/livewire/admin/settings/job-search-filters/edit.blade.php
```
