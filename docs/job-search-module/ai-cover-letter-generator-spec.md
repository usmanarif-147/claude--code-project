# AI Cover Letter Generator — Spec

Side: **ADMIN**

---

## 1. MODULE OVERVIEW

The AI Cover Letter Generator allows one-click generation of personalized cover letters for any job listing in the Job Feed. It sends the job description along with the user's resume data (profile bio, skills, work experience) to an AI provider (Claude or OpenAI) and returns a tailored cover letter. The user can edit the generated letter, copy it to the clipboard, download it as PDF, and save it for future reference.

### Features
- One-click generate a cover letter from any job listing in the feed
- AI uses job description + user's profile, skills, and experiences to personalize the letter
- Choose AI provider (Claude or OpenAI) based on available API keys
- Edit the generated cover letter in a rich textarea before saving
- Copy the final letter to clipboard
- Download the letter as a PDF
- Save generated cover letters to the database
- View all previously generated cover letters
- Delete saved cover letters
- Regenerate a cover letter for the same job listing

### Admin Features
- Generate cover letter from the cover letter index page by selecting a job listing
- Generate cover letter directly from the Job Feed (link/button navigates to generator with job pre-selected)
- View list of all saved cover letters with job title, company, date generated
- Open and edit any saved cover letter
- Delete saved cover letters
- Copy to clipboard or download as PDF from both the generator and the saved letter view

---

## 2. DATABASE SCHEMA

```
Table: cover_letters
Columns:
  - id (bigint, primary key, auto increment)
  - user_id (bigint, unsigned, required, FK -> users.id)
  - job_listing_id (bigint, unsigned, nullable, FK -> job_listings.id) — nullable in case the job listing is later deleted
  - job_title (string 500, required) — snapshot of job title at generation time
  - company_name (string 255, nullable) — snapshot of company name at generation time
  - job_description_snippet (text, nullable) — snippet of job description used for context (first 2000 chars)
  - content (longText, required) — the generated/edited cover letter body
  - ai_provider (string 30, required) — claude or openai
  - ai_model (string 100, nullable) — specific model used (e.g., claude-sonnet-4-20250514, gpt-4o)
  - prompt_tokens (unsignedInteger, nullable) — tokens used in the prompt
  - completion_tokens (unsignedInteger, nullable) — tokens in the response
  - is_edited (boolean, default false) — whether user manually edited the generated content
  - created_at, updated_at (timestamps)

Indexes:
  - index on user_id
  - index on job_listing_id
  - index on (user_id, job_listing_id)
  - index on created_at

Foreign keys:
  - user_id references users(id) on delete cascade
  - job_listing_id references job_listings(id) on delete set null
```

> Note: Tables `api_keys` and `job_listings` already exist. Models `ApiKey`, `JobListing`, `Profile`, `Skill`, and `Experience` already exist and are REFERENCED, not recreated.

---

## 3. FILE MAP

```
MIGRATIONS:
  - database/migrations/YYYY_MM_DD_000001_create_cover_letters_table.php

MODELS:
  - app/Models/JobSearch/CoverLetter.php
    - fillable: user_id, job_listing_id, job_title, company_name, job_description_snippet,
                content, ai_provider, ai_model, prompt_tokens, completion_tokens, is_edited
    - relationships:
      - user(): belongsTo(User::class)
      - jobListing(): belongsTo(JobListing::class)
    - casts:
      - is_edited -> boolean
      - prompt_tokens -> integer
      - completion_tokens -> integer
    - scopes:
      - scopeForUser(Builder $query, int $userId): filters by user_id
      - scopeForJob(Builder $query, int $jobListingId): filters by job_listing_id
    - constants:
      - PROVIDER_CLAUDE, PROVIDER_OPENAI

SERVICES:
  - app/Services/AiCoverLetterService.php
    - generate(User $user, JobListing $job, string $provider): CoverLetter — builds prompt from
      job description + user's profile/skills/experiences, calls AI API, saves and returns CoverLetter
    - buildPrompt(User $user, JobListing $job): string — assembles the system + user prompt with
      resume data (Profile bio/tagline, Skills with proficiency, Experiences with responsibilities)
      and job listing data (title, company, description, tech_stack, location)
    - callClaude(string $prompt, ApiKey $apiKey): array — sends prompt to Claude API, returns
      ['content' => string, 'model' => string, 'prompt_tokens' => int, 'completion_tokens' => int]
    - callOpenAI(string $prompt, ApiKey $apiKey): array — sends prompt to OpenAI API, returns
      same structure as callClaude
    - getAvailableProviders(User $user): array — checks which AI providers have connected API keys;
      returns array of provider names (e.g., ['claude', 'openai'] or ['claude'])
    - update(CoverLetter $coverLetter, string $content): CoverLetter — updates content, sets
      is_edited = true
    - delete(CoverLetter $coverLetter): void — deletes the cover letter
    - getLettersForUser(User $user, ?string $search, int $perPage): LengthAwarePaginator — returns
      paginated cover letters for the user, optionally filtered by search on job_title/company_name
    - generatePdf(CoverLetter $coverLetter): string — renders cover letter as PDF using a Blade
      template, returns the PDF file contents (uses barryvdh/laravel-dompdf or similar)

--- ADMIN FILES ---

LIVEWIRE COMPONENTS:
  - app/Livewire/Admin/JobSearch/AiCoverLetter/AiCoverLetterIndex.php
    - public properties: search
    - methods:
      - mount(): void — initializes defaults
      - deleteLetter(int $id): void — deletes a saved cover letter
      - getLettersProperty(): LengthAwarePaginator — computed, returns paginated cover letters
  - app/Livewire/Admin/JobSearch/AiCoverLetter/AiCoverLetterForm.php
    - public properties: coverLetterId, jobListingId, content, aiProvider, isGenerating, selectedJobTitle
    - methods:
      - mount(?int $coverLetterId, ?int $jobListingId): void — loads existing letter or pre-selects job
      - generate(): void — calls service to generate cover letter from selected job
      - save(): void — saves/updates edited content
      - downloadPdf(): StreamedResponse — generates and streams PDF download
      - getAvailableProvidersProperty(): array — computed, returns available AI providers
      - getJobListingsProperty(): Collection — computed, returns user's job listings for dropdown

VIEWS:
  - resources/views/livewire/admin/job-search/ai-cover-letter/index.blade.php
  - resources/views/livewire/admin/job-search/ai-cover-letter/form.blade.php
  - resources/views/cover-letter/templates/default.blade.php — PDF template for cover letter

ROUTES (admin):
  - routes/admin/job-search/ai-cover-letter.php
    - GET /admin/job-search/cover-letters → AiCoverLetterIndex → admin.job-search.cover-letters.index
    - GET /admin/job-search/cover-letters/create → AiCoverLetterForm → admin.job-search.cover-letters.create
    - GET /admin/job-search/cover-letters/create?job={jobListingId} → AiCoverLetterForm (pre-selected job)
    - GET /admin/job-search/cover-letters/{coverLetter}/edit → AiCoverLetterForm → admin.job-search.cover-letters.edit
```

---

## 4. COMPONENT CONTRACTS

### Admin Components

```
Component: App\Livewire\Admin\JobSearch\AiCoverLetter\AiCoverLetterIndex
Namespace:  App\Livewire\Admin\JobSearch\AiCoverLetter
Layout: #[Layout('components.layouts.admin')]
Traits: WithPagination

Properties:
  - $search (string, default '') — #[Url] keyword search across job_title, company_name

Methods:
  - mount()
    Input: none
    Does: initializes properties
    Output: none

  - deleteLetter(int $id)
    Input: cover letter ID
    Does: 1. Finds CoverLetter by ID (scoped to user)
          2. Calls AiCoverLetterService::delete($coverLetter)
          3. Flashes success message
    Output: session flash (success)

  - getLettersProperty() [Computed]
    Input: reads $search
    Does: calls AiCoverLetterService::getLettersForUser(auth()->user(), $search, 10)
    Output: LengthAwarePaginator of CoverLetter models

  - updatingSearch()
    Input: none
    Does: resets pagination to page 1
    Output: none
```

```
Component: App\Livewire\Admin\JobSearch\AiCoverLetter\AiCoverLetterForm
Namespace:  App\Livewire\Admin\JobSearch\AiCoverLetter
Layout: #[Layout('components.layouts.admin')]

Properties:
  - $coverLetterId (int|null, default null) — set when editing an existing letter
  - $jobListingId (int|null, default null) — selected job listing to generate for
  - $content (string, default '') — the cover letter body (editable textarea)
  - $aiProvider (string, default 'claude') — selected AI provider for generation
  - $isGenerating (bool, default false) — true while AI generation is in progress
  - $selectedJobTitle (string, default '') — display-only: title of selected job for UX clarity

Methods:
  - mount(?int $coverLetterId = null, ?int $jobListingId = null)
    Input: optional cover letter ID for edit mode, optional job listing ID for pre-selection
    Does: 1. If coverLetterId provided, loads existing CoverLetter (scoped to user), populates content,
             jobListingId, selectedJobTitle, aiProvider
          2. If jobListingId provided (from query string), loads JobListing, sets selectedJobTitle
          3. Sets aiProvider to first available provider if only one exists
    Output: none

  - generate()
    Input: none (reads $jobListingId, $aiProvider)
    Does: 1. Validates jobListingId is required and exists
          2. Validates aiProvider is required and in available providers
          3. Sets isGenerating = true
          4. Calls AiCoverLetterService::generate(auth()->user(), $job, $aiProvider)
          5. Sets content from returned CoverLetter, sets coverLetterId
          6. Sets isGenerating = false
          7. Flashes success message
          8. On failure: sets isGenerating = false, flashes error message (e.g., "AI generation failed.
             Check your API key configuration.")
    Output: session flash (success or error)

  - save()
    Input: none (reads $content)
    Does: 1. Validates content is required
          2. If coverLetterId exists: calls AiCoverLetterService::update($coverLetter, $content)
          3. If new (no coverLetterId but content exists from generation): already saved during generate()
             — this updates the edited content
          4. Flashes success message
    Output: session flash (success)

  - downloadPdf()
    Input: none (reads $coverLetterId)
    Does: 1. Finds CoverLetter by ID (scoped to user)
          2. Calls AiCoverLetterService::generatePdf($coverLetter)
          3. Returns StreamedResponse with PDF content
    Output: file download

  - getAvailableProvidersProperty() [Computed]
    Input: none
    Does: calls AiCoverLetterService::getAvailableProviders(auth()->user())
    Output: array of provider strings, e.g., ['claude', 'openai']

  - getJobListingsProperty() [Computed]
    Input: none
    Does: fetches JobListing::forUser(auth()->id())->visible()->latest('posted_at')->limit(100)->get(['id', 'title', 'company_name'])
    Output: Collection of JobListing models (id, title, company_name for dropdown)

Validation Rules:
  - jobListingId: required|integer|exists:job_listings,id
  - aiProvider: required|string|in:claude,openai
  - content: required|string|min:50
```

---

## 5. VIEW BLUEPRINTS

### Admin Views

```
View: resources/views/livewire/admin/job-search/ai-cover-letter/index.blade.php
Layout: components.layouts.admin
Side: ADMIN
Page title: "Cover Letters"

Design rules (from CLAUDE.md admin side):
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:

  1. Breadcrumb
     - Dashboard > Job Search > Cover Letters

  2. Page Header
     - Title: "Cover Letters" (text-2xl font-mono font-bold text-white uppercase tracking-wider)
     - Subtitle: "AI-generated cover letters tailored to your job applications."
     - Right side: "Generate New" primary button linking to create route

  3. Search Bar
     - Single search input (keyword filter on job_title, company_name)
     - Placed above the table/list area

  4. Cover Letters Table (bg-dark-800 rounded-xl card)
     - Columns:
       - Job Title (text-white, clickable link to edit)
       - Company (text-gray-400)
       - AI Provider (badge: Claude = purple, OpenAI = emerald)
       - Edited (badge: yes/no)
       - Date Generated (relative time, text-gray-500)
       - Actions (three-dot dropdown): Edit, Download PDF, Delete
     - Row click navigates to edit form

  5. Pagination
     - Standard Livewire pagination below the table
     - Shows "Showing X-Y of Z letters"

  6. Empty State (when no cover letters exist)
     - Icon: document-text outline
     - Title: "No cover letters yet"
     - Subtitle: "Generate your first AI-powered cover letter from a job listing."
     - CTA button: "Generate Cover Letter" (link to create route)

  7. Delete Confirmation Modal
     - Triggered from three-dot dropdown
     - "Are you sure you want to delete this cover letter?"
     - Cancel + Delete buttons
```

```
View: resources/views/livewire/admin/job-search/ai-cover-letter/form.blade.php
Layout: components.layouts.admin
Side: ADMIN
Page title: "Generate Cover Letter" (create) / "Edit Cover Letter" (edit)

Design rules (from CLAUDE.md admin side):
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:

  1. Breadcrumb
     - Dashboard > Job Search > Cover Letters > Generate (or Edit)

  2. Page Header
     - Title: "Generate Cover Letter" or "Edit Cover Letter" (dynamic)
     - Subtitle: "Create a personalized cover letter using AI." or "Review and edit your cover letter."
     - Right side: "Back" button linking to cover letters index

  3. Full-Width Form Layout (grid: 2/3 main + 1/3 sidebar)

     Main Content (xl:col-span-2):

       3a. Job Selection Card (only shown when creating, hidden in edit mode)
           - Card heading: "Select Job Listing"
           - Searchable select dropdown of job listings (title + company)
           - Shows selected job's details when chosen: title, company, location, salary (read-only summary)
           - If jobListingId was pre-selected via query param, this is pre-filled and shows as read-only

       3b. Cover Letter Content Card
           - Card heading: "Cover Letter"
           - Textarea (full-width, min-height ~400px) with content
             - bg-dark-700, border-dark-600, text-white, text-sm, leading-relaxed
             - Monospace font NOT used here — use regular Inter for readability
           - Character count below textarea (text-xs text-gray-500)
           - If no content yet (before generation): placeholder text "Click 'Generate' to create your cover letter..."
           - If isGenerating: show loading skeleton/spinner overlay on the textarea area

     Sidebar (xl:col-span-1):

       3c. AI Provider Card
           - Card heading: "AI Provider"
           - Radio buttons or styled toggle for available providers (Claude / OpenAI)
           - If only one provider available, show it selected with a note: "Only provider with a connected API key"
           - If no providers available: warning card with message "No AI API keys configured.
             Add a Claude or OpenAI key in Settings > API Keys." with link to API keys settings

       3d. Actions Card
           - "Generate" button (primary, full-width) — shown when creating or regenerating
             - Disabled while isGenerating (shows spinner + "Generating...")
             - Disabled if no jobListingId selected
           - "Save Changes" button (primary, full-width) — shown when content has been edited
           - "Copy to Clipboard" button (bg-dark-700 hover:bg-dark-600, full-width)
             - Uses Alpine.js to copy content to clipboard
             - Briefly shows "Copied!" feedback
           - "Download PDF" button (bg-dark-700 hover:bg-dark-600, full-width)
             - Disabled if no saved content
           - Divider
           - "Regenerate" button (text-sm, text-gray-400 hover:text-white) — shown only when editing
             existing letter, overwrites current content after confirmation

       3e. Job Summary Card (shown when a job is selected or in edit mode)
           - Card heading: "Job Details"
           - Job title (text-white, font-medium)
           - Company name (text-gray-400)
           - Location (text-gray-500) if available
           - Tech stack badges (bg-dark-700 text-gray-300 text-xs rounded) if available
           - "View Original Listing" link (opens job_url in new tab, text-primary-light)
```

```
View: resources/views/cover-letter/templates/default.blade.php
Side: ADMIN (PDF template only — not a page)

Layout: standalone HTML (for PDF rendering via DomPDF)
  - White background, black text, professional styling
  - User's name and contact info at the top (from Profile model)
  - Date
  - Company name and job title
  - Cover letter body content (rendered from $coverLetter->content)
  - Clean, minimal styling suitable for professional documents
```

---

## 6. VALIDATION RULES

```
Form: AiCoverLetterForm (generate action)
  - jobListingId: required|integer|exists:job_listings,id
  - aiProvider: required|string|in:claude,openai

Form: AiCoverLetterForm (save action)
  - content: required|string|min:50

Form: AiCoverLetterIndex (delete action)
  - id: required|integer|exists:cover_letters,id (scoped to user)
```

---

## 7. EDGE CASES & BUSINESS RULES

### API Key Availability
- Before generating, check that the selected AI provider has a connected API key via `ApiKey::forUser()->forProvider()->connected()->first()`
- If no AI keys are configured at all, the form sidebar shows a warning card linking to Settings > API Keys
- If a key exists but test_status is 'failed', still allow generation but show a warning: "This API key's last test failed. Generation may not work."

### AI Provider Selection
- Default to Claude if both providers are available
- If only one provider has a connected key, auto-select it and disable the toggle
- If neither provider has a connected key, disable the Generate button entirely

### Job Listing Deletion
- `job_listing_id` FK uses `on delete set null` — if a job listing is deleted, the cover letter remains but loses its job reference
- The `job_title` and `company_name` fields are snapshots taken at generation time, so the cover letter always shows which job it was for even if the listing is deleted

### Content Snapshots
- `job_title`, `company_name`, and `job_description_snippet` are copied from the JobListing at generation time
- `job_description_snippet` stores the first 2000 characters of the job description for audit/context purposes
- These snapshots prevent data loss if the original job listing is hidden or deleted

### Regeneration
- Clicking "Regenerate" on an existing cover letter overwrites the current content after a confirmation dialog
- The is_edited flag resets to false after regeneration
- Token counts update to reflect the new generation

### Edited Flag
- `is_edited` is set to true whenever the user saves manually edited content (not AI-generated)
- Regeneration resets it to false
- This flag helps the user identify which letters were customized vs. raw AI output

### Copy to Clipboard
- Uses Alpine.js `navigator.clipboard.writeText()` with fallback for older browsers
- Shows a brief "Copied!" tooltip or button text change for 2 seconds

### PDF Generation
- Uses the `default.blade.php` template under `resources/views/cover-letter/templates/`
- PDF includes user's name and contact info (from Profile), date, job title, company name, and the letter body
- Uses barryvdh/laravel-dompdf (check if already installed; if not, it must be added as a dependency)

### AI Prompt Construction
- The prompt includes:
  1. System instruction: "You are a professional cover letter writer..."
  2. User's profile: name (from User), tagline, bio, location (from Profile)
  3. User's skills: all active skills with proficiency levels (from Skill model)
  4. User's experience: all active work experiences with responsibilities (from Experience model)
  5. Job details: title, company, description, tech_stack, location, salary info (from JobListing)
  6. Output instruction: "Write a professional, personalized cover letter. Keep it concise (3-4 paragraphs). Focus on matching the candidate's skills and experience to the job requirements."
- The prompt does NOT include sensitive data (API keys, personal IDs, etc.)

### Token Tracking
- Store prompt_tokens and completion_tokens from the AI response for usage monitoring
- Both fields are nullable (some API errors may not return token counts)

### Rate Limiting / Cost Awareness
- No hard rate limit, but the Generate button shows a loading state to prevent double-clicks
- Livewire's built-in debouncing on wire:click handles accidental double submissions

### Sort Order
- Cover letters index is sorted by created_at DESC (newest first)

### Cascade on Delete
- User deletion cascades to all cover_letters
- Job listing deletion sets job_listing_id to null (cover letter is preserved)

### Sidebar Navigation
- "Cover Letters" link is added under the Job Search parent group in the sidebar
- Position: after Job Feed

---

## 8. IMPLEMENTATION ORDER

```
1. database/migrations/YYYY_MM_DD_000001_create_cover_letters_table.php
2. app/Models/JobSearch/CoverLetter.php
3. app/Services/AiCoverLetterService.php
4. routes/admin/job-search/ai-cover-letter.php
5. app/Livewire/Admin/JobSearch/AiCoverLetter/AiCoverLetterIndex.php
6. app/Livewire/Admin/JobSearch/AiCoverLetter/AiCoverLetterForm.php
7. resources/views/livewire/admin/job-search/ai-cover-letter/index.blade.php
8. resources/views/livewire/admin/job-search/ai-cover-letter/form.blade.php
9. resources/views/cover-letter/templates/default.blade.php
```

> Dependencies: This feature depends on the Job Feed feature being complete (JobListing model + job_listings table). It also depends on the Settings module (ApiKey model + ApiKeyService for AI provider keys). Profile, Skill, and Experience models are used read-only for prompt construction.
