# Social Scheduler — Implementation Plan

> Companion to `social-scheduler-plan.md` (architecture) and `platform-api-requirements.md` (LinkedIn setup).
> This file is the **build order**: which files to create, which to update, and in what sequence.
> No code in this file — descriptions only.

---

## 1. What we're building (one-paragraph recap)

A new **Social** admin module with three pages: **Scheduler** (write & schedule posts), **Templates** (browse layouts), **Connections** (manage LinkedIn token). Each post is composed in a multi-page editor (caption + cover slide + content slides + CTA slide + hashtags), rendered as a **multi-page PDF** via DomPDF, and auto-published as a LinkedIn carousel on a schedule driven by Hostinger cron.

---

## 2. Phases at a glance

| Phase | Goal | What you can do at the end |
|---|---|---|
| **1. Foundation** | DB + bare CRUD | Create/edit/list/delete posts in admin. No publishing yet. |
| **2. PDF + Preview** | Template rendering pipeline | See live preview of each carousel page; download the rendered PDF. |
| **3. LinkedIn Publishing** | Connect account + post to LinkedIn | "Publish Now" button works. Cron auto-publishes scheduled posts. |
| **4. Polish (later)** | OAuth UI, retries, reminders | Replace manual token paste with real OAuth; email reminders before token expiry. |

Build phases in order. Each phase is shippable and testable on its own.

---

## 3. Pre-flight (do these BEFORE coding starts)

These must be true before Phase 1 begins:

- [ ] LinkedIn Developer app created and both products attached (see `platform-api-requirements.md` §7)
- [ ] LinkedIn `CLIENT_ID`, `CLIENT_SECRET`, `REDIRECT_URI`, `API_VERSION` added to `.env`
- [ ] Hostinger cron is set up to run `php artisan schedule:run` every 15 minutes
- [ ] `php artisan storage:link` has been run (so rendered PDFs are publicly servable)
- [ ] DomPDF works (it's already installed; verify with a quick tinker test)

---

## 4. Phase 1 — Foundation

**Goal:** model + table + bare CRUD admin pages. No PDFs, no LinkedIn, no preview yet.

### Files to CREATE

| Path | Purpose |
|---|---|
| `database/migrations/YYYY_MM_DD_create_scheduled_posts_table.php` | Defines the `scheduled_posts` table schema (see §9). |
| `database/migrations/YYYY_MM_DD_create_platform_accounts_table.php` | Defines the `platform_accounts` table (LinkedIn token storage). |
| `app/Models/Social/ScheduledPost.php` | Eloquent model with `caption`, `hashtags`, `cover_page`, `content_pages` (JSON array), `final_page`, `template_slug`, `scheduled_at`, `status`, LinkedIn result columns, etc. |
| `app/Models/Social/PlatformAccount.php` | Eloquent model for LinkedIn account credentials. Token columns use Laravel's `encrypted` cast. |
| `app/Services/SocialPostService.php` | Business logic: create, update (resets cached PDF), delete, status transitions. All DB writes wrapped in transactions. Mirrors `BlogPostService` style. |
| `app/Livewire/Admin/Social/Scheduler/PostIndex.php` | List page Livewire component. Filters: search + status. Pagination via `WithPagination`. Includes delete action. |
| `app/Livewire/Admin/Social/Scheduler/PostForm.php` | Create/edit form. Manages dynamic content-pages array (add/remove/reorder). Saves draft + schedule actions. No preview yet in this phase. |
| `resources/views/livewire/admin/social/scheduler/index.blade.php` | Table view following the mockup in `social-scheduler-plan.md` §6.1. |
| `resources/views/livewire/admin/social/scheduler/form.blade.php` | Form view — caption, hashtags, locked cover, content-pages list with `+ Add Page`/`× Remove`/drag handles, locked final, schedule fields. |
| `routes/admin/social/scheduler.php` | Three routes: index, create, edit. Auto-loaded by the existing glob loader. |

### Files to UPDATE

| Path | Change |
|---|---|
| `resources/views/components/layouts/admin.blade.php` | Add new **Social** collapsible sidebar group with **Scheduler** link (mirror the Portfolio group's pattern). |
| `.env.example` | Add the four `LINKEDIN_*` keys (empty values) for documentation. |

### Phase 1 verification
1. Migrate fresh, sidebar shows the new **Social → Scheduler** link.
2. Click **+ New Post**, fill caption / cover / one content page / final / hashtags, save draft.
3. Edit it, add another content page via `+ Add Page`, reorder by drag, remove one, save.
4. Index lists the post with correct status badge.
5. Delete works with confirm.

---

## 5. Phase 2 — PDF rendering + live preview

**Goal:** turn the form data into a real multi-page PDF and show a live preview pane on the form.

### Files to CREATE

| Path | Purpose |
|---|---|
| `config/social-templates.php` | Registry array listing available templates (slug → label). Drives the template picker. |
| `app/Services/SocialPdfRenderService.php` | Takes a `ScheduledPost`, picks the right template Blade, builds an array of pages (cover + content + final), renders to a multi-page PDF via DomPDF. Caches result to `storage/app/public/social-posts/{id}.pdf`. Invalidates cache on edit. |
| `resources/views/social-posts/templates/_styles.blade.php` | Shared plain-CSS stylesheet used by ALL templates and the preview. **No Tailwind.** Same rule as the resume builder. |
| `resources/views/social-posts/templates/_layout.blade.php` | Base HTML wrapper used by every template (includes `_styles`, defines the page-break structure). |
| `resources/views/social-posts/templates/default.blade.php` | First shipping template. Renders cover / content / final pages with distinct styling for each role. |
| `app/Livewire/Admin/Social/Templates/TemplateIndex.php` | Templates browser page. Renders each template with placeholder data. |
| `resources/views/livewire/admin/social/templates/index.blade.php` | Templates gallery view (see mockup in `social-scheduler-plan.md` §6.4). |
| `routes/admin/social/templates.php` | One route: templates index. |

### Files to UPDATE

| Path | Change |
|---|---|
| `app/Livewire/Admin/Social/Scheduler/PostForm.php` | Add **template picker** (loads from `social-templates.php`). Add **live preview** state — re-renders the focused page after a debounced change. Add **download PDF** button for QA. |
| `resources/views/livewire/admin/social/scheduler/form.blade.php` | Add right-hand preview column following the mockup. Add template strip at top. Add thumbnail navigation strip. |
| `resources/views/components/layouts/admin.blade.php` | Add **Templates** link under the Social sidebar group. |

### Phase 2 verification
1. Open a post in the form. Right pane shows the cover slide rendered.
2. Edit cover text — preview updates (debounced).
3. Click thumbnails — preview switches pages.
4. Switch template — all pages re-render in the new style.
5. Click **Download PDF** — get a real multi-page PDF whose pages match the previews exactly.
6. Templates browser shows each template with placeholder data.

---

## 6. Phase 3 — LinkedIn publishing + cron

**Goal:** actually post to LinkedIn, manually and on a schedule.

### Files to CREATE

| Path | Purpose |
|---|---|
| `app/Services/LinkedInPublisher.php` | The 3-step LinkedIn document-upload flow: register asset → upload PDF bytes → create UGC post with `shareMediaCategory: DOCUMENT`. Returns the post URN + permalink on success; throws on failure. |
| `app/Services/SocialPublishingService.php` | Orchestrator. Takes a `ScheduledPost`, calls `SocialPdfRenderService` for the PDF, then calls `LinkedInPublisher`. Writes result back to the post (`linkedin_post_id`, `linkedin_post_url`, status, errors, attempt count). |
| `app/Console/Commands/PublishDueSocialPosts.php` | Console command `social:publish-due`. Finds posts where `status='scheduled' AND scheduled_at <= now()`, runs them through `SocialPublishingService` one at a time. |
| `app/Livewire/Admin/Social/Connections/ConnectionsIndex.php` | Connections page. **Phase 3 uses manual token paste** — a textarea where you paste a LinkedIn token from the Developer Console + a save button. Shows token health badge and expiry countdown. Has a disconnect action. |
| `resources/views/livewire/admin/social/connections/index.blade.php` | Connections view (see mockup in `social-scheduler-plan.md` §6.3). |
| `routes/admin/social/connections.php` | Connections index route. |

### Files to UPDATE

| Path | Change |
|---|---|
| `app/Livewire/Admin/Social/Scheduler/PostForm.php` | Add **Publish Now** button (calls `SocialPublishingService` directly, ignoring schedule). Add **Retry** action for failed posts. Show LinkedIn URL once posted. |
| `app/Livewire/Admin/Social/Scheduler/PostIndex.php` | Show LinkedIn status in the row, add Retry / View on LinkedIn actions. |
| `resources/views/livewire/admin/social/scheduler/index.blade.php` | Render status badges + LinkedIn link per row. |
| `bootstrap/app.php` | Add `->withSchedule(...)` block registering `social:publish-due` to run `everyFifteenMinutes()->withoutOverlapping()`. |
| `config/services.php` | Add a `linkedin` entry pulling from the four `LINKEDIN_*` `.env` keys. |
| `resources/views/components/layouts/admin.blade.php` | Add **Connections** link under the Social sidebar group. |

### Phase 3 verification
1. Paste a LinkedIn access token into the Connections page; badge turns green.
2. Open a draft post, click **Publish Now** — within seconds, the post appears on your LinkedIn feed as a carousel.
3. The post row in the index shows ✓ POSTED with a clickable LinkedIn URL.
4. Create another post, schedule it for the next `:00 / :15 / :30 / :45` slot 5+ minutes out, wait for cron to fire, confirm it publishes automatically.
5. Disconnect, try to publish — post is marked ✗ FAILED with an error message; reconnect, click Retry, post succeeds.

---

## 7. Phase 4 — Polish (deferred, not part of v1)

These are NOT built in v1. Listed here so we don't forget.

| Item | Why deferred |
|---|---|
| Real OAuth UI (replaces manual token paste) | Manual paste works fine for one user. Build real OAuth once Phase 3 is proven. |
| Daily token health check + email reminders | Needs a working publisher first to know what "healthy" means in context. |
| Auto-refresh of access token using refresh token | Same — wait until the manual flow proves the API works. |
| Drafts auto-save | Nice-to-have; manual save covers v1. |
| Per-post analytics | LinkedIn API analytics need separate access; out of scope. |

---

## 8. Database schema summary

Two tables only. Full column lists are in `social-scheduler-plan.md` §5, but here's the shape:

**`scheduled_posts`**
- Identity: `id`, `title`
- Caption section: `caption` (text), `hashtags` (text — space-separated, parser-friendly)
- Carousel pages: `cover_page` (text), `content_pages` (JSON array of strings), `final_page` (text)
- Template: `template_slug`
- Schedule: `scheduled_at` (rounded to 15-min slots), `status` (`draft|scheduled|publishing|posted|failed`)
- Rendered artifact: `rendered_pdf_path` (cache; cleared on edit)
- LinkedIn result: `linkedin_post_id`, `linkedin_post_url`, `linkedin_error`, `linkedin_attempts`, `linkedin_last_attempted_at`

**`platform_accounts`**
- One row per platform (only `linkedin` for v1; column is unique)
- `account_label` (display name), `remote_account_id` (URN)
- `access_token` + `refresh_token` (both **encrypted casts**)
- `token_expires_at`, `scope`, `last_health_check_at`

---

## 9. Sidebar additions (admin layout)

The existing admin sidebar gets one new collapsible group **Social**, added in the same style as the Portfolio group:

```
SOCIAL                  ▾    ← new group
  Scheduler             ← Phase 1
  Templates             ← Phase 2
  Connections           ← Phase 3
```

---

## 10. Routes summary

All under `routes/admin/social/` (auto-discovered by the existing glob loader in `bootstrap/app.php`):

| File | Routes |
|---|---|
| `scheduler.php` | `admin.social.scheduler.index`, `admin.social.scheduler.create`, `admin.social.scheduler.edit` |
| `templates.php` | `admin.social.templates.index` |
| `connections.php` | `admin.social.connections.index` (plus a POST route for saving the pasted token in Phase 3) |

---

## 11. Conventions to follow (from `CLAUDE.md`)

These are not optional — they're project rules. Reminders for the implementer:

- All admin Livewire components use `#[Layout('components.layouts.admin')]`.
- Admin cards use `rounded-xl` + `border-dark-700`. Use `primary` color alias (not `accent`).
- All `<h1>`, `<h2>`, `<h3>` use `font-mono font-bold text-white uppercase tracking-wider`.
- Business logic lives in services, NOT in Livewire components. Components are thin (validation, flash, redirect).
- Template Blade files in `resources/views/social-posts/templates/` use **plain CSS only** (no Tailwind) — preview and PDF must share one styling source. Same rule as the resume builder.
- Folder layout strictly follows `Admin/[ModuleGroup]/[Feature]/` for Livewire and `livewire/admin/[module-group]/[feature]/` for views.

---

## 12. Suggested ordering tips

- Build Phase 1 fully and verify before touching Phase 2 — getting CRUD right first avoids form-rewrites later.
- In Phase 2, build ONE template (`default`) end-to-end before adding more. Get the rendering pipeline locked.
- In Phase 3, test against your own LinkedIn account first using a token pasted from LinkedIn's Developer Console (no OAuth code needed).
- Run `php artisan schedule:list` after registering the command in `bootstrap/app.php` to confirm cron sees it before relying on Hostinger.

---

*Once a phase is done, mark its verification checkboxes as passing before moving on. If anything in this plan turns out wrong during build, update this file rather than letting the docs drift from reality.*
