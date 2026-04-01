# Morning Email Digest — Spec

Side: ADMIN

---

## 1. MODULE OVERVIEW

The Morning Email Digest feature fetches emails from Gmail via API, stores them locally, and uses AI to generate a concise morning summary. Instead of scrolling through dozens of emails, the admin sees a categorized digest with one-line AI summaries, sender info, and direct links to open each email in Gmail.

This feature creates the foundational `emails` table that other email module features will depend on.

### Features
- Fetch and store emails from Gmail (via Google Gmail API)
- Display paginated email inbox with search and filters
- AI-generated morning digest summary grouped by category (job responses, freelance inquiries, personal, newsletters)
- One-line AI summary per email
- Click-through to open full email in Gmail
- Total unread count display
- Manual "Generate Digest" trigger and automatic daily digest
- Digest history — view past digests

### Admin Features
- View fetched emails in a searchable, filterable list
- Trigger a Gmail sync manually
- Generate an AI digest for any date range
- View current and past digest summaries
- See unread count and email category breakdown via stat cards

---

## 2. DATABASE SCHEMA

```
Table: emails
Columns:
  - id (bigint, primary key, auto increment)
  - gmail_id (string, required, unique) — Gmail message ID for deduplication
  - thread_id (string, nullable) — Gmail thread ID for conversation grouping
  - from_email (string, required) — sender email address
  - from_name (string, nullable) — sender display name
  - to_email (string, nullable) — recipient email address
  - subject (string, nullable) — email subject line
  - snippet (text, nullable) — Gmail snippet / body preview (first ~200 chars)
  - body_preview (text, nullable) — cleaned plain-text preview (longer than snippet)
  - received_at (datetime, required) — when the email was received
  - is_read (boolean, required, default: false) — read/unread status from Gmail
  - is_starred (boolean, required, default: false) — starred status from Gmail
  - is_important (boolean, required, default: false) — Gmail importance marker
  - labels (json, nullable) — array of Gmail label IDs (INBOX, UNREAD, CATEGORY_PROMOTIONS, etc.)
  - category (string, nullable) — AI-assigned category: job_response, freelance, personal, newsletter, other
  - ai_summary (text, nullable) — one-line AI-generated summary of the email
  - gmail_link (string, nullable) — direct URL to open in Gmail
  - raw_payload (json, nullable) — full Gmail API response for future use
  - created_at, updated_at (timestamps)

Indexes:
  - unique index on gmail_id
  - index on received_at (for date range queries)
  - index on is_read (for unread filtering)
  - index on category (for grouping)
  - index on from_email (for sender search)

Foreign keys: none (standalone foundational table)
```

```
Table: email_digests
Columns:
  - id (bigint, primary key, auto increment)
  - digest_date (date, required) — the date this digest covers
  - period_start (datetime, required) — start of the digest time window
  - period_end (datetime, required) — end of the digest time window
  - total_emails (integer, required, default: 0) — count of emails in this digest
  - unread_count (integer, required, default: 0) — count of unread emails
  - summary (text, nullable) — AI-generated overall digest summary
  - categories_breakdown (json, nullable) — count per category: {"job_response": 3, "freelance": 1, ...}
  - highlights (json, nullable) — array of important email highlights: [{email_id, from_name, subject, ai_summary}, ...]
  - ai_model_used (string, nullable) — which AI model generated the digest (e.g., "gpt-4o", "claude-sonnet")
  - generated_at (datetime, nullable) — when the digest was generated
  - status (string, required, default: 'pending') — pending, generating, completed, failed
  - error_message (text, nullable) — error details if generation failed
  - created_at, updated_at (timestamps)

Indexes:
  - unique index on digest_date
  - index on status
  - index on generated_at

Foreign keys: none
```

```
Table: email_sync_logs
Columns:
  - id (bigint, primary key, auto increment)
  - synced_at (datetime, required) — when the sync ran
  - emails_fetched (integer, required, default: 0) — number of new emails fetched
  - emails_skipped (integer, required, default: 0) — number of duplicates skipped
  - status (string, required, default: 'success') — success, partial, failed
  - error_message (text, nullable) — error details if sync failed
  - duration_ms (integer, nullable) — how long the sync took in milliseconds
  - created_at, updated_at (timestamps)

Indexes:
  - index on synced_at
  - index on status
```

---

## 3. FILE MAP

```
MIGRATIONS:
  - database/migrations/2026_04_02_200001_create_emails_table.php
  - database/migrations/2026_04_02_200002_create_email_digests_table.php
  - database/migrations/2026_04_02_200003_create_email_sync_logs_table.php

MODELS:
  - app/Models/Email/Email.php
    - fillable: gmail_id, thread_id, from_email, from_name, to_email, subject, snippet,
      body_preview, received_at, is_read, is_starred, is_important, labels, category,
      ai_summary, gmail_link, raw_payload
    - casts: received_at → datetime, is_read → boolean, is_starred → boolean,
      is_important → boolean, labels → array, raw_payload → array
    - scopes: unread(), important(), starred(), category($cat), receivedBetween($start, $end)
    - no relationships (standalone)

  - app/Models/Email/EmailDigest.php
    - fillable: digest_date, period_start, period_end, total_emails, unread_count,
      summary, categories_breakdown, highlights, ai_model_used, generated_at, status, error_message
    - casts: digest_date → date, period_start → datetime, period_end → datetime,
      categories_breakdown → array, highlights → array, generated_at → datetime
    - scopes: completed(), pending(), forDate($date)

  - app/Models/Email/EmailSyncLog.php
    - fillable: synced_at, emails_fetched, emails_skipped, status, error_message, duration_ms
    - casts: synced_at → datetime

SERVICES:
  - app/Services/GmailSyncService.php
    - syncEmails(): array — fetches new emails from Gmail API, stores them, returns sync stats
    - fetchMessageList(string $query, int $maxResults): array — gets message IDs from Gmail
    - fetchMessageDetail(string $messageId): array — gets full message payload from Gmail
    - parseEmailPayload(array $payload): array — extracts fields from Gmail API response
    - buildGmailLink(string $messageId): string — constructs direct Gmail URL
    - getLastSyncTime(): ?Carbon — returns the most recent sync timestamp

  - app/Services/EmailDigestService.php
    - generateDigest(?string $date): EmailDigest — generates AI digest for given date (default: today)
    - getEmailsForDigest(Carbon $start, Carbon $end): Collection — fetches emails in time range
    - categorizeEmails(Collection $emails): Collection — AI-categorizes uncategorized emails
    - summarizeEmail(Email $email): string — generates one-line AI summary for a single email
    - buildDigestSummary(Collection $emails, array $categoriesBreakdown): string — generates overall digest text
    - getDigestHistory(int $perPage): LengthAwarePaginator — paginated past digests
    - getLatestDigest(): ?EmailDigest — returns the most recent completed digest

  - app/Services/EmailInboxService.php
    - getEmails(array $filters, int $perPage): LengthAwarePaginator — paginated filtered email list
    - getUnreadCount(): int — total unread emails
    - getCategoryBreakdown(): array — count per category
    - getRecentStats(): array — stats for stat cards (total, unread, important, today count)
    - markAsRead(int $emailId): void — marks email as read locally
    - searchEmails(string $query, int $perPage): LengthAwarePaginator — full-text search on subject/from/snippet

--- ADMIN FILES ---

LIVEWIRE COMPONENTS:
  - app/Livewire/Admin/Email/Inbox/EmailInboxIndex.php
    - public properties: $search, $filterCategory, $filterRead
    - methods: syncNow(), markRead($id), render()
    - view: resources/views/livewire/admin/email/inbox/index.blade.php
      — displays email list table with search, category filter, read/unread filter,
        sync button, stat cards (total, unread, important, today), pagination

  - app/Livewire/Admin/Email/Digest/EmailDigestIndex.php
    - public properties: $selectedDate
    - methods: generateDigest(), viewDigest($id), render()
    - view: resources/views/livewire/admin/email/digest/index.blade.php
      — displays current digest summary, categorized email highlights,
        digest history list, generate button, stat cards

ROUTES (admin):
  - routes/admin/email/inbox.php
    - GET /admin/email/inbox → EmailInboxIndex → admin.email.inbox.index

  - routes/admin/email/digest.php
    - GET /admin/email/digest → EmailDigestIndex → admin.email.digest.index
```

---

## 4. COMPONENT CONTRACTS

### Admin Components

```
Component: App\Livewire\Admin\Email\Inbox\EmailInboxIndex
Namespace: App\Livewire\Admin\Email\Inbox

Layout: #[Layout('components.layouts.admin')]

Properties:
  - $search (string, default: '') — #[Url] search query for subject/sender
  - $filterCategory (string, default: '') — #[Url] filter by category (job_response, freelance, personal, newsletter, other)
  - $filterRead (string, default: '') — #[Url] filter by read status (read, unread, or empty for all)

Methods:
  - syncNow()
    Input: none
    Does:
      1. Calls GmailSyncService::syncEmails()
      2. Flashes success with count of fetched emails, or error if sync fails
    Output: flash message

  - markRead($emailId)
    Input: email ID
    Does:
      1. Calls EmailInboxService::markAsRead($emailId)
    Output: refreshes list

  - render()
    Input: none
    Does:
      1. Calls EmailInboxService::getEmails(filters, perPage: 20)
      2. Calls EmailInboxService::getRecentStats()
    Output: returns view with $emails (paginated), $stats (array)

Uses: WithPagination
```

```
Component: App\Livewire\Admin\Email\Digest\EmailDigestIndex
Namespace: App\Livewire\Admin\Email\Digest

Layout: #[Layout('components.layouts.admin')]

Properties:
  - $selectedDate (string, default: today) — date for which to view/generate digest

Methods:
  - generateDigest()
    Input: none
    Does:
      1. Calls EmailDigestService::generateDigest($this->selectedDate)
      2. Flashes success or error based on result
    Output: flash message, refreshes view

  - viewDigest($digestId)
    Input: digest ID
    Does:
      1. Loads the digest and sets $selectedDate to its digest_date
    Output: refreshes view with selected digest

  - render()
    Input: none
    Does:
      1. Calls EmailDigestService::getLatestDigest() or loads digest for $selectedDate
      2. Calls EmailDigestService::getDigestHistory(perPage: 10)
      3. Calls EmailInboxService::getUnreadCount()
    Output: returns view with $currentDigest, $digestHistory (paginated), $unreadCount

Uses: WithPagination
```

---

## 5. VIEW BLUEPRINTS

### Email Inbox — Index

```
View: resources/views/livewire/admin/email/inbox/index.blade.php
Layout: components.layouts.admin
Side: ADMIN
Page title: "Email Inbox"

Design rules:
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:
  - Breadcrumb: Dashboard > Email > Inbox
  - Page header: "Email Inbox" title + "Sync Now" primary button (with loading spinner)
  - Stat cards row (4 columns):
    1. Total Emails (bg-primary/10, text-primary-light icon)
    2. Unread (bg-amber-500/10, text-amber-400 icon)
    3. Important (bg-red-500/10, text-red-400 icon)
    4. Today (bg-emerald-500/10, text-emerald-400 icon)
  - Filter bar: search input + category select + read/unread select
  - Email table:
    Columns: Status (dot: read/unread), Sender (from_name + from_email), Subject + snippet, Category (badge), Received (relative time), Actions (open in Gmail link)
    Row click: marks as read
  - Empty state: "No emails found. Click Sync Now to fetch from Gmail."
  - Pagination footer with count
```

### Email Digest — Index

```
View: resources/views/livewire/admin/email/digest/index.blade.php
Layout: components.layouts.admin
Side: ADMIN
Page title: "Morning Digest"

Design rules:
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:
  - Breadcrumb: Dashboard > Email > Morning Digest
  - Page header: "Morning Digest" title + date picker + "Generate Digest" primary button (with loading spinner)
  - Full-width layout (2/3 + 1/3 grid):

  Left column (2/3):
    - Current digest card:
      - Digest date and time range as subtitle
      - Status badge (pending/generating/completed/failed)
      - AI-generated summary text (text-gray-300, body text)
      - Category sections — each category as a sub-heading (font-mono uppercase):
        - "Job Responses" / "Freelance Inquiries" / "Personal" / "Newsletters" / "Other"
        - Under each: list of email highlights:
          - From name (text-white, font-medium) + subject (text-gray-300)
          - One-line AI summary (text-gray-400, text-sm)
          - "Open in Gmail" link (text-primary-light, hover:text-white)
      - Empty state if no digest: "No digest generated yet for this date."

  Right column (1/3):
    - Stats card:
      - Total emails in digest
      - Unread count
      - Categories breakdown (mini bar or list with counts)
    - Digest history card:
      - List of past digests (date, status badge, email count)
      - Click to load that digest
      - Paginated (10 per page)
    - Last sync info card:
      - When last Gmail sync happened
      - Emails fetched in last sync
      - "Sync Now" secondary button
```

---

## 6. VALIDATION RULES

```
Form: Sync Emails (no form — just a button click, no validation needed)

Form: Generate Digest
  - selectedDate: required|date|before_or_equal:today

Form: Filter Emails (URL query params, validated in component)
  - search: nullable|string|max:255
  - filterCategory: nullable|string|in:job_response,freelance,personal,newsletter,other
  - filterRead: nullable|string|in:read,unread
```

---

## 7. EDGE CASES & BUSINESS RULES

- **Gmail API auth**: Requires a valid Gmail OAuth token stored via the Settings > API Keys module. If no valid token exists, show an error card directing the user to Settings > API Keys to configure Gmail.
- **Deduplication**: Emails are deduplicated by `gmail_id` (unique constraint). On sync, existing emails are skipped, not updated.
- **Sync frequency**: Manual sync only for MVP. The sync button should be rate-limited in the UI (disable for 30 seconds after click) to prevent API quota issues.
- **AI categorization**: Emails without a category get categorized during digest generation. If AI service is unavailable, category stays null and is shown as "Uncategorized."
- **AI summary**: Generated lazily during digest generation, not on sync. Stored on the email record so it does not need to be regenerated.
- **Digest uniqueness**: Only one digest per date (unique constraint on `digest_date`). Regenerating overwrites the existing digest.
- **Digest status flow**: pending -> generating -> completed OR failed. If failed, `error_message` is populated.
- **Empty inbox**: If no emails exist for the digest period, the digest is created with `total_emails: 0` and a summary like "No emails received during this period."
- **Gmail link construction**: Format is `https://mail.google.com/mail/u/0/#inbox/{gmail_id}`.
- **Labels handling**: Stored as JSON array from Gmail. Used for filtering but not editable by the user.
- **No delete**: Emails are not deletable from the admin panel — they are read-only records fetched from Gmail.
- **No soft deletes**: Neither emails nor digests use soft deletes.
- **Sort order**: Emails default sort by `received_at DESC` (newest first). Digests sort by `digest_date DESC`.
- **Timezone**: All datetime values stored in UTC. Display uses the user's timezone from Profile settings.
- **Raw payload**: Stored for future features (e.g., attachment handling, full body view). Not displayed in the UI.
- **Sync log retention**: Keep all sync logs (no cleanup for now). Used for the "last sync" info display.
- **Rate limiting**: Gmail API has quota limits. The service should handle 429 responses gracefully and log the error.

---

## 8. IMPLEMENTATION ORDER

```
1. database/migrations/2026_04_02_200001_create_emails_table.php
2. database/migrations/2026_04_02_200002_create_email_digests_table.php
3. database/migrations/2026_04_02_200003_create_email_sync_logs_table.php
4. app/Models/Email/Email.php
5. app/Models/Email/EmailDigest.php
6. app/Models/Email/EmailSyncLog.php
7. app/Services/GmailSyncService.php
8. app/Services/EmailInboxService.php
9. app/Services/EmailDigestService.php
10. routes/admin/email/inbox.php
11. routes/admin/email/digest.php
12. app/Livewire/Admin/Email/Inbox/EmailInboxIndex.php
13. resources/views/livewire/admin/email/inbox/index.blade.php
14. app/Livewire/Admin/Email/Digest/EmailDigestIndex.php
15. resources/views/livewire/admin/email/digest/index.blade.php
16. Update resources/views/components/layouts/admin.blade.php — add Email sidebar group with Inbox and Morning Digest links
```

### Sidebar Addition

```
Email (parent, collapsible)
  ├── Inbox
  └── Morning Digest
```
