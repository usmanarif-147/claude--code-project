# Job Alerts — Spec

Side: **ADMIN**

---

## 1. MODULE OVERVIEW

Job Alerts notifies the user when a high-match job appears in their feed. When AI Match Scoring scores a newly fetched job above the user's configured threshold (e.g., 80%), the system generates an alert. Alerts are surfaced as dashboard notifications and optionally sent via email. The user controls alert frequency (instant, daily digest, weekly digest) and the minimum score threshold.

### Features
- Configure alert threshold (minimum match score percentage to trigger an alert)
- Choose alert frequency: instant, daily digest, weekly digest
- Toggle dashboard notifications on/off
- Toggle email notifications on/off
- View alert notification history with job details and match score
- Mark notifications as read/unread
- Dismiss (delete) individual notifications
- Bulk mark all as read
- Unread notification count badge in sidebar/header
- Stat cards: total alerts, unread count, high-match jobs this week, average match score of alerted jobs

### Admin Features
- Configure alert settings (threshold, frequency, channels)
- View and manage all alert notifications
- Mark notifications as read/unread individually or in bulk
- Dismiss notifications
- Filter notifications by read/unread status and date range
- Navigate directly to the job listing from a notification

---

## 2. DATABASE SCHEMA

```
Table: job_alerts
Columns:
  - id (bigint, primary key, auto increment)
  - user_id (bigint, unsigned, required, FK -> users.id)
  - is_enabled (boolean, default true) — master toggle for alerts
  - min_score_threshold (unsignedTinyInteger, default 80) — minimum match score to trigger alert (0-100)
  - frequency (string 20, default 'instant') — instant, daily, weekly
  - notify_dashboard (boolean, default true) — show alerts in dashboard notifications
  - notify_email (boolean, default false) — send email notifications
  - last_digest_sent_at (timestamp, nullable) — when the last digest email was sent
  - created_at, updated_at (timestamps)

Indexes:
  - unique on user_id (one config per user)

Foreign keys:
  - user_id references users(id) on delete cascade
```

```
Table: job_alert_notifications
Columns:
  - id (bigint, primary key, auto increment)
  - user_id (bigint, unsigned, required, FK -> users.id)
  - job_listing_id (bigint, unsigned, required, FK -> job_listings.id)
  - match_score (unsignedTinyInteger, required) — the score that triggered this alert (0-100)
  - match_summary (text, nullable) — AI-generated reason for the match (from job_match_scores)
  - is_read (boolean, default false)
  - notified_via (string 20, default 'dashboard') — dashboard, email, both
  - notified_at (timestamp, required) — when the notification was generated
  - created_at, updated_at (timestamps)

Indexes:
  - index on user_id
  - index on job_listing_id
  - index on (user_id, is_read) — for unread count queries
  - index on notified_at
  - index on (user_id, notified_at) — for chronological listing

Foreign keys:
  - user_id references users(id) on delete cascade
  - job_listing_id references job_listings(id) on delete cascade
```

> Note: Tables `job_listings` (Job Feed spec) and `job_match_scores` (AI Match Scoring — future) are referenced but NOT created by this spec. The `api_keys` table already exists (Settings module). This spec references `ApiKey` for email provider keys but does NOT recreate it.

---

## 3. FILE MAP

```
MIGRATIONS:
  - database/migrations/YYYY_MM_DD_000001_create_job_alerts_table.php
  - database/migrations/YYYY_MM_DD_000002_create_job_alert_notifications_table.php

MODELS:
  - app/Models/JobSearch/JobAlert.php
    - fillable: user_id, is_enabled, min_score_threshold, frequency, notify_dashboard,
                notify_email, last_digest_sent_at
    - relationships:
      - user(): belongsTo(User::class)
      - notifications(): hasMany(JobAlertNotification::class)
    - casts:
      - is_enabled -> boolean
      - min_score_threshold -> integer
      - notify_dashboard -> boolean
      - notify_email -> boolean
      - last_digest_sent_at -> datetime
    - scopes:
      - scopeForUser(Builder $query, int $userId): filters by user_id
      - scopeEnabled(Builder $query): where is_enabled = true
    - constants:
      - FREQUENCY_INSTANT = 'instant'
      - FREQUENCY_DAILY = 'daily'
      - FREQUENCY_WEEKLY = 'weekly'
      - ALL_FREQUENCIES = ['instant', 'daily', 'weekly']

  - app/Models/JobSearch/JobAlertNotification.php
    - fillable: user_id, job_listing_id, match_score, match_summary, is_read,
                notified_via, notified_at
    - relationships:
      - user(): belongsTo(User::class)
      - jobListing(): belongsTo(JobListing::class)
      - jobAlert(): belongsTo(JobAlert::class, foreignKey: 'user_id', ownerKey: 'user_id')
    - casts:
      - match_score -> integer
      - is_read -> boolean
      - notified_at -> datetime
    - scopes:
      - scopeForUser(Builder $query, int $userId): filters by user_id
      - scopeUnread(Builder $query): where is_read = false
      - scopeRead(Builder $query): where is_read = true
    - constants:
      - VIA_DASHBOARD = 'dashboard'
      - VIA_EMAIL = 'email'
      - VIA_BOTH = 'both'

SERVICES:
  - app/Services/JobAlertService.php
    - getOrCreateConfig(User $user): JobAlert — returns existing config or creates default
    - updateConfig(User $user, array $data): JobAlert — updates alert settings (threshold, frequency, channels)
    - evaluateAndNotify(User $user, JobListing $job, int $matchScore, ?string $matchSummary): ?JobAlertNotification
      — checks if score >= threshold and alerts are enabled; creates notification if so; returns notification or null
    - processInstantAlert(JobAlertNotification $notification): void
      — for instant frequency: marks notified_via and sends email if notify_email is true
    - processDailyDigest(User $user): int
      — collects unnotified-by-email alerts since last digest; sends single digest email; updates last_digest_sent_at; returns count
    - processWeeklyDigest(User $user): int
      — same as daily but for weekly window; returns count
    - getNotifications(User $user, array $filters, int $perPage): LengthAwarePaginator
      — returns paginated notifications with filters (read/unread, date range); ordered by notified_at DESC
    - markAsRead(JobAlertNotification $notification): void — sets is_read = true
    - markAsUnread(JobAlertNotification $notification): void — sets is_read = false
    - markAllAsRead(User $user): int — marks all unread as read; returns count affected
    - dismissNotification(JobAlertNotification $notification): void — deletes the notification
    - getUnreadCount(User $user): int — returns count of unread notifications
    - getStats(User $user): array — returns array with total_alerts, unread_count,
      high_match_this_week (alerts created in last 7 days), avg_match_score

--- ADMIN FILES ---

LIVEWIRE COMPONENTS:
  - app/Livewire/Admin/JobSearch/JobAlerts/JobAlertSettings.php
    - public properties: isEnabled, minScoreThreshold, frequency, notifyDashboard, notifyEmail
    - methods:
      - mount(): void — loads current config from service
      - save(): void — validates and saves config via service; flashes success

  - app/Livewire/Admin/JobSearch/JobAlerts/JobAlertIndex.php
    - public properties: filterStatus (read/unread/all), filterDateFrom, filterDateTo
    - methods:
      - mount(): void — initializes default filter values
      - markAsRead(int $notificationId): void — marks single notification as read
      - markAsUnread(int $notificationId): void — marks single notification as unread
      - markAllAsRead(): void — marks all unread notifications as read; flashes result
      - dismiss(int $notificationId): void — deletes notification
      - getNotificationsProperty(): LengthAwarePaginator — computed, returns filtered paginated notifications
      - getStatsProperty(): array — computed, returns stat card data
      - getUnreadCountProperty(): int — computed, returns unread count

VIEWS:
  - resources/views/livewire/admin/job-search/job-alerts/settings.blade.php
  - resources/views/livewire/admin/job-search/job-alerts/index.blade.php

ROUTES (admin):
  - routes/admin/job-search/job-alerts.php
    - GET /admin/job-search/alerts → JobAlertIndex → admin.job-search.alerts.index
    - GET /admin/job-search/alerts/settings → JobAlertSettings → admin.job-search.alerts.settings
```

---

## 4. COMPONENT CONTRACTS

### Admin Components

```
Component: App\Livewire\Admin\JobSearch\JobAlerts\JobAlertSettings
Namespace:  App\Livewire\Admin\JobSearch\JobAlerts
Layout: #[Layout('components.layouts.admin')]

Properties:
  - $isEnabled (bool, default true) — master toggle
  - $minScoreThreshold (int, default 80) — minimum match score (0-100)
  - $frequency (string, default 'instant') — instant, daily, weekly
  - $notifyDashboard (bool, default true) — dashboard notification toggle
  - $notifyEmail (bool, default false) — email notification toggle

Methods:
  - mount()
    Input: none
    Does: 1. Calls JobAlertService::getOrCreateConfig(auth()->user())
          2. Populates all properties from the config model
    Output: none

  - save()
    Input: none
    Does: 1. Validates properties
          2. Calls JobAlertService::updateConfig(auth()->user(), validated data)
          3. Flashes success: "Alert settings saved."
          4. On failure, flashes error
    Output: session flash (success or error)

Validation Rules:
  - isEnabled: required|boolean
  - minScoreThreshold: required|integer|min:0|max:100
  - frequency: required|string|in:instant,daily,weekly
  - notifyDashboard: required|boolean
  - notifyEmail: required|boolean
```

```
Component: App\Livewire\Admin\JobSearch\JobAlerts\JobAlertIndex
Namespace:  App\Livewire\Admin\JobSearch\JobAlerts
Layout: #[Layout('components.layouts.admin')]
Traits: WithPagination

Properties:
  - $filterStatus (string, default '') — #[Url] filter by read/unread status
  - $filterDateFrom (string, default '') — #[Url] start date filter (Y-m-d)
  - $filterDateTo (string, default '') — #[Url] end date filter (Y-m-d)

Methods:
  - mount()
    Input: none
    Does: initializes properties with defaults
    Output: none

  - markAsRead(int $notificationId)
    Input: notification ID
    Does: 1. Finds JobAlertNotification by ID (scoped to user)
          2. Calls JobAlertService::markAsRead($notification)
    Output: no flash — UI updates reactively

  - markAsUnread(int $notificationId)
    Input: notification ID
    Does: 1. Finds JobAlertNotification by ID (scoped to user)
          2. Calls JobAlertService::markAsUnread($notification)
    Output: no flash — UI updates reactively

  - markAllAsRead()
    Input: none
    Does: 1. Calls JobAlertService::markAllAsRead(auth()->user())
          2. Flashes success: "All notifications marked as read."
    Output: session flash

  - dismiss(int $notificationId)
    Input: notification ID
    Does: 1. Finds JobAlertNotification by ID (scoped to user)
          2. Calls JobAlertService::dismissNotification($notification)
    Output: no flash — notification disappears reactively

  - getNotificationsProperty() [Computed]
    Input: reads filterStatus, filterDateFrom, filterDateTo
    Does: calls JobAlertService::getNotifications() with current filters, 15 per page
    Output: LengthAwarePaginator of JobAlertNotification models (with jobListing eager-loaded)

  - getStatsProperty() [Computed]
    Input: none
    Does: calls JobAlertService::getStats(auth()->user())
    Output: array with keys: total_alerts, unread_count, high_match_this_week, avg_match_score

  - getUnreadCountProperty() [Computed]
    Input: none
    Does: calls JobAlertService::getUnreadCount(auth()->user())
    Output: int

  - updatingFilterStatus() / updatingFilterDate*()
    Input: none
    Does: resets pagination to page 1
    Output: none
```

---

## 5. VIEW BLUEPRINTS

### Admin Views

```
View: resources/views/livewire/admin/job-search/job-alerts/settings.blade.php
Layout: components.layouts.admin
Side: ADMIN
Page title: "Alert Settings"

Design rules (from CLAUDE.md admin side):
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:

  1. Breadcrumb
     - Dashboard > Job Search > Job Alerts > Settings

  2. Page Header
     - Title: "Alert Settings" (text-2xl font-mono font-bold text-white uppercase tracking-wider)
     - Subtitle: "Configure when and how you want to be notified about high-match jobs."
     - Right side: "Back to Alerts" secondary button linking to alerts index

  3. Settings Form (single full-width card)
     - Section: "General"
       - Master Toggle: "Enable Job Alerts" — toggle switch (isEnabled)
         - Description: "Receive notifications when jobs match above your threshold."

     - Section: "Threshold"
       - Range slider: "Minimum Match Score" (minScoreThreshold, 0-100, step 5)
         - Shows current value as percentage (e.g., "80%")
         - Progress bar visualization below the slider
         - Helper text: "You will only be alerted for jobs scoring at or above this percentage."

     - Section: "Frequency"
       - Radio group or select: frequency (instant / daily digest / weekly digest)
         - Instant: "Get notified immediately when a matching job is found."
         - Daily Digest: "Receive a summary of matching jobs once per day."
         - Weekly Digest: "Receive a summary of matching jobs once per week."

     - Section: "Notification Channels"
       - Toggle: "Dashboard Notifications" (notifyDashboard)
         - Description: "Show alerts in the admin panel notification area."
       - Toggle: "Email Notifications" (notifyEmail)
         - Description: "Send alerts to your registered email address."
         - Note: If email is enabled but no email API key is configured, show a warning
           linking to Settings > API Keys.

     - Save button: primary button with loading state — "Save Settings"

  4. Flash message area (success/error)
```

```
View: resources/views/livewire/admin/job-search/job-alerts/index.blade.php
Layout: components.layouts.admin
Side: ADMIN
Page title: "Job Alerts"

Design rules (from CLAUDE.md admin side):
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:

  1. Breadcrumb
     - Dashboard > Job Search > Job Alerts

  2. Page Header
     - Title: "Job Alerts" (text-2xl font-mono font-bold text-white uppercase tracking-wider)
     - Subtitle: "Notifications for jobs matching above your score threshold."
     - Right side actions:
       - "Mark All as Read" secondary button (disabled if no unread)
       - "Settings" icon button linking to alert settings page

  3. Stat Cards Row (4-column grid)
     - Total Alerts: total notification count (icon: bell, bg-primary/10)
     - Unread: unread notifications count (icon: envelope-open, bg-amber-500/10)
     - High Match This Week: alerts created in last 7 days (icon: fire, bg-emerald-500/10)
     - Avg Match Score: average score of alerted jobs (icon: chart-bar, bg-fuchsia-500/10)

  4. Filter Bar (bg-dark-800 rounded-xl card)
     - Status dropdown: All / Unread / Read
     - Date From input (date picker)
     - Date To input (date picker)

  5. Notification List (main content area)
     - Each notification is a card (bg-dark-800 border border-dark-700 rounded-xl p-5):
       - Left: unread indicator dot (w-2 h-2 rounded-full bg-primary) if is_read = false
       - Job title: text-base font-medium text-white (clickable, opens job_url in new tab)
       - Company name: text-sm text-gray-400
       - Match score badge: gradient badge showing score percentage
         - >= 90%: bg-gradient-to-r from-primary via-fuchsia-500 to-orange-500 text-white
         - >= 80%: bg-emerald-500/10 text-emerald-400
         - < 80%: bg-amber-500/10 text-amber-400
       - Match summary: text-sm text-gray-400 (1-2 lines, truncated with "Show more")
       - Notified at: relative time (text-xs text-gray-500)
       - Notified via badge: "Dashboard" / "Email" / "Both" (text-xs)
       - Action buttons row:
         - "Mark as Read" / "Mark as Unread" toggle button (text-gray-400)
         - "View Job" link: opens job_url in new tab (text-primary-light)
         - "Dismiss" icon button: removes notification (text-gray-500 hover:text-red-400)

     - Unread notifications have a subtle left border accent (border-l-2 border-primary)
     - Read notifications have slightly dimmed text

  6. Pagination
     - Standard Livewire pagination below the list
     - Shows "Showing X-Y of Z notifications"

  7. Empty State (when no notifications match filters)
     - Icon: bell-slash outline
     - Title: "No notifications found"
     - Subtitle: "Try adjusting your filters."

  8. Empty State (when no notifications at all)
     - Icon: bell outline with sparkles
     - Title: "No alerts yet"
     - Subtitle: "When jobs match above your score threshold, you'll see notifications here. Make sure alerts are enabled in Settings."
     - CTA button: "Configure Alert Settings" (link to settings page)
```

---

## 6. VALIDATION RULES

```
Form: Alert Settings (JobAlertSettings component)
  - isEnabled: required|boolean
  - minScoreThreshold: required|integer|min:0|max:100
  - frequency: required|string|in:instant,daily,weekly
  - notifyDashboard: required|boolean
  - notifyEmail: required|boolean
```

```
Service-level validation (in JobAlertService::evaluateAndNotify):
  - matchScore: required|integer|min:0|max:100
  - jobListing: must exist in job_listings table and belong to user
  - User must have an enabled JobAlert config
  - matchScore must be >= user's min_score_threshold
```

---

## 7. EDGE CASES & BUSINESS RULES

### One Config Per User
- The `job_alerts` table has a unique constraint on `user_id` — each user has exactly one alert config row
- `getOrCreateConfig()` returns the existing row or creates a default one (enabled, 80% threshold, instant, dashboard only)

### Alert Triggering Flow
- Alerts are triggered by the AI Match Scoring process (future spec): after a job is scored, the scoring service calls `JobAlertService::evaluateAndNotify()`
- This spec defines the alert infrastructure; the actual invocation happens in the AI Match Scoring feature
- If AI Match Scoring is not yet implemented, no alerts will be generated (no jobs will have scores)

### Duplicate Alert Prevention
- Before creating a notification, check if a notification already exists for the same user + job_listing_id
- If a duplicate exists, skip creation (use firstOrCreate pattern on user_id + job_listing_id)
- This prevents duplicate alerts if a job is re-scored

### Frequency Behavior
- **Instant**: Notification is created immediately and marked as `notified_via = 'dashboard'`. If `notify_email` is true, email is sent immediately and `notified_via` is updated to `'both'`.
- **Daily Digest**: Notification is created with `notified_via = 'dashboard'`. A scheduled command (daily at configured time) calls `processDailyDigest()` which collects all unread dashboard-only notifications since `last_digest_sent_at`, sends one digest email, and updates `last_digest_sent_at`. Notifications that were emailed get `notified_via = 'both'`.
- **Weekly Digest**: Same as daily but runs weekly. The scheduled command runs once per week.
- Dashboard notifications always appear instantly regardless of frequency setting (frequency only controls email batching)

### Email Notification Dependencies
- Email sending requires a configured email API key (Gmail provider in `api_keys` table)
- If `notify_email` is true but no Gmail API key is configured/connected, show a warning on the settings page but do NOT prevent saving
- When attempting to send email and no key is available, log the failure silently — do not create an error notification

### Notification Lifecycle
- Created: when a job scores above threshold
- Read: user clicks "Mark as Read" or views the notification detail
- Dismissed: user clicks "Dismiss" — permanently deleted from database
- No soft delete — dismissed notifications are gone

### Cascade on Delete
- User deletion cascades to all `job_alerts` and `job_alert_notifications`
- Job listing deletion cascades to related `job_alert_notifications` (FK on job_listing_id)
- Deleting a job alert config does NOT delete existing notifications (they remain viewable)

### Match Score Display
- Score is displayed as a percentage badge with color coding:
  - 90-100%: gradient badge (premium feel) — `bg-gradient-to-r from-primary via-fuchsia-500 to-orange-500`
  - 80-89%: emerald badge — `bg-emerald-500/10 text-emerald-400`
  - Below 80%: amber badge — `bg-amber-500/10 text-amber-400`
- This threshold coloring is purely visual; the actual alert threshold is user-configurable

### Sort Order
- Default sort: `notified_at DESC` (newest first)
- Unread notifications are NOT pinned to top — chronological order is preserved
- User can filter to show only unread if desired

### Unread Count Badge
- The unread count should be exposed as a computed property for use in the sidebar/header
- The admin layout can poll or listen for this count to show a badge next to "Job Alerts" in the sidebar
- Implementation of the sidebar badge itself is outside this spec (layout concern), but the data method is provided

### Threshold Slider Behavior
- Slider range: 0% to 100%, step of 5
- Default: 80%
- Setting threshold to 0 effectively means "alert for every scored job"
- Setting threshold to 100 means "only alert for perfect matches"
- Live preview: as user drags slider, show the percentage value updating in real-time (wire:model.live)

### Digest Scheduling (Future Enhancement)
- The daily/weekly digest requires a Laravel scheduled command (e.g., `php artisan alerts:send-digest`)
- This command iterates all users with enabled alerts and the corresponding frequency, then calls the appropriate digest method
- The scheduled command is NOT part of this spec's file map (it will be added when the full email pipeline is built)
- For MVP, instant dashboard notifications are the primary delivery mechanism

---

## 8. IMPLEMENTATION ORDER

```
1. database/migrations/YYYY_MM_DD_000001_create_job_alerts_table.php
2. database/migrations/YYYY_MM_DD_000002_create_job_alert_notifications_table.php
3. app/Models/JobSearch/JobAlert.php
4. app/Models/JobSearch/JobAlertNotification.php
5. app/Services/JobAlertService.php
6. routes/admin/job-search/job-alerts.php
7. app/Livewire/Admin/JobSearch/JobAlerts/JobAlertSettings.php
8. resources/views/livewire/admin/job-search/job-alerts/settings.blade.php
9. app/Livewire/Admin/JobSearch/JobAlerts/JobAlertIndex.php
10. resources/views/livewire/admin/job-search/job-alerts/index.blade.php
```

> Dependencies: This feature depends on the Job Feed spec being complete (`job_listings` table and `JobListing` model must exist). It also references the future AI Match Scoring feature — the `evaluateAndNotify()` method will be called by the scoring service once that feature is built. The `api_keys` table (Settings module) is referenced for email configuration checks but is NOT recreated.
