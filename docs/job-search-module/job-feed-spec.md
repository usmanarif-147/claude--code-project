# Job Feed — Spec

Side: **ADMIN**

---

## 1. MODULE OVERVIEW

The Job Feed is the primary feature within the Job Search module group. It aggregates job listings from multiple external platforms (Indeed, Glassdoor, LinkedIn via JSearch; RemoteOK; Remotive; Adzuna; Rozee.pk and Mustakbil.com via SerpAPI) into a single unified feed. Jobs are fetched using stored API keys and filtered by user-configured preferences from the Settings module. An AI deduplication step removes duplicate listings that appear across platforms.

### Features
- Fetch jobs from multiple sources using configured API keys (JSearch, RemoteOK, Remotive, Adzuna, SerpAPI)
- Display jobs in a unified card-based feed: title, company, salary (if available), location, source platform, posted date
- Filter feed by: remote/onsite/hybrid, salary range, tech stack, country (Pakistan / International)
- Search jobs by keyword within fetched results
- Click to open original job posting in a new tab
- Mark jobs as "interested" or "not relevant" (persisted per user)
- AI-powered deduplication to merge identical listings from different platforms
- Pagination of the job feed
- Manual "Fetch New Jobs" trigger plus display of last fetch timestamp
- Stat cards: total jobs, new today, interested count, platforms active

### Admin Features
- View and manage all fetched job listings
- Trigger manual fetch from all enabled platforms
- Mark individual jobs as interested / not relevant / unmarked
- Filter and search across the feed
- Dismiss (hide) irrelevant jobs from the feed
- View source platform badge per job

---

## 2. DATABASE SCHEMA

```
Table: job_listings
Columns:
  - id (bigint, primary key, auto increment)
  - user_id (bigint, unsigned, required, FK -> users.id)
  - external_id (string 255, required) — unique ID from the source platform
  - source_platform (string 30, required) — jsearch, remoteok, remotive, adzuna, rozee, mustakbil
  - title (string 500, required)
  - company_name (string 255, nullable)
  - company_logo_url (string 1000, nullable)
  - description (text, nullable) — short excerpt or full description
  - location (string 255, nullable) — e.g. "Remote", "Lahore, Pakistan", "New York, US"
  - location_type (string 30, nullable) — remote, onsite, hybrid
  - country (string 100, nullable) — parsed country name
  - salary_min (unsignedInteger, nullable)
  - salary_max (unsignedInteger, nullable)
  - salary_currency (string 3, nullable) — USD, PKR, etc.
  - salary_text (string 255, nullable) — raw salary string from source (e.g. "$80k-$120k")
  - tech_stack (json, nullable) — array of technology keywords extracted from listing
  - job_url (string 2000, required) — link to original posting
  - posted_at (timestamp, nullable) — when the job was posted on the source platform
  - fetched_at (timestamp, required) — when we fetched this listing
  - user_status (string 20, nullable) — null (unseen), interested, not_relevant
  - is_hidden (boolean, default false) — dismissed from feed
  - duplicate_group_id (string 100, nullable) — jobs with same group ID are duplicates; only primary is shown
  - is_duplicate_primary (boolean, default true) — if true, this is the representative listing for its duplicate group
  - created_at, updated_at (timestamps)

Indexes:
  - index on user_id
  - index on source_platform
  - index on (user_id, source_platform) — composite for per-platform queries
  - index on (user_id, external_id, source_platform) — unique composite to prevent re-inserting same job
  - index on posted_at
  - index on fetched_at
  - index on user_status
  - index on is_hidden
  - index on location_type
  - index on country
  - index on duplicate_group_id

Unique constraints:
  - unique on (user_id, external_id, source_platform) — same job from same platform is never duplicated

Foreign keys:
  - user_id references users(id) on delete cascade
```

```
Table: job_fetch_logs
Columns:
  - id (bigint, primary key, auto increment)
  - user_id (bigint, unsigned, required, FK -> users.id)
  - platform (string 30, required) — which platform was fetched
  - status (string 20, required) — success, failed, partial
  - jobs_fetched (unsignedInteger, default 0) — how many new jobs were added
  - duplicates_found (unsignedInteger, default 0) — how many duplicates detected
  - error_message (text, nullable) — error details if failed
  - started_at (timestamp, required)
  - completed_at (timestamp, nullable)
  - created_at, updated_at (timestamps)

Indexes:
  - index on user_id
  - index on platform
  - index on (user_id, platform)
  - index on started_at

Foreign keys:
  - user_id references users(id) on delete cascade
```

> Note: Tables `job_search_filters` and `api_keys` already exist (created by the Settings module). This spec references `JobSearchFilter` and `ApiKey` models but does NOT recreate them.

---

## 3. FILE MAP

```
MIGRATIONS:
  - database/migrations/YYYY_MM_DD_000001_create_job_listings_table.php
  - database/migrations/YYYY_MM_DD_000002_create_job_fetch_logs_table.php

MODELS:
  - app/Models/JobSearch/JobListing.php
    - fillable: user_id, external_id, source_platform, title, company_name, company_logo_url,
                description, location, location_type, country, salary_min, salary_max,
                salary_currency, salary_text, tech_stack, job_url, posted_at, fetched_at,
                user_status, is_hidden, duplicate_group_id, is_duplicate_primary
    - relationships:
      - user(): belongsTo(User::class)
    - casts:
      - tech_stack -> array
      - posted_at -> datetime
      - fetched_at -> datetime
      - is_hidden -> boolean
      - is_duplicate_primary -> boolean
      - salary_min -> integer
      - salary_max -> integer
    - scopes:
      - scopeForUser(Builder $query, int $userId): filters by user_id
      - scopeVisible(Builder $query): where is_hidden = false AND is_duplicate_primary = true
      - scopeByPlatform(Builder $query, string $platform): filters by source_platform
      - scopeByStatus(Builder $query, string $status): filters by user_status
      - scopeByLocationType(Builder $query, string $type): filters by location_type
      - scopeByCountry(Builder $query, string $country): filters by country
    - constants:
      - PLATFORM_JSEARCH, PLATFORM_REMOTEOK, PLATFORM_REMOTIVE, PLATFORM_ADZUNA, PLATFORM_ROZEE, PLATFORM_MUSTAKBIL
      - STATUS_INTERESTED, STATUS_NOT_RELEVANT
      - ALL_PLATFORMS (array mapping constant to display name)

  - app/Models/JobSearch/JobFetchLog.php
    - fillable: user_id, platform, status, jobs_fetched, duplicates_found, error_message,
                started_at, completed_at
    - relationships:
      - user(): belongsTo(User::class)
    - casts:
      - started_at -> datetime
      - completed_at -> datetime
      - jobs_fetched -> integer
      - duplicates_found -> integer
    - constants:
      - STATUS_SUCCESS, STATUS_FAILED, STATUS_PARTIAL

SERVICES:
  - app/Services/JobFeedService.php
    - fetchAllPlatforms(User $user): array — orchestrates fetching from all enabled platforms;
      reads enabled_platforms from JobSearchFilter; calls individual fetch methods; returns summary array
    - fetchFromJSearch(User $user, JobSearchFilter $filter): int — fetches from JSearch API using
      RapidAPI key; parses response; saves JobListing records; returns count of new jobs
    - fetchFromRemoteOK(User $user, JobSearchFilter $filter): int — fetches from RemoteOK free API;
      no key needed; filters by user preferences; returns count
    - fetchFromRemotive(User $user, JobSearchFilter $filter): int — fetches from Remotive free API;
      filters by user preferences; returns count
    - fetchFromAdzuna(User $user, JobSearchFilter $filter): int — fetches from Adzuna API using
      app_id/app_key from ApiKey; returns count
    - fetchFromSerpApi(User $user, JobSearchFilter $filter, string $site): int — fetches Pakistani
      job sites (Rozee.pk, Mustakbil) via SerpAPI Google search; returns count
    - deduplicateJobs(User $user): int — uses title + company similarity to group duplicates;
      sets duplicate_group_id and is_duplicate_primary; returns count of duplicates found
    - getFilteredFeed(User $user, array $filters, int $perPage): LengthAwarePaginator — returns
      paginated visible jobs with applied filters (search, location_type, country, user_status, platform, salary range)
    - updateJobStatus(JobListing $job, ?string $status): void — sets user_status to interested,
      not_relevant, or null (clear)
    - hideJob(JobListing $job): void — sets is_hidden = true
    - getStats(User $user): array — returns array with total_jobs, new_today, interested_count,
      platforms_active, last_fetch_at
    - getLastFetchLog(User $user): ?JobFetchLog — returns most recent fetch log
    - logFetch(User $user, string $platform, string $status, int $jobsFetched, int $duplicatesFound, ?string $error, Carbon $startedAt): JobFetchLog — creates a fetch log entry

--- ADMIN FILES ---

LIVEWIRE COMPONENTS:
  - app/Livewire/Admin/JobSearch/JobFeed/JobFeedIndex.php
    - public properties: search, filterPlatform, filterLocationType, filterCountry,
                         filterStatus, filterSalaryMin, filterSalaryMax
    - methods:
      - mount(): void — initializes default filter values
      - fetchJobs(): void — triggers fetchAllPlatforms via service; flashes result summary
      - updateStatus(int $jobId, ?string $status): void — marks job as interested/not_relevant/clear
      - hideJob(int $jobId): void — dismisses job from feed
      - getJobsProperty(): LengthAwarePaginator — computed property returning filtered paginated jobs
      - getStatsProperty(): array — computed property returning stat card data

VIEWS:
  - resources/views/livewire/admin/job-search/job-feed/index.blade.php
    - displays the job feed page with stat cards, filters, and job listing cards

ROUTES (admin):
  - routes/admin/job-search/job-feed.php
    - GET /admin/job-search/feed → JobFeedIndex → admin.job-search.feed.index
```

---

## 4. COMPONENT CONTRACTS

### Admin Components

```
Component: App\Livewire\Admin\JobSearch\JobFeed\JobFeedIndex
Namespace:  App\Livewire\Admin\JobSearch\JobFeed
Layout: #[Layout('components.layouts.admin')]
Traits: WithPagination

Properties:
  - $search (string, default '') — #[Url] keyword search across title, company, description
  - $filterPlatform (string, default '') — #[Url] filter by source_platform
  - $filterLocationType (string, default '') — #[Url] filter by location_type (remote/onsite/hybrid)
  - $filterCountry (string, default '') — #[Url] filter by country
  - $filterStatus (string, default '') — #[Url] filter by user_status (interested/not_relevant)
  - $filterSalaryMin (int|null, default null) — #[Url] minimum salary filter
  - $filterSalaryMax (int|null, default null) — #[Url] maximum salary filter

Methods:
  - mount()
    Input: none
    Does: initializes properties; no special logic needed (defaults suffice)
    Output: none

  - fetchJobs()
    Input: none
    Does: 1. Calls JobFeedService::fetchAllPlatforms(auth()->user())
          2. Calls JobFeedService::deduplicateJobs(auth()->user())
          3. Flashes success message with summary (e.g., "Fetched 24 new jobs from 4 platforms, 3 duplicates removed")
          4. On failure, flashes error message
    Output: session flash (success or error)

  - updateStatus(int $jobId, ?string $status)
    Input: job listing ID + new status (interested, not_relevant, or null to clear)
    Does: 1. Finds JobListing by ID (scoped to user)
          2. Calls JobFeedService::updateJobStatus($job, $status)
    Output: no flash — UI updates reactively

  - hideJob(int $jobId)
    Input: job listing ID
    Does: 1. Finds JobListing by ID (scoped to user)
          2. Calls JobFeedService::hideJob($job)
    Output: no flash — job disappears from feed reactively

  - getJobsProperty() [Computed]
    Input: reads all filter properties
    Does: calls JobFeedService::getFilteredFeed() with current filters, 15 per page
    Output: LengthAwarePaginator of JobListing models

  - getStatsProperty() [Computed]
    Input: none
    Does: calls JobFeedService::getStats(auth()->user())
    Output: array with keys: total_jobs, new_today, interested_count, platforms_active, last_fetch_at

  - updatingSearch() / updatingFilter*()
    Input: none
    Does: resets pagination to page 1
    Output: none
```

---

## 5. VIEW BLUEPRINTS

### Admin View

```
View: resources/views/livewire/admin/job-search/job-feed/index.blade.php
Layout: components.layouts.admin
Side: ADMIN
Page title: "Job Feed"

Design rules (from CLAUDE.md admin side):
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:

  1. Breadcrumb
     - Dashboard > Job Search > Job Feed

  2. Page Header
     - Title: "Job Feed" (text-2xl font-mono font-bold text-white uppercase tracking-wider)
     - Subtitle: "Jobs fetched from your enabled platforms, filtered by your preferences."
     - Right side: "Fetch New Jobs" primary button with loading state
       - Shows spinner while fetching
       - Below button: "Last fetched: [relative time]" in text-xs text-gray-500

  3. Stat Cards Row (4-column grid)
     - Total Jobs: total visible jobs count (icon: briefcase, bg-primary/10)
     - New Today: jobs fetched today count (icon: sparkles, bg-emerald-500/10)
     - Interested: jobs marked interested count (icon: heart, bg-fuchsia-500/10)
     - Active Platforms: number of platforms with successful fetch (icon: globe, bg-blue-500/10)

  4. Filter Bar (bg-dark-800 rounded-xl card)
     - Row 1:
       - Search input (keyword, full width on mobile, flex-1 on desktop)
       - Platform dropdown (All Platforms / JSearch / RemoteOK / Remotive / Adzuna / Rozee.pk / Mustakbil)
       - Location Type dropdown (All / Remote / Onsite / Hybrid)
     - Row 2 (collapsible "More Filters" toggle):
       - Country dropdown (All / Pakistan / International)
       - Status dropdown (All / Interested / Not Relevant / Unseen)
       - Salary Min input (number)
       - Salary Max input (number)

  5. Job Listing Cards (main feed area)
     - Each job is a card (bg-dark-800 border border-dark-700 rounded-xl p-5):
       - Top row: company logo (if available, 40x40 rounded-lg) or fallback initial avatar
       - Title: text-base font-medium text-white (clickable, opens job_url in new tab)
       - Company name: text-sm text-gray-400
       - Badges row:
         - Source platform badge (color-coded per platform)
         - Location type badge (remote=emerald, onsite=amber, hybrid=blue)
         - Country badge if present
       - Location text: text-sm text-gray-500
       - Salary: text-sm text-emerald-400 if available, otherwise hidden
       - Tech stack: row of small rounded badges (bg-dark-700 text-gray-300 text-xs)
       - Posted date: relative time (text-xs text-gray-500)
       - Action buttons row (bottom):
         - "Interested" button: toggleable, emerald when active (bg-emerald-500/10 text-emerald-400)
         - "Not Relevant" button: toggleable, red when active (bg-red-500/10 text-red-400)
         - "Hide" button: icon-only, dims and removes card (text-gray-500 hover:text-gray-300)
         - "Open" link: opens job_url in new tab (text-primary-light)

     - If a job has user_status = 'interested': card has left border accent (border-l-2 border-emerald-500)
     - If a job has user_status = 'not_relevant': card is slightly dimmed (opacity-60) — or hidden depending on filter

  6. Pagination
     - Standard Livewire pagination below the feed
     - Shows "Showing X-Y of Z jobs"

  7. Empty State (when no jobs match filters)
     - Icon: briefcase outline
     - Title: "No jobs found"
     - Subtitle: "Try adjusting your filters or fetch new jobs from your enabled platforms."
     - CTA button: "Fetch New Jobs"

  8. Empty State (when no jobs at all — first visit)
     - Icon: rocket or search outline
     - Title: "Your job feed is empty"
     - Subtitle: "Configure your job search filters in Settings, add API keys, then fetch your first batch of jobs."
     - CTA buttons: "Configure Filters" (link to settings) + "Fetch Jobs" (primary)
```

---

## 6. VALIDATION RULES

No traditional form validation — this feature does not have a create/edit form. Validation occurs on:

```
Filter inputs (sanitized in component):
  - search: nullable|string|max:200
  - filterPlatform: nullable|string|in:jsearch,remoteok,remotive,adzuna,rozee,mustakbil
  - filterLocationType: nullable|string|in:remote,onsite,hybrid
  - filterCountry: nullable|string|max:100
  - filterStatus: nullable|string|in:interested,not_relevant
  - filterSalaryMin: nullable|integer|min:0
  - filterSalaryMax: nullable|integer|min:0

updateStatus action:
  - jobId: required|integer|exists:job_listings,id (scoped to user)
  - status: nullable|string|in:interested,not_relevant

hideJob action:
  - jobId: required|integer|exists:job_listings,id (scoped to user)
```

---

## 7. EDGE CASES & BUSINESS RULES

### API Key Availability
- Before fetching from a platform, check if the required API key exists and is connected in `api_keys` table
- RemoteOK and Remotive are free APIs — no API key required
- JSearch requires JSEARCH key (RapidAPI)
- Adzuna requires ADZUNA key (app_id + app_key in extra_data)
- Rozee.pk and Mustakbil require SERPAPI key
- If a key is missing or failed test_status, skip that platform and log a warning in the fetch log

### Deduplication
- After fetching, run deduplication comparing title + company_name (normalized lowercase, stripped of special chars)
- Jobs with >85% similarity in title AND same company are grouped under one duplicate_group_id
- The first-fetched listing (earliest fetched_at) becomes is_duplicate_primary = true
- Only primary listings appear in the feed (scopeVisible filters out non-primaries)
- When showing a primary, optionally show "Also found on: [platform badges]" using the duplicate group

### Unique Constraint
- The unique constraint on (user_id, external_id, source_platform) prevents inserting the same job twice from the same platform
- On conflict, skip the duplicate (use updateOrCreate or catch the unique violation)

### User Status Toggling
- Clicking "Interested" on an already-interested job clears the status (toggles off)
- Clicking "Not Relevant" on an already-not-relevant job clears the status (toggles off)
- These are mutually exclusive — setting one clears the other

### Hidden Jobs
- Hidden jobs (is_hidden = true) are permanently removed from the feed for that user
- No "undo" — once hidden, only visible via database
- Hidden jobs are excluded from stat counts

### Fetch Throttling
- Prevent rapid re-fetching: disable "Fetch New Jobs" button if the last fetch was < 15 minutes ago
- Show countdown timer or "Available in X minutes" text

### Empty Platforms
- If no platforms are enabled in job_search_filters, show a message directing user to Settings
- If enabled platforms have no matching API keys, show a message directing user to API Keys settings

### Salary Handling
- salary_text stores the raw string from the source (displayed as-is when salary_min/max cannot be parsed)
- salary_min and salary_max are parsed integers for filtering; nullable when source doesn't provide structured salary
- salary_currency defaults to USD if not specified by source; PKR for Pakistani platforms

### Sort Order
- Default sort: posted_at DESC (newest first)
- Secondary sort: fetched_at DESC (most recently fetched first when posted_at is equal)
- Interested jobs could optionally be pinned to top (future enhancement, not MVP)

### Cascade on Delete
- user deletion cascades to all job_listings and job_fetch_logs
- No other cascade dependencies

### Platform Badge Colors (for consistent visual identity)
- JSearch (Indeed/Glassdoor/LinkedIn): bg-blue-500/10 text-blue-400
- RemoteOK: bg-emerald-500/10 text-emerald-400
- Remotive: bg-fuchsia-500/10 text-fuchsia-400
- Adzuna: bg-amber-500/10 text-amber-400
- Rozee.pk: bg-cyan-500/10 text-cyan-400
- Mustakbil: bg-purple-500/10 text-purple-400

---

## 8. IMPLEMENTATION ORDER

```
1. database/migrations/YYYY_MM_DD_000001_create_job_listings_table.php
2. database/migrations/YYYY_MM_DD_000002_create_job_fetch_logs_table.php
3. app/Models/JobSearch/JobListing.php
4. app/Models/JobSearch/JobFetchLog.php
5. app/Services/JobFeedService.php
6. routes/admin/job-search/job-feed.php
7. app/Livewire/Admin/JobSearch/JobFeed/JobFeedIndex.php
8. resources/views/livewire/admin/job-search/job-feed/index.blade.php
```

> Dependencies: This feature depends on the Settings module being complete (ApiKey model + ApiKeyService, JobSearchFilter model + JobSearchFilterService). These already exist and should be referenced, not recreated.
