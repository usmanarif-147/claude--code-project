# /start-work-on — Module Orchestrator

Build an entire module from plan files: $ARGUMENTS

You are a **Project Manager**. Your job is to orchestrate the full implementation of a module by spawning parallel agents. You do NOT write code yourself — you delegate to agents and coordinate their work.

---

## Phase 0 — Analyze & Plan

### Step 1: Parse Input

`$ARGUMENTS` is a path to a module plan folder (e.g., `plan/3-tasks`).

1. List all `.md` files in the folder
2. **Exclude** any file named `requirements.md` (these are setup docs, not features)
3. Read every remaining `.md` file completely
4. Read `CLAUDE.md` completely
5. Read `resources/views/DESIGN-SYSTEM.md` completely

### Step 2: Derive Module Info

From the folder name, extract:
```
plan/3-tasks → module group = "tasks" (strip numeric prefix)
plan/5-ai-assistant → module group = "ai-assistant"
```

Spec output folder: `docs/[module-group]-module/`
Create this directory if it doesn't exist.

### Step 3: Determine Feature Dependencies

Read all plan files and identify:
- **Shared models** — if feature B references a model that feature A creates, A must be built first
- **Independent features** — features with no dependencies on each other can be built in parallel

Build a dependency order. Example:
```
Independent (can parallel): task-categories, quick-capture
Depends on task-categories: daily-task-planner, recurring-tasks
Depends on daily-task-planner: ai-task-prioritization, weekly-review-summary
Independent: calendar-view
```

### Step 4: Report Plan to User

Before doing any work, report:
```
MODULE: [Module Name]
Module group: [module-group]
Features found: [count]
Specs will be saved to: docs/[module-group]-module/

Feature build order:
  Batch 1 (parallel): feature-a, feature-b, feature-c
  Batch 2 (parallel, after batch 1): feature-d, feature-e
  Batch 3 (parallel, after batch 2): feature-f, feature-g

Proceeding to generate specs...
```

---

## Phase 1 — Generate ALL Specs (Parallel Agents)

Generate specs for ALL features before writing any code.

### Spawn Spec Agents

Launch up to 3 agents in parallel. Each agent generates one spec file.

For each feature, spawn an agent with this prompt:

```
You are a senior software architect generating a spec file.

Read these files completely before doing anything:
- CLAUDE.md
- resources/views/DESIGN-SYSTEM.md
- [plan file path for this feature]

Then follow the COMPLETE logic from .claude/commands/specs.md to generate the spec.

The spec file must be saved at: docs/[module-group]-module/[feature-name]-spec.md

Key rules from specs.md:
- Step 0: Output path is already determined (above)
- Step 3: Identify Side (ADMIN / PUBLIC / BOTH)
- Step 4: Generate ALL sections: Module Overview, Database Schema, File Map, Component Contracts, View Blueprints, Validation Rules, Edge Cases, Implementation Order
- Step 5: Report what was created

IMPORTANT: Read .claude/commands/specs.md completely and follow every step exactly.
```

If there are more than 3 features, process them in batches of 3.

### Validate Specs

After all spec agents complete:
1. Verify every spec file exists in `docs/[module-group]-module/`
2. Check for **shared tables** — if two specs define the same table, flag it
3. Check for **model conflicts** — if two specs create the same model, flag it

### PAUSE — Ask User to Review

Use AskUserQuestion:
```
All [N] specs generated for [Module Name]:

  - docs/[module-group]-module/feature-a-spec.md
  - docs/[module-group]-module/feature-b-spec.md
  - ...

Please review the specs. When ready, approve to start building.
```

**Do NOT proceed to Phase 2 until the user approves.**

---

## Phase 2 — Build ALL Features (Parallel)

### Build Strategy

Each feature needs: backend first → then frontend (because frontend reads real Livewire properties).

Spawn one **Builder Agent** per feature. Each builder agent handles BOTH backend and frontend for its feature, sequentially. Multiple builder agents run in parallel (up to 3 at a time).

### Determine Sidebar Responsibility

Pick ONE feature as the **sidebar owner** (preferably the last feature in the batch or the most "index-like" feature). Only this feature's frontend step will update the admin sidebar with ALL features for this module. All other frontend agents skip sidebar updates to avoid conflicts.

Tell non-sidebar agents: "Do NOT update the sidebar layout file."
Tell the sidebar-owner agent: "After creating your views, update the sidebar in `resources/views/components/layouts/admin.blade.php` to add ALL [module] features: [list all feature names and their routes]."

### Spawn Builder Agents

Process features in dependency-order batches. Launch up to 3 agents in parallel per batch.

For each feature, spawn a builder agent with this prompt:

```
You are a senior Laravel developer. You will build ONE feature end-to-end: backend first, then frontend.

## Your Feature
Spec file: docs/[module-group]-module/[feature-name]-spec.md

## Step 1: Backend
Read .claude/commands/build-backend.md completely, then follow every step:
- Read the spec file
- Read CLAUDE.md for architecture rules
- Read existing patterns (models, services, components, routes)
- Create files in order: migrations → models → services → routes → Livewire components
- Run: docker compose exec app php artisan migrate
- Run: docker compose exec app ./vendor/bin/pint
- Verify routes: docker compose exec app php artisan route:list | grep [feature]

Do NOT create any blade/view files in this step.

## Step 2: Frontend
Read .claude/commands/build-frontend.md completely, then follow every step:
- Read the spec file's view blueprints section
- Read resources/views/DESIGN-SYSTEM.md completely
- Read 2-3 existing admin views for reference
- Read the actual Livewire component files you just created in Step 1
- Cross-check: every wire:model and wire:click must match real properties/methods
- Create view files in the correct subfolder: resources/views/livewire/admin/[module-group]/[feature]/

[IF SIDEBAR OWNER]: After creating views, update the sidebar in resources/views/components/layouts/admin.blade.php. Add a collapsible "[Module Name]" group with links for ALL features: [list all features with route names]. Follow the existing sidebar pattern in CLAUDE.md.

[IF NOT SIDEBAR OWNER]: Do NOT modify the sidebar layout file.

## Step 3: Self-Check
After both steps complete, verify:
- All files from the spec exist
- No PHP errors: docker compose exec app php artisan route:list
- Code formatted: docker compose exec app ./vendor/bin/pint

Report: list all files created, any issues encountered.
```

### Wait for Batch Completion

If features have dependencies, wait for the current batch to finish before starting the next batch.

---

## Phase 3 — Review ALL Features (Parallel Agents)

After all builder agents complete, spawn review agents.

Launch up to 3 review agents in parallel. Each reviews one feature.

For each feature, spawn a review agent with this prompt:

```
You are a senior QA engineer reviewing a completed feature.

Read .claude/commands/review-working.md completely, then follow every step:

Spec file: docs/[module-group]-module/[feature-name]-spec.md

1. Read the spec file
2. Read CLAUDE.md and resources/views/DESIGN-SYSTEM.md
3. Find and read ALL files created for this feature
4. Run automated checks:
   - docker compose exec app php artisan route:list | grep [feature]
   - docker compose exec app ./vendor/bin/pint
5. Backend checklist: service logic, model fillable, validation, auth, no DB logic in components
6. Frontend checklist: dark theme, font-mono headings, correct wire bindings, empty states, breadcrumbs
7. Spec compliance: all features implemented, all routes registered, all views created

FIX any critical issues directly. Report pass/fail with details.
```

### Collect Review Results

After all review agents complete, collect their reports. If any agent reported critical unfixed issues, spawn a fix agent for that specific issue.

---

## Phase 4 — Final Verification & Report

### Run Final Checks

```bash
docker compose exec app php artisan migrate:status
docker compose exec app php artisan route:list | grep [module-group]
docker compose exec app ./vendor/bin/pint
docker compose exec app php artisan test
```

Fix any failures.

### Final Report

```
════════════════════════════════════════════════
  MODULE COMPLETE — [Module Name]
════════════════════════════════════════════════

Module group: [module-group]
Features completed: [X]/[Y]

Specs created:
  ✅ docs/[module-group]-module/feature-a-spec.md
  ✅ docs/[module-group]-module/feature-b-spec.md
  ...

Files created per feature:
  [feature-a]:
    ✅ app/Models/...
    ✅ app/Services/...
    ✅ app/Livewire/Admin/...
    ✅ resources/views/livewire/admin/...
    ✅ routes/admin/...

  [feature-b]:
    ✅ ...

Sidebar: ✅ Updated with all [module] features

Review results:
  ✅ feature-a: passed
  ✅ feature-b: passed
  ⚠️ feature-c: minor issues noted (list them)

Final checks:
  ✅ Migrations: up to date
  ✅ Routes: all registered
  ✅ Pint: clean
  ✅ Tests: passing

Status: READY FOR YOUR TESTING
════════════════════════════════════════════════
```

---

## Rules

1. **You are the manager** — delegate work to agents, do not write code yourself
2. **Specs before code** — never start building until all specs are generated and user approves
3. **Backend before frontend** — per feature, always. Frontend needs real Livewire properties
4. **Parallel where possible** — independent features build simultaneously (up to 3 agents)
5. **Sequential when dependent** — if feature B needs feature A's models, wait for A to finish
6. **One sidebar update** — only one agent updates the sidebar to avoid merge conflicts
7. **Fix before reporting** — review agents must fix critical issues, not just list them
8. **Docker for everything** — all commands via `docker compose exec app`
9. **Read before write** — every agent must read existing patterns before creating files
10. **Spec is source of truth** — agents build exactly what the spec defines, no more, no less
