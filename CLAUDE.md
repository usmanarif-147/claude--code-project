# Portfolio Project

## Tech Stack
- Laravel 12, Livewire 4.1, Tailwind CSS 4 (with Vite 7)
- PHP 8.2+
- Alpine.js for interactive UI elements

## Architecture
- Livewire components live in app/Livewire/ (admin components in Admin/ subdirectory)
- Blade views live in resources/views/livewire/ (matching subdirectory structure)
- Admin components use the layout attribute: #[Layout('components.layouts.admin')]
- Feature routes live in routes/admin/[module-group]/*.php (auto-loaded via bootstrap/app.php using `glob('routes/admin/*/*.php')` — any new module folder is auto-discovered, no registration needed)
- Core routes (login, logout, dashboard, profile, files) stay in routes/web.php
- Models are in app/Models/
- Service classes are in app/Services/ — ALL business logic goes here, not in controllers/components
- Livewire components are thin: validation, flash messages, redirects only

## Two Sides of This Project

This project has two completely separate sides. Never mix their patterns.

| | Public Portfolio | Admin Panel |
|---|---|---|
| Who | Visitors, recruiters, clients | Only you (authenticated) |
| Access | No login required | Requires login (`auth` middleware) |
| Purpose | Display/showcase data (read-only) | Create, edit, delete data (read-write) |
| Layout | `components.layouts.app` — single scrollable page | `components.layouts.admin` — sidebar + content |
| Route | `/` via `PortfolioController` | `/admin/*` via Livewire components |
| Blade | Plain Blade + Alpine.js (no Livewire except contact form) | Livewire components only |
| Cards | `rounded-2xl` with `border-white/[0.04]` | `rounded-xl` with `border-dark-700` |
| Buttons | Accent bg + black text | Accent bg + white text |
| Animations | Scroll fade-in (Alpine `x-intersect`), hover scale | Hover states and transitions only |
| Color alias | `accent` / `accent-light` | `primary` / `primary-light` |

### Public Side Rules
- The public side currently belongs to the **Portfolio module only** (`/` route, `welcome.blade.php`)
- Layout: always `components.layouts.app`
- Controller: `PortfolioController` handles all public routes — data is read-only
- No Livewire components on public pages except `ContactForm`
- Use `rounded-2xl` on cards (not `rounded-xl`)
- Use `accent` color alias (not `primary`)
- Animations via Alpine.js `x-intersect` for scroll reveals
- Contact form validates inputs but does NOT save to DB or send email
- If a future module needs a public page (e.g., public AI chatbot), it gets its own controller and view — NOT added to PortfolioController

### Admin Side Rules
- Layout: always `#[Layout('components.layouts.admin')]` on Livewire components
- All routes under `/admin/*` with `auth` middleware, organized by module group: `/admin/portfolio/*`, `/admin/tasks/*`, `/admin/job-search/*`, etc.
- All pages are Livewire components — no plain controllers
- Use `rounded-xl` on cards
- Use `primary` color alias
- All business logic in Service classes
- Components go under `Admin/[ModuleGroup]/[Feature]/` — never flat in Admin/

---

## Project Directory Structure

This is the complete project structure. Every new file MUST follow this exact layout.
New modules (Tasks, Job Search, etc.) follow the same pattern under their own parent folder.

```
portfolio/
│
│   ╔══════════════════════════════════════════════════════╗
│   ║  PUBLIC SIDE  (no login required)                   ║
│   ╚══════════════════════════════════════════════════════╝
│
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── PortfolioController.php       ← [PUBLIC] index() fetches ALL data, passes to welcome.blade.php
│   │   │   └── ResumeController.php          ← [PUBLIC] resume PDF download
│   │   └── Middleware/
│   │       └── TrackVisitor.php              ← [PUBLIC] logs visitor on every public page load
│   │
│   ├── Livewire/
│   │   └── ContactForm.php                   ← [PUBLIC] ONLY Livewire component on public side
│   │                                            validates inputs, does NOT save to DB or send email
│   │
│   ├── Models/                               ← used by PortfolioController to fetch display data
│   │   ├── Blog/
│   │   │   └── BlogPost.php                  ← latest articles section (published only)
│   │   ├── Experience/
│   │   │   ├── Experience.php                ← work history + education timeline sections
│   │   │   └── ExperienceResponsibility.php  ← responsibilities listed under each experience
│   │   ├── Project/
│   │   │   └── Project.php                   ← projects grid section (featured + all)
│   │   ├── PortfolioVisitor.php              ← visitor tracking records
│   │   ├── Profile.php                       ← hero section: tagline, bio, photo, social links
│   │   ├── ResumeDownload.php                ← resume download count tracking
│   │   ├── Skill.php                         ← about section: skill cards with proficiency
│   │   ├── Technology.php                    ← skills & technologies section: grouped by category
│   │   ├── Testimonial.php                   ← testimonials section (visible only)
│   │   └── User.php                          ← name used in hero heading + footer copyright
│   │
│   └── Services/                             ← [SHARED] used by both public controllers and admin components
│       ├── AnalyticsService.php
│       ├── BlogPostService.php
│       ├── ExperienceService.php
│       ├── ProjectService.php
│       ├── ResumeService.php
│       ├── SkillService.php
│       ├── TechnologyService.php
│       └── TestimonialService.php
│
├── resources/views/
│   ├── components/layouts/
│   │   └── app.blade.php                     ← [PUBLIC] layout: fonts, meta tags, body wrapper
│   ├── livewire/
│   │   └── contact-form.blade.php            ← [PUBLIC] contact form view
│   ├── resume/templates/                     ← [PUBLIC+ADMIN] PDF templates (used by ResumeService)
│   │   ├── classic.blade.php
│   │   ├── compact.blade.php
│   │   └── modern.blade.php
│   └── welcome.blade.php                     ← [PUBLIC] entire landing page in one file
│                                                sections: nav, hero, about, skills, experience,
│                                                projects, testimonials, education, blog, contact, footer
│
└── routes/
    └── web.php (public section)              ← GET /  → PortfolioController@index
                                                 GET /resume/download/{template}  → ResumeController
│
│   ╔══════════════════════════════════════════════════════╗
│   ║  ADMIN SIDE  (login required)                       ║
│   ╚══════════════════════════════════════════════════════╝
│
├── app/
│   ├── Livewire/
│   │   └── Admin/                            ← [ADMIN] ALL admin Livewire components
│   │       ├── Portfolio/                    ← Portfolio module group
│   │       │   ├── Analytics.php             ← portfolio analytics dashboard
│   │       │   ├── Blog/
│   │       │   │   ├── BlogPostForm.php
│   │       │   │   └── BlogPostIndex.php
│   │       │   ├── Experiences/
│   │       │   │   ├── ExperienceForm.php
│   │       │   │   └── ExperienceIndex.php
│   │       │   ├── Projects/
│   │       │   │   ├── ProjectForm.php
│   │       │   │   └── ProjectIndex.php
│   │       │   ├── Skills/
│   │       │   │   ├── SkillForm.php
│   │       │   │   └── SkillIndex.php
│   │       │   ├── Technologies/
│   │       │   │   ├── TechnologyForm.php
│   │       │   │   └── TechnologyIndex.php
│   │       │   └── Testimonials/
│   │       │       ├── TestimonialForm.php
│   │       │       └── TestimonialIndex.php
│   │       ├── Tasks/                        ← Tasks module group (future — same pattern)
│   │       │   └── DailyPlanner/
│   │       │       └── DailyPlannerIndex.php
│   │       ├── JobSearch/                    ← Job Search module group (future — same pattern)
│   │       │   └── JobFeed/
│   │       │       └── JobFeedIndex.php
│   │       ├── Dashboard.php                 ← core — stays in Admin/ root (not in a module subfolder)
│   │       ├── FileManager.php               ← core — stays in Admin/ root
│   │       ├── Login.php                     ← core — stays in Admin/ root
│   │       ├── ProfileEdit.php               ← core — stays in Admin/ root
│   │       └── ResumeGenerator.php           ← core — stays in Admin/ root
│   │
│   └── Models/                               ← [SHARED] same models used by admin for CRUD
│       ├── Blog/                             ← grouped: 2+ related models go in subfolder
│       │   ├── BlogPost.php
│       │   └── BlogPostTag.php
│       ├── Experience/
│       │   ├── Experience.php
│       │   └── ExperienceResponsibility.php
│       ├── Project/
│       │   ├── Project.php
│       │   └── ProjectImage.php
│       ├── File.php                          ← single model — no subfolder needed
│       ├── PortfolioVisitor.php
│       ├── Profile.php
│       ├── ResumeDownload.php
│       ├── Skill.php
│       ├── Technology.php
│       ├── Testimonial.php
│       └── User.php
│
├── database/
│   └── migrations/                           ← always flat, no subfolders
│       └── YYYY_MM_DD_XXXXXX_[action]_[table]_table.php
│
├── resources/views/
│   ├── components/layouts/
│   │   ├── admin.blade.php                   ← [ADMIN] sidebar + content area layout
│   │   └── admin-guest.blade.php             ← [ADMIN] login page layout
│   └── livewire/
│       └── admin/                            ← [ADMIN] views — mirrors Livewire/Admin/ exactly
│           ├── portfolio/                    ← mirrors Livewire/Admin/Portfolio/
│           │   ├── analytics.blade.php
│           │   ├── blog/
│           │   │   ├── form.blade.php
│           │   │   └── index.blade.php
│           │   ├── experiences/
│           │   │   ├── form.blade.php
│           │   │   └── index.blade.php
│           │   ├── projects/
│           │   │   ├── form.blade.php
│           │   │   └── index.blade.php
│           │   ├── skills/
│           │   │   ├── form.blade.php
│           │   │   └── index.blade.php
│           │   ├── technologies/
│           │   │   ├── form.blade.php
│           │   │   └── index.blade.php
│           │   └── testimonials/
│           │       ├── form.blade.php
│           │       └── index.blade.php
│           ├── tasks/                        ← mirrors Livewire/Admin/Tasks/ (future)
│           │   └── daily-planner/
│           │       └── index.blade.php
│           ├── job-search/                   ← mirrors Livewire/Admin/JobSearch/ (future)
│           │   └── job-feed/
│           │       └── index.blade.php
│           ├── dashboard.blade.php           ← core views — stay in admin/ root
│           ├── file-manager.blade.php
│           ├── login.blade.php
│           ├── profile-edit.blade.php
│           └── resume-generator.blade.php
│
└── routes/
    ├── web.php (admin section)               ← login, logout, dashboard, profile, file-manager
    └── admin/
        ├── portfolio/                        ← Portfolio module routes
        │   ├── analytics.php
        │   ├── blog.php
        │   ├── experiences.php
        │   ├── projects.php
        │   ├── resume.php
        │   ├── skills.php
        │   ├── technologies.php
        │   └── testimonials.php
        ├── tasks/                            ← Tasks module routes (future — same pattern)
        │   └── daily-planner.php
        └── job-search/                       ← Job Search module routes (future — same pattern)
            └── job-feed.php
```

---

## Folder Structure Rules

### Livewire Components
ALWAYS create module components inside a module subfolder — NEVER directly in Admin/
For non-portfolio modules, nest under the module group: Admin/[ModuleGroup]/[Feature]/

✅ Correct:   app/Livewire/Admin/Portfolio/Skills/SkillIndex.php
✅ Correct:   app/Livewire/Admin/Portfolio/Skills/SkillForm.php
✅ Correct:   app/Livewire/Admin/Portfolio/Projects/ProjectIndex.php
✅ Correct:   app/Livewire/Admin/Portfolio/Blog/BlogPostIndex.php
✅ Correct:   app/Livewire/Admin/Tasks/DailyPlanner/DailyPlannerIndex.php
✅ Correct:   app/Livewire/Admin/JobSearch/JobFeed/JobFeedIndex.php
❌ Wrong:     app/Livewire/Admin/SkillIndex.php           (missing module subfolder)
❌ Wrong:     app/Livewire/Admin/Portfolio/SkillIndex.php  (missing feature subfolder)
❌ Wrong:     app/Livewire/Admin/DailyPlannerIndex.php     (missing both folders)

### Views
ALWAYS mirror the Livewire subfolder structure exactly — NEVER flat in admin/

✅ Correct:   resources/views/livewire/admin/portfolio/skills/index.blade.php
✅ Correct:   resources/views/livewire/admin/portfolio/skills/form.blade.php
✅ Correct:   resources/views/livewire/admin/portfolio/projects/index.blade.php
✅ Correct:   resources/views/livewire/admin/tasks/daily-planner/index.blade.php
✅ Correct:   resources/views/livewire/admin/job-search/job-feed/index.blade.php
❌ Wrong:     resources/views/livewire/admin/skill-index.blade.php
❌ Wrong:     resources/views/livewire/admin/project-form.blade.php

### Models
Group related models in a subfolder when a module has 2 or more models.
Single-model modules stay directly in app/Models/

✅ Correct:   app/Models/Blog/BlogPost.php
✅ Correct:   app/Models/Blog/BlogPostTag.php
✅ Correct:   app/Models/Project/Project.php
✅ Correct:   app/Models/Project/ProjectImage.php
✅ Correct:   app/Models/Experience/Experience.php
✅ Correct:   app/Models/Experience/ExperienceResponsibility.php
✅ Correct:   app/Models/Skill.php         (single model — no subfolder)
✅ Correct:   app/Models/Testimonial.php   (single model — no subfolder)
❌ Wrong:     app/Models/BlogPost.php      (has related model BlogPostTag)
❌ Wrong:     app/Models/ProjectImage.php  (belongs in Project subfolder)

### Services
One service per module — always directly in app/Services/ with no subfolders

✅ Correct:   app/Services/SkillService.php
✅ Correct:   app/Services/BlogPostService.php
✅ Correct:   app/Services/ProjectService.php

### Routes
One route file per feature — inside routes/admin/[module-group]/

✅ Correct:   routes/admin/portfolio/skills.php
✅ Correct:   routes/admin/portfolio/projects.php
✅ Correct:   routes/admin/portfolio/blog.php
✅ Correct:   routes/admin/tasks/daily-planner.php
✅ Correct:   routes/admin/job-search/job-feed.php
❌ Wrong:     routes/admin/portfolio/daily-planner.php   (Tasks feature, not Portfolio)
❌ Wrong:     routes/admin/job-feed.php                  (missing module-group folder)

### Naming Conventions
- Livewire class:   [Module][Action].php       → SkillIndex.php, SkillForm.php
- View file:        [action].blade.php          → index.blade.php, form.blade.php
- Model subfolder:  PascalCase module name      → Blog/, Project/, Experience/
- Service:          [Module]Service.php         → SkillService.php
- Route file:       lowercase-kebab.php         → skills.php, blog-posts.php

### Before Creating Any File
1. Read CLAUDE.md folder structure rules (this section)
2. Check where similar existing files live
3. Create in the correct subfolder — never guess

---

## Sidebar Navigation Rules

### Module Grouping
Every feature belongs to a parent module group in the sidebar.
NEVER add a feature as a standalone root-level sidebar item.

✅ Correct:
Portfolio (parent, collapsible)
  ├── Profile
  ├── Skills
  ├── Technologies
  ├── Experiences
  ├── Projects
  ├── Testimonials
  ├── Blog
  └── Analytics        ← nested inside Portfolio, not standalone

❌ Wrong:
Portfolio (parent)
  ├── Skills
  └── ...
Analytics              ← standalone at root level — NEVER do this

### Analytics Belongs to Its Module
Each module that has analytics MUST have its analytics link nested inside that module's sidebar group — not at root level and not in a generic "Analytics" section.

Some modules have analytics, some do not:
- Portfolio → has Analytics ✅
- Tasks → may have Analytics (nest under Tasks if so)
- Job Search → may have Analytics (nest under Job Search if so)
- A module with no analytics → simply has no analytics link

### Rule for Every New Module
When adding sidebar links for a new module:
1. Create or find the parent module group in the sidebar
2. Add ALL feature links (including analytics if applicable) INSIDE that group
3. Never place any feature link outside its parent module group

### Future Modules Example
```
Tasks (parent)
  ├── Task List
  ├── Categories
  └── Analytics        ← Tasks analytics, nested under Tasks

Job Search (parent)
  ├── Job Feed
  ├── Applications
  └── Analytics        ← Job analytics, nested under Job Search
```

---

## Design System (Xintra-Inspired Dark Theme)

### IMPORTANT: Read `resources/views/DESIGN-SYSTEM.md` for complete component snippets.

### Color Palette (defined in resources/css/app.css @theme)
- **Backgrounds:** dark-950 (#050508), dark-900 (#0a0a0f page bg), dark-800 (#111118 cards), dark-700 (#1a1a24 borders/inputs), dark-600 (#25253a input borders)
- **Primary accent:** primary (#7c3aed purple), primary-light (#a78bfa), primary-dark (#6d28d9), primary-hover (#8b5cf6)
- **Backward compat aliases:** accent = primary (#7c3aed), accent-light = primary-light (#a78bfa)
- **Status:** success (#22c55e), warning (#f59e0b), danger (#ef4444), info (#3b82f6)
- **Gradients:** purple-to-fuchsia-to-orange for premium CTAs and highlights
- **Text:** text-white (headings), text-gray-300 (body), text-gray-400 (secondary), text-gray-500 (muted/placeholders)

### Typography
- **Headings font:** Fira Code (monospace) — all page titles, card headings, section titles use `font-mono uppercase tracking-wider`
- **Body font:** Inter (sans-serif) — labels, body text, table cells, buttons, inputs
- Headings: `font-mono font-bold text-white uppercase tracking-wider`
- Labels: `text-sm font-medium text-gray-300` (Inter, normal case)
- Body text: `text-gray-400` (Inter)
- Muted: `text-gray-500` (Inter)
- RULE: Every `<h1>`, `<h2>`, `<h3>` in admin panel MUST use `font-mono uppercase tracking-wider`

### Component Patterns
- Cards: `bg-dark-800 border border-dark-700 rounded-xl p-6`
- Inputs: `bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent`
- Primary buttons: `bg-primary hover:bg-primary-hover text-white font-medium rounded-lg px-5 py-2.5 transition-colors`
- Badges: `px-2.5 py-1 rounded-full text-xs font-medium` with status-specific bg/text colors
- Tables: dark-800 bg, dark-700 header bg, dark-700/50 row borders
- Sidebar active: `bg-primary/10 text-primary-light`

### Gradient Accents (for special UI elements)
- Badge highlight: `bg-gradient-to-r from-primary via-fuchsia-500 to-orange-500`
- CTA cards: `bg-gradient-to-br from-primary/20 to-fuchsia-600/20 border-primary/30`
- Progress bars: `bg-gradient-to-r from-primary to-fuchsia-500`

### Design Rules
1. NEVER use light backgrounds — everything is dark-800/700/900
2. Purple (#7c3aed) is the ONLY primary accent — no indigo, no blue as primary
3. Use gradient accents sparingly — only for featured/premium elements
4. All interactive elements need hover states and transitions
5. Cards always have border + rounded corners — never sharp corners
   (Admin: rounded-xl | Public: rounded-2xl — see Two Sides rules above)
6. Status colors are fixed: green=success, amber=warning, red=danger, blue=info
7. Maintain consistent spacing: p-6 for cards, gap-5 for form fields, mb-8 for page headers

---

## Livewire Patterns
- All components extend Livewire\Component
- Use PHP attributes: #[Layout], #[Url], #[Validate]
- Flash messages: session()->flash('success' or 'error', $message)
- Navigation: $this->redirect(route('...'), navigate: true)
- Use WithPagination trait for list pages
- Form validation with $this->validate() and $this->validateOnly()

---

## Commands
- Dev server: composer dev (runs at localhost:8021)
- Tests: php artisan test
- Lint/Format: ./vendor/bin/pint
- Build: npm run build
- All commands in Docker: docker compose exec app <command>