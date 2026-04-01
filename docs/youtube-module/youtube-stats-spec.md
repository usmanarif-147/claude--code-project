# YouTube Stats — Spec

Side: ADMIN

---

## 1. MODULE OVERVIEW

YouTube Stats is an admin-only dashboard that displays YouTube channel performance data without needing to open YouTube Studio. It pulls data from the YouTube Data API v3 using a Google API key stored in the existing `api_keys` table (provider: `youtube`), caches results locally, and presents channel-level and per-video statistics with weekly comparisons.

### Features

- Channel overview stats: total subscribers, total views, total watch time, video count
- Recent videos list with per-video stats (views, likes, comments, published date)
- Revenue/earnings display (if monetized — stored as manually entered value since the YouTube Data API does not expose revenue; only the YouTube Analytics API with OAuth does)
- Weekly comparison: this week vs last week for key metrics (subscribers gained, views, watch time)
- Manual refresh button to pull fresh data from the API on demand
- Auto-cache: data is stored locally so the page loads instantly; API is only called on refresh or if cache is stale (older than 6 hours)

### Admin Features

- View channel stats dashboard at `/admin/youtube/stats`
- Click "Refresh" to fetch latest data from YouTube API
- See weekly delta indicators (up/down arrows with percentage change)
- View a table of recent videos sorted by published date
- See revenue card (manually entered via an inline edit, since YouTube Data API v3 does not expose earnings)

### Dependencies on Existing System

- **ApiKey model** (`app/Models/ApiKey.php`) — already has `PROVIDER_YOUTUBE` constant. The YouTube API key is stored here with `key_value` = Google API key. The `extra_data` JSON field stores `channel_id`.
- **ApiKeyService** (`app/Services/ApiKeyService.php`) — already has `testYoutube()` method.
- **Settings > API Keys page** — already supports configuring the YouTube provider.

---

## 2. DATABASE SCHEMA

### Table: youtube_channel_stats

Caches the latest channel-level snapshot so the dashboard loads without hitting the API every time.

```
Table: youtube_channel_stats
Columns:
  - id (bigint, primary key, auto increment)
  - user_id (bigint, required, foreign key → users.id)
  - channel_id (string, required) — YouTube channel ID
  - channel_title (string, required) — channel display name
  - channel_thumbnail_url (string, nullable) — channel avatar URL
  - subscriber_count (bigint, default 0)
  - total_view_count (bigint, default 0)
  - video_count (integer, default 0)
  - estimated_watch_hours (decimal 12,2, default 0) — total watch time in hours (computed from API)
  - monthly_revenue (decimal 10,2, nullable) — manually entered monthly earnings estimate
  - fetched_at (timestamp, required) — when this snapshot was pulled from YouTube API
  - created_at, updated_at (timestamps)

Indexes:
  - unique(user_id) — one snapshot per user
Foreign keys:
  - user_id → users.id ON DELETE CASCADE
```

### Table: youtube_videos

Caches recent video data fetched from the API.

```
Table: youtube_videos
Columns:
  - id (bigint, primary key, auto increment)
  - user_id (bigint, required, foreign key → users.id)
  - video_id (string, required) — YouTube video ID
  - title (string, required)
  - thumbnail_url (string, nullable)
  - published_at (timestamp, required)
  - view_count (bigint, default 0)
  - like_count (bigint, default 0)
  - comment_count (bigint, default 0)
  - duration (string, nullable) — ISO 8601 duration from API (e.g., "PT4M13S")
  - created_at, updated_at (timestamps)

Indexes:
  - unique(user_id, video_id) — no duplicate videos per user
  - index(user_id, published_at) — for sorting recent videos
Foreign keys:
  - user_id → users.id ON DELETE CASCADE
```

### Table: youtube_weekly_snapshots

Stores weekly metric snapshots for comparison (this week vs last week).

```
Table: youtube_weekly_snapshots
Columns:
  - id (bigint, primary key, auto increment)
  - user_id (bigint, required, foreign key → users.id)
  - week_start (date, required) — Monday of that week
  - subscriber_count (bigint, default 0) — subscriber count at end of week
  - view_count (bigint, default 0) — total views at end of week
  - video_count (integer, default 0) — total videos at end of week
  - estimated_watch_hours (decimal 12,2, default 0)
  - created_at, updated_at (timestamps)

Indexes:
  - unique(user_id, week_start) — one snapshot per user per week
Foreign keys:
  - user_id → users.id ON DELETE CASCADE
```

---

## 3. FILE MAP

### MIGRATIONS

```
database/migrations/2026_04_01_600001_create_youtube_channel_stats_table.php
database/migrations/2026_04_01_600002_create_youtube_videos_table.php
database/migrations/2026_04_01_600003_create_youtube_weekly_snapshots_table.php
```

### MODELS

Three related models — grouped in `app/Models/YouTube/` subfolder.

```
app/Models/YouTube/YouTubeChannelStat.php
  - fillable: user_id, channel_id, channel_title, channel_thumbnail_url, subscriber_count, total_view_count, video_count, estimated_watch_hours, monthly_revenue, fetched_at
  - casts: subscriber_count → integer, total_view_count → integer, video_count → integer, estimated_watch_hours → decimal:2, monthly_revenue → decimal:2, fetched_at → datetime
  - relationships: belongsTo(User)

app/Models/YouTube/YouTubeVideo.php
  - fillable: user_id, video_id, title, thumbnail_url, published_at, view_count, like_count, comment_count, duration
  - casts: view_count → integer, like_count → integer, comment_count → integer, published_at → datetime
  - relationships: belongsTo(User)

app/Models/YouTube/YouTubeWeeklySnapshot.php
  - fillable: user_id, week_start, subscriber_count, view_count, video_count, estimated_watch_hours
  - casts: week_start → date, subscriber_count → integer, view_count → integer, video_count → integer, estimated_watch_hours → decimal:2
  - relationships: belongsTo(User)
```

### SERVICES

```
app/Services/YouTubeStatsService.php
  Methods:
  - getChannelStats(int $userId): ?YouTubeChannelStat — returns cached stats for user
  - getRecentVideos(int $userId, int $limit = 10): Collection — returns cached videos sorted by published_at desc
  - getWeeklyComparison(int $userId): array — returns ['current' => YouTubeWeeklySnapshot, 'previous' => YouTubeWeeklySnapshot, 'deltas' => [...]]
  - refreshFromApi(int $userId): bool — fetches fresh data from YouTube Data API v3, updates all three tables, returns true on success
  - updateMonthlyRevenue(int $userId, float $amount): YouTubeChannelStat — updates the manually entered revenue field
  - isStale(int $userId): bool — returns true if fetched_at is older than 6 hours or no data exists
  - fetchChannelData(string $apiKey, string $channelId): array — calls YouTube API channels endpoint (part=snippet,statistics)
  - fetchRecentVideos(string $apiKey, string $channelId, int $maxResults = 10): array — calls YouTube API search + videos endpoints
  - saveWeeklySnapshot(int $userId): void — creates/updates snapshot for current week based on current channel stats
```

### LIVEWIRE COMPONENTS (ADMIN)

```
app/Livewire/Admin/YouTube/Stats/YouTubeStatsIndex.php
  - Layout: #[Layout('components.layouts.admin')]
  - Public properties:
    - $channelStats (YouTubeChannelStat|null)
    - $recentVideos (Collection)
    - $weeklyComparison (array)
    - $isConfigured (bool) — whether YouTube API key + channel ID exist
    - $isLoading (bool, default false) — loading state during refresh
    - $monthlyRevenue (string) — bound to inline revenue input
  - Methods:
    - mount(): load cached data via service, check if API key configured
    - refreshStats(): calls service refreshFromApi(), reloads all properties, flashes success/error
    - updateRevenue(): validates and saves monthly revenue via service
  - Computed: none (data loaded in mount and after refresh)
```

### VIEWS (ADMIN)

```
resources/views/livewire/admin/youtube/stats/index.blade.php
  - YouTube Stats dashboard page
  - See View Blueprints section below for full layout
```

### ROUTES (ADMIN)

```
routes/admin/youtube/stats.php
  Routes:
  - GET /admin/youtube/stats → YouTubeStatsIndex → admin.youtube.stats.index
```

### SIDEBAR UPDATE

```
resources/views/components/layouts/admin.blade.php
  - Add "YouTube" module group to sidebar
  - Nested items:
    - Stats → admin.youtube.stats.index
```

---

## 4. COMPONENT CONTRACTS

### Admin Component

```
Component: App\Livewire\Admin\YouTube\Stats\YouTubeStatsIndex
Namespace: App\Livewire\Admin\YouTube\Stats

Properties:
  - $channelStats (?YouTubeChannelStat) — cached channel data or null
  - $recentVideos (Collection) — collection of YouTubeVideo models
  - $weeklyComparison (array) — keys: current, previous, deltas
  - $isConfigured (bool) — true if ApiKey for 'youtube' exists with key_value and extra_data.channel_id
  - $isLoading (bool) — true while API refresh is in progress
  - $monthlyRevenue (string) — editable revenue value

Methods:
  - mount()
    Input: none
    Does:
      1. Check ApiKey::where('provider', 'youtube')->first() exists and has extra_data.channel_id
      2. Set $isConfigured accordingly
      3. If configured, load $channelStats, $recentVideos, $weeklyComparison from service (cached data)
      4. If service reports stale data, trigger background refresh
      5. Set $monthlyRevenue from $channelStats->monthly_revenue or ''
    Output: properties populated

  - refreshStats()
    Input: none
    Does:
      1. Set $isLoading = true
      2. Call YouTubeStatsService::refreshFromApi($userId)
      3. Reload all properties from service
      4. Set $isLoading = false
      5. Flash success or error message
    Output: flash message, updated properties

  - updateRevenue()
    Input: none (reads $monthlyRevenue)
    Does:
      1. Validate monthlyRevenue is numeric, min:0
      2. Call YouTubeStatsService::updateMonthlyRevenue($userId, $monthlyRevenue)
      3. Flash success message
    Output: flash message

Validation Rules:
  - monthlyRevenue: nullable|numeric|min:0|max:9999999.99
```

---

## 5. VIEW BLUEPRINTS

### Admin View: Stats Dashboard

```
View: resources/views/livewire/admin/youtube/stats/index.blade.php
Layout: components.layouts.admin
Side: ADMIN
Page title: "YouTube Stats"

Design rules (from CLAUDE.md admin side):
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:

1. Breadcrumb
   Dashboard > YouTube > Stats

2. Page Header
   - Title: "YouTube Stats" (h1, font-mono uppercase tracking-wider)
   - Right side: "Refresh" button (bg-primary hover:bg-primary-hover, with refresh icon)
   - Show loading spinner on button when $isLoading is true

3. Not Configured State (shown when $isConfigured is false)
   - Card with warning icon
   - Message: "YouTube API key not configured. Add your Google API key and channel ID in Settings > API Keys."
   - Link button to admin.settings.api-keys route

4. Channel Header Card (shown when $isConfigured and $channelStats exists)
   - Channel thumbnail (rounded-full, 48px), channel title, "Last updated: X minutes ago"
   - Subtle bg-dark-800 card

5. Stats Grid (4 columns on desktop, 2 on mobile)
   Card 1: Subscribers
     - Icon: users icon with bg-primary/10 text-primary-light
     - Value: formatted number (e.g., "12.4K")
     - Weekly delta: green up arrow or red down arrow with percentage
   Card 2: Total Views
     - Icon: eye icon with bg-blue-500/10 text-blue-400
     - Value: formatted number
     - Weekly delta
   Card 3: Watch Time
     - Icon: clock icon with bg-fuchsia-500/10 text-fuchsia-400
     - Value: formatted hours (e.g., "1,234 hrs")
     - Weekly delta
   Card 4: Revenue (Monthly)
     - Icon: dollar icon with bg-emerald-500/10 text-emerald-400
     - Value: editable inline — click to edit, blur or Enter to save
     - Label: "Monthly estimate"
     - Uses wire:model="monthlyRevenue" with wire:blur="updateRevenue"

6. Weekly Comparison Card
   - Title: "This Week vs Last Week" (font-mono uppercase tracking-wider)
   - Two-column comparison: metric name | this week | last week | change
   - Rows: Subscribers gained, Views, Watch hours
   - Change column: green text + up arrow for positive, red text + down arrow for negative
   - If no previous week data: show "No data for last week yet"

7. Recent Videos Table
   - Title: "Recent Videos" (font-mono uppercase tracking-wider)
   - Table columns: Thumbnail (small 80px), Title, Published, Views, Likes, Comments, Duration
   - Table uses dark-800 bg, dark-700 header bg, dark-700/50 row borders
   - Thumbnail is clickable (opens YouTube video in new tab)
   - Published date formatted as relative time (e.g., "3 days ago")
   - Numbers formatted with compact notation (e.g., "1.2K")
   - Empty state: "No videos found" with muted text

8. Flash Messages
   - Success: green banner at top
   - Error: red banner at top (e.g., API error, rate limit)
```

---

## 6. VALIDATION RULES

```
Form: Revenue Update (inline edit)
  - monthlyRevenue: nullable|numeric|min:0|max:9999999.99
```

No other forms exist — this is a read-only dashboard with one inline editable field.

---

## 7. EDGE CASES & BUSINESS RULES

### API Key Not Configured
- If no ApiKey record exists for provider `youtube`, show the "not configured" state with link to Settings > API Keys
- If ApiKey exists but `extra_data.channel_id` is missing, show a specific message asking the user to add their channel ID

### API Errors
- If the YouTube API returns an error (invalid key, quota exceeded, network error), flash an error message and keep showing the last cached data
- Never clear cached data on API failure — always show stale data rather than nothing
- Show "Last updated: X" timestamp so user knows data freshness

### Rate Limiting
- YouTube Data API quota is 10,000 units/day
- channels.list (snippet, statistics) = 5 units
- search.list = 100 units
- videos.list (snippet, statistics, contentDetails) = 5 units
- Limit manual refresh to once per 5 minutes (check fetched_at before allowing refresh)
- Show "Please wait X minutes before refreshing" if rate limited

### Weekly Snapshots
- A snapshot is taken for the current week whenever refreshFromApi() is called
- week_start is always Monday of that week (computed via Carbon::now()->startOfWeek())
- If a snapshot already exists for this week, update it (upsert on user_id + week_start)
- Weekly comparison shows current week vs the previous week
- If no previous week snapshot exists, show "No comparison data yet" instead of zeros

### Revenue
- Revenue is manually entered because the YouTube Data API v3 does not expose earnings
- Only the YouTube Analytics API (which requires OAuth) has revenue data, which is out of scope
- Revenue is stored as a nullable decimal — null means "not set", 0 means "no revenue"
- Display "$0.00" when set to 0, display "Not set" when null

### Data Freshness / Auto-Refresh
- Data is considered stale after 6 hours (configurable in service)
- On mount, if data is stale, automatically trigger a refresh
- Show a subtle "Refreshing..." indicator during auto-refresh (same as manual refresh loading state)

### Delete Behavior
- All YouTube tables cascade on user delete (ON DELETE CASCADE)
- No soft deletes — cached data can always be re-fetched
- When refreshing, videos that are no longer in the API response (deleted/private) are removed from the local cache

### Number Formatting
- Use compact notation for large numbers: 1,000 → "1K", 1,500,000 → "1.5M"
- Watch time displayed in hours (converted from minutes if API returns minutes)
- Revenue displayed with currency symbol and 2 decimal places

### Channel ID Storage
- The channel ID is stored in ApiKey's `extra_data` JSON field as `extra_data.channel_id`
- The API key itself is stored in `key_value` (the Google API key)
- The Settings > API Keys page already handles this — no changes needed there

---

## 8. IMPLEMENTATION ORDER

```
1. database/migrations/2026_04_01_600001_create_youtube_channel_stats_table.php
2. database/migrations/2026_04_01_600002_create_youtube_videos_table.php
3. database/migrations/2026_04_01_600003_create_youtube_weekly_snapshots_table.php
4. app/Models/YouTube/YouTubeChannelStat.php
5. app/Models/YouTube/YouTubeVideo.php
6. app/Models/YouTube/YouTubeWeeklySnapshot.php
7. app/Services/YouTubeStatsService.php
8. routes/admin/youtube/stats.php
9. app/Livewire/Admin/YouTube/Stats/YouTubeStatsIndex.php
10. resources/views/livewire/admin/youtube/stats/index.blade.php
11. resources/views/components/layouts/admin.blade.php (sidebar update — add YouTube group)
12. docs/PROJECT-STATUS.md (update with YouTube module)
```
