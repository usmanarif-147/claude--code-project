# /start-work-on — Module Orchestrator

Build an entire module from plan files: $ARGUMENTS

You are a **Project Manager**. Your job is to orchestrate the full implementation of a module by spawning parallel agents. You do NOT write code yourself — you delegate to agents and coordinate their work.

**DOCKER RULE:** Every shell command in this project MUST run via `docker compose exec app <command>`. No exceptions — not for you, not for any agent you spawn. Never install packages or run commands directly on the host machine.

---

## Phase 0 — Analyze & Prepare

### Step 1: Baseline Health Check

Before touching anything, verify the project is healthy. Run these checks:

```bash
docker compose exec app php artisan migrate:status
docker compose exec app php artisan route:list
docker compose exec app ./vendor/bin/pint
docker compose exec app php artisan test
```

If ANY check fails → **STOP**. Report the failures to the user. Do NOT build on top of broken code. Say:
```
BASELINE CHECK FAILED — cannot proceed.
[list failures]
Please fix these issues first, then run /start-work-on again.
```

If all checks pass → continue.

### Step 2: Parse Input

`$ARGUMENTS` is a path to a module plan folder (e.g., `plan/3-tasks`).

1. List all `.md` files in the folder
2. **Exclude** any file named `requirements.md` (these are setup docs, not features)
3. Read every remaining `.md` file completely
4. Read `CLAUDE.md` completely
5. Read `resources/views/DESIGN-SYSTEM.md` completely

### Step 3: Derive Module Info

From the folder name, extract:
```
plan/3-tasks → module group = "tasks" (strip numeric prefix)
plan/5-ai-assistant → module group = "ai-assistant"
```

Spec output folder: `docs/[module-group]-module/`
Create this directory if it doesn't exist.

### Step 4: Determine Feature Dependencies

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

### Step 5: Report Plan to User

Before doing any work, report:
```
MODULE: [Module Name]
Module group: [module-group]
Features found: [count]
Baseline check: ✅ passed
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

DOCKER RULE: Every shell command MUST use `docker compose exec app <command>`. Never run commands directly.

Read these files completely before doing anything:
- CLAUDE.md
- resources/views/DESIGN-SYSTEM.md
- [plan file path for this feature]

Then read .claude/commands/specs.md completely and follow EVERY step exactly to generate the spec.

The spec file must be saved at: docs/[module-group]-module/[feature-name]-spec.md

Follow all sections from specs.md: Module Overview, Database Schema, File Map, Component Contracts, View Blueprints, Validation Rules, Edge Cases, Implementation Order.

Report what was created when done.
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

**CRITICAL — Migration Rule:** Builder agents create migration FILES but do NOT run `php artisan migrate`. The manager runs migrations ONCE after each batch of builder agents completes. This prevents parallel migration conflicts.

### Determine Sidebar Responsibility

Pick ONE feature as the **sidebar owner** — always the LAST feature in the LAST batch. Only this feature's frontend step will update the admin sidebar with ALL features for this module. All other frontend agents skip sidebar updates to avoid conflicts.

Tell non-sidebar agents: "Do NOT update the sidebar layout file."
Tell the sidebar-owner agent: "After creating your views, update the sidebar in `resources/views/components/layouts/admin.blade.php`. Read the FULL existing sidebar first. ADD a new collapsible '[Module Name]' group with links for ALL features: [list all feature names and their routes]. Do NOT modify or remove any existing sidebar items. Follow the sidebar pattern from CLAUDE.md exactly."

### Spawn Builder Agents

Process features in dependency-order batches. Launch up to 3 agents in parallel per batch.

For each feature, spawn a builder agent with this prompt:

```
You are a senior Laravel developer. You will build ONE feature end-to-end: backend first, then frontend.

DOCKER RULE: Every shell command MUST use `docker compose exec app <command>`. Never run commands directly on the host machine.

PROTECT EXISTING CODE — these rules are non-negotiable:
- NEVER modify existing model files — only create NEW model files
- NEVER modify existing service files — only create NEW service files
- NEVER modify existing route files — only create NEW route files
- NEVER modify existing Livewire component files
- NEVER modify existing view/blade files (except sidebar if you are the sidebar owner)
- If the spec says to update an existing file (e.g., PortfolioController for public side), ADD new code to it — never delete or rewrite existing code
- If you are unsure whether a file is new or existing, READ it first

## Your Feature
Spec file: docs/[module-group]-module/[feature-name]-spec.md

## Step 1: Backend
Read .claude/commands/build-backend.md completely, then follow every step:
- Read the spec file
- Read CLAUDE.md for architecture rules
- Read existing patterns (models, services, components, routes) — match their style exactly
- Create files in order: migrations → models → services → routes → Livewire components
- Do NOT run `php artisan migrate` — the manager will run migrations after this batch
- Run: docker compose exec app ./vendor/bin/pint
- Verify your route file syntax is correct (no PHP errors)

Do NOT create any blade/view files in this step.

## Step 2: Frontend
Read .claude/commands/build-frontend.md completely, then follow every step:
- Read the spec file's view blueprints section
- Read resources/views/DESIGN-SYSTEM.md completely — this is the single source of truth for admin UI
- Read 2-3 existing admin views for reference
- Read the actual Livewire component files you just created in Step 1
- Cross-check: every wire:model and wire:click MUST match real properties/methods from the component
- Create view files in the correct subfolder: resources/views/livewire/admin/[module-group]/[feature]/

[IF SIDEBAR OWNER]: Read the FULL existing sidebar in resources/views/components/layouts/admin.blade.php. ADD a new collapsible "[Module Name]" group with links for ALL module features: [list all features with route names]. Do NOT remove or modify any existing sidebar items. Follow the collapsible group pattern from CLAUDE.md sidebar rules.

[IF NOT SIDEBAR OWNER]: Do NOT modify the sidebar layout file or any existing blade file.

## Step 3: Self-Check
After both steps complete, verify:
- All files listed in the spec have been created
- Run: docker compose exec app ./vendor/bin/pint
- List all files you created (full paths)
- List any issues encountered

Report: files created, issues found.
```

### After Each Batch Completes

The manager (you) runs migrations ONCE for the entire batch:

```bash
docker compose exec app php artisan migrate
```

If migration fails → investigate, fix the migration file, and retry. Do NOT proceed to the next batch until migrations succeed.

Then verify routes for the batch:

```bash
docker compose exec app php artisan route:list | grep [module-group]
```

Then proceed to the next batch.

---

## Phase 3 — Review ALL Features (Parallel Agents)

After all builder agents complete and all migrations have run, spawn review agents.

Launch up to 3 review agents in parallel. Each reviews one feature.

For each feature, spawn a review agent with this prompt:

```
You are a senior QA engineer reviewing a completed feature.

DOCKER RULE: Every shell command MUST use `docker compose exec app <command>`. Never run commands directly.

Read .claude/commands/review-working.md completely, then follow every step:

Spec file: docs/[module-group]-module/[feature-name]-spec.md

1. Read the spec file completely
2. Read CLAUDE.md and resources/views/DESIGN-SYSTEM.md
3. Find and read ALL files created for this feature (check the spec's File Map section for paths)
4. Run automated checks:
   - docker compose exec app php artisan route:list | grep [feature]
   - docker compose exec app ./vendor/bin/pint
5. Backend checklist: service logic, model fillable, validation, auth, no DB logic in components
6. Frontend checklist: dark theme only, font-mono on all headings, correct wire:model/wire:click bindings, empty states, breadcrumbs on every page
7. Spec compliance: every feature implemented, every route registered, every view created
8. PROTECTION CHECK: verify no existing files were modified that shouldn't have been

FIX any critical issues directly (broken routes, wrong bindings, missing files, PHP errors).
Do NOT fix style preferences — only real bugs.

Report:
  - PASS or FAIL
  - Files reviewed (count)
  - Issues found and fixed (list each)
  - Minor issues noted but not fixed (list each)
```

### Collect Review Results

After all review agents complete, collect their reports:
- If all passed → proceed to Phase 4
- If any agent reported critical UNFIXED issues → spawn a targeted fix agent for that specific issue, then re-review

---

## Phase 4 — Final Verification & Report

### Run Full Test Suite

```bash
docker compose exec app php artisan migrate:status
docker compose exec app php artisan route:list | grep [module-group]
docker compose exec app ./vendor/bin/pint
docker compose exec app php artisan test
docker compose exec app npm run build
```

**Compare test results with the baseline from Phase 0.** If any previously passing test now fails, a builder agent broke existing code. Investigate and fix before reporting.

Fix any failures before producing the final report.

### Final Report

```
════════════════════════════════════════════════
  MODULE COMPLETE — [Module Name]
════════════════════════════════════════════════

Module group: [module-group]
Features completed: [X]/[Y]

Baseline: ✅ all checks passed before starting
Post-build: ✅ all checks still passing (no regressions)

Specs created:
  ✅ docs/[module-group]-module/feature-a-spec.md
  ✅ docs/[module-group]-module/feature-b-spec.md
  ...

Files created per feature:
  [feature-a]:
    ✅ database/migrations/...
    ✅ app/Models/...
    ✅ app/Services/...
    ✅ app/Livewire/Admin/[ModuleGroup]/[Feature]/...
    ✅ resources/views/livewire/admin/[module-group]/[feature]/...
    ✅ routes/admin/[module-group]/...

  [feature-b]:
    ✅ ...

Sidebar: ✅ Updated with all [module] features (nested under module group)

Review results:
  ✅ feature-a: passed
  ✅ feature-b: passed
  ⚠️ feature-c: minor issues noted (list them)

Final checks:
  ✅ Migrations: up to date
  ✅ Routes: [N] registered for this module
  ✅ Pint: clean
  ✅ Tests: passing (no regressions)
  ✅ Vite build: successful

Status: READY FOR YOUR TESTING
════════════════════════════════════════════════
```

---

## Rules

1. **You are the manager** — delegate work to agents, do not write code yourself
2. **Baseline first** — verify project health before starting. Stop if broken
3. **Specs before code** — never start building until all specs are generated and user approves
4. **Backend before frontend** — per feature, always. Frontend needs real Livewire properties
5. **Parallel where possible** — independent features build simultaneously (up to 3 agents)
6. **Sequential when dependent** — if feature B needs feature A's models, wait for A to finish
7. **Migrations by manager only** — agents create migration files, manager runs them between batches
8. **One sidebar update** — only the last agent in the last batch updates the sidebar
9. **Protect existing code** — agents NEVER modify existing files unless explicitly required by the spec
10. **Docker for everything** — every command via `docker compose exec app`. No exceptions. No host installs
11. **Fix before reporting** — review agents must fix critical issues, not just list them
12. **No regressions** — compare final test results with baseline. If something broke, fix it
13. **Read before write** — every agent must read existing patterns before creating files
14. **Spec is source of truth** — agents build exactly what the spec defines, no more, no less
