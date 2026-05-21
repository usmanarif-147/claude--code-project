# Resume Builder — Plan

> Status: **Draft v3 — Phase 1 only.** Awaiting user approval. Iterate this file until aligned before any implementation begins.

---

## 1. Context

A resume is a **summary** of the user's portfolio data — it is *not* a re‑management UI for the portfolio modules. So this feature is being built incrementally in clear phases. **This plan covers Phase 1 only.**

### Phase 1 — goal (this plan)

Build the **visual shell** of the resume builder and prove the section‑level add‑data flow works end‑to‑end **without touching any database**.

What "done" looks like for Phase 1:

1. The user can open a new page at `/admin/portfolio/resume-builder`.
2. The page renders the **exact same two‑column blue layout** as `Usman_Arif_Laravel_Resume.pdf`.
3. On first load, every section is **empty** and shows a `+` icon.
4. Clicking `+` on a section opens a modal with the fields relevant to that section.
5. Saving the modal fills the section **in memory** (Livewire component state).
6. Refreshing the page **wipes all data** — it was never persisted. This is intentional.
7. **No new migrations. No existing tables touched. No service calls. No PDF export yet.**

### Future phases (out of scope here — listed only for context)

- Phase 2: persistence (two‑way binding with existing portfolio tables — `profiles`, `experiences`, `projects`, `skills`, `strengths`, `educations`).
- Phase 3: live PDF export with the same blue layout.
- Phase 4: any extra polish (reordering, deletion confirmations, etc.).

---

## 2. Reference layout (from the supplied PDF)

```
┌──────────────────────────────────────────────────────────────────────────┐
│  USMAN ARIF                                       (full‑width header)    │
│  SOFTWARE ENGINEER | LARAVEL & FULL‑STACK DEVELOPMENT                    │
│  ✆ phone   ✉ email   📍 location   ⌂ github                              │
└──────────────────────────────────────────────────────────────────────────┘
┌────────────────────────────────────┐  ┌────────────────────────────────┐
│ PROFILE                            │  │ SKILLS                         │
│ paragraph summary                  │  │  Backend & Frontend            │
│                                    │  │    [PHP] [Laravel] …           │
│ WORK EXPERIENCE                    │  │  Real‑Time                     │
│ Company  Start–End                 │  │    [WebSocket] [WebRTC]…       │
│ Role                               │  │                                │
│ • bullet                           │  │ STRENGTHS                      │
│ • bullet                           │  │   ★ title  ★ title …           │
│                                    │  │                                │
│ KEY PROJECTS                       │  │ KEY ACHIEVEMENTS               │
│ Project — short_description        │  │   • …                          │
│ company_or_url                     │  │                                │
│ • bullet                           │  │ EDUCATION                      │
│ Tech: …                            │  │   degree                       │
│                                    │  │   institution                  │
│                                    │  │   start – end                  │
└────────────────────────────────────┘  └────────────────────────────────┘
                       (blue accent #2563EB throughout)
```

Left column ~60% (Profile, Work Experience, Key Projects). Right column ~40% (Skills, Strengths, Key Achievements, Education). Header is full‑width above both columns.

---

## 3. Initial empty state (first thing the user sees)

```
┌──────────────────────────────────────────────────────────────────────┐
│  HEADER                                                       [ + ] │
│  (empty — name, tagline, phone, email, location, github)            │
└──────────────────────────────────────────────────────────────────────┘
┌────────────────────────────────┐  ┌────────────────────────────────┐
│ PROFILE                  [ + ] │  │ SKILLS                  [ + ]  │
│ (empty — summary paragraph)    │  │ (empty — grouped skill tags)   │
└────────────────────────────────┘  └────────────────────────────────┘
┌────────────────────────────────┐  ┌────────────────────────────────┐
│ WORK EXPERIENCE          [ + ] │  │ STRENGTHS               [ + ]  │
│ (empty — company / role …)     │  │ (empty — strength list)        │
│                                │  └────────────────────────────────┘
│                                │  ┌────────────────────────────────┐
│                                │  │ KEY ACHIEVEMENTS        [ + ]  │
│                                │  │ (empty — bullet list)          │
└────────────────────────────────┘  └────────────────────────────────┘
┌────────────────────────────────┐  ┌────────────────────────────────┐
│ KEY PROJECTS             [ + ] │  │ EDUCATION               [ + ]  │
│ (empty — project + bullets …)  │  │ (empty — degree / school …)    │
└────────────────────────────────┘  └────────────────────────────────┘
```

Each section card:
- Section title (top‑left)
- `+` button (top‑right)
- Faint placeholder text describing the section
- Once filled (in memory), placeholder is replaced by the rendered content and the `+` becomes a small `✎ Edit` button.

---

## 4. Modal flows (Phase 1 — in‑memory only)

The same modal component renders 8 different forms based on a `section` argument. Save just updates the parent Livewire component's public properties — nothing is written to the database.

### 4.1 Header
```
Full Name | Tagline | Phone | Email | Location | GitHub URL
```

### 4.2 Profile
```
Summary (textarea)
```

### 4.3 Work Experience  (repeatable rows)
```
Per row: Company | Role | Start | End | ☐ Current | Bullets[]
[ + add another job ]
```

### 4.4 Key Projects  (repeatable rows)
```
Per row: Title | Short description | Bullets[] | Tech stack (comma list) | Demo URL | GitHub URL
[ + add another project ]
```

### 4.5 Skills  (grouped tags)
```
Per group: Category name | Tags[]
[ + add another category ]
```

### 4.6 Strengths  (flat list)
```
List of short strength labels
```

### 4.7 Key Achievements  (flat list)
```
List of bullet strings
```

### 4.8 Education  (repeatable rows)
```
Per row: Degree | Institution | Start | End
[ + add another ]
```

---

## 5. Architecture (intentionally minimal)

Follows `CLAUDE.md` folder rules.

| Concern        | Path                                                                   |
|----------------|------------------------------------------------------------------------|
| Livewire root  | `app/Livewire/Admin/Portfolio/ResumeBuilder/`                          |
| Views root     | `resources/views/livewire/admin/portfolio/resume-builder/`             |
| Routes         | `routes/admin/portfolio/resume-builder.php` (auto‑discovered)          |
| Sidebar entry  | nested inside Portfolio group as "Resume Builder"                      |
| Migrations     | **None**                                                               |
| Models         | **None**                                                               |
| Services       | **None**                                                               |
| Controllers    | **None**                                                               |

### Livewire components — only one

`ResumeBuilderIndex` does everything for Phase 1:

- Public properties hold the in‑memory data for each section:
  - `array $header = []`
  - `string $profile = ''`
  - `array $experiences = []`
  - `array $projects = []`
  - `array $skillGroups = []`
  - `array $strengths = []`
  - `array $achievements = []`
  - `array $educations = []`
- A `?string $openModal` property tracks which modal (if any) is visible — `'header'`, `'profile'`, `'work'`, etc.
- Methods:
  - `openModal(string $section)` — sets `$openModal` and pre‑fills modal form state from the matching property.
  - `closeModal()` — resets `$openModal = null` and clears modal form state.
  - `save{Section}()` for each of the 8 sections — writes the modal form back to the matching public property.
- Re‑rendering shows updated section content immediately (standard Livewire reactivity).
- A page refresh resets all public properties → blank skeleton again. **By design.**

That's the entire backend for Phase 1.

---

## 6. Files to create

```
app/Livewire/Admin/Portfolio/ResumeBuilder/
  ResumeBuilderIndex.php

resources/views/livewire/admin/portfolio/resume-builder/
  index.blade.php

routes/admin/portfolio/
  resume-builder.php
```

Plus one small edit to `resources/views/components/layouts/admin.blade.php` for the sidebar link.

**Total: 3 new files + 1 small edit.**

The 8 modals live as conditional Blade sections inside `index.blade.php` (using `@if ($openModal === 'header') … @endif`). No separate modal component needed for Phase 1 — keeps everything in one place and easy to reason about.

---

## 7. Verification

1. `php artisan route:list | grep resume-builder` shows the route registered.
2. Sign in, click "Resume Builder" in the Portfolio sidebar group → page loads at `/admin/portfolio/resume-builder`.
3. Page renders the empty skeleton (matches §3), all 8 `+` buttons visible, blue accent, two‑column layout.
4. Click `+` on Header → modal opens with the 6 header fields. Fill them, click Save → modal closes, header section now renders the entered name in blue + tagline + contact line; the `+` becomes `✎ Edit`. Clicking `✎ Edit` reopens the modal pre‑filled with the saved values.
5. Repeat for the 7 remaining sections; each modal saves into in‑memory state and the section renders correctly in the right column.
6. Add multiple experiences / projects / skill groups / educations / strengths / achievements → each renders in its slot, in input order.
7. Hard refresh the page → all data is gone, skeleton is empty again. ✅ (Expected for Phase 1.)
8. `./vendor/bin/pint` passes.

---

## 8. Out of scope (explicitly NOT in Phase 1)

- ❌ Persisting data to any table.
- ❌ Two‑way binding with `profiles` / `experiences` / `projects` / `skills` / `strengths` / `educations`.
- ❌ Live PDF preview rendering.
- ❌ "Download PDF" button.
- ❌ Reordering items.
- ❌ Per‑section delete / clear buttons.
- ❌ Any new migrations or model files.
- ❌ Any new service class.

All of these are deferred to later phases and will be planned separately.

---

## 9. Open questions for the user

1. **`+` placement** — top‑right corner of each empty section card (as in §3 mockups). Confirm OK, or do you want it centered inside the empty area?
2. **Sidebar position** — "Resume Builder" link nested under the existing Portfolio sidebar group, just below "Resume Generator". Confirm OK.
3. **Live preview color scheme** — preview is rendered on a **white paper background** even though the admin UI is dark (so it looks like the printed PDF). Confirm OK.

---

> ✏️ **Next step:** read this Phase 1 plan and tell me what to change. I update **this same file** on each iteration. When you say it's approved, I'll call ExitPlanMode and start implementing Phase 1 only.
