# Blog & Post — Plan

> Status: **Draft v1 — awaiting review.** Iterate this file until approved before any implementation begins.

> Companion plans in this folder will cover OAuth (later) and a future migration off the old `social` and `blog` modules.

---

## 1. Context

### Why a new module?
The existing **Social Scheduler** module proved that LinkedIn auto-publishing works — but only for post types that LinkedIn's API gates lightly. The PDF carousel format we built is **blocked at LinkedIn's API permission level** (requires Marketing Developer Platform / Community Management API approval — not realistically attainable for a personal portfolio app).

What we **can** publish with the existing `w_member_social` scope:
- Text-only posts
- Single image posts
- Single video posts
- Article re-shares (URL + LinkedIn auto-fetches og: preview)

### The new module's purpose
Build a clean, scope-aware publishing surface that supports exactly those 4 post types. Replace both the existing `social` and `blog` modules eventually — but leave them untouched until this one is shipped and proven.

### Strict scope rules (per user direction)
- **Do not modify** `app/Livewire/Admin/Social/**`, `app/Livewire/Admin/Portfolio/Blog/**`, `app/Services/SocialPostService.php`, `app/Services/SocialPdfRenderService.php`, `app/Services/SocialPublishingService.php`, `app/Services/LinkedInPublisher.php`, or any social-module/blog-module view/route/migration.
- **Do reuse** the existing `platform_accounts` table (the LinkedIn token is already saved there — no re-paste needed).
- **Do reference** the social module's `LinkedInPublisher` for the 3-step image/video upload flow and the `ugcPosts` payload shape — but write fresh code.

---

## 2. End-state at a glance

```
Sidebar
├── … existing items …
├── Portfolio                (untouched)
├── Social         ▾         (untouched — will be removed later)
├── Blogging                 (untouched — will be removed later)
└── Blog & Post              ← NEW single top-level link
        │
        └── /admin/blog-and-post  →  PostIndex (the only page in this module)
                │
                ├── + New Post button → opens TYPE-PICKER MODAL
                │       ├── Text only
                │       ├── Text + Image
                │       ├── Text + Video
                │       └── Article share
                │
                ├── Tabular list of all posts (mixed types, filter by type)
                ├── Edit / Delete / Publish Now / Retry actions per row
                └── Status badges (draft / scheduled / posted / failed)
```

---

## 3. Module layout (follows `CLAUDE.md` conventions)

```
app/
├── Livewire/Admin/BlogAndPost/
│   ├── PostIndex.php                 ← /admin/blog-and-post — single listing page + modal trigger
│   ├── TextPostForm.php              ← /admin/blog-and-post/create/text  and  /{post}/edit
│   ├── ImagePostForm.php             ← /admin/blog-and-post/create/image and /{post}/edit
│   ├── VideoPostForm.php             ← /admin/blog-and-post/create/video and /{post}/edit
│   └── ArticlePostForm.php           ← /admin/blog-and-post/create/article and /{post}/edit
│
├── Models/BlogAndPost/
│   └── Post.php                      ← single Eloquent model, type-aware
│
└── Services/
    ├── BlogAndPostService.php        ← CRUD (create/update/delete/schedule/markAsDraft)
    └── BlogAndPostPublisher.php      ← sends Post → LinkedIn (branches by type internally)

resources/views/
└── livewire/admin/blog-and-post/
    ├── index.blade.php               ← table + new-post modal
    ├── text-form.blade.php
    ├── image-form.blade.php
    ├── video-form.blade.php
    └── article-form.blade.php

routes/admin/blog-and-post/
└── posts.php                         ← all 9 routes (index + 4×create + 4×edit)

database/migrations/
└── {timestamp}_create_posts_table.php
```

**Note on the flatter layout:** the CLAUDE.md convention `Admin/[ModuleGroup]/[Feature]/` assumes the module group contains multiple features. This module has a single feature — managing posts — so the Feature subfolder would be redundant nesting (`Admin/BlogAndPost/Posts/PostIndex.php` reads worse than `Admin/BlogAndPost/PostIndex.php`). Deliberate flatter layout, called out here so it's not mistaken for sloppiness.

---

## 4. Database schema — single `posts` table

| Column | Type | Notes |
|---|---|---|
| `id` | bigIncrements | |
| `title` | varchar(255) | admin label (not published) |
| `type` | varchar(20) indexed | enum: `text` \| `image` \| `video` \| `article` |
| `caption` | text | the post body sent to LinkedIn |
| `hashtags` | text nullable | space-separated; publisher prefixes `#` if missing |
| `meta` | json nullable | type-specific data (see below) |
| `status` | varchar(20) indexed | `draft` \| `scheduled` \| `publishing` \| `posted` \| `failed` |
| `scheduled_at` | timestamp nullable indexed | rounded to 15-min slots |
| `linkedin_post_id` | varchar(255) nullable | URN after publish |
| `linkedin_post_url` | varchar(500) nullable | permalink |
| `linkedin_error` | text nullable | last failure message |
| `linkedin_attempts` | unsignedTinyInteger default 0 | |
| `linkedin_last_attempted_at` | timestamp nullable | |
| timestamps | | |

### `meta` JSON shape per type

| Type | `meta` contents |
|---|---|
| `text` | `null` (no extra fields needed) |
| `image` | `{"image_path": "blog-and-post/images/{post}.png"}` |
| `video` | `{"video_path": "blog-and-post/videos/{post}.mp4"}` |
| `article` | `{"url": "https://your-blog/post-slug"}` |

Stored as JSON so we don't need separate columns per type. Model casts `meta` to array.

### Reused, not duplicated
- `platform_accounts` table — **reused as-is** from the social module. The new module reads the LinkedIn account via `PlatformAccount::where('platform','linkedin')->first()`.

---

## 5. Sidebar entry

A new **single top-level link** below "Blogging", titled **"Blog & Post"**, using a paper-airplane / megaphone SVG icon distinct from both the existing Social (share) icon and Blogging (pencil) icon. Active state: `request()->routeIs('admin.blog-and-post.*')`.

Pattern mirrors the existing standalone "Blogging" link — NOT a collapsible group.

---

## 6. Admin UI — ASCII mockups

### 6.1 The listing page (`/admin/blog-and-post`)

```
┌──────────────────────────────────────────────────────────────────────────────┐
│ Admin > Blog & Post                                                          │
├──────────────────────────────────────────────────────────────────────────────┤
│  BLOG & POST                                              [ + NEW POST ]     │
│                                                                              │
│  ┌────────────────────────────────────────────────────────────────────────┐ │
│  │  [Search title/caption...]   Type [All ▼]   Status [All ▼]             │ │
│  │                                          LinkedIn: 🟢 connected         │ │
│  └────────────────────────────────────────────────────────────────────────┘ │
│                                                                              │
│  ┌─────┬─────────────────────┬──────────┬──────────┬──────────┬───────────┐│
│  │ TYPE│ TITLE / SNIPPET     │ MEDIA    │ SCHEDULED│ STATUS   │  ACTIONS  ││
│  ├─────┼─────────────────────┼──────────┼──────────┼──────────┼───────────┤│
│  │  📝  │ Async PHP traps     │ —        │ —        │ ✓ POSTED │ [↗] [✎] ⋯ ││
│  │ TEXT│ Three subtle...     │          │          │          │           ││
│  ├─────┼─────────────────────┼──────────┼──────────┼──────────┼───────────┤│
│  │  🖼  │ Laravel 12 features │ [thumb]  │ Today    │ ⏱ SCHED  │ [✎] ⋯    ││
│  │ IMG │ Quick visual...     │          │ 09:00    │          │           ││
│  ├─────┼─────────────────────┼──────────┼──────────┼──────────┼───────────┤│
│  │  🎬  │ Vim demo            │ 0:32     │ —        │ ▭ DRAFT  │ [✎] ⋯    ││
│  │ VID │ Why I switched...   │          │          │          │           ││
│  ├─────┼─────────────────────┼──────────┼──────────┼──────────┼───────────┤│
│  │  🔗  │ My new blog post    │ ↗ link   │ —        │ ✗ FAILED │ [↻] ⋯    ││
│  │ ART │ Deep dive into...   │          │          │          │           ││
│  └─────┴─────────────────────┴──────────┴──────────┴──────────┴───────────┘│
│                                                          « 1 2 3 »          │
│                                                                              │
│  Type icons:  📝 text   🖼 image   🎬 video   🔗 article                    │
│  Status:      ⏱ scheduled   ✓ posted   ✗ failed (↻ retry)   ▭ draft        │
└──────────────────────────────────────────────────────────────────────────────┘
```

### 6.2 The "New Post" type-picker modal

```
            ╔═══════════════════════════════════════════════╗
            ║   PICK A POST TYPE                       [×]  ║
            ║                                                ║
            ║   ┌────────────────────┐ ┌────────────────────┐║
            ║   │   📝               │ │   🖼               │║
            ║   │                    │ │                    │║
            ║   │   TEXT ONLY        │ │   TEXT + IMAGE     │║
            ║   │   Caption + tags   │ │   Caption + photo  │║
            ║   │                    │ │                    │║
            ║   └────────────────────┘ └────────────────────┘║
            ║                                                ║
            ║   ┌────────────────────┐ ┌────────────────────┐║
            ║   │   🎬               │ │   🔗               │║
            ║   │                    │ │                    │║
            ║   │   TEXT + VIDEO     │ │   ARTICLE SHARE    │║
            ║   │   Caption + clip   │ │   URL + caption    │║
            ║   │                    │ │                    │║
            ║   └────────────────────┘ └────────────────────┘║
            ║                                                ║
            ║                                    [ Cancel ]  ║
            ╚═══════════════════════════════════════════════╝
```

Click a card → navigates to the corresponding form route (e.g. `/admin/blog-and-post/create/text`).

### 6.3 Form layouts (one per type)

All four forms share the **same outer chrome**: breadcrumb, page header, action bar (Cancel / Save Draft / Schedule / Publish Now). They differ only in the middle "type-specific fields" section.

#### Text post

```
┌──────────────────────────────────────────────────────────────────────────────┐
│ Admin > Blog & Post > New Text Post                                          │
├──────────────────────────────────────────────────────────────────────────────┤
│  TITLE  (admin label, not published)                                         │
│  [_________________________________________________________]                 │
│                                                                              │
│  CAPTION  (the body of your post)                                            │
│  ┌────────────────────────────────────────────────────────────────────────┐ │
│  │                                                                         │ │
│  │  Multiline textarea — supports emoji, line breaks, • bullets,          │ │
│  │  inline URLs. LinkedIn renders this as-is.                              │ │
│  │                                                                         │ │
│  └────────────────────────────────────────────────────────────────────────┘ │
│                                                                              │
│  HASHTAGS  (space-separated)                                                 │
│  [_________________________________________________________]                 │
│  Laravel PHP WebDevelopment                                                  │
│                                                                              │
│  SCHEDULE                                                                    │
│  [ 2026-05-25 ▾ ]   [ 09:00 ▾ ]   (15-min slots)                            │
│                                                                              │
│  ──────────────────────────────────────────────────────────────────────      │
│  [ Cancel ]   [ Save Draft ]   [ Publish Now ]   [ Schedule ]                │
└──────────────────────────────────────────────────────────────────────────────┘
```

#### Text + Image post

Same as Text, PLUS:

```
│  IMAGE  (PNG or JPG, max 5 MB, recommended 1200×627 or 1200×1200)            │
│  ┌────────────────────────────────────────────────────────────────────────┐ │
│  │   [drag & drop or click to upload]                                     │ │
│  │   ↓ on upload:                                                         │ │
│  │   ┌──────────────────────────────┐                                     │ │
│  │   │   [thumbnail preview]        │   [ × remove ]                      │ │
│  │   └──────────────────────────────┘                                     │ │
│  └────────────────────────────────────────────────────────────────────────┘ │
```

#### Text + Video post

Same as Text, PLUS:

```
│  VIDEO  (MP4, max 5 GB, max 10 min)                                          │
│  ┌────────────────────────────────────────────────────────────────────────┐ │
│  │   [drag & drop or click to upload]                                     │ │
│  │   ↓ on upload:                                                         │ │
│  │   ┌──────────────────────────────┐                                     │ │
│  │   │   [video preview thumbnail]  │   [ × remove ]                      │ │
│  │   │   00:00 / 00:32              │                                     │ │
│  │   └──────────────────────────────┘                                     │ │
│  └────────────────────────────────────────────────────────────────────────┘ │
```

#### Article share post

Same as Text, PLUS:

```
│  ARTICLE URL  (LinkedIn auto-fetches og: tags to build a preview card)       │
│  [_________________________________________________________]                 │
│  https://usmaniqbal.dev/blog/async-php-traps                                 │
│                                                                              │
│  ℹ  No extra fields — LinkedIn renders the preview from your page's        │
│     og:title, og:description, and og:image meta tags. Make sure your blog   │
│     post has those set.                                                     │
```

---

## 7. Phased build order (5 phases as you described)

### Phase 1 — Foundation
**Goal:** the new module is reachable. Posts table exists. Index page loads (empty). Sidebar shows the new link. No forms yet.

**Files to CREATE**
- `database/migrations/{timestamp}_create_posts_table.php`
- `app/Models/BlogAndPost/Post.php` (with type/status scopes, casts)
- `app/Services/BlogAndPostService.php` (just the CRUD skeleton — methods stubbed)
- `app/Livewire/Admin/BlogAndPost/PostIndex.php` (lists posts, filters, delete; no modal yet)
- `resources/views/livewire/admin/blog-and-post/index.blade.php`
- `routes/admin/blog-and-post/posts.php` (just the index route initially)

**Files to UPDATE**
- `resources/views/components/layouts/admin.blade.php` (add the sidebar link)

**Done when:** navigating to `/admin/blog-and-post` renders an empty-state list page; sidebar link works; can manually insert a post row via tinker and see it listed.

### Phase 2 — Form pages per post type
**Goal:** all 4 form pages work (without preview, without the type-picker modal). User can navigate directly to a form URL and create a post of that type.

**Files to CREATE**
- `app/Livewire/Admin/BlogAndPost/TextPostForm.php`
- `app/Livewire/Admin/BlogAndPost/ImagePostForm.php` (uses `WithFileUploads`)
- `app/Livewire/Admin/BlogAndPost/VideoPostForm.php` (uses `WithFileUploads`)
- `app/Livewire/Admin/BlogAndPost/ArticlePostForm.php`
- `resources/views/livewire/admin/blog-and-post/text-form.blade.php`
- `resources/views/livewire/admin/blog-and-post/image-form.blade.php`
- `resources/views/livewire/admin/blog-and-post/video-form.blade.php`
- `resources/views/livewire/admin/blog-and-post/article-form.blade.php`

**Files to UPDATE**
- `routes/admin/blog-and-post/posts.php` (add 4 create routes + 4 edit routes)
- `app/Services/BlogAndPostService.php` (flesh out create/update with file-storage logic for image/video)

**Done when:** typing each create URL directly opens the right form; saving as draft creates a row with the correct `type` + `meta`; editing pre-populates correctly.

### Phase 3 — Polish (modal + image previews + article fetch)
**Goal:** the listing page becomes the entry point. Type-picker modal works. Image/video have inline previews. Article URL field shows a small preview of what the og: tags will render (admin-side only — uses guzzle to fetch the URL's `<head>`).

**Files to CREATE**
- Possibly a small `OgTagFetcher` service (or inline in ArticlePostForm) to read og: meta tags from a URL for the in-admin preview.

**Files to UPDATE**
- `PostIndex.php` + view: add the "+ New Post" button that triggers the modal; modal markup with 4 type cards; each card links to its create route via `wire:navigate`.
- `ImagePostForm` + view: add thumbnail preview after upload.
- `VideoPostForm` + view: add video preview (HTML5 `<video>` element).
- `ArticlePostForm` + view: add live og: tag preview card on URL paste (debounced fetch).

**Done when:** the full UX from listing → modal → form → save → back-to-list works smoothly. All 4 types have proper visual feedback during editing.

### Phase 4 — Draft / Publish CRUD (no scheduler yet)
**Goal:** CRUD is rock-solid. "Publish Now" works for all 4 types and actually lands a post on LinkedIn. Errors are handled cleanly.

**Files to CREATE**
- `app/Services/BlogAndPostPublisher.php` — the new publisher. Methods:
  - `publish(Post $post): void` — orchestrator; resolves LinkedIn token, branches by type
  - Internal helpers: `publishText()`, `publishImage()`, `publishVideo()`, `publishArticle()` — each uses the appropriate `shareMediaCategory` and (where needed) the 3-step asset upload flow.
- The publisher reuses the SHAPE of `LinkedInPublisher` but is a standalone class (no edits to the social module's version).

**Files to UPDATE**
- `BlogAndPostService.php` — add a `markAsPosted()` and `markAsFailed()` helper.
- All 4 form components: add `publishNow()` action method (saves first, then calls `BlogAndPostPublisher::publish`).
- `PostIndex.php`: add `retry()` action for failed rows.
- All 4 form views + index view: add "Publish Now" button + LinkedIn permalink display + Retry button.

**Done when:** for each post type, you can create a draft, click Publish Now, and see the post live on LinkedIn within seconds. Failures show as a red flash (not a 500). Retry works for failed posts.

### Phase 5 — Scheduler + automation
**Goal:** scheduled posts auto-publish via cron, exactly like the social module's pattern.

**Files to CREATE**
- `app/Console/Commands/PublishDueBlogAndPosts.php` (signature `blog-and-post:publish-due`)
- Possibly `BlogAndPostPublishingOrchestrator` if the orchestration logic outgrows the publisher class.

**Files to UPDATE**
- `bootstrap/app.php` — add another `$schedule->command('blog-and-post:publish-due')->everyFifteenMinutes()->withoutOverlapping()` entry in the existing `withSchedule()` block.

**Done when:** schedule a post for the next 15-min slot, wait, see it auto-publish without any manual trigger.

---

## 8. Reuse strategy (what we borrow vs build fresh)

| Concern | Strategy |
|---|---|
| LinkedIn token storage | **Reuse** `PlatformAccount` model + `platform_accounts` table (read-only access). The new module never writes to this table. |
| LinkedIn API call shape (text + ugcPosts) | **Reference** social module's `LinkedInPublisher` — write fresh code in `BlogAndPostPublisher` (no shared base class to keep modules decoupled). |
| 3-step asset upload (image/video) | **Reference** the same `registerUpload → PUT bytes → ugcPosts` pattern — but use `feedshare-image` / `feedshare-video` recipes (NOT `feedshare-document` which we know is blocked). |
| Hashtag formatting | Write a small helper in `BlogAndPostPublisher`. Same logic as social module's `formatHashtags()`. |
| File upload (image/video) | Use Livewire's `WithFileUploads` + `Storage::disk('public')->putFileAs(...)`. Mirror the blog module's cover-image pattern. |
| Cron / scheduler | Mirror the social module's `withSchedule()` registration. New command name — `blog-and-post:publish-due`. |
| Admin layout / breadcrumbs / flash / confirm-buttons | **Reuse** the existing layout components, design-system classes, and `x-admin.confirm-button`. Standard admin chrome. |

---

## 9. Conventions reminder (from `CLAUDE.md`)

- Layout: `#[Layout('components.layouts.admin')]` on every Livewire component.
- Cards: `rounded-xl`, `bg-dark-800`, `border-dark-700`.
- Color alias: `primary` (NOT `accent`).
- Headings: `font-mono font-bold text-white uppercase tracking-wider`.
- Business logic in services, components stay thin.
- File uploads stored via `Storage::disk('public')`; paths persisted in `meta` JSON.

---

## 10. Verification (per phase)

| Phase | How to verify |
|---|---|
| 1 | Migrate; visit `/admin/blog-and-post` → empty-state renders; insert a row via tinker → it appears in the list with the right type icon and status badge. |
| 2 | Hit each create URL directly; fill form; Save Draft creates a row; reload Edit → fields prefilled correctly; for image/video, the file is stored and `meta.image_path` / `meta.video_path` is set. |
| 3 | Click + New Post on the list → modal opens with 4 cards; clicking a card navigates to the matching form; URL paste in article form shows a fetched og: preview; image/video forms show inline previews. |
| 4 | For each of the 4 types: create draft → Publish Now → confirm post appears on LinkedIn within seconds; delete manually from LinkedIn; verify status is 'posted' and `linkedin_post_url` is populated. Disconnect token → publish → fails gracefully with a red flash. |
| 5 | Schedule one of each type for the next 15-min slot; run `php artisan schedule:work` locally; confirm all 4 auto-publish without manual triggering. |

---

## 11. Open questions (none blocking — all locked from your earlier picks)

- ~~Namespace~~ → `BlogAndPost` ✓
- ~~Token storage~~ → reuse `platform_accounts` ✓
- ~~Schema~~ → single `posts` table + `type` + `meta` JSON ✓
- ~~Article fields~~ → URL only, no overrides ✓

If you want to revisit any of these, say so before approval.

---

*This is a living document. Mark up anything wrong, missing, or out of order — I'll iterate the same file before any code is written.*
