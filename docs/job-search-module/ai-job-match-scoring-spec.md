# AI Job Match Scoring — Spec

Side: **ADMIN**

---

## 1. MODULE OVERVIEW

AI Job Match Scoring analyzes each job listing's description and compares it against the user's resume, skills, preferred technologies, and job search preferences. It produces a percentage match score (0-100%) with a human-readable explanation of why the job matched or didn't. Jobs can be sorted by score and filtered by minimum score threshold.

### Features
- AI-powered scoring of job listings against user profile (skills, tech stack, experience, preferred titles)
- Match score stored per job listing (0-100%) with explanation text
- Score breakdown showing matched skills, missing skills, and bonus factors
- Bulk scoring: score all unscored jobs in one action
- Re-score individual jobs (e.g., after profile update)
- Sort job feed by match score (best matches first)
- Filter by minimum match score (e.g., show only 70%+ matches)
- Score summary stat cards: average score, top match count, unscored count
- Provider selection: prefer Claude, fallback to OpenAI (same pattern as AI Task Prioritization)

### Admin Features
- View all job listings with their match scores on a dedicated page
- Trigger "Score All Unscored" bulk action
- Trigger "Re-Score All" to refresh every score
- Re-score an individual job listing
- Filter by minimum score, platform, location type
- Sort by score descending (default), posted date, or company name
- View detailed score breakdown per job (matched skills, missing skills, explanation)

---

## 2. DATABASE SCHEMA

```
Table: job_match_scores
Columns:
  - id (bigint, primary key, auto increment)
  - user_id (bigint, unsigned, required, FK -> users.id)
  - job_listing_id (bigint, unsigned, required, FK -> job_listings.id)
  - score (unsignedTinyInteger, required) — 0-100 percentage match
  - explanation (text, nullable) — AI-generated human-readable explanation of why it matched/didn't
  - matched_skills (json, nullable) — array of skills the user has that the job requires
  - missing_skills (json, nullable) — array of skills the job requires that the user lacks
  - bonus_factors (json, nullable) — array of extra positive signals (e.g., preferred location, salary range match)
  - ai_provider (string 20, required) — claude or openai (which provider generated this score)
  - ai_model (string 50, nullable) — specific model used (e.g., claude-sonnet-4-20250514, gpt-4o)
  - scored_at (timestamp, required) — when the score was generated
  - created_at, updated_at (timestamps)

Indexes:
  - unique on (user_id, job_listing_id) — one score per job per user
  - index on user_id
  - index on job_listing_id
  - index on score
  - index on (user_id, score) — composite for filtered queries by score

Foreign keys:
  - user_id references users(id) on delete cascade
  - job_listing_id references job_listings(id) on delete cascade
```

> Note: Tables `job_listings`, `job_search_filters`, and `api_keys` already exist. Models `JobListing`, `JobSearchFilter`, and `ApiKey` are referenced but NOT recreated. The `Profile` and `Skill` models are also referenced for building the user's resume context.

---

## 3. FILE MAP

```
MIGRATIONS:
  - database/migrations/YYYY_MM_DD_000001_create_job_match_scores_table.php

MODELS:
  - app/Models/JobSearch/JobMatchScore.php
    - fillable: user_id, job_listing_id, score, explanation, matched_skills, missing_skills,
                bonus_factors, ai_provider, ai_model, scored_at
    - relationships:
      - user(): belongsTo(User::class)
      - jobListing(): belongsTo(JobListing::class)
    - casts:
      - matched_skills -> array
      - missing_skills -> array
      - bonus_factors -> array
      - scored_at -> datetime
      - score -> integer
    - scopes:
      - scopeForUser(Builder $query, int $userId): filters by user_id
      - scopeMinScore(Builder $query, int $minScore): where score >= $minScore
      - scopeHighMatches(Builder $query): where score >= 80
      - scopeUnscored — NOT on this model (this is used on JobListing via whereDoesntHave)
    - constants:
      - PROVIDER_CLAUDE = 'claude'
      - PROVIDER_OPENAI = 'openai'

  EXISTING MODEL UPDATES (no new file — add relationship to existing model):
  - app/Models/JobSearch/JobListing.php — add relationship:
    - matchScore(): hasOne(JobMatchScore::class)

SERVICES:
  - app/Services/AiJobMatchService.php
    - getConfiguredProvider(int $userId): ?string — checks ApiKey for Claude (preferred) then OpenAI;
      returns 'claude', 'openai', or null if neither configured
    - scoreJob(JobListing $job, User $user): JobMatchScore — scores a single job listing:
      1. Builds user context (skills from Skill model, preferred_tech + preferred_titles from JobSearchFilter,
         experiences from Profile bio)
      2. Builds job context (title, description, tech_stack, location, salary)
      3. Sends prompt to AI provider requesting JSON response with score, explanation,
         matched_skills, missing_skills, bonus_factors
      4. Parses AI response
      5. Upserts JobMatchScore record (updateOrCreate on user_id + job_listing_id)
      6. Returns the JobMatchScore
    - scoreUnscored(User $user): array — scores all visible job listings that don't have a score yet;
      returns summary array ['scored' => int, 'failed' => int, 'skipped' => int]
    - rescoreAll(User $user): array — deletes all existing scores for user, then scores all visible jobs;
      returns summary array
    - rescoreJob(JobListing $job, User $user): JobMatchScore — deletes existing score if any,
      then calls scoreJob()
    - buildUserContext(User $user): array — assembles user skills, tech preferences, preferred titles,
      experience summary into a structured array for the AI prompt
    - buildJobContext(JobListing $job): array — extracts title, description, tech_stack, location,
      salary info into a structured array for the AI prompt
    - buildPrompt(array $userContext, array $jobContext): string — constructs the AI prompt requesting
      a JSON response with score (0-100), explanation, matched_skills, missing_skills, bonus_factors
    - callClaude(string $prompt, ApiKey $apiKey): array — sends prompt to Claude API, returns parsed JSON
    - callOpenai(string $prompt, ApiKey $apiKey): array — sends prompt to OpenAI API, returns parsed JSON
    - parseAiResponse(array $response): array — validates and normalizes the AI response structure
    - getScoredFeed(User $user, array $filters, int $perPage): LengthAwarePaginator — returns paginated
      job listings joined/left-joined with match scores; supports filters: search, minScore, platform,
      locationType, sortBy (score, posted_at, company_name)
    - getScoreStats(User $user): array — returns array with: average_score, high_match_count (>=80),
      total_scored, total_unscored, last_scored_at

--- ADMIN FILES ---

LIVEWIRE COMPONENTS:
  - app/Livewire/Admin/JobSearch/AiMatchScoring/AiMatchScoringIndex.php
    - public properties: search, filterPlatform, filterLocationType, filterMinScore, sortBy
    - methods:
      - mount(): void — initializes default filter/sort values
      - scoreAllUnscored(): void — triggers bulk scoring of unscored jobs via service
      - rescoreAll(): void — triggers re-scoring of all jobs via service
      - rescoreJob(int $jobId): void — triggers re-scoring of a single job via service
      - getJobsProperty(): LengthAwarePaginator — computed property returning filtered paginated jobs with scores
      - getStatsProperty(): array — computed property returning score stat card data
      - getProviderProperty(): ?string — computed property returning configured AI provider name or null

VIEWS:
  - resources/views/livewire/admin/job-search/ai-match-scoring/index.blade.php
    - displays the AI match scoring page with stat cards, filters, job listing cards with scores

ROUTES (admin):
  - routes/admin/job-search/ai-match-scoring.php
    - GET /admin/job-search/ai-match-scoring → AiMatchScoringIndex → admin.job-search.ai-match-scoring.index
```

---

## 4. COMPONENT CONTRACTS

### Admin Components

```
Component: App\Livewire\Admin\JobSearch\AiMatchScoring\AiMatchScoringIndex
Namespace:  App\Livewire\Admin\JobSearch\AiMatchScoring
Layout: #[Layout('components.layouts.admin')]
Traits: WithPagination

Properties:
  - $search (string, default '') — #[Url] keyword search across job title, company, description
  - $filterPlatform (string, default '') — #[Url] filter by source_platform
  - $filterLocationType (string, default '') — #[Url] filter by location_type (remote/onsite/hybrid)
  - $filterMinScore (int|null, default null) — #[Url] minimum match score threshold (0-100)
  - $sortBy (string, default 'score') — #[Url] sort field: score, posted_at, company_name

Methods:
  - mount()
    Input: none
    Does: initializes properties with defaults; no special logic needed
    Output: none

  - scoreAllUnscored()
    Input: none
    Does: 1. Checks getProviderProperty() is not null — if null, flashes error "No AI provider configured. Add a Claude or OpenAI API key in Settings."
          2. Calls AiJobMatchService::scoreUnscored(auth()->user())
          3. Flashes success with summary (e.g., "Scored 18 jobs, 2 failed, 0 skipped")
          4. On failure, flashes error message
    Output: session flash (success or error)

  - rescoreAll()
    Input: none
    Does: 1. Checks getProviderProperty() is not null — if null, flashes error
          2. Calls AiJobMatchService::rescoreAll(auth()->user())
          3. Flashes success with summary (e.g., "Re-scored 42 jobs")
          4. On failure, flashes error message
    Output: session flash (success or error)

  - rescoreJob(int $jobId)
    Input: job listing ID
    Does: 1. Checks getProviderProperty() is not null — if null, flashes error
          2. Finds JobListing by ID (scoped to user)
          3. Calls AiJobMatchService::rescoreJob($job, auth()->user())
          4. Flashes success (e.g., "Job re-scored: 85% match")
          5. On failure, flashes error
    Output: session flash (success or error)

  - getJobsProperty() [Computed]
    Input: reads all filter and sort properties
    Does: calls AiJobMatchService::getScoredFeed() with current filters, 15 per page
    Output: LengthAwarePaginator of JobListing models with matchScore relationship eager-loaded

  - getStatsProperty() [Computed]
    Input: none
    Does: calls AiJobMatchService::getScoreStats(auth()->user())
    Output: array with keys: average_score, high_match_count, total_scored, total_unscored, last_scored_at

  - getProviderProperty() [Computed]
    Input: none
    Does: calls AiJobMatchService::getConfiguredProvider(auth()->user()->id)
    Output: string|null ('claude', 'openai', or null)

  - updatingSearch() / updatingFilter*()
    Input: none
    Does: resets pagination to page 1
    Output: none
```

---

## 5. VIEW BLUEPRINTS

### Admin View

```
View: resources/views/livewire/admin/job-search/ai-match-scoring/index.blade.php
Layout: components.layouts.admin
Side: ADMIN
Page title: "AI Match Scoring"

Design rules (from CLAUDE.md admin side):
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:

  1. Breadcrumb
     - Dashboard > Job Search > AI Match Scoring

  2. Page Header
     - Title: "AI Match Scoring" (text-2xl font-mono font-bold text-white uppercase tracking-wider)
     - Subtitle: "AI analyzes job listings against your skills and preferences."
     - Right side: Two buttons:
       - "Score Unscored" primary button with loading state (spinner while scoring)
       - "Re-Score All" secondary button (bg-dark-700 hover:bg-dark-600 text-gray-300) with confirmation dialog
     - Below buttons: "AI Provider: Claude" or "AI Provider: OpenAI" badge (bg-primary/10 text-primary-light text-xs)
     - If no provider configured: warning banner (bg-amber-500/10 border border-amber-500/20 rounded-xl p-4)
       with message "No AI provider configured" and link to Settings > API Keys

  3. Stat Cards Row (4-column grid)
     - Average Score: average match score across all scored jobs (icon: chart-bar, bg-primary/10)
       Display as percentage with progress ring or bold number
     - High Matches: count of jobs with score >= 80 (icon: fire, bg-emerald-500/10)
     - Scored Jobs: total scored count (icon: check-circle, bg-blue-500/10)
     - Unscored Jobs: total unscored count (icon: clock, bg-amber-500/10)

  4. Filter Bar (bg-dark-800 rounded-xl card)
     - Row:
       - Search input (keyword search across title, company, description)
       - Platform dropdown (All Platforms / JSearch / RemoteOK / Remotive / Adzuna / Rozee.pk / Mustakbil)
       - Location Type dropdown (All / Remote / Onsite / Hybrid)
       - Min Score input (number, 0-100, with "%" suffix label) or dropdown (All / 50%+ / 60%+ / 70%+ / 80%+ / 90%+)
       - Sort by dropdown (Best Match / Newest / Company Name)

  5. Job Listing Cards with Scores (main feed area)
     - Each job is a card (bg-dark-800 border border-dark-700 rounded-xl p-5):
       - Left section (flex-1):
         - Title: text-base font-medium text-white (clickable, opens job_url in new tab)
         - Company name: text-sm text-gray-400
         - Badges row:
           - Source platform badge (color-coded per platform, same as job-feed spec)
           - Location type badge (remote=emerald, onsite=amber, hybrid=blue)
         - Tech stack: row of small rounded badges (bg-dark-700 text-gray-300 text-xs)
         - Salary: text-sm text-emerald-400 if available
         - Posted date: relative time (text-xs text-gray-500)
       - Right section (score display):
         - Score circle/ring: large circular progress indicator showing score percentage
           - >= 80: text-emerald-400 ring-emerald-500
           - 60-79: text-amber-400 ring-amber-500
           - < 60: text-red-400 ring-red-500
           - Unscored: text-gray-500 with "—" or "N/A"
         - Below score: "Re-score" icon button (refresh icon, text-gray-500 hover:text-primary-light)
       - Expandable section (click to expand, Alpine.js x-show):
         - "Why this score" explanation text (text-sm text-gray-400)
         - Matched skills: row of green badges (bg-emerald-500/10 text-emerald-400 text-xs)
         - Missing skills: row of red badges (bg-red-500/10 text-red-400 text-xs)
         - Bonus factors: row of blue badges (bg-blue-500/10 text-blue-400 text-xs)

     - Cards sorted by score descending by default
     - Unscored jobs appear at the bottom with muted styling

  6. Pagination
     - Standard Livewire pagination below the feed
     - Shows "Showing X-Y of Z jobs"

  7. Empty State (when no jobs exist at all)
     - Icon: sparkles outline
     - Title: "No jobs to score"
     - Subtitle: "Fetch job listings from the Job Feed first, then come back to score them."
     - CTA button: "Go to Job Feed" (link to admin.job-search.feed.index)

  8. Empty State (when no jobs match filters)
     - Icon: funnel outline
     - Title: "No jobs match your filters"
     - Subtitle: "Try lowering the minimum score or adjusting other filters."
```

---

## 6. VALIDATION RULES

No traditional form validation — this feature does not have a create/edit form. Validation occurs on:

```
Filter inputs (sanitized in component):
  - search: nullable|string|max:200
  - filterPlatform: nullable|string|in:jsearch,remoteok,remotive,adzuna,rozee,mustakbil
  - filterLocationType: nullable|string|in:remote,onsite,hybrid
  - filterMinScore: nullable|integer|min:0|max:100
  - sortBy: required|string|in:score,posted_at,company_name

rescoreJob action:
  - jobId: required|integer|exists:job_listings,id (scoped to user)
```

---

## 7. EDGE CASES & BUSINESS RULES

### AI Provider Availability
- Before any scoring action, check if Claude or OpenAI API key exists and is connected via `api_keys` table
- Prefer Claude (PROVIDER_CLAUDE), fallback to OpenAI (PROVIDER_OPENAI) — same pattern as AiTaskPrioritizationService
- If neither provider is configured, disable scoring buttons and show a warning banner directing user to Settings > API Keys
- Store which provider and model were used in `ai_provider` and `ai_model` columns for auditability

### Scoring Logic
- The AI prompt includes: user's skills (from `skills` table), preferred tech and titles (from `job_search_filters`), profile bio/summary (from `profiles` table)
- The AI prompt includes: job title, full description, tech_stack, location, salary range
- AI is asked to return a JSON object with: score (0-100), explanation (string), matched_skills (array), missing_skills (array), bonus_factors (array)
- Score is clamped to 0-100 range after parsing
- If AI response cannot be parsed, log error and skip that job (count as "failed" in summary)

### Unique Constraint
- The unique constraint on (user_id, job_listing_id) ensures one score per job per user
- Re-scoring uses updateOrCreate to overwrite the existing score
- rescoreAll deletes all existing scores first, then scores fresh (avoids stale data)

### Score Thresholds for UI
- >= 80: "High Match" — emerald/green styling
- 60-79: "Moderate Match" — amber/yellow styling
- < 60: "Low Match" — red styling
- Unscored (no JobMatchScore record): gray/muted styling, shown at bottom of list

### Bulk Scoring
- scoreUnscored only scores jobs that don't have a JobMatchScore record yet (efficient for incremental use)
- rescoreAll wipes all scores and re-scores everything (useful after profile update)
- Both operations process jobs sequentially (one API call per job) to respect rate limits
- Each job scoring is wrapped in a try/catch — a single failure does not abort the batch
- Summary returns counts: scored (success), failed (API error or parse error), skipped (hidden or non-primary jobs)

### Job Visibility
- Only score visible jobs: is_hidden = false AND is_duplicate_primary = true (same scopeVisible from JobListing)
- Hidden jobs and non-primary duplicates are skipped during bulk scoring
- If a job is hidden after being scored, its score remains in the database but is not displayed

### Cascade on Delete
- Deleting a job_listing cascades to its job_match_scores record (FK on delete cascade)
- Deleting a user cascades to all job_match_scores (FK on delete cascade)

### Rate Limiting / Throttling
- Add a brief delay between API calls during bulk scoring (e.g., 500ms) to avoid hitting rate limits
- If the AI provider returns a rate limit error (429), pause for the retry-after duration or 5 seconds, then retry once
- After one retry failure, mark that job as "failed" and continue to the next

### Sort Order
- Default sort: score DESC (highest match first), with unscored jobs at the bottom (NULL scores sorted last)
- Secondary sort options: posted_at DESC (newest), company_name ASC (alphabetical)
- When sorting by non-score fields, scored and unscored jobs are mixed normally

### Sidebar Placement
- "AI Match Scoring" link is added under the "Job Search" parent group in the sidebar
- Positioned after "Job Feed" in the sidebar menu

### Relationship to Job Feed
- This feature depends on job_listings being populated by the Job Feed feature
- The AI Match Scoring page links to Job Feed for fetching new jobs
- Match scores can optionally be displayed on the Job Feed page in the future (not in this spec's scope)

---

## 8. IMPLEMENTATION ORDER

```
1. database/migrations/YYYY_MM_DD_000001_create_job_match_scores_table.php
2. app/Models/JobSearch/JobMatchScore.php
3. Update app/Models/JobSearch/JobListing.php — add matchScore() hasOne relationship
4. app/Services/AiJobMatchService.php
5. routes/admin/job-search/ai-match-scoring.php
6. app/Livewire/Admin/JobSearch/AiMatchScoring/AiMatchScoringIndex.php
7. resources/views/livewire/admin/job-search/ai-match-scoring/index.blade.php
8. Update sidebar (components/layouts/admin.blade.php) — add "AI Match Scoring" link under Job Search group
```

> Dependencies: This feature depends on the Job Feed feature being complete (job_listings table, JobListing model, JobFeedService). It also depends on the Settings module (ApiKey model + ApiKeyService, JobSearchFilter model). The Profile and Skill models from the Portfolio module are read to build user context for AI scoring.
