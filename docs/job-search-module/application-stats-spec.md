# Application Stats — Spec

Side: **ADMIN**

---

## 1. MODULE OVERVIEW

Application Stats is an analytics dashboard within the Job Search module group that visualizes the authenticated user's job search progress. It aggregates data from the existing `job_applications` and `job_listings` tables to compute statistics like application volume, response rates, interview conversion, rejection patterns, and activity over time. No new database tables are required — this feature is read-only analytics over existing data.

### Features
- Stat cards showing total applications, applications this week/month, response rate, and interview rate
- Time-period selector (7 days, 30 days, 90 days) to scope all stats and charts
- Area/bar chart showing applications over time (grouped by day or week depending on period)
- Status breakdown donut/pie chart showing distribution across saved, applied, interview, offer, rejected
- Response rate calculation: (interview + offer + rejected) / total applied
- Interview rate calculation: interview / total applied
- Most common rejection sources table (companies with most rejections)
- Recent activity feed showing latest status changes
- Pipeline funnel visualization: saved -> applied -> interview -> offer

### Admin Features
- View all application statistics scoped to the authenticated user
- Toggle time period to see trends across 7d, 30d, 90d windows
- Drill into status breakdowns and rejection patterns
- See pipeline conversion rates at each stage

---

## 2. DATABASE SCHEMA

No new tables required. This feature queries existing tables:

```
Existing Table: job_applications (from Application Tracker spec)
Relevant Columns:
  - id (bigint, primary key)
  - status (string 50) — saved, applied, interview, offer, rejected
  - applied_date (date, nullable) — when application was submitted
  - company (string 255) — company name
  - position (string 255) — job title
  - created_at, updated_at (timestamps)

Existing Table: job_listings (from Job Feed spec)
Relevant Columns:
  - id (bigint, primary key)
  - source_platform (string 30) — platform origin
  - user_status (string 20, nullable) — interested, not_relevant
  - fetched_at (timestamp) — when fetched
  - created_at, updated_at (timestamps)
```

> No migrations needed for this feature.

---

## 3. FILE MAP

```
MIGRATIONS:
  - None — analytics-only feature, no new tables

MODELS:
  - None — uses existing models:
    - App\Models\JobSearch\JobApplication (from Application Tracker)
    - App\Models\JobSearch\JobListing (from Job Feed)

SERVICES:
  - app/Services/ApplicationStatsService.php
    - getSummaryStats(string $period): array — returns stat card data: total_applications,
      applied_this_week, applied_this_month, response_rate, interview_rate, offer_count,
      rejected_count, active_count (applied + interview statuses)
    - getApplicationsByDay(string $period): array — returns array of {date, count} for charting
      applications over time, grouped by day; uses applied_date or created_at
    - getStatusBreakdown(): array — returns count per status: ['saved' => int, 'applied' => int,
      'interview' => int, 'offer' => int, 'rejected' => int]
    - getTopRejectedCompanies(string $period, int $limit = 10): Collection — returns companies
      with most rejections in the period, with count per company
    - getRecentActivity(int $limit = 15): Collection — returns most recent job_applications
      ordered by updated_at DESC, for the activity feed
    - getPipelineConversion(): array — returns funnel data: total saved, how many moved to applied,
      how many reached interview, how many got offer; with conversion percentages at each stage
    - periodToDays(string $period): int — helper converting '7d'/'30d'/'90d' to integer days

--- ADMIN FILES ---

LIVEWIRE COMPONENTS:
  - app/Livewire/Admin/JobSearch/ApplicationStats/ApplicationStatsIndex.php
    - public properties: period (string, default '30d')
    - methods: mount(), updatedPeriod(), render()

VIEWS:
  - resources/views/livewire/admin/job-search/application-stats/index.blade.php
    - analytics dashboard with stat cards, charts, tables

ROUTES (admin):
  - routes/admin/job-search/application-stats.php
    - GET /admin/job-search/application-stats → ApplicationStatsIndex → admin.job-search.application-stats.index
```

---

## 4. COMPONENT CONTRACTS

### Admin Components

```
Component: App\Livewire\Admin\JobSearch\ApplicationStats\ApplicationStatsIndex
Namespace: App\Livewire\Admin\JobSearch\ApplicationStats
Layout: #[Layout('components.layouts.admin')]

Properties:
  - $period (string, default '30d') — time period filter: '7d', '30d', '90d'

Methods:
  - mount()
    Input: none
    Does: sets default period to '30d'
    Output: none

  - updatedPeriod()
    Input: none (reads $this->period)
    Does: validates period is one of '7d', '30d', '90d'; resets to '30d' if invalid
    Output: none — re-render triggers fresh data

  - render(ApplicationStatsService $service)
    Input: injected ApplicationStatsService
    Does: passes all computed stats to the view:
      1. $stats = $service->getSummaryStats($this->period)
      2. $chartData = $service->getApplicationsByDay($this->period)
      3. $statusBreakdown = $service->getStatusBreakdown()
      4. $topRejected = $service->getTopRejectedCompanies($this->period)
      5. $recentActivity = $service->getRecentActivity()
      6. $pipeline = $service->getPipelineConversion()
    Output: returns view with all data arrays
```

---

## 5. VIEW BLUEPRINTS

### Admin View

```
View: resources/views/livewire/admin/job-search/application-stats/index.blade.php
Layout: components.layouts.admin
Side: ADMIN
Page title: "Application Stats"

Design rules (from CLAUDE.md admin side):
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:

  1. Breadcrumb
     - Dashboard > Job Search > Application Stats

  2. Page Header
     - Title: "Application Stats" (text-2xl font-mono font-bold text-white uppercase tracking-wider)
     - Subtitle: "Track your job search progress and conversion rates."
     - Right side: Period selector — three toggle buttons (7D / 30D / 90D)
       - Active: bg-primary text-white rounded-lg
       - Inactive: bg-dark-700 text-gray-400 hover:text-white rounded-lg
       - wire:click to update $period

  3. Stat Cards Row (4-column grid, gap-5, mb-8)
     Card 1 — Total Applications:
       - Icon: document-text, bg-primary/10 text-primary-light
       - Value: total count of all job_applications
       - Label: "Total Applications"
     Card 2 — Applied This Month:
       - Icon: calendar, bg-blue-500/10 text-blue-400
       - Value: count of applications with status != 'saved' and applied_date in current month
       - Label: "Applied This Month"
     Card 3 — Response Rate:
       - Icon: chat-bubble, bg-emerald-500/10 text-emerald-400
       - Value: percentage (interview + offer + rejected) / total applied, formatted as "XX%"
       - Label: "Response Rate"
       - Trend badge if data available (compared to previous period)
     Card 4 — Interview Rate:
       - Icon: user-group, bg-fuchsia-500/10 text-fuchsia-400
       - Value: percentage interview / total applied, formatted as "XX%"
       - Label: "Interview Rate"

  4. Charts Row (2-column grid on lg, gap-6, mb-8)
     Left — Applications Over Time (col-span-1):
       - Card with header "Applications Over Time" (font-mono uppercase tracking-wider)
       - Area chart (Alpine.js + Chart.js or lightweight chart library)
       - X-axis: dates, Y-axis: application count
       - Fill: gradient from primary/30 to transparent
       - Line: primary color
       - Data: $chartData array [{date, count}, ...]
     Right — Status Breakdown (col-span-1):
       - Card with header "Status Breakdown" (font-mono uppercase tracking-wider)
       - Donut/ring chart showing distribution by status
       - Colors per status:
         - Saved: blue-400
         - Applied: primary-light (#a78bfa)
         - Interview: amber-400
         - Offer: emerald-400
         - Rejected: red-400
       - Legend below chart with count per status
       - Data: $statusBreakdown array

  5. Pipeline Funnel (full width, mb-8)
     - Card with header "Application Pipeline" (font-mono uppercase tracking-wider)
     - Horizontal funnel visualization showing conversion at each stage:
       Saved → Applied → Interview → Offer
     - Each stage shows: count + percentage of previous stage
     - Progress bar style: bg-gradient-to-r from-primary to-fuchsia-500 with decreasing width
     - Data: $pipeline array

  6. Bottom Row (2-column grid on lg, gap-6)
     Left — Top Rejected Companies:
       - Card with header "Top Rejection Sources" (font-mono uppercase tracking-wider)
       - Table with columns: Company, Rejections, Percentage
       - Rows sorted by rejection count DESC
       - Max 10 rows
       - Each row: company name (text-white), count (text-red-400), percentage bar
       - Empty state: "No rejections recorded yet." with muted icon
       - Data: $topRejected collection
     Right — Recent Activity:
       - Card with header "Recent Activity" (font-mono uppercase tracking-wider)
       - Vertical timeline/feed of latest application changes
       - Each entry: status badge + company + position + relative timestamp
       - Status badge colors match status breakdown colors
       - Max 15 entries, scrollable if overflow (max-h-96 overflow-y-auto)
       - Empty state: "No applications yet. Start tracking your job search."
       - Data: $recentActivity collection
```

---

## 6. VALIDATION RULES

No form validation needed — this is a read-only analytics page.

```
Period filter (inline validation in updatedPeriod):
  - period: required|string|in:7d,30d,90d — defaults to '30d' if invalid
```

---

## 7. EDGE CASES & BUSINESS RULES

- **No applications exist:** All stat cards show 0 or "0%". Charts show empty state with message "Start tracking applications to see stats here." Pipeline shows all zeros.
- **Division by zero:** Response rate and interview rate calculations must guard against division by zero when no applications have status other than 'saved'. Return 0% in that case.
- **Period scoping:** The period filter ('7d', '30d', '90d') applies to stat cards, the applications-over-time chart, and the top rejected companies table. Status breakdown and pipeline funnel show all-time data (not period-scoped) since they represent the overall funnel.
- **Applied date vs created_at:** For charting applications over time, prefer `applied_date` when available; fall back to `created_at` for applications that have no `applied_date` (e.g., those still in 'saved' status).
- **Response rate definition:** An application counts as "responded" if its status is interview, offer, or rejected. Applications in 'saved' or 'applied' status have not received a response. The denominator is all applications with status != 'saved' (i.e., those that were actually submitted).
- **Interview rate definition:** interview count / total applied (status != 'saved'). Applications still in 'applied' status are in the denominator but not the numerator.
- **Pipeline funnel:** Shows cumulative conversion. "Saved" is total count. "Applied" is count that moved past saved (status in applied, interview, offer, rejected). "Interview" is count that reached interview or beyond (interview, offer). "Offer" is count with offer status.
- **Rejection sources:** Groups by `company` column (case-insensitive). Only counts applications with status = 'rejected'.
- **Recent activity sort:** Orders by `updated_at` DESC to capture the most recent status changes, not creation date.
- **No sidebar link duplication:** The "Application Stats" link must be added inside the existing Job Search sidebar group, not as a standalone root item.
- **Chart rendering:** Charts use Alpine.js with a lightweight charting approach (inline SVG, or a small library like Chart.js loaded via CDN or npm). The chart must render within the dark theme — dark background, light grid lines (dark-700), white/gray text labels.

---

## 8. IMPLEMENTATION ORDER

```
1. app/Services/ApplicationStatsService.php
2. routes/admin/job-search/application-stats.php
3. app/Livewire/Admin/JobSearch/ApplicationStats/ApplicationStatsIndex.php
4. resources/views/livewire/admin/job-search/application-stats/index.blade.php
5. Update sidebar in resources/views/components/layouts/admin.blade.php — add "Application Stats" link under Job Search group
```
