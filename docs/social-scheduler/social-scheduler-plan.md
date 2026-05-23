# Social Scheduler — Plan

> Status: **Draft v2 — LinkedIn-only scope. Awaiting review.** Iterate this file until aligned before any implementation begins.
>
> _v2 change:_ Facebook removed from scope (verification overhead too high for v1). Single-platform schema simplification. Architecture leaves room to add Facebook/Instagram/Twitter later.

---

## 1. Context

### The problem
The portfolio site exists to make you visible, but it's a passive surface — recruiters, peers, and clients only see it if they visit it. Reach comes from posting where the audience already is: **LinkedIn**. Writing those posts manually, twice a day, is the bottleneck you want to remove.

### The idea
Add a new **Social Scheduler** module in the admin panel where you:

1. Write a short post (title, body/caption, optional code/snippet field).
2. Pick a **visual template** (HTML/CSS layout, like Canva — only the data is dynamic).
3. Schedule a publish time.
4. The system **renders the template to a PNG image**, then **auto-publishes** that image (with a text caption) to your **LinkedIn personal profile**.

Target cadence: 2 posts/day. Hostinger cron drives the schedule. No queue worker required.

### Why this design
- Image posts on LinkedIn outperform plain text in most categories — Canva-style cards are the whole reason this isn't just "send a string."
- Mirrors the **resume builder** pattern you already use: shared plain-CSS template Blade, rendered identically for preview and for the exported artifact ([[feedback-resume-preview-pdf-single-source]]).
- Stays inside what shared hosting can run: DomPDF (installed) + Imagick (PHP ext) for HTML→PDF→PNG. No headless Chrome, no Node, no external image API in v1.
- LinkedIn-only first: zero approval process, ~15 min to set up developer access. Other platforms can be added later as separate publisher classes without schema migrations.

### What's explicitly out of scope (for now)
- **Facebook, Instagram, Twitter/X, Threads, Mastodon** — additive later; architecture leaves room but none ship in v1.
- Public on-site feed (`/posts` page on the portfolio itself) — you opted out for v1.
- Image uploads inside the post body — templates are the only visual source.
- AI-assisted drafting — Phase 3 idea.

---

## 2. Feasibility on Hostinger shared hosting (Single / Premium)

| Capability | Verdict | Notes |
|---|---|---|
| Cron jobs | ✅ Yes | Single/Premium minimum interval = **15 minutes**. Posts are scheduled to `:00 :15 :30 :45` slots. Business+ allows 1-min cron — upgrade later if you want exact-minute precision. |
| HTML → PDF (DomPDF) | ✅ Yes | Already installed (`barryvdh/laravel-dompdf`). |
| PDF → PNG (Imagick) | ⚠️ **Needs verification** | PHP `imagick` extension + Ghostscript must be present. Pre-flight check in §11. |
| Long-running queue worker | ❌ No | Not allowed on shared. Mitigation: publish synchronously inside the cron-fired command. |
| OAuth callback URLs | ✅ Yes | Standard `/admin/social/oauth/linkedin/callback` route. |
| Encrypted token storage | ✅ Yes | Laravel `encrypted` cast on `access_token` column. |
| LinkedIn token refresh | ⚠️ Partial | LinkedIn tokens last 60 days; refresh tokens last 365 days. We'll email you 7 days before access-token expiry and prompt full re-auth before the refresh expires. |

---

## 3. End-to-end architecture (one picture)

```
┌─────────────────┐     1×/day token health check
│ Hostinger cron  │ ─────────────────────────────────┐
│   */15 min      │                                  │
└────────┬────────┘                                  ▼
         │                                  ┌────────────────────┐
         │ php artisan schedule:run         │ TokenHealthService │
         │                                  └────────────────────┘
         ▼
┌──────────────────────────┐
│ bootstrap/app.php        │  ::command('social:publish-due')
│ withSchedule()           │      ->everyFifteenMinutes();
└────────┬─────────────────┘
         ▼
┌─────────────────────────────────┐
│ PublishDueSocialPosts (cmd)     │
└────────┬────────────────────────┘
         ▼
┌─────────────────────────────────────────────────────────────────────┐
│ SocialPublishingService::publishDue()                              │
│                                                                     │
│  for each ScheduledPost where status=scheduled & scheduled_at<=now: │
│    ├─ SocialImageRenderService::render($post)                       │
│    │     Blade template + data  ─► DomPDF ─► Imagick ─► 1200×1200 PNG│
│    │     cached at storage/app/public/social-posts/{id}.png         │
│    │                                                                 │
│    ├─ LinkedInPublisher::post($post, $image)                        │
│    │     uses access_token from PlatformAccount                     │
│    │     saves linkedin_post_id + linkedin_post_url on the post     │
│    │                                                                 │
│    └─ post.status ← posted | failed                                 │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 4. New module layout (mirrors CLAUDE.md conventions)

A new module group **`Social`** under the admin sidebar, with three features: **Scheduler**, **Templates**, **Connections**.

```
app/
├── Console/Commands/
│   └── PublishDueSocialPosts.php                ← cron entrypoint
│
├── Http/Controllers/
│   └── SocialOAuthController.php                ← OAuth redirect + callback
│
├── Livewire/Admin/Social/
│   ├── Scheduler/
│   │   ├── PostIndex.php
│   │   └── PostForm.php
│   ├── Templates/
│   │   └── TemplateIndex.php                    ← gallery of available templates
│   └── Connections/
│       └── ConnectionsIndex.php                 ← connect/disconnect LinkedIn
│
├── Models/Social/
│   ├── ScheduledPost.php
│   └── PlatformAccount.php                      ← stored OAuth tokens
│
└── Services/
    ├── SocialPostService.php                    ← CRUD on ScheduledPost
    ├── SocialPublishingService.php              ← orchestrates the publisher
    ├── SocialImageRenderService.php             ← Blade → PDF → PNG
    ├── SocialConnectionService.php              ← OAuth start/exchange/refresh
    └── LinkedInPublisher.php                    ← LinkedIn UGC posts API

routes/admin/social/
├── scheduler.php
├── templates.php
└── connections.php

resources/views/
├── livewire/admin/social/
│   ├── scheduler/
│   │   ├── index.blade.php
│   │   └── form.blade.php
│   ├── templates/
│   │   └── index.blade.php
│   └── connections/
│       └── index.blade.php
│
└── social-posts/templates/                      ← shared by preview AND PDF→PNG
    ├── _styles.blade.php                        ← single plain-CSS source
    ├── tech-tip.blade.php
    ├── feature-note.blade.php
    ├── hack-of-the-day.blade.php
    └── code-snippet.blade.php

database/migrations/
├── YYYY_MM_DD_create_scheduled_posts_table.php
└── YYYY_MM_DD_create_platform_accounts_table.php
```

**Notes on convention compliance:**
- Livewire components nested as `Admin/[ModuleGroup]/[Feature]/` ✓
- Views mirror Livewire path exactly ✓
- Models grouped under `Models/Social/` (2 models — subfolder per CLAUDE.md rule) ✓
- One service per concern, all flat in `app/Services/` ✓
- Routes per-feature under `routes/admin/social/` ✓
- Template files live in `resources/views/social-posts/templates/` and use **plain CSS only** (no Tailwind) — same rule as the resume builder ([[feedback-resume-preview-pdf-single-source]]).

**Forward compatibility note:** when Facebook/Instagram/Twitter are added later, each gets its own `XxxPublisher.php` service. The schema gains per-platform status columns via a new migration. No structural rewrite.

---

## 5. Database schema

### `scheduled_posts`
| Column | Type | Notes |
|---|---|---|
| `id` | bigIncrements | |
| `title` | varchar(255) | admin-only label |
| `body` | text | the caption text sent with the image |
| `template_slug` | varchar(50) | e.g. `tech-tip` — must match a Blade file |
| `template_data` | json nullable | extra fields some templates need (code snippet, takeaway line, etc.) |
| `scheduled_at` | timestamp indexed | rounded to `:00/:15/:30/:45` in UI |
| `status` | varchar(20) indexed | `draft \| scheduled \| publishing \| posted \| failed` |
| `rendered_image_path` | varchar(255) nullable | cached PNG path; re-renders if body or template change |
| `linkedin_post_id` | varchar(255) nullable | URN of the published post |
| `linkedin_post_url` | varchar(500) nullable | live permalink |
| `linkedin_error` | text nullable | last failure message |
| `linkedin_attempts` | unsignedTinyInteger default 0 | |
| `linkedin_last_attempted_at` | timestamp nullable | |
| `created_at / updated_at` | timestamps | |

### `platform_accounts`  (1 row per connected platform)
| Column | Type | Notes |
|---|---|---|
| `id` | bigIncrements | |
| `platform` | varchar(20) **unique** | `linkedin` (more allowed later) |
| `account_label` | varchar(255) | display name shown in admin |
| `remote_account_id` | varchar(255) | LinkedIn URN |
| `access_token` | text | **encrypted cast** |
| `refresh_token` | text nullable | **encrypted cast** |
| `token_expires_at` | timestamp | |
| `scope` | varchar(500) | granted scopes |
| `last_health_check_at` | timestamp nullable | |
| timestamps | | |

---

## 6. Admin UI — ASCII mockups

### 6.1 Scheduler index (`/admin/social/scheduler`)

```
┌──────────────────────────────────────────────────────────────────────────────┐
│ Admin > Social > Scheduler                                                   │
├──────────────────────────────────────────────────────────────────────────────┤
│  SCHEDULED POSTS                                            [ + NEW POST ]   │
│                                                                              │
│  ┌────────────────────────────────────────────────────────────────────────┐ │
│  │  [Search title/body...]   Status [All ▼]                               │ │
│  │                                            LinkedIn: 🟢 connected      │ │
│  └────────────────────────────────────────────────────────────────────────┘ │
│                                                                              │
│  ┌──────┬───────────────────────────┬──────────┬───────────┬─────────────┐ │
│  │THUMB │ TITLE / SNIPPET           │ TEMPLATE │ SCHEDULED │ STATUS      │ │
│  ├──────┼───────────────────────────┼──────────┼───────────┼─────────────┤ │
│  │ [▣]  │ Async PHP traps           │ TechTip  │ Today     │ ✓ POSTED    │ │
│  │      │ Three subtle gotchas...   │          │ 09:00     │ [view ↗]    │ │
│  ├──────┼───────────────────────────┼──────────┼───────────┼─────────────┤ │
│  │ [▣]  │ Laravel 12 Pulse          │ Feature  │ Today     │ ⏱ SCHEDULED │ │
│  │      │ Why it replaces Telesc... │ Note     │ 17:00     │             │ │
│  ├──────┼───────────────────────────┼──────────┼───────────┼─────────────┤ │
│  │ [▣]  │ vim-mode in VS Code       │ Hack     │ Tomorrow  │ ▭ DRAFT     │ │
│  │      │ 5-line config that…       │          │ 09:00     │             │ │
│  ├──────┼───────────────────────────┼──────────┼───────────┼─────────────┤ │
│  │ [▣]  │ git rerere                │ TechTip  │ Yesterday │ ✗ FAILED    │ │
│  │      │ Reuse recorded conflict…  │          │ 17:00     │ [retry ↻]   │ │
│  └──────┴───────────────────────────┴──────────┴───────────┴─────────────┘ │
│                                                          « 1 2 3 »          │
│                                                                              │
│  Legend:  ⏱ scheduled   ✓ posted   ✗ failed (click ↻ to retry)   ▭ draft   │
└──────────────────────────────────────────────────────────────────────────────┘
```

**Row actions (hover):** Edit · Duplicate · Delete · Publish now (drafts only) · Retry (failed only) · View on LinkedIn (posted only)

### 6.2 Post form (`/admin/social/scheduler/create` and `/{id}/edit`)

```
┌────────────────────────────────────────────────────────────────────────────────┐
│ Admin > Social > Scheduler > New Post                                          │
├──────────────────────────────────────┬────────────────────────────────────────┤
│  LEFT — COMPOSE                      │  RIGHT — LIVE PREVIEW                  │
│                                      │                                         │
│  Title (admin label, not posted)     │   ┌─────────────────────────────────┐  │
│  ┌────────────────────────────────┐  │   │                                  │  │
│  │ Async PHP traps                │  │   │   [ RENDERED TEMPLATE IMAGE ]    │  │
│  └────────────────────────────────┘  │   │                                  │  │
│                                      │   │   ┌─ Tech Tip ──────────────┐    │  │
│  Body / caption (posted as text)     │   │   │ Async PHP traps          │    │  │
│  ┌────────────────────────────────┐  │   │   │                          │    │  │
│  │ Three subtle gotchas in Swoole │  │   │   │ Three subtle gotchas in  │    │  │
│  │ runtime when you forget that   │  │   │   │ Swoole runtime when you  │    │  │
│  │ workers persist…               │  │   │   │ forget that workers      │    │  │
│  │                                │  │   │   │ persist…                 │    │  │
│  │ #php #async #swoole            │  │   │   │                          │    │  │
│  └────────────────────────────────┘  │   │   │  — usmaniqbal.dev        │    │  │
│                                      │   │   └─────────────────────────┘    │  │
│  Template                            │   │                                  │  │
│  ┌────────────────────────────────┐  │   └─────────────────────────────────┘  │
│  │ TechTip ▼      [ Browse all ]  │  │     ↑ This image is what gets posted   │
│  └────────────────────────────────┘  │       to your LinkedIn feed            │
│                                      │                                         │
│  Template fields (template-specific) │   Caption preview (text below image):  │
│   Code snippet                       │   ┌─────────────────────────────────┐  │
│   ┌──────────────────────────────┐   │   │ Three subtle gotchas in Swoole  │  │
│   │ swoole_set(['worker_num'=>4])│   │   │ runtime when you forget that    │  │
│   └──────────────────────────────┘   │   │ workers persist…                │  │
│                                      │   │                                  │  │
│  Schedule                            │   │ #php #async #swoole              │  │
│  ┌────────────────────────────────┐  │   └─────────────────────────────────┘  │
│  │ 2026-05-25  ▼    09:00 ▼       │  │                                         │
│  └────────────────────────────────┘  │                                         │
│  Slots: :00 :15 :30 :45              │                                         │
│                                      │                                         │
│  Publishing to: 🅻 LinkedIn 🟢       │                                         │
│                                      │                                         │
│  ──────────────────────────────────  │                                         │
│  [Cancel]  [Save Draft]  [Schedule]  │                                         │
└──────────────────────────────────────┴────────────────────────────────────────┘
```

The preview rerenders live (debounced) as you type — uses the same Blade partial + `_styles.blade.php` that the publishing pipeline will use, so what you see is exactly what posts. (Same trick as the resume builder.)

### 6.3 Connections page (`/admin/social/connections`)

```
┌──────────────────────────────────────────────────────────────────────────────┐
│ Admin > Social > Connections                                                 │
├──────────────────────────────────────────────────────────────────────────────┤
│  CONNECTED ACCOUNTS                                                          │
│                                                                              │
│  ┌────────────────────────────────────────────────────────────────────────┐ │
│  │  🅻  LINKEDIN  •  Personal Profile                                     │ │
│  │      Connected as: Usman Iqbal (linkedin.com/in/usman-iqbal)           │ │
│  │      Access token expires: 2026-07-15  (53 days)     🟢 Healthy        │ │
│  │      Refresh token expires: 2027-05-15  (357 days)                     │ │
│  │      Last published: 2026-05-22 17:00 UTC                              │ │
│  │      [ Reconnect ]   [ Disconnect ]                                    │ │
│  └────────────────────────────────────────────────────────────────────────┘ │
│                                                                              │
│  Access tokens auto-refresh silently using the refresh token. You'll get    │
│  an email reminder 7 days before the refresh token itself expires (yearly). │
└──────────────────────────────────────────────────────────────────────────────┘
```

States the badge can show:
- 🟢 **Healthy** — refresh token >7 days from expiry
- 🟡 **Expiring soon** — refresh token ≤7 days
- 🔴 **Expired / revoked** — needs reconnect; scheduler pauses publishing
- ⚪ **Not connected** — only the "Connect LinkedIn" CTA shows

### 6.4 Templates gallery (`/admin/social/templates`)

```
┌──────────────────────────────────────────────────────────────────────────────┐
│ Admin > Social > Templates                                                   │
├──────────────────────────────────────────────────────────────────────────────┤
│  TEMPLATES                                                                   │
│  Each template is a Blade file. Same data renders differently per template.  │
│  Add more by dropping a file in `resources/views/social-posts/templates/`.   │
│                                                                              │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐         │
│  │   ▣ ▣ ▣     │  │   ▣ ▣ ▣     │  │   ▣ ▣ ▣     │  │   ▣ ▣ ▣     │         │
│  │             │  │             │  │             │  │             │         │
│  │  [render]   │  │  [render]   │  │  [render]   │  │  [render]   │         │
│  │             │  │             │  │             │  │             │         │
│  │  Tech Tip   │  │  Feature    │  │  Hack of    │  │  Code       │         │
│  │             │  │  Note       │  │  the Day    │  │  Snippet    │         │
│  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘         │
│                                                                              │
│  Each preview is rendered with placeholder data so you can see the layout.  │
└──────────────────────────────────────────────────────────────────────────────┘
```

### 6.5 Sidebar entry

```
…existing sidebar items…
─────────────────────────
PORTFOLIO        ▾
  Profile
  Skills
  …
─────────────────────────
SOCIAL           ▾          ← NEW module group (collapsible)
  Scheduler                 ← active when route matches admin.social.scheduler.*
  Templates
  Connections
─────────────────────────
Blogging                    ← unchanged
```

Per CLAUDE.md sidebar rule: features always live under their parent module group, never as standalone root items.

---

## 7. The template system (Canva-like layer)

### 7.1 How a template is defined

A template is **one Blade file** in `resources/views/social-posts/templates/`. It receives a `$post` object and may render anything. All styling comes from a single shared partial `_styles.blade.php` — **plain CSS only**, no Tailwind, exactly like the resume builder rule ([[feedback-resume-preview-pdf-single-source]]).

```blade
{{-- resources/views/social-posts/templates/tech-tip.blade.php --}}
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  @include('social-posts.templates._styles')
</head>
<body class="tpl-tech-tip">
  <div class="card">
    <div class="badge">TECH TIP</div>
    <h1>{{ $post->title }}</h1>
    <div class="body">{!! nl2br(e($post->body)) !!}</div>
    @if(!empty($post->template_data['code']))
      <pre class="snippet">{{ $post->template_data['code'] }}</pre>
    @endif
    <div class="footer">usmaniqbal.dev</div>
  </div>
</body>
</html>
```

### 7.2 Template registry

A tiny PHP array (`config/social-templates.php`) lists available templates so the admin form can show a dropdown without scanning the filesystem at runtime:

```php
return [
  'tech-tip'        => ['label' => 'Tech Tip',        'fields' => ['code']],
  'feature-note'    => ['label' => 'Feature Note',    'fields' => []],
  'hack-of-the-day' => ['label' => 'Hack of the Day', 'fields' => []],
  'code-snippet'    => ['label' => 'Code Snippet',    'fields' => ['code', 'language']],
];
```

`fields` drives which extra inputs appear in the form under the body textarea (stored in `template_data` JSON).

### 7.3 Ship-with templates (v1)

Four templates to start — you can drop in more as Blade files without code changes:

1. **Tech Tip** — bold title, body, optional code line, footer.
2. **Feature Note** — "what's new in X" headline + bulleted body.
3. **Hack of the Day** — short punchy headline + body, big emoji accent.
4. **Code Snippet** — body on top, syntax-highlighted code block below (highlighting baked in via PHP `highlight_string` — DomPDF-safe).

All four target the same canvas: **1200 × 1200 px** (LinkedIn-recommended square aspect).

---

## 8. Image rendering pipeline (`SocialImageRenderService`)

```
render(ScheduledPost $post) : string  // returns path
  │
  ├─ if $post->rendered_image_path exists and is fresh → return it (cache)
  │
  ├─ $html = view("social-posts.templates.{$post->template_slug}", ['post'=>$post])
  │            ->render();
  │
  ├─ $pdf = Pdf::loadHTML($html)
  │            ->setPaper([0, 0, 1200, 1200])      // points; matches CSS canvas
  │            ->output();                          // raw PDF bytes
  │
  ├─ $im = new \Imagick();
  │   $im->setResolution(150, 150);                 // 150 DPI → crisp PNG
  │   $im->readImageBlob($pdf);
  │   $im->setIteratorIndex(0);                     // first page only
  │   $im->setImageFormat('png');
  │   $im->setImageBackgroundColor('white');
  │   $im->setImageAlphaChannel(\Imagick::ALPHACHANNEL_REMOVE);
  │   $im->resizeImage(1200, 1200, \Imagick::FILTER_LANCZOS, 1);
  │
  ├─ Storage::disk('public')->put($path, $im->getImageBlob())
  │     // $path = "social-posts/{$post->id}.png"
  │
  └─ $post->update(['rendered_image_path' => $path])
     return Storage::disk('public')->path($path);
```

**Cache invalidation:** delete `rendered_image_path` whenever `body`, `template_slug`, or `template_data` changes (handled in `SocialPostService::update`).

**DomPDF gotchas the templates must respect:**
- No flexbox / no grid — use `table` + `display: table-cell` or absolute positioning.
- Web fonts require explicit registration via `dompdf_config.custom.php`. For v1 use a font already on the system or embed via `@font-face` with a local TTF in `storage/fonts/`.
- Gradients support is patchy — flat color blocks render best.
- Use inline images via base64 if a logo/avatar is needed (file paths can be flaky).

---

## 9. Publishing pipeline — LinkedIn

LinkedIn image posts are a 3-step dance:

```
LinkedInPublisher::post(ScheduledPost $post, string $imagePath)
  │
  ├─ Step 1 — register the asset
  │   POST https://api.linkedin.com/v2/assets?action=registerUpload
  │   Body: { registerUploadRequest: { owner: "urn:li:person:{id}",
  │           recipes: ["urn:li:digitalmediaRecipe:feedshare-image"],
  │           serviceRelationships: [...] } }
  │   → returns uploadUrl + asset URN
  │
  ├─ Step 2 — PUT the bytes
  │   PUT $uploadUrl with image binary
  │
  └─ Step 3 — create the post
      POST https://api.linkedin.com/v2/ugcPosts
      Body: { author: "urn:li:person:{id}",
              lifecycleState: "PUBLISHED",
              specificContent: {
                "com.linkedin.ugc.ShareContent": {
                  shareCommentary: { text: $post->body },
                  shareMediaCategory: "IMAGE",
                  media: [{ status: "READY", media: $assetUrn }]
                }},
              visibility: { "com.linkedin.ugc.MemberNetworkVisibility": "PUBLIC" }}
      → returns post URN
      → linkedin_post_url = "https://www.linkedin.com/feed/update/{urn}"
```

**Required:** LinkedIn Developer App + products **"Sign In with LinkedIn using OpenID Connect"** + **"Share on LinkedIn"**. Scope `w_member_social` is auto-approved (no review). See `platform-api-requirements.md` for the full setup checklist.

### Failure handling
- Wrap the publish call in try/catch.
- On exception → write `linkedin_error`, `linkedin_attempts++`, `linkedin_last_attempted_at = now()`, post `status = failed`.
- On success → set `linkedin_post_id`, `linkedin_post_url`, post `status = posted`.
- Retry button on the index re-runs the publish for failed posts.

---

## 10. OAuth flow — LinkedIn

```
1. ADMIN clicks "Connect LinkedIn" on /admin/social/connections
2. SocialConnectionService::startLinkedIn() redirects to
   https://www.linkedin.com/oauth/v2/authorization?
     response_type=code
     &client_id={LINKEDIN_CLIENT_ID}
     &redirect_uri=https://usmaniqbal.dev/admin/social/oauth/linkedin/callback
     &state={csrf_signed}
     &scope=openid%20profile%20w_member_social
3. User approves → redirected back with ?code=...
4. SocialOAuthController@linkedinCallback:
     - validates state
     - POST /oauth/v2/accessToken (exchange code for access + refresh tokens)
     - GET /v2/userinfo (get sub = the person URN id, name)
     - upsert PlatformAccount('linkedin', access_token, refresh_token,
       token_expires_at = now + expires_in seconds)
5. redirect /admin/social/connections with flash success
```

### Token health & refresh

A daily scheduled task `TokenHealthService::checkAll()`:
- For each `PlatformAccount`, if access token is within 24 hours of expiry → silently refresh using the refresh token.
- If the refresh token itself is within 7 days of expiry → email reconnect reminder.
- If revoked / both expired → mark unhealthy; scheduler pauses publishing.

---

## 11. Pre-flight checks (do these on Hostinger before coding starts)

| Check | Command / where | Required outcome |
|---|---|---|
| Imagick installed | `php -m \| grep -i imagick` | `imagick` listed |
| Ghostscript installed | `which gs` | a path returned |
| DomPDF works | `php artisan tinker → Barryvdh\DomPDF\Facade\Pdf::loadHTML('<h1>hi</h1>')->output();` | non-empty bytes |
| Cron available + minimum interval | Hostinger control panel → Cron Jobs | confirm 15-min minimum on your plan |
| HTTPS callback URL | DNS / SSL | `https://usmaniqbal.dev/admin/social/oauth/linkedin/callback` reachable |
| LinkedIn Developer account | developer.linkedin.com | App created, both products attached (see `platform-api-requirements.md`) |
| Storage symlink | `php artisan storage:link` | `/storage` → `storage/app/public` |

**If Imagick is NOT available** → fallback options (decide before coding):
1. Ask Hostinger support to enable it (often free).
2. Switch to an external rendering API (htmlcsstoimage.com ~$14/mo).
3. Use `gd` + simpler image composition (loses HTML/CSS template flexibility — defeats the design).

---

## 12. Cron entry on Hostinger

In Hostinger control panel → Advanced → Cron Jobs, add:

```
*/15 * * * *   cd /home/uXXXXXXXX/domains/usmaniqbal.dev/public_html && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

Inside `bootstrap/app.php`:

```php
->withSchedule(function (Schedule $schedule) {
    $schedule->command('social:publish-due')->everyFifteenMinutes()->withoutOverlapping();
    $schedule->command('social:token-health')->dailyAt('08:00');
})
```

---

## 13. Phase plan

### Phase 1 — MVP (ship in days, no OAuth UI yet)
- Migrations, models, `SocialPostService` CRUD
- 4 Blade templates + `_styles.blade.php`
- `SocialImageRenderService` (DomPDF → Imagick → PNG)
- Connections page **with manual token paste** (skip OAuth flow; paste tokens from LinkedIn Developer console)
- `LinkedInPublisher`
- `PublishDueSocialPosts` command + cron schedule
- "Publish now" button on each draft (manual trigger for debugging)
- Sidebar entry

**Acceptance:** you can draft a post, hit "Publish now," and see it land on your LinkedIn feed within seconds.

### Phase 2 — Polish & UX
- Real OAuth flow (replaces manual token paste)
- Token auto-refresh + expiry email reminders (`social:token-health` daily)
- Retry button for failed posts on the index
- Live preview re-render on form edit (debounced)
- Templates gallery page

### Phase 3 — Future ideas (not committed)
- Add Facebook Page publisher (once you've completed Meta's Business Verification dance)
- Add Instagram + Twitter/X publishers (new `XxxPublisher.php` + schema columns)
- AI-assisted drafting ("write a tech tip about X")
- Analytics: impressions/clicks per post
- Auto-cross-post to a public `/posts` feed on the portfolio

---

## 14. End-to-end verification plan

After Phase 1 ships:

1. **Connection** — Paste LinkedIn token in the connections page; badge goes 🟢.
2. **Create a draft** — Title "Test post," body "hello world #test," template TechTip.
3. **Preview check** — Right pane shows the rendered card; download the PNG locally and confirm 1200×1200.
4. **Publish now** — Click; watch `storage/logs/laravel.log`:
   - "Rendering image for post 1"
   - "Posting to linkedin" → 201 Created, URN returned
5. **Verify on LinkedIn** — Open your LinkedIn feed; the post is visible with the image and caption.
6. **Verify DB** — `scheduled_posts.linkedin_post_url` populated and clickable from the index.
7. **Scheduled run** — Create another post, schedule it for the next `:00 / :15 / :30 / :45` slot 5+ min out. Wait for cron to fire. Confirm it publishes automatically.
8. **Failure path** — Temporarily corrupt the LinkedIn token (edit DB row); publish a post; should mark `failed` with an `linkedin_error` message. Restore token → click retry → flips to `posted`.

---

## 15. Open questions for you

These choices change the plan and I want your call before writing code:

1. **Cron precision** — On Single/Premium (15-min min) as confirmed earlier. UI will round to `:00 :15 :30 :45`. Confirm that's fine?
2. **Manual token paste in v1?** — Phase 1 above skips OAuth UI to ship faster. OK to start there, or do you want OAuth from day one?
3. **Canvas size** — 1200 × 1200 square confirmed?
4. **Template fields** — I proposed `code` and `language` for code-snippet templates. Want any other reusable fields (e.g. CTA URL, takeaway line, footer text)?
5. **Hashtag handling** — Caption currently includes hashtags inline. Want a separate "tags" field that the publisher appends automatically (lets templates show them differently)?
6. **Templates first list** — Tech Tip / Feature Note / Hack of the Day / Code Snippet — any to add or drop for v1?

---

*This plan is intentionally a living document. Mark up anything that's wrong, missing, or in the wrong order — I'll iterate the file before any code is written.*
