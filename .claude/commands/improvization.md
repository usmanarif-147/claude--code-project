# /improvization — Module Improvement Orchestrator

Improve an existing module from update plan files: $ARGUMENTS

You are a **Senior Project Manager** handling updates to a live module. This is MORE CRITICAL than building from scratch — you are modifying working code. One wrong change can break the updated module AND linked modules. Think like a surgeon: precise, careful, aware of the full body.

**DOCKER RULE:** Every shell command MUST run via `docker compose exec app <command>`. No exceptions.

---

## Argument Parsing

`$ARGUMENTS` may contain an optional mode followed by the plan folder path:

```
/improvization plan/portfolio-module-updates                     → mode: dev (default)
/improvization production plan/tasks-module-updates              → mode: production
```

**Mode rules:**
- `dev` (default when omitted) — database tables for this module can be empty. Destructive migrations allowed (drop column, drop table). Data loss is acceptable.
- `production` — data EXISTS and must be PRESERVED. No destructive migrations without a data backup/migration strategy. Every migration must have a working `down()` method. Column renames need data copy. Column drops need data backup first.

---

## Phase 0 — Deep Analysis

### Step 1: Baseline Health Check

```bash
docker compose exec app php artisan migrate:status
docker compose exec app php artisan route:list
docker compose exec app ./vendor/bin/pint
docker compose exec app php artisan test
```

If ANY check fails → **STOP**. Report failures. Do NOT modify code on top of broken code.

### Step 2: Parse Input & Read Context

1. Detect mode from arguments (`production` or default `dev`)
2. List all `.md` files in the plan folder
3. Read every plan file completely
4. Read `CLAUDE.md` completely
5. Read `resources/views/DESIGN-SYSTEM.md` completely
6. Read `docs/PROJECT-STATUS.md` — identify the target module and all other modules

### Step 3: Identify Target Module

From the folder name, extract the module group:
```
plan/portfolio-module-updates → module group = "portfolio"
plan/tasks-module-updates → module group = "tasks"
plan/job-search-module-updates → module group = "job-search"
```

Rule: strip `-module-updates` suffix to get the module group name.

Verify this module EXISTS in `docs/PROJECT-STATUS.md`. If not → **STOP**. Report: "Module '[name]' not found in PROJECT-STATUS.md. Use /start-work-on to build it first."

### Step 4: Deep-Read the Target Module

This is the most important step. You MUST understand the module completely before planning changes.

Spawn an **Explore agent** to read the entire module:

```
Thoroughly explore the [module-group] module. Read EVERY file listed below completely and report:

1. ALL model files — list every property ($fillable, $casts, relationships), note foreign keys
2. ALL service files — list every public method with its signature and what it does
3. ALL Livewire component files — list every public property and method
4. ALL blade view files — note what wire:model and wire:click bindings they use
5. ALL route files — list every route with name, method, path, component
6. The sidebar section for this module in admin.blade.php

Files to read (from PROJECT-STATUS.md):
[list all files for this module from PROJECT-STATUS.md]

Also read the original specs if they exist:
[list spec files from docs/[module-group]-module/]

Report the complete current state of this module.
```

### Step 5: Map Cross-Module Dependencies

Spawn another **Explore agent** in parallel to find what OTHER modules depend on this module:

```
Search the ENTIRE codebase for references to [module-group] module's models and tables. I need to know which OTHER modules will be affected if we change this module.

Search for:
1. grep -r "use App\\Models\\[each model name]" app/ routes/ --include="*.php" — find every file that imports this module's models
2. grep -r "[table_name]" app/ database/ --include="*.php" — find every file that references this module's tables
3. Check if any other module's service or component queries this module's tables
4. Check if any views reference this module's routes (route('admin.[module].*'))
5. Check if the dashboard aggregates data from this module

For each reference found outside the target module, report:
  - Which file references it
  - Which module that file belongs to
  - What exactly it references (model, table, route, column)
  - What kind of reference (import, query, relationship, route link)
```

### Step 6: Report to User

```
IMPROVEMENT ANALYSIS — [Module Name]
Mode: [dev / production]

Target module: [module-group]
Current state: [N] models, [N] services, [N] components, [N] routes
Update plans found: [N] files

Cross-module dependencies:
  - Dashboard → queries [table].[column] for stats
  - [Other module] → imports [ModelName] for relationship
  - ... (or "None found" if isolated)

Updates to process:
  1. [feature-updates-name] — [1-line summary from plan]
  2. [feature-updates-name] — [1-line summary from plan]
  ...

[If production mode:]
⚠️  PRODUCTION MODE — data preservation required. Destructive migrations will include backup strategies.

Proceeding to generate improvement specs...
```

---

## Phase 1 — Generate Improvement Specs (Parallel Agents)

Generate specs for ALL updates before modifying any code.

Improvement specs are saved in: `docs/[module-group]-module/updates/`
Create this directory if it doesn't exist.

### Spawn Spec Agents

Launch up to 3 agents in parallel. Each generates one improvement spec.

For each update, spawn an agent with this prompt:

```
You are a senior software architect creating an IMPROVEMENT SPEC for an existing module. This is NOT a new build — you are planning changes to working code.

DOCKER RULE: Every shell command MUST use `docker compose exec app <command>`.

MODE: [dev / production]
[If production]: Data exists and must be preserved. Every schema change needs a safe migration strategy. No data loss allowed.

Read these files completely:
- CLAUDE.md
- resources/views/DESIGN-SYSTEM.md
- docs/PROJECT-STATUS.md
- [update plan file path]
- The original spec (if exists): docs/[module-group]-module/[original-feature-spec].md

Then read ALL existing files for the feature being updated:
[list relevant model, service, component, view, route files]

Generate the improvement spec at: docs/[module-group]-module/updates/[feature-name]-updates-spec.md

The spec MUST contain these sections:

### 1. UPDATE OVERVIEW
- What is being changed and why
- Type: ADD new functionality / REMOVE functionality / IMPROVE existing functionality
- Mode: dev / production

### 2. CURRENT STATE (BEFORE)
- Existing database columns relevant to this change
- Existing model properties and methods affected
- Existing service methods affected
- Existing component properties and methods affected
- Existing view sections affected
- Current behavior that will change

### 3. TARGET STATE (AFTER)
- New/modified database columns
- New/modified model properties and methods
- New/modified service methods
- New/modified component properties and methods
- New/modified view sections
- New behavior after the change

### 4. MIGRATION PATH (BEFORE → AFTER)
For each change, specify:
- What to ADD (new columns, new methods, new files)
- What to MODIFY (changed columns, updated methods, updated views)
- What to REMOVE (dropped columns, deleted methods, removed view sections)
- Order of operations (what must happen first)

[If production mode, for each schema change]:
- Data backup strategy (if dropping/renaming columns)
- Data migration steps (if transforming data)
- Reversibility: can this be rolled back with `down()` method?

### 5. FILES TO MODIFY
List every existing file that will be changed:
```
MODIFY: app/Models/[ModelName].php
  - Add: $fillable += ['new_column']
  - Add: new relationship method
  - Remove: old method (if applicable)

MODIFY: app/Services/[ServiceName].php
  - Add: newMethod()
  - Modify: existingMethod() — change [what]
  - Remove: oldMethod() (if applicable)

MODIFY: app/Livewire/Admin/[path]/Component.php
  - Add: new property $newProp
  - Modify: existing method save() — add new field handling

MODIFY: resources/views/livewire/admin/[path]/view.blade.php
  - Add: new form field section
  - Modify: table columns
  - Remove: old button (if applicable)
```

### 6. FILES TO CREATE (if any)
List any new files needed:
```
CREATE: database/migrations/xxxx_add_[column]_to_[table].php
CREATE: app/Models/NewModel.php (if new model needed)
```

### 7. FILES TO DELETE (if any)
```
DELETE: app/Models/OldModel.php
DELETE: resources/views/livewire/admin/old-view.blade.php
```

### 8. CROSS-MODULE IMPACT
List every file OUTSIDE this module that must be checked or updated:
```
CHECK: app/Livewire/Admin/Dashboard.php — queries [table].[column], verify column still exists
UPDATE: app/Services/OtherService.php — imports [OldModel], update import path if model moved
NO IMPACT: [Other module] — no references to changed files
```

If no cross-module impact: "None — this change is isolated to the [module] module."

### 9. VALIDATION RULES (if forms changed)
List updated validation for any modified forms.

### 10. EDGE CASES & RISKS
- What could go wrong?
- What data could be lost? (production mode)
- What queries could break?
- What views could show wrong data?

### 11. IMPLEMENTATION ORDER
Exact sequence:
1. Migration(s)
2. Model changes
3. Service changes
4. Component changes
5. View changes
6. Cross-module fixes
7. Cleanup (delete old files, remove orphaned code)

Report what was created when done.
```

### Validate Specs

After all spec agents complete:
1. Verify every spec exists in `docs/[module-group]-module/updates/`
2. Check for conflicts between specs — if two specs modify the same file, flag it and determine safe order
3. Aggregate all cross-module impacts into a single list

### PAUSE — Ask User to Review

```
All [N] improvement specs generated for [Module Name]:

  - docs/[module-group]-module/updates/feature-a-updates-spec.md
  - docs/[module-group]-module/updates/feature-b-updates-spec.md
  ...

Cross-module impact summary:
  - Dashboard: [describe impact]
  - [Other module]: [describe impact]
  - Or: "No cross-module impact"

Mode: [dev / production]

Please review the specs. When ready, approve to start implementing.
```

**Do NOT proceed until user approves.**

---

## Phase 2 — Implement Updates (Parallel Where Safe)

### Conflict Check

Before spawning agents, check if any two update specs modify the SAME file. If yes, those updates MUST be sequential (not parallel) to avoid merge conflicts. Independent updates can be parallel.

### Spawn Update Agents

Launch up to 3 agents in parallel (only for non-conflicting updates).

For each update, spawn an agent with this prompt:

```
You are a senior Laravel developer implementing an UPDATE to an existing module. You are modifying working code — be precise and careful.

DOCKER RULE: Every shell command MUST use `docker compose exec app <command>`.

MODE: [dev / production]
[If production]: Data must be preserved. Migrations must be reversible. No destructive operations without the backup strategy from the spec.

## Your Update
Spec file: docs/[module-group]-module/updates/[feature-name]-updates-spec.md

## Rules for Modifying Existing Code
- READ the full file before making ANY change
- Change ONLY what the spec says to change — nothing else
- When adding to a file (new method, new property), add it in a logical position — don't append randomly
- When modifying a method, keep the parts that don't need changing IDENTICAL
- When removing code, verify nothing else in the file references it before deleting
- NEVER rewrite an entire file — make surgical edits only

## Step 1: Backend Changes
Read the spec's "FILES TO MODIFY" and "FILES TO CREATE" sections.

For each migration:
  - Create the migration file
  - [If production]: Ensure `down()` method works and reverses the change
  - Do NOT run `php artisan migrate` — the manager will run it

For each model change:
  - Read the FULL existing model file
  - Make ONLY the changes listed in the spec (add to $fillable, add relationship, etc.)
  - Do NOT change anything the spec doesn't mention

For each service change:
  - Read the FULL existing service file
  - Add new methods, modify listed methods, remove listed methods
  - Do NOT change methods the spec doesn't mention

For each component change:
  - Read the FULL existing component file
  - Add new properties, modify listed methods
  - Do NOT change methods the spec doesn't mention

For each route change:
  - Read the FULL existing route file
  - Add new routes or modify existing ones as specified

Run: docker compose exec app ./vendor/bin/pint

## Step 2: Frontend Changes
Read the spec's view modifications.

For each view change:
  - Read the FULL existing view file
  - Read resources/views/DESIGN-SYSTEM.md for UI patterns
  - Make ONLY the changes listed in the spec
  - Verify all wire:model and wire:click still match the component after your backend changes
  - New UI elements must follow DESIGN-SYSTEM.md exactly

## Step 3: Cross-Module Fixes
Read the spec's "CROSS-MODULE IMPACT" section.

For each file outside this module that needs updating:
  - Read the FULL file
  - Make the specific fix described in the spec
  - Verify the fix doesn't break that module's other functionality

## Step 4: Cleanup
If the spec lists files to DELETE:
  - Verify nothing imports or references the file
  - Delete it

If the spec lists code to REMOVE from existing files:
  - Verify nothing in the same file calls the removed code
  - Remove it

## Step 5: Self-Check
- All changes from the spec are implemented
- Run: docker compose exec app ./vendor/bin/pint
- List all files modified, created, deleted
- List any issues encountered

Report: files changed, issues found.
```

### After Each Batch

Manager runs migrations:

```bash
docker compose exec app php artisan migrate
```

If migration fails → investigate, fix, retry. Do NOT proceed until migrations succeed.

Verify routes:

```bash
docker compose exec app php artisan route:list | grep [module-group]
```

---

## Phase 3 — Review (Broader Than /start-work-on)

### Step 3A: Review Updated Features

Launch up to 3 review agents in parallel. Each reviews one update.

```
You are a senior QA engineer reviewing an UPDATE to an existing module. This is higher risk than reviewing a new build — focus on what changed and what could have broken.

DOCKER RULE: Every shell command MUST use `docker compose exec app <command>`.

MODE: [dev / production]

Spec file: docs/[module-group]-module/updates/[feature-name]-updates-spec.md

1. Read the improvement spec completely — understand BEFORE, AFTER, and MIGRATION PATH
2. Read CLAUDE.md and DESIGN-SYSTEM.md
3. Read ALL files that were modified (listed in the spec)
4. Verify each change matches the spec exactly — nothing more, nothing less

5. Check for COLLATERAL DAMAGE:
   - Did any unchanged method in a modified file break? (check for references to renamed/removed things)
   - Do all wire:model and wire:click bindings still match component properties/methods?
   - Are there orphaned imports (use statements for removed models/classes)?
   - Are there orphaned routes (routes pointing to removed components)?

6. Run automated checks:
   - docker compose exec app php artisan route:list | grep [module-group]
   - docker compose exec app ./vendor/bin/pint

7. [If production mode]:
   - Verify migration has working down() method
   - Verify no data loss in column changes
   - Check for orphaned storage files (if file upload changed)

FIX any critical issues directly. Report pass/fail with details.
```

### Step 3B: Review Cross-Module Impact

If the specs identified cross-module dependencies, spawn a dedicated **Cross-Module Review Agent**:

```
You are a senior QA engineer verifying that updates to the [module-group] module did NOT break other modules.

DOCKER RULE: Every shell command MUST use `docker compose exec app <command>`.

The following cross-module impacts were identified:
[list from spec aggregation in Phase 1]

For EACH impacted file:
1. Read the file completely
2. Verify it still works with the changes made to [module-group]
3. Check: do all queries reference columns that still exist?
4. Check: do all imports reference classes that still exist at the same path?
5. Check: do all route references still resolve?

Also run a broad check:
- docker compose exec app php artisan route:list (full list — check for any errors)
- docker compose exec app php artisan test

FIX any issues found. Report pass/fail.
```

If no cross-module impact was identified, skip this step.

### Collect Results

- If all passed → Phase 4
- If critical unfixed issues → spawn fix agent, then re-review

---

## Phase 4 — Final Verification & Report

### Run Full Test Suite

```bash
docker compose exec app php artisan migrate:status
docker compose exec app php artisan route:list
docker compose exec app ./vendor/bin/pint
docker compose exec app php artisan test
docker compose exec app npm run build
```

**Compare with Phase 0 baseline.** Any regression = fix before reporting.

### Final Report

```
════════════════════════════════════════════════════════════
  MODULE UPDATED — [Module Name]
════════════════════════════════════════════════════════════

Module group: [module-group]
Mode: [dev / production]
Updates completed: [X]/[Y]

Baseline: ✅ all checks passed before starting
Post-update: ✅ all checks still passing (no regressions)

Improvement specs:
  ✅ docs/[module-group]-module/updates/feature-a-updates-spec.md
  ✅ docs/[module-group]-module/updates/feature-b-updates-spec.md
  ...

Changes per update:
  [feature-a-updates]:
    Modified: [list files]
    Created: [list files]
    Deleted: [list files]
    Migration: [describe]

  [feature-b-updates]:
    Modified: [list files]
    ...

Cross-module impact:
  ✅ Dashboard: verified, no issues
  ✅ [Other module]: verified, updated [file] to fix [issue]
  Or: No cross-module impact

Review results:
  ✅ feature-a-updates: PASS
  ✅ feature-b-updates: PASS
  ✅ Cross-module review: PASS

Final checks:
  ✅ Migrations: up to date
  ✅ Routes: all registered
  ✅ Pint: clean
  ✅ Tests: passing (no regressions)
  ✅ Vite build: successful

Status: READY FOR YOUR TESTING
════════════════════════════════════════════════════════════
```

### Update Project Status

Update `docs/PROJECT-STATUS.md`:
- Find the target module's section
- Update its "Last updated" date
- Add or modify feature entries as needed
- Add a brief note about what was changed

Do NOT rewrite the entire section — only update what changed.

---

## Rules

1. **You are the manager** — delegate to agents, do not write code yourself
2. **Baseline first** — verify project health before starting. Stop if broken
3. **Understand before changing** — deep-read the entire module + cross-module links before generating specs
4. **Specs before code** — never start modifying until all specs are generated and user approves
5. **Surgical edits only** — modify ONLY what the spec says. Read full files before editing. Never rewrite entire files
6. **Backend before frontend** — per update, always
7. **Parallel only when safe** — if two updates touch the same file, run them sequentially
8. **Migrations by manager only** — agents create files, manager runs them between batches
9. **Production mode = data safety** — reversible migrations, no destructive ops without backup, preserve data
10. **Dev mode = flexibility** — destructive operations allowed, empty tables acceptable
11. **Cross-module review is mandatory** — if any dependency was found, it MUST be verified after changes
12. **Docker for everything** — every command via `docker compose exec app`. No exceptions
13. **Fix before reporting** — review agents must fix critical issues, not just list them
14. **No regressions** — compare final tests with baseline. If something broke, fix it
15. **Update PROJECT-STATUS.md** — reflect the changes so future sessions know what was updated
