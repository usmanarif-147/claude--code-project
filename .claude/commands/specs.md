# /spec — Generate Spec from Plan

Read the plan file at: $ARGUMENTS

You are a senior software architect. Your job is ONLY to produce a spec file — do NOT write any code.

---

## Step 0: Derive Output Path from Plan File Path

Extract the module group and feature name from the plan file path:

```
Input:  plan/1-portfolio/manage-skills.md
              ^^^^^^^^^^ ^^^^^^^^^^^^
              module group  feature name

Output: docs/portfolio-module/manage-skills-spec.md
```

Rules:
- Strip any numeric prefix from the folder name: `1-portfolio` → `portfolio`
- Module folder: `docs/[module-group]-module/`
- Spec file: `[feature-name]-spec.md` (feature name = plan filename without .md)
- Create the `docs/[module-group]-module/` directory if it doesn't exist

Write down the derived output path before proceeding.

---

## Step 1: Read Project Context

Read these files completely before doing anything:
- `CLAUDE.md` — project architecture, two sides rules, folder structure rules, patterns, tech stack
- `resources/views/DESIGN-SYSTEM.md` — UI components, colors, patterns

---

## Step 2: Read the Plan File

Read the plan file provided in $ARGUMENTS completely.

---

## Step 3: Identify Which Side This Feature Belongs To

Before writing anything, determine the side by asking:
- Does this feature manage/edit data? → ADMIN
- Does this feature display data to visitors? → PUBLIC
- Does this feature do both? → BOTH

```
ADMIN only   — feature lives entirely in admin panel (CRUD, forms, management)
PUBLIC only  — feature lives on the landing page (display, read-only)
BOTH         — feature has an admin side (manage data) AND a public side (display data)
```

Based on CLAUDE.md "Two Sides of This Project", apply the correct rules:

### If ADMIN:
- Layout: `components.layouts.admin`
- Livewire components under: `app/Livewire/Admin/[ModuleGroup]/[Feature]/`
- Views under: `resources/views/livewire/admin/[module-group]/[feature]/`
- Routes in: `routes/admin/[module-group]/[feature].php` with auth middleware
- Cards: `rounded-xl`, color alias: `primary`
- All logic in Service classes

### If PUBLIC:
- Layout: `components.layouts.app`
- For Portfolio module: update `PortfolioController` and add section to `welcome.blade.php`
- For other modules: create a dedicated controller and view file (e.g., `ChatbotController` + `chatbot.blade.php`)
- Routes in: `routes/web.php` — no auth middleware
- Cards: `rounded-2xl` with `border-white/[0.04]`, color alias: `accent`
- Plain Blade + Alpine.js only — NO Livewire (except ContactForm)
- Data fetched in controller, passed to view — read-only

### If BOTH:
- Apply ADMIN rules for the admin side files
- Apply PUBLIC rules for the public side files
- Clearly separate the two in every section of the spec
- Shared: Models and Services are used by both sides

Write the identified side at the top of the spec file:
```
Side: ADMIN | PUBLIC | BOTH
```

---

## Step 4: Generate Spec File

Create a spec file at the path derived in Step 0: `docs/[module-group]-module/[feature-name]-spec.md`

The spec must contain ALL of the following sections:

---

### 1. MODULE OVERVIEW
- Side: ADMIN | PUBLIC | BOTH (determined in Step 3)
- What this module does in 2-3 sentences
- List of all features (from the plan)
- Admin features (what admin can do) — if ADMIN or BOTH
- Public features (what visitors can see/do) — if PUBLIC or BOTH

---

### 2. DATABASE SCHEMA

For every table needed, provide:

```
Table: table_name
Columns:
  - id (bigint, primary key, auto increment)
  - column_name (type, nullable/required, default, notes)
  - ...
  - created_at, updated_at (timestamps)

Indexes: list any indexes needed
Foreign keys: list relationships
```

---

### 3. FILE MAP

List every single file to create with its exact path.
Apply folder structure rules from CLAUDE.md for every path.

```
MIGRATIONS:
  - database/migrations/xxxx_create_[table]_table.php

MODELS:
  - Single model  → app/Models/[ModelName].php
  - 2+ related    → app/Models/[ModuleName]/[ModelName].php
    - fillable fields
    - relationships
    - casts

SERVICES:
  - app/Services/[ModuleName]Service.php
    - list every method: methodName(params): returnType — what it does

--- ADMIN FILES (if ADMIN or BOTH) ---

LIVEWIRE COMPONENTS:
  - app/Livewire/Admin/[ModuleGroup]/[Feature]/[ComponentName].php
    - public properties
    - list every method: methodName() — what it does
  - resources/views/livewire/admin/[module-group]/[feature]/[view-name].blade.php
    - what this view displays

ROUTES (admin):
  - routes/admin/[module-group]/[feature].php
    - list every route: METHOD /path → ComponentName → route-name

--- PUBLIC FILES (if PUBLIC or BOTH) ---

CONTROLLER:
  - Portfolio module → update existing PortfolioController.php index() method
  - Other modules → create a dedicated controller: app/Http/Controllers/[Name]Controller.php

VIEWS (public):
  - Portfolio module → add section to welcome.blade.php
  - Other modules → resources/views/[view-name].blade.php (standalone page)
    - what this view displays
    - Alpine.js interactions needed

ROUTES (public):
  - routes/web.php
    - list any new route if needed
```

---

### 4. COMPONENT CONTRACTS

#### Admin Components (if ADMIN or BOTH)

For each Livewire component:

```
Component: App\Livewire\Admin\[ModuleGroup]\[Feature]\[ComponentName]
Namespace:  App\Livewire\Admin\[ModuleGroup]\[Feature]

Properties:
  - $propertyName (type) — purpose

Methods:
  - methodName(params)
    Input: what it receives
    Does: what it does step by step
    Output: redirect or flash message

Validation Rules:
  - field: rule1|rule2|rule3
```

#### Public Controller (if PUBLIC or BOTH)

```
Portfolio module:
  Controller: PortfolioController (existing — update index() method)
  - $variableName = ServiceClass::method() — passed to welcome.blade.php

Other modules:
  Controller: [Name]Controller (new dedicated controller)
  - method: what it does
  - returns: which view with which variables
```

---

### 5. VIEW BLUEPRINTS

#### Admin Views (if ADMIN or BOTH)

For each admin view:

```
View: resources/views/livewire/admin/[module-group]/[feature]/[view-name].blade.php
Layout: components.layouts.admin
Side: ADMIN
Page title: "..."

Design rules (from CLAUDE.md admin side):
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:
  - Page header: title + action button (if any)
  - [describe each section]
  - Table columns (if list page): col1, col2, col3, actions
  - Form fields (if form): field label, input type, validation hint
  - Empty state: what shows when no data
```

#### Public Views (if PUBLIC or BOTH)

For each public view or welcome.blade.php section:

```
View: welcome.blade.php (new section) OR resources/views/[name].blade.php
Layout: components.layouts.app
Side: PUBLIC

Design rules (from CLAUDE.md public side):
  - Cards: rounded-2xl, border-white/[0.04]
  - Color alias: accent / accent-light
  - NO Livewire — plain Blade + Alpine.js only
  - Animations: Alpine x-intersect for scroll reveal

Sections:
  - [describe each section of the public view]
  - Data variables available from the controller
  - Alpine.js interactions needed
```

---

### 6. VALIDATION RULES

List every form and its validation (admin forms only — public side is read-only):

```
Form: [form name]
  - field_name: required|string|max:255
  - field_name: nullable|image|mimes:jpg,png|max:2048
  ...
```

---

### 7. EDGE CASES & BUSINESS RULES

List things that need special handling:
- What happens on delete (cascade? block? soft delete?)
- Unique constraints
- Null handling
- Order/sort logic
- File upload cleanup on update/delete
- Public visibility rules (e.g. only show published, only show visible=true)
- Sections hidden on public side when no data exists

---

### 8. IMPLEMENTATION ORDER

List the exact sequence files should be created:
```
1. Migration(s)
2. Model(s)
3. Service class
4. [If ADMIN or BOTH] Routes file
5. [If ADMIN or BOTH] Livewire component(s) — in dependency order
6. [If ADMIN or BOTH] Admin views — in dependency order
7. [If PUBLIC or BOTH] Controller update or creation
8. [If PUBLIC or BOTH] Public view (welcome.blade.php section for Portfolio, standalone view for others)
```

---

## Step 5: Confirm

After writing the spec file, report:
- Spec saved at: docs/[module-group]-module/[feature-name]-spec.md
- Side: ADMIN | PUBLIC | BOTH
- Number of tables, files, components, routes
- Any assumptions you made that I should review
- Any unclear parts in the plan that need my input

Do NOT write any code. The spec is a blueprint only.