# /build-frontend — Frontend Implementation

Read the spec file at: $ARGUMENTS

You are a senior frontend developer. Your job is ONLY views — do NOT touch any PHP, migration, model, service, or route files.

**IMPORTANT: The spec is the source of truth — not this command's examples.** Not every module follows standard CRUD with Index + Form pages. Some modules may only need a single settings page, a dashboard view, a list with no create form, or a real-time chat interface. Build exactly what the spec's view blueprints define — no more, no less. The patterns and examples below are references to show style and conventions, not rigid templates. If the spec says a feature needs a kanban board instead of a table, build the kanban board. Think like a senior developer — adapt to requirements while staying within the design system.

---

## Step 1: Load Context (Mandatory — Do Not Skip)

Read ALL of these before writing a single line of HTML:

1. `CLAUDE.md` — design rules, TWO SIDES rules, folder structure rules, layout patterns
2. `resources/views/DESIGN-SYSTEM.md` — single source of truth for ADMIN views ONLY
   → For PUBLIC views: read `resources/views/welcome.blade.php` directly instead
3. The spec file at $ARGUMENTS — read the Side declaration and view blueprints section
4. Read existing views relevant to the side you are building:
   - For ADMIN views → read 2-3 existing files in `resources/views/livewire/admin/`
   - For PUBLIC views → read the existing `resources/views/welcome.blade.php` completely

---

## Step 2: Identify the Side

Read the spec file and find:
```
Side: ADMIN | PUBLIC | BOTH
```

This determines which views to build and which design rules to apply.
**Never mix admin patterns into public views or public patterns into admin views.**

```
ADMIN only → build admin views only
PUBLIC only → build public landing page section only
BOTH       → build admin views first, then public section separately
```

---

## Step 3: Confirm Correct File Paths (Before Creating Anything)

### If ADMIN or BOTH — Admin View Paths
Views MUST mirror the Livewire subfolder structure exactly (module-group/feature/):
```
✅ resources/views/livewire/admin/[module-group]/[feature]/index.blade.php
✅ resources/views/livewire/admin/[module-group]/[feature]/form.blade.php
Examples:
  resources/views/livewire/admin/portfolio/skills/index.blade.php
  resources/views/livewire/admin/tasks/daily-planner/index.blade.php
❌ resources/views/livewire/admin/skill-index.blade.php
❌ resources/views/livewire/admin/portfolio/skill-index.blade.php
```
Naming: list view → `index.blade.php`, create/edit view → `form.blade.php`

Cross-check with backend — read the actual Livewire component files:
- Confirm public properties → use these in `wire:model`
- Confirm public methods → use these in `wire:click`
- Confirm namespace → verify correct module folder name

### If PUBLIC or BOTH — Public View Paths
```
Portfolio module:
  ✅ resources/views/welcome.blade.php — add a new section inside this file

Other modules:
  ✅ resources/views/[name].blade.php — standalone public page

Never:
  ❌ resources/views/livewire/[anything].blade.php  — never put public views here
  ❌ resources/views/livewire/admin/[anything]       — never mix with admin views
```

Cross-check with backend — read the actual public controller file:
- Confirm which variables are passed to the view
- Use ONLY those variable names — never invent new ones

Write down all confirmed paths before proceeding to Step 4.

---

## Step 4: Build Admin Views (ADMIN or BOTH only — skip entirely if PUBLIC)

### Design Rules — Admin Side
From CLAUDE.md admin side rules — enforce on EVERY admin view:

- Layout: `components.layouts.admin` (set via `#[Layout]` on Livewire component — not in view)
- Backgrounds: dark-950, dark-900, dark-800, dark-700 ONLY — NEVER light backgrounds
- Cards: `bg-dark-800 border border-dark-700 rounded-xl p-6`
- Inputs: `bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent`
- Primary buttons: `bg-primary hover:bg-primary-hover text-white font-medium rounded-lg px-5 py-2.5 transition-colors`
- Tables: dark-800 background, dark-700 header, dark-700/50 row borders
- Badges: `px-2.5 py-1 rounded-full text-xs font-medium`
- Color alias: `primary` / `primary-light` — NEVER use `accent` in admin views
- Headings: `font-mono font-bold uppercase tracking-wider text-white`
- Sidebar active: `bg-primary/10 text-primary-light`

### List/Index Pages
```
1. Page header
   - Title: font-mono uppercase tracking-wider
   - Subtitle: text-gray-400
   - Action button (Add New): bg-primary, top right

2. Filter/search bar (if needed)
   - bg-dark-800 border border-dark-700 rounded-xl p-4

3. Table or card grid
   - Table: bg-dark-800 border border-dark-700 rounded-xl overflow-hidden
   - Header row: bg-dark-700 text-gray-400 text-xs uppercase tracking-wider
   - Data rows: border-b border-dark-700/50 hover:bg-dark-700/30 transition-colors
   - Action column: Edit (text-primary-light) and Delete (text-danger) links

4. Empty state (when no records)
   - Centered icon + heading + description + action button

5. Pagination (if using WithPagination)
   - Match existing pagination style
```

### Form Pages (Create/Edit)
```
1. Page header
   - Title: "Add [Item]" or "Edit [Item]"
   - Back link to list page

2. Form card: bg-dark-800 border border-dark-700 rounded-xl p-6
   - Labels above inputs
   - Error messages: text-danger text-sm mt-1
   - Field spacing: gap-5 between fields

3. Submit button: bg-primary — match existing alignment pattern

4. Flash messages: match existing layout pattern
```

### Sidebar Navigation
- Add new module link inside its parent module group — never at root level
- Analytics link goes INSIDE the module group (per CLAUDE.md Sidebar Navigation Rules)
- Match exact existing sidebar item structure and icon style
- Active state: `bg-primary/10 text-primary-light`

### Wire Up Livewire
- `wire:model` for inputs, `wire:click` for actions, `wire:loading` for button states
- Every binding MUST reference a real property/method from the actual component file
- Do not invent properties or methods

---

## Step 5: Build Public Views (PUBLIC or BOTH only — skip entirely if ADMIN)

### Design Rules — Public Side
From CLAUDE.md public side rules — enforce on EVERY public view:

- Layout: `components.layouts.app` — already set in welcome.blade.php
- Cards: `rounded-2xl` with `border-white/[0.04]` — NOT rounded-xl, NOT border-dark-700
- Color alias: `accent` / `accent-light` — NEVER use `primary` in public views
- Buttons: accent background + black text — NOT white text
- Backgrounds: dark-950 page, dark-800 cards — same dark palette, softer borders
- Animations: Alpine.js `x-intersect` for scroll reveal on every new section
- NO Livewire components — plain Blade only (ContactForm is the only exception)
- Data comes from controller variables — never hardcode content

### How to Add a Public Section

**Portfolio module** — adding to `welcome.blade.php`:
1. Read `welcome.blade.php` completely first
2. Find the correct position in the page for this section (follow the section order in the spec)
3. Add the new section in that position — do not append blindly at the bottom
4. Match the exact Alpine.js scroll animation pattern used by existing sections
5. Use only the variables passed from `PortfolioController` — confirm from the actual controller file
6. Add the section anchor link to the navigation bar if the spec requires it

**Other modules** — creating a standalone public view:
1. Read `welcome.blade.php` for design reference (card styles, animation patterns, color aliases)
2. Create a new view file: `resources/views/[name].blade.php`
3. Use `components.layouts.app` layout
4. Follow the same public design rules (accent colors, rounded-2xl, Alpine.js animations)
5. Use only the variables passed from the dedicated controller

### Public Section Structure
```
<!-- Section: [name] -->
<section id="[section-id]" class="py-20"
  x-data="{ visible: false }"
  x-intersect="visible = true">

  <div x-show="visible"
       x-transition:enter="transition duration-700"
       x-transition:enter-start="opacity-0 translate-y-8"
       x-transition:enter-end="opacity-100 translate-y-0">

    <!-- Section heading -->
    <h2 class="font-mono font-bold uppercase tracking-wider text-white">
      [Section Title]
    </h2>

    <!-- Cards: rounded-2xl border-white/[0.04] -->
    <!-- Color: accent / accent-light — never primary -->
    <!-- Data: from $variableName passed by the public controller -->

  </div>
</section>
```

### Public Conditional Rendering
Some sections should be hidden when no data exists:
```blade
@if($items->isNotEmpty())
  <!-- section content -->
@endif
```
Check the spec Edge Cases section to see which sections need this.

---

## Step 6: Verify Everything

### Admin Views Checklist (if ADMIN or BOTH)
- [ ] All views in correct subfolder: `resources/views/livewire/admin/[module-group]/[feature]/`
- [ ] No flat files in `resources/views/livewire/admin/` root
- [ ] No light backgrounds — dark theme only
- [ ] All headings: font-mono uppercase
- [ ] Cards: rounded-xl border-dark-700
- [ ] Color alias: `primary` — never `accent`
- [ ] All wire:model and wire:click match real component properties/methods
- [ ] Empty states on all list pages
- [ ] Flash messages display correctly
- [ ] Sidebar updated — analytics nested inside module group, not standalone

### Public Views Checklist (if PUBLIC or BOTH)
- [ ] Portfolio: new section added in correct position in welcome.blade.php / Other: standalone view created
- [ ] Cards: rounded-2xl border-white/[0.04] — NOT rounded-xl
- [ ] Color alias: `accent` — never `primary`
- [ ] Buttons: accent bg + black text — NOT white text
- [ ] Alpine x-intersect scroll animation on the section
- [ ] NO Livewire components added
- [ ] All variables match exactly what the public controller passes
- [ ] Section hidden with @if when no data exists (if spec requires)
- [ ] Nav link added if spec requires

### No Mixing Check
- [ ] Admin views contain ZERO public-side patterns (no rounded-2xl, no accent alias)
- [ ] Public views contain ZERO admin-side patterns (no rounded-xl, no primary alias, no wire: directives)

---

## Step 7: Report

When done, report exactly:

```
FRONTEND COMPLETE — [Module Name]
Side: ADMIN | PUBLIC | BOTH

[If ADMIN or BOTH:]
Admin Files Created:
  ✅ resources/views/livewire/admin/[module-group]/[feature]/index.blade.php
  ✅ resources/views/livewire/admin/[module-group]/[feature]/form.blade.php

Admin Files Updated:
  ✅ [sidebar updated — link nested inside correct module group]

Admin Design Check:
  ✅ Dark theme — rounded-xl, primary alias, font-mono headings
  ✅ Livewire bindings verified against actual component files
  ✅ Sidebar: analytics nested inside module, not standalone

[If PUBLIC or BOTH:]
Public Files Updated:
  ✅ [Portfolio: welcome.blade.php section added / Other: standalone view created]

Public Design Check:
  ✅ rounded-2xl cards, accent alias, black text on buttons
  ✅ Alpine x-intersect scroll animation
  ✅ No Livewire components
  ✅ Variables verified against the public controller

No Mixing:
  ✅ Admin views use only admin patterns
  ✅ Public views use only public patterns

Ready for: /review-working docs/[module]-module/[feature]-spec.md
```