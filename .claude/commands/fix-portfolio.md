# /fix-portfolio — Restructure Portfolio Module

Fix the existing Portfolio module's directory structure and admin UI/UX to match CLAUDE.md and DESIGN-SYSTEM.md rules.

**This command is a one-time fix for the Portfolio module only.** It does NOT build new features — it moves existing files to correct locations and fixes UI violations.

**DOCKER RULE:** Every shell command MUST use `docker compose exec app <command>`. No exceptions.

---

## Phase 0 — Baseline Check

Before touching anything, verify the project is healthy:

```bash
docker compose exec app php artisan migrate:status
docker compose exec app php artisan route:list
docker compose exec app ./vendor/bin/pint
docker compose exec app php artisan test
```

If ANY check fails → **STOP**. Report failures. Do NOT proceed on broken code.

If all pass → save the output as your baseline reference. Continue.

---

## Phase 1 — Directory Structure (Move Files + Update References)

**CRITICAL RULES:**
- Route NAMES must NOT change (sidebar links depend on them)
- No database changes, no migrations, no data loss
- Move file → update namespace → update view path → update all imports → delete old file
- Verify after EACH batch of moves

### Step 1.1: Create Target Directories

```bash
# Livewire component directories
mkdir -p app/Livewire/Admin/Portfolio/Skills
mkdir -p app/Livewire/Admin/Portfolio/Technologies
mkdir -p app/Livewire/Admin/Portfolio/Experiences
mkdir -p app/Livewire/Admin/Portfolio/Projects
mkdir -p app/Livewire/Admin/Portfolio/Testimonials
mkdir -p app/Livewire/Admin/Portfolio/Blog

# View directories
mkdir -p resources/views/livewire/admin/portfolio/skills
mkdir -p resources/views/livewire/admin/portfolio/technologies
mkdir -p resources/views/livewire/admin/portfolio/experiences
mkdir -p resources/views/livewire/admin/portfolio/projects
mkdir -p resources/views/livewire/admin/portfolio/testimonials
mkdir -p resources/views/livewire/admin/portfolio/blog

# Model directories
mkdir -p app/Models/Blog
mkdir -p app/Models/Project
mkdir -p app/Models/Experience
```

All via `docker compose exec app` or directly on host (mkdir is safe).

### Step 1.2: Move Models (Do This First — Components Import Models)

Move these 6 model files and update their namespace:

| Old Path | New Path | New Namespace |
|---|---|---|
| `app/Models/BlogPost.php` | `app/Models/Blog/BlogPost.php` | `App\Models\Blog` |
| `app/Models/BlogPostTag.php` | `app/Models/Blog/BlogPostTag.php` | `App\Models\Blog` |
| `app/Models/Project.php` | `app/Models/Project/Project.php` | `App\Models\Project` |
| `app/Models/ProjectImage.php` | `app/Models/Project/ProjectImage.php` | `App\Models\Project` |
| `app/Models/Experience.php` | `app/Models/Experience/Experience.php` | `App\Models\Experience` |
| `app/Models/ExperienceResponsibility.php` | `app/Models/Experience/ExperienceResponsibility.php` | `App\Models\Experience` |

For EACH model file:
1. Read the file completely
2. Create the new file at the new path with updated namespace
3. Keep everything else IDENTICAL (fillable, casts, relationships, methods)
4. Check if any relationship references a model that also moved — update the `use` import if so

**Then update ALL files that import these models.** Search the entire codebase:

```bash
grep -r "use App\\\\Models\\\\BlogPost;" app/ routes/ --include="*.php" -l
grep -r "use App\\\\Models\\\\BlogPostTag;" app/ routes/ --include="*.php" -l
grep -r "use App\\\\Models\\\\Project;" app/ routes/ --include="*.php" -l
grep -r "use App\\\\Models\\\\ProjectImage;" app/ routes/ --include="*.php" -l
grep -r "use App\\\\Models\\\\Experience;" app/ routes/ --include="*.php" -l
grep -r "use App\\\\Models\\\\ExperienceResponsibility;" app/ routes/ --include="*.php" -l
```

Update every import found:
- `use App\Models\BlogPost;` → `use App\Models\Blog\BlogPost;`
- `use App\Models\BlogPostTag;` → `use App\Models\Blog\BlogPostTag;`
- `use App\Models\Project;` → `use App\Models\Project\Project;`
- `use App\Models\ProjectImage;` → `use App\Models\Project\ProjectImage;`
- `use App\Models\Experience;` → `use App\Models\Experience\Experience;`
- `use App\Models\ExperienceResponsibility;` → `use App\Models\Experience\ExperienceResponsibility;`

These imports exist in: Services, Livewire components, PortfolioController, and possibly route files.

After updating all imports, delete the old model files.

**Verify:** `docker compose exec app php artisan route:list` (should show all routes, no errors)

### Step 1.3: Move Livewire Components

Move these 13 component files, update namespace AND view path in each:

**Skills:**
- `app/Livewire/Admin/SkillIndex.php` → `app/Livewire/Admin/Portfolio/Skills/SkillIndex.php`
  - Namespace: `App\Livewire\Admin` → `App\Livewire\Admin\Portfolio\Skills`
  - View path in render(): `livewire.admin.skill-index` → `livewire.admin.portfolio.skills.index`
- `app/Livewire/Admin/SkillForm.php` → `app/Livewire/Admin/Portfolio/Skills/SkillForm.php`
  - Namespace: `App\Livewire\Admin` → `App\Livewire\Admin\Portfolio\Skills`
  - View path: `livewire.admin.skill-form` → `livewire.admin.portfolio.skills.form`

**Technologies:**
- `app/Livewire/Admin/TechnologyIndex.php` → `app/Livewire/Admin/Portfolio/Technologies/TechnologyIndex.php`
  - Namespace: `App\Livewire\Admin` → `App\Livewire\Admin\Portfolio\Technologies`
  - View path: `livewire.admin.technology-index` → `livewire.admin.portfolio.technologies.index`
- `app/Livewire/Admin/TechnologyForm.php` → `app/Livewire/Admin/Portfolio/Technologies/TechnologyForm.php`
  - Namespace: `App\Livewire\Admin` → `App\Livewire\Admin\Portfolio\Technologies`
  - View path: `livewire.admin.technology-form` → `livewire.admin.portfolio.technologies.form`

**Experiences:**
- `app/Livewire/Admin/ExperienceIndex.php` → `app/Livewire/Admin/Portfolio/Experiences/ExperienceIndex.php`
  - Namespace: `App\Livewire\Admin` → `App\Livewire\Admin\Portfolio\Experiences`
  - View path: `livewire.admin.experience-index` → `livewire.admin.portfolio.experiences.index`
- `app/Livewire/Admin/ExperienceForm.php` → `app/Livewire/Admin/Portfolio/Experiences/ExperienceForm.php`
  - Namespace: `App\Livewire\Admin` → `App\Livewire\Admin\Portfolio\Experiences`
  - View path: `livewire.admin.experience-form` → `livewire.admin.portfolio.experiences.form`

**Projects:**
- `app/Livewire/Admin/ProjectIndex.php` → `app/Livewire/Admin/Portfolio/Projects/ProjectIndex.php`
  - Namespace: `App\Livewire\Admin` → `App\Livewire\Admin\Portfolio\Projects`
  - View path: `livewire.admin.project-index` → `livewire.admin.portfolio.projects.index`
- `app/Livewire/Admin/ProjectForm.php` → `app/Livewire/Admin/Portfolio/Projects/ProjectForm.php`
  - Namespace: `App\Livewire\Admin` → `App\Livewire\Admin\Portfolio\Projects`
  - View path: `livewire.admin.project-form` → `livewire.admin.portfolio.projects.form`

**Testimonials:**
- `app/Livewire/Admin/TestimonialIndex.php` → `app/Livewire/Admin/Portfolio/Testimonials/TestimonialIndex.php`
  - Namespace: `App\Livewire\Admin` → `App\Livewire\Admin\Portfolio\Testimonials`
  - View path: `livewire.admin.testimonial-index` → `livewire.admin.portfolio.testimonials.index`
- `app/Livewire/Admin/TestimonialForm.php` → `app/Livewire/Admin/Portfolio/Testimonials/TestimonialForm.php`
  - Namespace: `App\Livewire\Admin` → `App\Livewire\Admin\Portfolio\Testimonials`
  - View path: `livewire.admin.testimonial-form` → `livewire.admin.portfolio.testimonials.form`

**Blog:**
- `app/Livewire/Admin/BlogPostIndex.php` → `app/Livewire/Admin/Portfolio/Blog/BlogPostIndex.php`
  - Namespace: `App\Livewire\Admin` → `App\Livewire\Admin\Portfolio\Blog`
  - View path: `livewire.admin.blog-post-index` → `livewire.admin.portfolio.blog.index`
- `app/Livewire/Admin/BlogPostForm.php` → `app/Livewire/Admin/Portfolio/Blog/BlogPostForm.php`
  - Namespace: `App\Livewire\Admin` → `App\Livewire\Admin\Portfolio\Blog`
  - View path: `livewire.admin.blog-post-form` → `livewire.admin.portfolio.blog.form`

**Analytics:**
- `app/Livewire/Admin/Analytics.php` → `app/Livewire/Admin/Portfolio/Analytics.php`
  - Namespace: `App\Livewire\Admin` → `App\Livewire\Admin\Portfolio`
  - View path: `livewire.admin.analytics` → `livewire.admin.portfolio.analytics`

For EACH file:
1. Read the original file completely
2. Create new file at new path with updated namespace and view path
3. Keep ALL logic, properties, methods, imports IDENTICAL (except namespace, view path, and model imports already updated in Step 1.2)
4. Delete old file after creating new one

### Step 1.4: Move Blade Views

Move these 13 view files (content stays identical — just move the file):

| Old Path | New Path |
|---|---|
| `resources/views/livewire/admin/skill-index.blade.php` | `resources/views/livewire/admin/portfolio/skills/index.blade.php` |
| `resources/views/livewire/admin/skill-form.blade.php` | `resources/views/livewire/admin/portfolio/skills/form.blade.php` |
| `resources/views/livewire/admin/technology-index.blade.php` | `resources/views/livewire/admin/portfolio/technologies/index.blade.php` |
| `resources/views/livewire/admin/technology-form.blade.php` | `resources/views/livewire/admin/portfolio/technologies/form.blade.php` |
| `resources/views/livewire/admin/experience-index.blade.php` | `resources/views/livewire/admin/portfolio/experiences/index.blade.php` |
| `resources/views/livewire/admin/experience-form.blade.php` | `resources/views/livewire/admin/portfolio/experiences/form.blade.php` |
| `resources/views/livewire/admin/project-index.blade.php` | `resources/views/livewire/admin/portfolio/projects/index.blade.php` |
| `resources/views/livewire/admin/project-form.blade.php` | `resources/views/livewire/admin/portfolio/projects/form.blade.php` |
| `resources/views/livewire/admin/testimonial-index.blade.php` | `resources/views/livewire/admin/portfolio/testimonials/index.blade.php` |
| `resources/views/livewire/admin/testimonial-form.blade.php` | `resources/views/livewire/admin/portfolio/testimonials/form.blade.php` |
| `resources/views/livewire/admin/blog-post-index.blade.php` | `resources/views/livewire/admin/portfolio/blog/index.blade.php` |
| `resources/views/livewire/admin/blog-post-form.blade.php` | `resources/views/livewire/admin/portfolio/blog/form.blade.php` |
| `resources/views/livewire/admin/analytics.blade.php` | `resources/views/livewire/admin/portfolio/analytics.blade.php` |

For each: read full content, write to new path with identical content, delete old file.

### Step 1.5: Update Route File Imports

Read and update ALL 7 route files in `routes/admin/portfolio/`:

**routes/admin/portfolio/skills.php:**
- `use App\Livewire\Admin\SkillIndex;` → `use App\Livewire\Admin\Portfolio\Skills\SkillIndex;`
- `use App\Livewire\Admin\SkillForm;` → `use App\Livewire\Admin\Portfolio\Skills\SkillForm;`

**routes/admin/portfolio/technologies.php:**
- `use App\Livewire\Admin\TechnologyIndex;` → `use App\Livewire\Admin\Portfolio\Technologies\TechnologyIndex;`
- `use App\Livewire\Admin\TechnologyForm;` → `use App\Livewire\Admin\Portfolio\Technologies\TechnologyForm;`

**routes/admin/portfolio/experiences.php:**
- `use App\Livewire\Admin\ExperienceIndex;` → `use App\Livewire\Admin\Portfolio\Experiences\ExperienceIndex;`
- `use App\Livewire\Admin\ExperienceForm;` → `use App\Livewire\Admin\Portfolio\Experiences\ExperienceForm;`

**routes/admin/portfolio/projects.php:**
- `use App\Livewire\Admin\ProjectIndex;` → `use App\Livewire\Admin\Portfolio\Projects\ProjectIndex;`
- `use App\Livewire\Admin\ProjectForm;` → `use App\Livewire\Admin\Portfolio\Projects\ProjectForm;`

**routes/admin/portfolio/testimonials.php:**
- `use App\Livewire\Admin\TestimonialIndex;` → `use App\Livewire\Admin\Portfolio\Testimonials\TestimonialIndex;`
- `use App\Livewire\Admin\TestimonialForm;` → `use App\Livewire\Admin\Portfolio\Testimonials\TestimonialForm;`

**routes/admin/portfolio/blog.php:**
- `use App\Livewire\Admin\BlogPostIndex;` → `use App\Livewire\Admin\Portfolio\Blog\BlogPostIndex;`
- `use App\Livewire\Admin\BlogPostForm;` → `use App\Livewire\Admin\Portfolio\Blog\BlogPostForm;`

**routes/admin/portfolio/analytics.php:**
- `use App\Livewire\Admin\Analytics;` → `use App\Livewire\Admin\Portfolio\Analytics;`

**DO NOT change route names, paths, or middleware.** Only update the `use` import statements.

### Step 1.6: Verify Structure

```bash
docker compose exec app php artisan route:list
docker compose exec app ./vendor/bin/pint
docker compose exec app php artisan test
```

All must pass. If anything fails, fix it before proceeding to Phase 2.

Also verify no old files remain:
```bash
ls app/Livewire/Admin/*.php
# Should only show: Dashboard.php, FileManager.php, Login.php, ProfileEdit.php, ResumeGenerator.php

ls resources/views/livewire/admin/*.blade.php
# Should only show: dashboard.blade.php, file-manager.blade.php, login.blade.php, profile-edit.blade.php, resume-generator.blade.php
```

---

## Phase 2 — UI/UX Fixes

Read `resources/views/DESIGN-SYSTEM.md` completely before making any changes.

### Step 2.1: Fix Sidebar — Nest Analytics + Resume Under Portfolio

File: `resources/views/components/layouts/admin.blade.php`

1. Read the FULL sidebar section
2. Find the Analytics standalone link and the Resume standalone link
3. Move BOTH inside the Portfolio collapsible group (after Blog link)
4. Update the Portfolio group's active state detection to include `admin.analytics*` and `admin.resume*`
5. Remove the standalone Analytics and Resume links from root level

The Portfolio group should look like:
```
Portfolio (collapsible)
  ├── Profile
  ├── Skills
  ├── Technologies
  ├── Experiences
  ├── Projects
  ├── Testimonials
  ├── Blog
  ├── Analytics      ← moved inside
  └── Resume         ← moved inside
```

DO NOT change route names. DO NOT change any other sidebar items (Dashboard, File Manager).

### Step 2.2: Add Breadcrumbs to Portfolio Views

Add breadcrumbs to ALL 13 portfolio views (now in their new locations).

Use the exact breadcrumb pattern from DESIGN-SYSTEM.md section 3:

**Index pages** (Dashboard > Module > Current):
```blade
<div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
    <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-gray-300">[Page Name]</span>
</div>
```

**Form pages** (Dashboard > Module > List > Create/Edit):
```blade
<div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
    <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('admin.[module].index') }}" wire:navigate class="hover:text-gray-300 transition-colors">[Module]</a>
    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-gray-300">{{ $[id] ? 'Edit' : 'Create' }} [Item]</span>
</div>
```

Place breadcrumbs at the very TOP of each view, BEFORE the page header div.

Add breadcrumbs to these 13 files:
1. `portfolio/skills/index.blade.php` — Dashboard > Skills
2. `portfolio/skills/form.blade.php` — Dashboard > Skills > Create/Edit Skill
3. `portfolio/technologies/index.blade.php` — Dashboard > Technologies
4. `portfolio/technologies/form.blade.php` — Dashboard > Technologies > Create/Edit Technology
5. `portfolio/experiences/index.blade.php` — Dashboard > Experiences
6. `portfolio/experiences/form.blade.php` — Dashboard > Experiences > Create/Edit Experience
7. `portfolio/projects/index.blade.php` — Dashboard > Projects
8. `portfolio/projects/form.blade.php` — Dashboard > Projects > Create/Edit Project
9. `portfolio/testimonials/index.blade.php` — Dashboard > Testimonials
10. `portfolio/testimonials/form.blade.php` — Dashboard > Testimonials > Create/Edit Testimonial
11. `portfolio/blog/index.blade.php` — Dashboard > Blog
12. `portfolio/blog/form.blade.php` — Dashboard > Blog > Create/Edit Post
13. `portfolio/analytics.blade.php` — Dashboard > Analytics

### Step 2.3: Fix Stat Card Icon Colors

**File: `resources/views/livewire/admin/dashboard.blade.php`**

Read the file. Find ALL stat cards. Each stat card has an icon with `bg-primary/10 text-primary-light`. Change them to VARIED colors per DESIGN-SYSTEM.md section 5:

Use these colors (one per card, no duplicates):
- Skills card: `bg-primary/10 text-primary-light` (keep purple)
- Technologies card: `bg-cyan-500/10 text-cyan-400`
- Experiences card: `bg-blue-500/10 text-blue-400`
- Projects card: `bg-fuchsia-500/10 text-fuchsia-400`
- Testimonials card: `bg-amber-500/10 text-amber-400`
- Blog Posts card: `bg-emerald-500/10 text-emerald-400`
- Page Views card: `bg-blue-500/10 text-blue-400`
- Resume Downloads card: `bg-fuchsia-500/10 text-fuchsia-400`

**File: `resources/views/livewire/admin/portfolio/analytics.blade.php`** (new path)

Find the 4 stat cards. Vary their colors:
- Total Visits: `bg-blue-500/10 text-blue-400`
- Unique Visitors: `bg-cyan-500/10 text-cyan-400`
- Page Views: `bg-primary/10 text-primary-light`
- Resume Downloads: `bg-fuchsia-500/10 text-fuchsia-400`

### Step 2.4: Fix Chart Gradient Color

**File: `resources/views/livewire/admin/portfolio/analytics.blade.php`** (new path)

Find the chart JavaScript. Replace indigo color `#6366f1` with primary purple `#7c3aed` everywhere in the chart config. Also update the gradient if it uses indigo:
- `rgba(99, 102, 241, ...)` → `rgba(124, 58, 237, ...)`

### Step 2.5: Fix Form Label Colors

Search all form views for `text-gray-400` on labels and change to `text-gray-300`:

**Files to check:**
- `resources/views/livewire/admin/profile-edit.blade.php`
- `resources/views/livewire/admin/portfolio/skills/form.blade.php`
- `resources/views/livewire/admin/portfolio/experiences/form.blade.php`

Read each file. Find `<label` elements with `text-gray-400`. Change to `text-gray-300`.
Only change LABEL elements — do not change body text, descriptions, or placeholders that correctly use `text-gray-400`.

### Step 2.6: Fix File Manager Heading

**File: `resources/views/livewire/admin/file-manager.blade.php`**

Find the h3 tag with `font-medium` and change to `font-semibold` per DESIGN-SYSTEM.md heading rules.

---

## Phase 3 — Final Verification

### Run Full Checks

```bash
docker compose exec app php artisan route:list
docker compose exec app ./vendor/bin/pint
docker compose exec app php artisan test
docker compose exec app npm run build
```

ALL must pass. Compare with Phase 0 baseline — no regressions allowed.

### Verify File Structure

```bash
# Livewire components — should be in Portfolio subfolders
ls app/Livewire/Admin/Portfolio/Skills/
ls app/Livewire/Admin/Portfolio/Technologies/
ls app/Livewire/Admin/Portfolio/Experiences/
ls app/Livewire/Admin/Portfolio/Projects/
ls app/Livewire/Admin/Portfolio/Testimonials/
ls app/Livewire/Admin/Portfolio/Blog/
ls app/Livewire/Admin/Portfolio/Analytics.php

# Views — should mirror component structure
ls resources/views/livewire/admin/portfolio/skills/
ls resources/views/livewire/admin/portfolio/technologies/
ls resources/views/livewire/admin/portfolio/experiences/
ls resources/views/livewire/admin/portfolio/projects/
ls resources/views/livewire/admin/portfolio/testimonials/
ls resources/views/livewire/admin/portfolio/blog/
ls resources/views/livewire/admin/portfolio/analytics.blade.php

# Models — grouped models in subfolders
ls app/Models/Blog/
ls app/Models/Project/
ls app/Models/Experience/

# No old files remaining in flat locations
ls app/Livewire/Admin/*.php
# Should ONLY show: Dashboard.php, FileManager.php, Login.php, ProfileEdit.php, ResumeGenerator.php
```

### Final Report

```
═══════════════════════════════════════════════════
  PORTFOLIO MODULE FIX COMPLETE
═══════════════════════════════════════════════════

Part 1 — Directory Structure:
  ✅ 13 Livewire components moved to Admin/Portfolio/[Feature]/
  ✅ 13 blade views moved to admin/portfolio/[feature]/
  ✅ 6 models moved to Model subfolders (Blog/, Project/, Experience/)
  ✅ 7 route files updated with new imports
  ✅ Service files updated with new model imports
  ✅ PortfolioController updated with new model imports
  ✅ Old files deleted

Part 2 — UI/UX Fixes:
  ✅ Sidebar: Analytics + Resume nested inside Portfolio group
  ✅ Breadcrumbs: added to all 13 portfolio views
  ✅ Stat cards: varied icon colors on dashboard + analytics
  ✅ Chart: purple gradient (#7c3aed) instead of indigo
  ✅ Form labels: text-gray-300 on all labels
  ✅ File Manager: heading font-semibold

Verification:
  ✅ Routes: all registered, names unchanged
  ✅ Pint: clean
  ✅ Tests: passing (no regressions)
  ✅ Vite build: successful
  ✅ No old files remaining in flat locations

Status: PORTFOLIO MODULE RESTRUCTURED — READY FOR NEW MODULES
═══════════════════════════════════════════════════
```

---

## Rules

1. **Read before write** — read every file completely before modifying
2. **No functionality changes** — only move files and fix UI. No logic changes
3. **No data loss** — no database changes, no migration changes
4. **Route names unchanged** — sidebar links must keep working
5. **Docker for everything** — `docker compose exec app` for all commands
6. **Verify after each phase** — route:list + pint + test between phases
7. **Delete old files** — no duplicates left behind
8. **DESIGN-SYSTEM.md is the source of truth** — copy UI patterns exactly from there
