# Recruiter Alert — Spec

Side: ADMIN

---

## 1. MODULE OVERVIEW

The Recruiter Alert feature uses AI to detect emails from recruiters, hiring managers, and freelance clients, then surfaces them as highlighted alerts so time-sensitive opportunities are never missed. It monitors the `emails` table (created by morning-email-digest) and uses AI analysis of sender info, subject lines, and email content to determine if an email is from a recruiter, hiring manager, or freelance client. Users can configure which alert types to receive, enable browser notifications, and optionally forward urgent alerts to another email address.

### Features
- AI detection of recruiter, hiring manager, and freelance client emails
- Alert dashboard with unread/dismissed state management
- Urgency classification (normal vs urgent) for prioritization
- Configurable alert settings per alert type
- Browser push notification support for new alerts
- Optional email forwarding for urgent alerts
- Stat cards showing alert counts by type and urgency
- Integration with email categories from auto-categorize-emails feature

### Admin Features
- View all recruiter alerts in a filterable, searchable list
- Mark alerts as read or dismissed
- Filter alerts by type (recruiter, hiring manager, freelance client), urgency, and read status
- Configure alert preferences (enable/disable per type, browser notifications, email forwarding)
- View alert statistics (total alerts, unread count, urgent count, breakdown by type)
- Manually trigger AI scan on unchecked emails

---

## 2. DATABASE SCHEMA

### Table: recruiter_alerts
```
Columns:
  - id (bigint, primary key, auto increment)
  - email_id (bigint unsigned, required, foreign key -> emails.id, ON DELETE CASCADE)
  - alert_type (string 30, required) — enum-like: "recruiter", "hiring_manager", "freelance_client"
  - confidence_score (decimal 5,2, nullable) — AI confidence 0.00-100.00
  - detected_signals (json, nullable) — array of reasons AI flagged this email e.g. ["sender domain: linkedin.com", "subject contains: opportunity", "body mentions: role"]
  - is_read (boolean, required, default: false)
  - is_dismissed (boolean, required, default: false)
  - urgency (string 10, required, default: "normal") — "normal" or "urgent"
  - notified_at (datetime, nullable) — when browser/email notification was sent
  - created_at, updated_at (timestamps)

Indexes:
  - unique index on email_id (one alert per email)
  - index on alert_type
  - index on is_read
  - index on is_dismissed
  - index on urgency
  - index on created_at (for chronological listing)

Foreign keys:
  - email_id -> emails.id ON DELETE CASCADE
```

### Table: recruiter_alert_settings
```
Columns:
  - id (bigint, primary key, auto increment)
  - is_enabled (boolean, required, default: true) — master toggle for the entire feature
  - alert_on_recruiter (boolean, required, default: true)
  - alert_on_hiring_manager (boolean, required, default: true)
  - alert_on_freelance_client (boolean, required, default: true)
  - min_confidence_score (integer, required, default: 70) — minimum AI confidence to create alert (0-100)
  - browser_notification (boolean, required, default: false)
  - email_forward (boolean, required, default: false)
  - forward_email (string 255, nullable) — email address to forward urgent alerts to
  - created_at, updated_at (timestamps)

Indexes: none (single-row config table)
Foreign keys: none
```

---

## 3. FILE MAP

### MIGRATIONS
```
database/migrations/2026_04_01_400001_create_recruiter_alerts_table.php
database/migrations/2026_04_01_400002_create_recruiter_alert_settings_table.php
```

### MODELS
```
app/Models/Email/RecruiterAlert.php
  - fillable: email_id, alert_type, confidence_score, detected_signals, is_read, is_dismissed, urgency, notified_at
  - relationships:
    - email() belongsTo(Email::class)
  - casts:
    - is_read -> boolean
    - is_dismissed -> boolean
    - detected_signals -> array
    - notified_at -> datetime
    - confidence_score -> decimal:2
  - scopes:
    - scopeUnread($query) — where is_read = false
    - scopeUndismissed($query) — where is_dismissed = false
    - scopeUrgent($query) — where urgency = "urgent"
    - scopeOfType($query, string $type) — where alert_type = $type

app/Models/Email/RecruiterAlertSetting.php
  - fillable: is_enabled, alert_on_recruiter, alert_on_hiring_manager, alert_on_freelance_client, min_confidence_score, browser_notification, email_forward, forward_email
  - relationships: none
  - casts:
    - is_enabled -> boolean
    - alert_on_recruiter -> boolean
    - alert_on_hiring_manager -> boolean
    - alert_on_freelance_client -> boolean
    - min_confidence_score -> integer
    - browser_notification -> boolean
    - email_forward -> boolean
  - static method:
    - getSettings(): self — returns the single row, creates with defaults if missing
```

Note: The existing Email model (`app/Models/Email/Email.php`, created by morning-email-digest) will need this addition:
  - relationship: recruiterAlert() hasOne(RecruiterAlert::class)

### SERVICES
```
app/Services/RecruiterAlertService.php
  - getAlerts(array $filters, int $perPage = 15): LengthAwarePaginator
    — paginated alerts with email relationship, filterable by type, urgency, read status, search
  - getAlertById(int $id): RecruiterAlert
    — find or fail with email relationship
  - markAsRead(int $id): void
    — set is_read = true
  - markAsUnread(int $id): void
    — set is_read = false
  - dismissAlert(int $id): void
    — set is_dismissed = true
  - undismissAlert(int $id): void
    — set is_dismissed = false
  - markAllAsRead(): int
    — bulk mark all unread alerts as read, returns count
  - dismissAll(): int
    — bulk dismiss all undismissed alerts, returns count
  - scanEmails(): int
    — scan unchecked emails using AI to detect recruiter/hiring manager/freelance client emails,
      create RecruiterAlert records for matches, returns count of new alerts created.
      Respects alert settings (is_enabled, per-type toggles, min_confidence_score).
  - analyzeEmail(Email $email): ?array
    — AI analysis of a single email, returns null if not a match or array with:
      {alert_type, confidence_score, detected_signals, urgency}
  - sendNotifications(RecruiterAlert $alert): void
    — send browser notification and/or email forward based on settings
  - getStats(): array
    — returns {total, unread, urgent, by_type: {recruiter: n, hiring_manager: n, freelance_client: n}, recent_24h: n}
  - getSettings(): RecruiterAlertSetting
    — returns current settings (delegates to model static method)
  - updateSettings(array $data): RecruiterAlertSetting
    — validate and update settings
  - deleteAlert(int $id): void
    — permanently delete an alert record
```

### LIVEWIRE COMPONENTS (Admin)
```
app/Livewire/Admin/Email/RecruiterAlerts/RecruiterAlertIndex.php
  - list/dashboard page showing alerts with stats, filters, and actions

app/Livewire/Admin/Email/RecruiterAlerts/RecruiterAlertSettings.php
  - settings form for configuring alert preferences
```

### VIEWS (Admin)
```
resources/views/livewire/admin/email/recruiter-alerts/index.blade.php
  - alert dashboard with stat cards, filter bar, and alert list

resources/views/livewire/admin/email/recruiter-alerts/settings.blade.php
  - settings form with toggles and email forward configuration
```

### ROUTES (Admin)
```
routes/admin/email/recruiter-alerts.php
  - GET /admin/email/recruiter-alerts -> RecruiterAlertIndex -> admin.email.recruiter-alerts.index
  - GET /admin/email/recruiter-alerts/settings -> RecruiterAlertSettings -> admin.email.recruiter-alerts.settings
```

---

## 4. COMPONENT CONTRACTS

### Component: App\Livewire\Admin\Email\RecruiterAlerts\RecruiterAlertIndex

```
Namespace: App\Livewire\Admin\Email\RecruiterAlerts
Layout: #[Layout('components.layouts.admin')]
Traits: WithPagination

Properties:
  - $search (string, #[Url]) — search query for email subject/sender
  - $filterType (string, #[Url]) — filter by alert_type: "", "recruiter", "hiring_manager", "freelance_client"
  - $filterUrgency (string, #[Url]) — filter by urgency: "", "normal", "urgent"
  - $filterStatus (string, #[Url]) — filter by read status: "", "unread", "read", "dismissed"
  - $stats (array) — computed stats from service

Methods:
  - mount()
    Input: none
    Does: load initial stats
    Output: sets $stats

  - getAlertsProperty(): LengthAwarePaginator (computed)
    Input: uses $search, $filterType, $filterUrgency, $filterStatus
    Does: calls RecruiterAlertService::getAlerts() with current filters
    Output: paginated alerts

  - markAsRead(int $id)
    Input: alert ID
    Does: calls RecruiterAlertService::markAsRead(), refreshes stats
    Output: flash success

  - markAsUnread(int $id)
    Input: alert ID
    Does: calls RecruiterAlertService::markAsUnread(), refreshes stats
    Output: flash success

  - dismiss(int $id)
    Input: alert ID
    Does: calls RecruiterAlertService::dismissAlert(), refreshes stats
    Output: flash success

  - markAllAsRead()
    Input: none
    Does: calls RecruiterAlertService::markAllAsRead(), refreshes stats
    Output: flash success with count

  - dismissAll()
    Input: none
    Does: calls RecruiterAlertService::dismissAll(), refreshes stats
    Output: flash success with count

  - scanEmails()
    Input: none
    Does: calls RecruiterAlertService::scanEmails(), refreshes stats
    Output: flash success with count of new alerts found

  - deleteAlert(int $id)
    Input: alert ID
    Does: calls RecruiterAlertService::deleteAlert()
    Output: flash success

  - refreshStats()
    Input: none
    Does: calls RecruiterAlertService::getStats()
    Output: updates $stats
```

### Component: App\Livewire\Admin\Email\RecruiterAlerts\RecruiterAlertSettings

```
Namespace: App\Livewire\Admin\Email\RecruiterAlerts
Layout: #[Layout('components.layouts.admin')]

Properties:
  - $is_enabled (bool) — master toggle
  - $alert_on_recruiter (bool)
  - $alert_on_hiring_manager (bool)
  - $alert_on_freelance_client (bool)
  - $min_confidence_score (int) — 0-100 range slider
  - $browser_notification (bool)
  - $email_forward (bool)
  - $forward_email (string)

Methods:
  - mount()
    Input: none
    Does: loads current settings from RecruiterAlertService::getSettings() into properties
    Output: populates all properties

  - save()
    Input: none
    Does: validates, calls RecruiterAlertService::updateSettings() with all properties
    Output: flash success "Settings saved."

Validation Rules:
  - is_enabled: required|boolean
  - alert_on_recruiter: required|boolean
  - alert_on_hiring_manager: required|boolean
  - alert_on_freelance_client: required|boolean
  - min_confidence_score: required|integer|min:0|max:100
  - browser_notification: required|boolean
  - email_forward: required|boolean
  - forward_email: nullable|required_if:email_forward,true|email|max:255
```

---

## 5. VIEW BLUEPRINTS

### View: resources/views/livewire/admin/email/recruiter-alerts/index.blade.php
```
Layout: components.layouts.admin
Side: ADMIN
Page title: "Recruiter Alerts"

Design rules:
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:
  1. Breadcrumb: Dashboard > Email > Recruiter Alerts
  2. Page header: "Recruiter Alerts" title + two action buttons:
     - "Scan Emails" (primary button, triggers scanEmails())
     - "Settings" (secondary button, links to settings page)

  3. Stat cards row (4 columns):
     - Total Alerts: count with bg-primary/10 icon
     - Unread: count with bg-amber-500/10 icon
     - Urgent: count with bg-red-500/10 icon
     - Last 24h: count with bg-emerald-500/10 icon

  4. Filter bar (bg-dark-800 rounded-xl):
     - Search input (search subject/sender)
     - Type select: All Types, Recruiter, Hiring Manager, Freelance Client
     - Urgency select: All, Normal, Urgent
     - Status select: All, Unread, Read, Dismissed
     - "Mark All Read" button (small, secondary)
     - "Dismiss All" button (small, danger-tinted)

  5. Alerts list (NOT a table — card-based list for better visual hierarchy):
     Each alert card (bg-dark-800 rounded-xl p-5, left border colored by type):
     - Left color border: purple for recruiter, blue for hiring_manager, emerald for freelance_client
     - Urgency badge: "Urgent" (red) or "Normal" (gray) — top right
     - Type badge: "Recruiter" / "Hiring Manager" / "Freelance Client" with type-appropriate color
     - Email subject (text-white font-medium, bold if unread)
     - Sender name + email (text-gray-400)
     - Email snippet (text-gray-500, truncated to 2 lines)
     - Confidence score (small progress bar or percentage)
     - Detected signals (text-xs text-gray-500, comma-separated)
     - Received date (text-xs text-gray-500, relative time e.g. "2 hours ago")
     - Actions: Mark Read/Unread, Dismiss, Delete

  6. Pagination below the list

  7. Empty state: "No alerts found" with icon and suggestion to scan emails
```

### View: resources/views/livewire/admin/email/recruiter-alerts/settings.blade.php
```
Layout: components.layouts.admin
Side: ADMIN
Page title: "Recruiter Alert Settings"

Design rules:
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:
  1. Breadcrumb: Dashboard > Email > Recruiter Alerts > Settings
  2. Page header: "Alert Settings" title + Back button (links to index)

  3. Form layout (full width, stacked sections):

     Section card 1 — "General":
       - Master toggle: "Enable Recruiter Alerts" (toggle switch with description)
       - Min confidence score: range slider 0-100 with live percentage display

     Section card 2 — "Alert Types":
       - Toggle: "Alert on Recruiter emails" with description
       - Toggle: "Alert on Hiring Manager emails" with description
       - Toggle: "Alert on Freelance Client emails" with description

     Section card 3 — "Notifications":
       - Toggle: "Browser Notifications" with description ("Get push notifications for new alerts")
       - Toggle: "Email Forwarding" with description ("Forward urgent alerts to another email")
       - Conditional: if email_forward is true, show email input field for forward_email

     Submit section:
       - Save button (primary, with loading state)
       - Cancel button (secondary, links back to index)
```

---

## 6. VALIDATION RULES

### Form: Recruiter Alert Settings
```
  - is_enabled: required|boolean
  - alert_on_recruiter: required|boolean
  - alert_on_hiring_manager: required|boolean
  - alert_on_freelance_client: required|boolean
  - min_confidence_score: required|integer|min:0|max:100
  - browser_notification: required|boolean
  - email_forward: required|boolean
  - forward_email: nullable|required_if:email_forward,true|email|max:255
```

Note: The index page has no form — all interactions are single-action methods (mark read, dismiss, delete).

---

## 7. EDGE CASES & BUSINESS RULES

### Alert Creation
- One alert per email maximum (unique index on email_id prevents duplicates)
- If the feature is disabled (is_enabled = false), scanEmails() should return 0 without processing
- If a specific type toggle is off (e.g., alert_on_recruiter = false), emails detected as that type are skipped
- Emails that already have a recruiter_alert record are skipped during scanning
- Only scan emails received in the last 7 days to avoid processing old history on first scan
- Confidence score below min_confidence_score setting should NOT create an alert

### Urgency Classification
- Urgent: subject contains time-sensitive keywords (interview, urgent, deadline, ASAP, time-sensitive, expiring, tomorrow, this week) OR sender domain is a known recruiter platform (linkedin.com, indeed.com, glassdoor.com)
- Normal: all other detected alerts

### Alert Type Detection (AI signals)
- Recruiter: sender from recruiting agencies, LinkedIn recruiter messages, emails mentioning "opportunity", "position", "role", "candidate"
- Hiring Manager: sender from company domains (not recruiting agencies), emails mentioning "team", "hiring for", "looking for", direct company role descriptions
- Freelance Client: emails mentioning "project", "freelance", "budget", "quote", "proposal", from Fiverr/Upwork notification addresses or direct client inquiries

### Deletion Behavior
- Deleting an alert is a hard delete (no soft delete) — the underlying email is NOT affected
- Deleting an email (from emails table) cascades to delete the recruiter_alert (ON DELETE CASCADE)
- Deleting is confirmed via wire:confirm

### Dismissal vs Read
- "Read" means the user has seen the alert (visual indicator changes, unread count decreases)
- "Dismissed" means the user has acknowledged and hidden the alert (filtered out by default, not shown unless "Dismissed" filter is selected)
- An alert can be both read and dismissed

### Notifications
- Browser notifications require user permission (handled client-side with Alpine.js/JS)
- Email forwarding only applies to alerts with urgency = "urgent"
- notified_at is set once when the notification is first sent — not re-sent on subsequent views

### Settings Table
- Single-row table — getSettings() creates a default row if none exists
- Settings are global (not per-user, since this is a single-user admin panel)

### Integration with Email Categories
- The AI detection in this feature is independent from the auto-categorize-emails category assignment
- However, emails already categorized as "Job Response" or "Freelance" in email_categories can be used as a signal to boost confidence score
- The recruiter alert does NOT modify the email's category_id

---

## 8. IMPLEMENTATION ORDER

```
1. database/migrations/2026_04_01_400001_create_recruiter_alerts_table.php
2. database/migrations/2026_04_01_400002_create_recruiter_alert_settings_table.php
3. app/Models/Email/RecruiterAlert.php
4. app/Models/Email/RecruiterAlertSetting.php
5. Update app/Models/Email/Email.php — add recruiterAlert() hasOne relationship
6. app/Services/RecruiterAlertService.php
7. routes/admin/email/recruiter-alerts.php
8. app/Livewire/Admin/Email/RecruiterAlerts/RecruiterAlertIndex.php
9. resources/views/livewire/admin/email/recruiter-alerts/index.blade.php
10. app/Livewire/Admin/Email/RecruiterAlerts/RecruiterAlertSettings.php
11. resources/views/livewire/admin/email/recruiter-alerts/settings.blade.php
12. Update sidebar (components/layouts/admin.blade.php) — add Recruiter Alerts link under Email group
```
