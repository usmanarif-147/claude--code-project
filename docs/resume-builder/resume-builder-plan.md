# Resume Builder — Plan

> Status: **Draft v2** — awaiting user approval. Iterate this file until both sides agree before any implementation begins.

---

## 1. Context

The user wants a new admin feature: a **dynamic resume builder** where the resume page starts as an **empty skeleton** showing only 8 fixed sections, each with a `+` icon. Clicking `+` opens a modal where the user enters that section's details. As sections fill, the **same page acts as the live preview** rendered in the exact same blue two‑column style as `Usman_Arif_Laravel_Resume.pdf`. A "Download PDF" button exports the same layout to PDF.

**Why this is being built:**
- A single place where the user can visually compose a resume and see the result instantly, without bouncing between separate CRUD pages.
- Visual fidelity to the supplied PDF is required (fixed layout, no template picker).

**Intended outcome:**
- New page at `/admin/portfolio/resume-builder` showing the empty skeleton on first load **when no data exists** in the relevant existing tables.
- Each of the 8 sections has its own modal for adding / editing data.
- The modal **reads from and writes back to the existing portfolio tables** — **no new migrations**, **no new models**. Two‑way binding with the same data the existing admin CRUD pages use.
- Live HTML preview renders the blue two‑column layout from the reference PDF on the same page.
- "Download PDF" button exports the preview to PDF.

---

## 2. ⚠️ Key architecture change (v2)

The previous draft proposed 11 new tables. **Discard that.** This feature is a **new UI on top of existing tables** — nothing else.

| Section            | Existing table(s) reused                                                       | Existing service to call            |
|--------------------|--------------------------------------------------------------------------------|-------------------------------------|
| Header             | `users` (name, email) + `profiles` (tagline, phone, location, github_url)      | `ProfileSettingsService`            |
| Profile summary    | `profiles` (`bio` column)                                                      | `ProfileSettingsService`            |
| Work Experience    | `experiences` + `experience_responsibilities` (filter `type = 'work'`)         | `ExperienceService`                 |
| Key Projects       | `projects` (+ `tech_stack` JSON column)                                        | `ProjectService`                    |
| Skills             | `skills` (with `category`, `proficiency`) — grouped by `category` in the UI    | `SkillService`                      |
| Strengths          | `strengths`                                                                    | `StrengthService`                   |
| Key Achievements   | **No table exists yet** — see §10 open questions                               | TBD                                 |
| Education          | `educations`                                                                   | `EducationService`                  |

Resume builder Livewire components **delegate all DB writes to the existing services**. No new service class is required for sections that already have one. (Only exception is Key Achievements — pending the user's decision in §10.)

---

## 3. Existing table columns (verified from migrations)

```
profiles                                  experiences
  user_id (unique FK)                       type            ('work' / 'education')
  tagline                                   role
  bio                                       company
  profile_image                             start_date
  secondary_email                           end_date
  phone                                     is_current
  location                                  description
  linkedin_url                              degree
  github_url                                field_of_study
  fiverr_url                                sort_order
  youtube_url                               is_active
  availability_status
  timezone                                experience_responsibilities
  language                                  experience_id (FK)
                                            description
                                            sort_order
projects
  title                                   skills
  slug                                      title
  short_description                         icon
  description                               category
  cover_image                               proficiency
  tech_stack (JSON)                         sort_order
  demo_url                                  is_active
  github_url
  is_featured                             strengths
  sort_order                                title
  is_active                                 icon
  completed_at                              sort_order
                                            is_active
educations
  degree_title
  institution
  start_date
  end_date
  sort_order
```

> No `user_id` columns on the per‑item tables — they're single‑user implicit. The builder respects that pattern and does not introduce one.

---

## 4. Reference layout (from the supplied PDF)

```
┌──────────────────────────────────────────────────────────────────────────┐
│  USMAN ARIF                                       (full‑width header)    │
│  SOFTWARE ENGINEER | LARAVEL & FULL‑STACK DEVELOPMENT                    │
│  ✆ phone   ✉ email   📍 location   ⌂ github                              │
└──────────────────────────────────────────────────────────────────────────┘
┌────────────────────────────────────┐  ┌────────────────────────────────┐
│ PROFILE                            │  │ SKILLS (grouped by `category`) │
│ paragraph summary (profiles.bio)   │  │   Backend & Frontend           │
│                                    │  │     [PHP] [Laravel] …          │
│ WORK EXPERIENCE                    │  │   Real‑Time                    │
│ Company  Start–End                 │  │     [WebSocket] [WebRTC]…      │
│ Role                               │  │                                │
│ • bullet  (experience_resp.)       │  │ STRENGTHS                      │
│ • bullet                           │  │   ★ title  ★ title …           │
│                                    │  │                                │
│ KEY PROJECTS                       │  │ KEY ACHIEVEMENTS               │
│ Project — short_description        │  │   • …                          │
│ company_or_url                     │  │                                │
│ • bullet                           │  │ EDUCATION                      │
│ Tech: …                            │  │   degree_title                 │
│                                    │  │   institution                  │
│                                    │  │   start_date – end_date        │
└────────────────────────────────────┘  └────────────────────────────────┘
```

---

## 5. Initial empty state (what the user sees first)

```
┌──────────────────────────────────────────────────────────────────────┐
│  HEADER                                                       [ + ] │
│  (empty — name, tagline, phone, email, location, github)            │
└──────────────────────────────────────────────────────────────────────┘
┌────────────────────────────────┐  ┌────────────────────────────────┐
│ PROFILE                  [ + ] │  │ SKILLS                  [ + ]  │
│ (empty — summary paragraph)    │  │ (empty — skills grouped by cat)│
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

                       Top action bar:   [ Download PDF ]
```

Each section card shows:
- Section title (top‑left)
- `+` button (top‑right) when the underlying table has no data for that section
- Faint placeholder text describing the section content
- Once data exists, the placeholder is replaced by the rendered content and the `+` becomes a small `✎ Edit` button.

> Two‑way binding rule: emptiness is decided per section, not globally. If `profiles` exists but `experiences` is empty, only the Work Experience section shows the `+` placeholder — the rest already shows live data pulled from existing CRUD.

---

## 6. Modal flows — one per section

Each modal reads / writes the **same fields** that the existing CRUD pages do, so data flows through one source of truth.

### 6.1 Header modal → `users` + `profiles`
```
┌─────────────────────────────────────────┐
│  Header Details                  [ × ]  │
│ ─────────────────────────────────────── │
│  Full Name      [______________________]│  ← users.name
│  Email          [______________________]│  ← users.email
│  Tagline        [______________________]│  ← profiles.tagline
│  Phone          [______________________]│  ← profiles.phone
│  Location       [______________________]│  ← profiles.location
│  GitHub URL     [______________________]│  ← profiles.github_url
│                                         │
│                   [ Cancel ]  [ Save ]  │
└─────────────────────────────────────────┘
```
Updates the authenticated user row + their single `profiles` row.

### 6.2 Profile summary modal → `profiles.bio`
```
┌─────────────────────────────────────────┐
│  Profile Summary                 [ × ]  │
│ ─────────────────────────────────────── │
│  Summary                                │
│  ┌─────────────────────────────────────┐│
│  │ (textarea — profiles.bio)           ││
│  └─────────────────────────────────────┘│
│                   [ Cancel ]  [ Save ]  │
└─────────────────────────────────────────┘
```

### 6.3 Work Experience modal → `experiences` (`type=work`) + `experience_responsibilities`
```
┌──────────────────────────────────────────────────────┐
│  Work Experience                              [ × ]  │
│ ──────────────────────────────────────────────────── │
│  ── Job #1 ──────────────────────────── [ remove ]   │
│  Company        [____________________]    ← company  │
│  Role           [____________________]    ← role     │
│  Start          [______]   End  [______]  ← dates    │
│  ☐ Current                             ← is_current  │
│  Bullets:                              ← experience_responsibilities.description │
│   • [_________________________________] [ x ]        │
│   [ + add bullet ]                                   │
│                                                      │
│  [ + add another job ]                               │
│                          [ Cancel ]  [ Save ]        │
└──────────────────────────────────────────────────────┘
```

### 6.4 Key Projects modal → `projects`
```
┌──────────────────────────────────────────────────────┐
│  Key Projects                                  [ × ] │
│ ──────────────────────────────────────────────────── │
│  ── Project #1 ─────────────────────── [ remove ]    │
│  Title              [_____________________] ← title          │
│  Short description  [_____________________] ← short_description │
│  Description        [_____________________] ← description (used for bullets — see §10) │
│  Demo URL           [_____________________] ← demo_url        │
│  GitHub URL         [_____________________] ← github_url      │
│  Tech stack         [Laravel, Rust, Kafka, …] ← tech_stack (JSON) │
│                                                                  │
│  [ + add another project ]                                       │
│                          [ Cancel ]  [ Save ]                    │
└──────────────────────────────────────────────────────┘
```

### 6.5 Skills modal → `skills` (grouped by `category`)
```
┌──────────────────────────────────────────────────────┐
│  Skills                                        [ × ] │
│ ──────────────────────────────────────────────────── │
│  ── Group #1 ──────────────────────── [ remove ]     │
│  Category name   [Backend & Frontend       ] ← skills.category  │
│  Skills          [PHP][Laravel][Livewire]     ← skills.title    │
│                  + add skill (with proficiency 0–100) │
│                                                      │
│  [ + add another category ]                          │
│                          [ Cancel ]  [ Save ]        │
└──────────────────────────────────────────────────────┘
```
Adding a tag inserts a `skills` row with that `category`. Removing a tag deletes the row.

### 6.6 Strengths modal → `strengths`
```
┌─────────────────────────────────────────┐
│  Strengths                       [ × ]  │
│ ─────────────────────────────────────── │
│  • [API Design                ] [ x ]   ← strengths.title
│  • [Problem Solving           ] [ x ]
│  [ + add strength ]                     │
│                   [ Cancel ]  [ Save ]  │
└─────────────────────────────────────────┘
```

### 6.7 Key Achievements modal → see §10
Decision pending: either a new `achievements` table (one‑off migration), or store as JSON on `profiles`, or reuse an existing field.

### 6.8 Education modal → `educations`
```
┌──────────────────────────────────────────────────────┐
│  Education                                     [ × ] │
│ ──────────────────────────────────────────────────── │
│  ── Entry #1 ─────────────────────── [ remove ]      │
│  Degree title   [B.S. Software Engineering        ] ← degree_title   │
│  Institution    [University of Management & Tech. ] ← institution    │
│  Start date     [______]   End date  [______]       ← start_/end_date │
│                                                                       │
│  [ + add another ]                                                    │
│                          [ Cancel ]  [ Save ]                         │
└──────────────────────────────────────────────────────┘
```

---

## 7. Architecture (follows `CLAUDE.md` rules)

### 7.1 Module placement

The builder is a Portfolio sub‑feature.

| Concern        | Path                                                                  |
|----------------|-----------------------------------------------------------------------|
| Livewire root  | `app/Livewire/Admin/Portfolio/ResumeBuilder/`                         |
| Views root     | `resources/views/livewire/admin/portfolio/resume-builder/`            |
| Routes         | `routes/admin/portfolio/resume-builder.php` (auto‑discovered)         |
| Sidebar entry  | nested inside Portfolio group as "Resume Builder"                     |
| Service        | **None** — reuse existing `ProfileSettingsService`, `ExperienceService`, `ProjectService`, `SkillService`, `StrengthService`, `EducationService` |
| Migrations     | **None** (unless §10 lands on a new `achievements` table)             |
| Models         | **None** — reuse `Profile`, `Experience\Experience`, `Experience\ExperienceResponsibility`, `Project\Project`, `Skill\Skill`, `Strength`, `Education`, `User` |

### 7.2 Livewire components (only 2)

| Component                                   | Responsibility                                                                                                          |
|---------------------------------------------|-------------------------------------------------------------------------------------------------------------------------|
| `ResumeBuilderIndex`                        | The page. Loads existing data from all tables, renders empty/filled state per section, manages which modal is open, exposes Download PDF |
| `SectionModal`                              | One modal component, switches form by `section` prop. On save it calls the **matching existing service**. Emits `section-saved` so the parent re-pulls fresh data. |

### 7.3 Live preview

A Blade partial — `_preview.blade.php` — renders the resume in the blue two‑column PDF layout. Same view is the editor and the preview. Empty sections show `+`; filled sections show rendered content with `✎ Edit`.

Preview wrapped in `bg-white text-slate-900` so it looks like paper inside the dark admin chrome.

### 7.4 PDF export

- Route: `GET /admin/portfolio/resume-builder/download`
- Handler: thin controller `ResumeBuilderDownloadController` (binary stream)
- Template: new print‑optimized blade at `resources/views/resume/templates/builder.blade.php` (A4, blue accent, two‑column)
- Uses the existing PDF library in the project (whichever `ResumeService` already uses)

---

## 8. Files to create

```
app/
  Livewire/Admin/Portfolio/ResumeBuilder/
    ResumeBuilderIndex.php
    SectionModal.php
  Http/Controllers/
    ResumeBuilderDownloadController.php

resources/views/
  livewire/admin/portfolio/resume-builder/
    index.blade.php
    _preview.blade.php
    section-modal.blade.php
  resume/templates/
    builder.blade.php

routes/admin/portfolio/
  resume-builder.php
```

Plus one tiny edit to `resources/views/components/layouts/admin.blade.php` for the sidebar link.

**Zero new migrations. Zero new models. Zero new services.** (Subject to the Key Achievements decision in §10.)

---

## 9. Verification

1. Visit `/admin/portfolio/resume-builder` with empty tables → every section shows `+`.
2. Click `+` on Header, fill name/email/tagline/phone/location/github → `users` + `profiles` rows updated; section renders the filled state with `✎ Edit`.
3. Open the existing **Profile** admin page → see the same data populated (two‑way binding confirmed).
4. Repeat for each section: add data via the builder modal, then verify the existing CRUD pages (`/admin/portfolio/experiences`, `/projects`, `/skills`, `/strengths`, `/educations`) show the same rows.
5. Conversely: add a new experience via the existing experiences admin page → return to the resume builder → it appears in the Work Experience section without any extra action.
6. Add multiple experiences / projects / skills — confirm `sort_order` is respected in the preview.
7. Click **Download PDF** → produces an A4 PDF that visually matches the live preview AND the supplied reference PDF.
8. `./vendor/bin/pint` passes; `php artisan test` passes.

---

## 10. Open questions for the user

1. **Key Achievements — there is no `achievements` table today.** Three options, pick one:
   - (a) Create a **single** new migration `achievements` (`id`, `text`, `sort_order`, `is_active`) — only one new table.
   - (b) Reuse an existing JSON field on `profiles` (add nothing, store achievements as a JSON array of strings).
   - (c) Hide / skip the Key Achievements section entirely.
   > Recommendation: **(a)** — keeps the data model clean and matches the pattern of `strengths`.

2. **Reset / clear button** — earlier draft had a global "Reset all". Since data now lives in the shared portfolio tables, a Reset would wipe data used by the public portfolio too. **Removed from v2.** Confirm OK.

3. **Project bullets** — the supplied PDF shows multiple bullet points under each project, but `projects` has no per‑bullet child table. Either:
   - (a) Treat `projects.description` as Markdown / newline‑separated bullets and render lines as `<li>` (no schema change).
   - (b) Create a small `project_highlights` table (similar to `experience_responsibilities`).
   > Recommendation: **(a)** — no migrations, matches v2's "reuse existing tables" rule.

4. **Skills grouping** — group by `skills.category` (existing column). Empty `category` falls under a default "Other" group. Confirm OK.

5. **`+` placement** — top‑right corner of each empty section card (current mockups). Confirm OK.

---

> ✏️ **Next step:** read this v2 plan and tell me what to change — especially answers to §10. I will update **this same file** until you give explicit approval, only then will I start implementation.
