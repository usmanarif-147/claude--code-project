# /build-backend — Backend Implementation

Read the spec file at: $ARGUMENTS

You are a senior Laravel backend developer. Your job is ONLY backend — do NOT create any blade/view files.

**IMPORTANT: The spec is the source of truth — not this command's examples.** Not every module follows standard CRUD. Some modules may only need a list page with no form. Some may need API integration services (HTTP clients, response parsing), scheduled artisan commands, queue jobs, or AI service calls (prompt templates, Gemini/Groq API). Build exactly what the spec defines — no more, no less. The patterns and examples below are references to show style and conventions, not rigid templates. If the spec says a feature needs an artisan command instead of a Livewire component, build the artisan command. Think like a senior developer — adapt to requirements.

---

## Step 1: Load Context

Read these files completely before writing any code:
- `CLAUDE.md` — architecture rules, two sides rules, folder structure rules, naming conventions, commands
- The spec file at: $ARGUMENTS

---

## Step 2: Identify the Side

Read the spec file and find the line:
```
Side: ADMIN | PUBLIC | BOTH
```

This determines exactly which backend files to create:

```
ADMIN only → create: migrations, models, services, routes, Livewire components
PUBLIC only → create: migrations, models, services, controller (update existing or create new), public routes
BOTH       → create: migrations, models, services, routes, Livewire components + public controller + public routes
```

Write down the side before proceeding. Do not mix admin and public patterns.

---

## Step 3: Read Existing Patterns

Before creating any file, read a similar existing file first:

- Before creating a Model → read an existing Model in app/Models/
- Before creating a Service → read an existing Service in app/Services/
- Before creating a Livewire component → read an existing component in app/Livewire/Admin/
- Before creating a routes file → read an existing file in routes/admin/ (match the module group)
- Before creating a migration → read an existing migration in database/migrations/
- Before updating or creating a public controller → read the full existing PortfolioController.php for style reference

This is mandatory. Match the exact patterns, naming, and style you find.

---

## Step 4: Determine Correct File Paths (Before Creating Anything)

Before writing a single file, confirm the correct path for every file using CLAUDE.md Folder Structure Rules:

### Livewire Components (ADMIN or BOTH only)
ALWAYS place in module-group/feature subfolder — never directly in Admin/
```
✅ app/Livewire/Admin/[ModuleGroup]/[Feature]/[Feature]Index.php
✅ app/Livewire/Admin/[ModuleGroup]/[Feature]/[Feature]Form.php
❌ app/Livewire/Admin/[Feature]Index.php
❌ app/Livewire/Admin/[ModuleGroup]/[Feature]Index.php  (missing feature subfolder)
```

### Models
Check if the module has 2 or more related models:
- If YES → place all related models in a subfolder: `app/Models/[ModuleName]/`
- If NO (single model) → place directly in: `app/Models/`
```
✅ app/Models/Blog/BlogPost.php         (module has BlogPost + BlogPostTag)
✅ app/Models/Blog/BlogPostTag.php
✅ app/Models/Skill.php                 (single model — no subfolder)
❌ app/Models/BlogPost.php              (wrong — has related model)
```

### Services
Always flat — no subfolders:
```
✅ app/Services/[ModuleName]Service.php
```

### Routes (ADMIN or BOTH only)
One file per feature inside the module group folder:
```
✅ routes/admin/[module-group]/[feature].php
Examples:
  routes/admin/portfolio/skills.php
  routes/admin/tasks/daily-planner.php
  routes/admin/job-search/job-feed.php
❌ routes/admin/portfolio/daily-planner.php   (wrong module group)
```

### Public Controller (PUBLIC or BOTH only)
```
Portfolio module → update existing PortfolioController.php index() method
Other modules   → create a dedicated controller: app/Http/Controllers/[Name]Controller.php
```

Write down all confirmed paths before proceeding to Step 5.

---

## Step 5: Create Files in This Exact Order

### 5.1 Migrations
- Create all migrations from the spec
- Column types, indexes, foreign keys exactly as specced
- Run migration: `docker compose exec app php artisan migrate`
- Confirm it ran successfully before continuing

### 5.2 Models
- Place in correct path confirmed in Step 4
- Namespace must match folder: `App\Models\[ModuleName]\[ModelName]` for subfolder models
- Fillable fields as defined in spec
- Relationships (hasMany, belongsTo, etc.) as defined in spec
- Casts (array, boolean, datetime) as defined in spec
- No business logic in models

### 5.3 Service Class
- Create `app/Services/[ModuleName]Service.php`
- Import models using correct namespace (subfolder if applicable)
- Implement every method listed in the spec
- ALL database logic goes here — no exceptions
- Use `DB::transaction()` for operations that touch multiple tables
- Handle file uploads/deletions here if needed
- Return data or throw exceptions — never flash messages from service

### 5.4 Admin Routes File (ADMIN or BOTH only — skip if PUBLIC)
- Create `routes/admin/[module-group]/[feature].php` (create the module-group folder if it doesn't exist)
- Import Livewire component using correct namespace: `App\Livewire\Admin\[ModuleGroup]\[Feature]\[ComponentName]`
- Follow exact route naming convention from existing routes
- Apply auth middleware as seen in existing routes

### 5.5 Livewire Components (ADMIN or BOTH only — skip if PUBLIC)
- Place in correct path confirmed in Step 4: `app/Livewire/Admin/[ModuleGroup]/[Feature]/`
- Namespace must be: `App\Livewire\Admin\[ModuleGroup]\[Feature]`
- Properties exactly as defined in component contracts in spec
- Methods exactly as defined in component contracts in spec
- Import models using correct namespace (subfolder if applicable)
- Components are THIN: validate → call service → flash message → redirect
- Use `#[Layout('components.layouts.admin')]` attribute
- Use `#[Validate]` or `$this->validate()` as seen in existing components
- Flash: `session()->flash('success', '...')` or `session()->flash('error', '...')`
- Redirect: `$this->redirect(route('...'), navigate: true)`
- Do NOT create blade view files

### 5.6 Public Controller (PUBLIC or BOTH only — skip if ADMIN)

**Portfolio module** — update existing PortfolioController:
- Read the full existing `PortfolioController.php` before making any changes
- Add new data variables to the existing `index()` method only
- Pass new variables to `welcome.blade.php` via the existing return view() call
- Do NOT create a new controller for Portfolio features

**Other modules** — create a dedicated controller:
- Create `app/Http/Controllers/[Name]Controller.php`
- Read `PortfolioController.php` first for style reference
- Use the Service class to fetch data — no direct DB queries in the controller
- Return the appropriate view with data
- Add the public route in `routes/web.php`

---

## Step 6: Verify

Run these checks:
```bash
docker compose exec app php artisan route:list | grep [module-name]
docker compose exec app php artisan test
docker compose exec app ./vendor/bin/pint
```

Fix any errors before finishing.

---

## Step 7: Report

When done, report exactly:

```
BACKEND COMPLETE — [Module Name]
Side: ADMIN | PUBLIC | BOTH

Files Created:
  ✅ database/migrations/[filename]
  ✅ app/Models/[correct-path]/[filename]
  ✅ app/Services/[filename]

  [If ADMIN or BOTH:]
  ✅ routes/admin/[module-group]/[filename]
  ✅ app/Livewire/Admin/[ModuleGroup]/[Feature]/[filename]

  [If PUBLIC or BOTH:]
  ✅ app/Http/Controllers/[Controller].php — updated or created

Namespaces Used:
  ✅ Model:     App\Models\[ModuleName]\[ModelName] or App\Models\[ModelName]
  ✅ Component: App\Livewire\Admin\[ModuleGroup]\[Feature]\[ComponentName]

Migration: ✅ ran successfully
Routes: ✅ registered (list them)
Tests: ✅ passing
Pint: ✅ clean

Ready for: /build-frontend docs/[module]-module/[feature]-spec.md
```

If anything failed, fix it and report what you fixed.