# /review — Self Review & Fix

Spec file to review: $ARGUMENTS

Read the spec file at: `$ARGUMENTS`

You are a senior QA engineer and code reviewer. Your job is to find and FIX real problems — not suggest style preferences.

---

## Step 1: Load Context

Read:
- `CLAUDE.md`
- `resources/views/DESIGN-SYSTEM.md`
- `$ARGUMENTS`

---

## Step 2: Discover All Files

Find every file created for this module:
```bash
# Find backend files
find app/Models app/Services app/Livewire/Admin -name "*.php" | xargs grep -l "[ModuleName]" 2>/dev/null

# Find view files
find resources/views/livewire/admin/[module-folder] -name "*.blade.php" 2>/dev/null

# Find routes
find routes/admin -name "[module]*.php" 2>/dev/null

# Find migrations
find database/migrations -name "*[module]*" 2>/dev/null
```

Read EVERY file completely. Do not review from memory.

---

## Step 3: Run Automated Checks First

```bash
docker compose exec app php artisan route:list | grep [module]
docker compose exec app php artisan migrate:status
docker compose exec app ./vendor/bin/pint
docker compose exec app php artisan test
```

Fix any failures immediately before continuing.

---

## Step 4: Backend Review Checklist

Go through each item. Fix critical issues. Note minor ones.

**Service Class:**
- [ ] ALL database logic is in the service — none in Livewire components
- [ ] Multi-table operations wrapped in `DB::transaction()`
- [ ] File uploads: old file deleted when updated or record deleted
- [ ] No raw SQL — using Eloquent properly

**Models:**
- [ ] Fillable fields match migration columns
- [ ] Relationships defined correctly
- [ ] Casts defined for boolean, array, datetime fields

**Livewire Components:**
- [ ] Components are thin — only validate, call service, flash, redirect
- [ ] Validation covers all user inputs
- [ ] No direct DB queries in components
- [ ] Auth/ownership checks — user can only access their own data
- [ ] Edit: loads correct record, can't load another user's record (IDOR check)
- [ ] Delete: confirms before deleting, cascades handled

**Routes:**
- [ ] All routes have auth middleware
- [ ] Route names follow existing naming convention
- [ ] No missing routes (every component has a route)

---

## Step 5: Frontend Review Checklist

**Design System Compliance:**
- [ ] No light backgrounds (no white, gray-100, gray-200 as backgrounds)
- [ ] All h1/h2/h3 use `font-mono uppercase tracking-wider`
- [ ] All inputs match: `bg-dark-700 border border-dark-600 rounded-lg`
- [ ] All primary buttons match: `bg-primary hover:bg-primary-hover`
- [ ] Cards match: `bg-dark-800 border border-dark-700 rounded-xl`

**Functionality:**
- [ ] Every `wire:model` references a real public property in the component
- [ ] Every `wire:click` references a real public method in the component
- [ ] Form validation errors display under each field
- [ ] Flash success/error messages display correctly
- [ ] Empty state exists on every list page
- [ ] Edit form pre-fills with existing data
- [ ] Delete has confirmation (wire:confirm or Alpine modal)

**Navigation:**
- [ ] New module added to sidebar
- [ ] Active state works on sidebar link
- [ ] Back links go to correct pages

---

## Step 6: Spec Compliance Check

Compare what was built against the spec:
- [ ] All features from spec are implemented
- [ ] All database columns from spec exist
- [ ] All routes from spec are registered
- [ ] All views from spec are created
- [ ] Public-facing pages (if any) are implemented

---

## Step 7: Fix All Critical Issues

Fix every critical issue found directly — do not just report them.

Critical = anything that causes:
- PHP errors or exceptions
- Broken routes (404)
- Form that doesn't save
- Wrong data displayed
- Security vulnerability (IDOR, unvalidated input, unescaped output)
- Dark theme violation (wrong background color)
- Broken Livewire binding

---

## Step 8: Final Report

```
REVIEW COMPLETE — [Module Name]

Automated Checks:
  ✅/❌ Routes: registered correctly
  ✅/❌ Migration: up to date
  ✅/❌ Pint: clean
  ✅/❌ Tests: passing

Issues Found & Fixed:
  🔧 [describe what was wrong and what you fixed]
  🔧 [...]

Minor Issues (not fixed — your awareness):
  ⚠️  [describe minor issue]

Spec Compliance:
  ✅ All [N] features implemented
  ✅ All [N] routes registered
  ✅ All [N] views created

Status: ✅ READY TO TEST
```

If there are unfixed critical issues, list them clearly and explain why they need your input.