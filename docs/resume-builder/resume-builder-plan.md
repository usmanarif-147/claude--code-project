# Resume Builder — Plan

> Status: **Draft** — awaiting user approval. Iterate this file until both sides agree before any implementation begins.

---

## 1. Context

The user wants a brand‑new admin feature: a **dynamic resume builder** where the resume page starts as an **empty skeleton** showing only 8 fixed sections, each with a `+` icon. Clicking `+` opens a modal where the user enters that section's details. As sections fill, a live HTML preview renders the resume in **the exact same layout and blue two‑column style as `Usman_Arif_Laravel_Resume.pdf`**. A "Download PDF" button exports the same layout to PDF.

**Why this is being built:**
- The existing admin **Resume Generator** pre‑populates from portfolio tables. The user wants a separate, dedicated builder where the resume is composed section‑by‑section via modals — independent of existing portfolio data.
- Visual fidelity to the supplied PDF is required (fixed layout, no template picker).

**Intended outcome:**
- New page at `/admin/portfolio/resume-builder` showing the empty skeleton on first load.
- Each of the 8 sections has its own modal for adding / editing data.
- Data persists in dedicated DB tables (single‑user app, but each table keeps `user_id` per the user's explicit acceptance).
- Live HTML preview renders the same blue two‑column layout from the reference PDF.
- "Download PDF" button exports the preview to PDF.

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
│ paragraph summary…                 │  │ Backend & Frontend             │
│                                    │  │  [PHP] [Laravel] [Livewire]…   │
│                                    │  │ Real‑Time                      │
│                                    │  │  [WebSocket] [WebRTC]…         │
│                                    │  │ Architecture & Databases       │
│                                    │  │  [Microservices] [Kafka]…      │
│ WORK EXPERIENCE                    │  │ DevOps & Testing               │
│ JSYS Tech              Aug 2025 –  │  │  [Docker] [Nginx]…             │
│ Software Engineer — Full Stack     │  │ Tools & PM                     │
│ • bullet                           │  │  [Git] [GitHub]…               │
│ • bullet                           │  │                                │
│ Horizam                2022 – Aug  │  │ STRENGTHS                      │
│ Software Engineer — Full Stack     │  │  ★ API Design  ★ Problem Solv. │
│ • bullet                           │  │  ★ Clean Code  ★ Real‑Time…    │
│                                    │  │                                │
│ Softenica              2021 – 2022 │  │ KEY ACHIEVEMENTS               │
│ Junior Laravel Developer           │  │  • Delivered 7 full‑cycle…     │
│ • bullet                           │  │  • Contributed to a real‑time… │
│                                    │  │  • Migrated 2 legacy CI apps…  │
│ KEY PROJECTS                       │  │  • 10+ freelance projects…     │
│ RehabSuite — Real‑Time Chat …      │  │                                │
│ JSYS Tech · Multi‑tenant…          │  │ EDUCATION                      │
│ • bullet  • bullet                 │  │  B.S. Software Engineering     │
│ Tech: Laravel 12, Rust/Axum…       │  │  University of Management…     │
│                                    │  │  2016 – 2021                   │
│ Autotheory — Multi‑Vendor…         │  │                                │
│ autotheory.com                     │  │                                │
│ • bullet                           │  │                                │
│ Tech: Laravel, Livewire…           │  │                                │
└────────────────────────────────────┘  └────────────────────────────────┘
                                            (blue accent color throughout)
```

Two columns. Left ~60% (Profile, Work Experience, Key Projects). Right ~40% (Skills, Strengths, Key Achievements, Education). Header is full‑width above both columns.

---

## 3. Initial empty state (what the user sees first)

```
┌──────────────────────────────────────────────────────────────────────┐
│  HEADER                                                       [ + ] │
│  (empty — name, tagline, phone, email, location, github)            │
└──────────────────────────────────────────────────────────────────────┘
┌────────────────────────────────┐  ┌────────────────────────────────┐
│ PROFILE                  [ + ] │  │ SKILLS                  [ + ]  │
│ (empty — summary paragraph)    │  │ (empty — skill groups + tags)  │
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

                       ┌───────────────────────────────┐
                       │ Top action bar:               │
                       │  [ Reset all ]  [ Download PDF ] │
                       └───────────────────────────────┘
```

Each section card shows:
- Section title (top‑left)
- `+` button (top‑right) when section is empty
- Faint placeholder text describing what the section will contain
- Once filled, the `+` becomes a small `✎ Edit` button and the placeholder is replaced with the actual rendered content.

---

## 4. Modal flows — one per section

### 4.1 Header modal
```
┌─────────────────────────────────────────┐
│  Add Header Details              [ × ]  │
│ ─────────────────────────────────────── │
│  Full Name      [______________________]│
│  Tagline        [______________________]│
│  Phone          [______________________]│
│  Email          [______________________]│
│  Location       [______________________]│
│  GitHub URL     [______________________]│
│                                         │
│                   [ Cancel ]  [ Save ]  │
└─────────────────────────────────────────┘
```

### 4.2 Profile modal
```
┌─────────────────────────────────────────┐
│  Add Profile Summary             [ × ]  │
│ ─────────────────────────────────────── │
│  Summary                                │
│  ┌─────────────────────────────────────┐│
│  │                                     ││
│  │  (textarea, ~4–6 lines)             ││
│  │                                     ││
│  └─────────────────────────────────────┘│
│                   [ Cancel ]  [ Save ]  │
└─────────────────────────────────────────┘
```

### 4.3 Work Experience modal (repeatable rows)
```
┌──────────────────────────────────────────────────────┐
│  Add Work Experience                          [ × ]  │
│ ──────────────────────────────────────────────────── │
│  ── Job #1 ──────────────────────────── [ remove ]  │
│  Company        [____________________]               │
│  Role           [____________________]               │
│  Start date     [______]   End date  [______]        │
│  Bullets:                                            │
│   • [_________________________________] [ x ]        │
│   • [_________________________________] [ x ]        │
│   [ + add bullet ]                                   │
│                                                      │
│  [ + add another job ]                               │
│                          [ Cancel ]  [ Save ]        │
└──────────────────────────────────────────────────────┘
```

### 4.4 Key Projects modal (repeatable rows)
```
┌──────────────────────────────────────────────────────┐
│  Add Key Projects                              [ × ] │
│ ──────────────────────────────────────────────────── │
│  ── Project #1 ─────────────────────── [ remove ]   │
│  Name             [____________________]             │
│  Company / URL    [____________________]             │
│  Bullets:                                            │
│   • [_________________________________] [ x ]        │
│   [ + add bullet ]                                   │
│  Tech stack       [Laravel, Rust, Kafka, …       ]   │
│                                                      │
│  [ + add another project ]                           │
│                          [ Cancel ]  [ Save ]        │
└──────────────────────────────────────────────────────┘
```

### 4.5 Skills modal (grouped tags)
```
┌──────────────────────────────────────────────────────┐
│  Add Skills                                    [ × ] │
│ ──────────────────────────────────────────────────── │
│  ── Group #1 ──────────────────────── [ remove ]    │
│  Group name      [Backend & Frontend       ]         │
│  Tags            [PHP] [Laravel] [Livewire]  + add   │
│                                                      │
│  [ + add another group ]                             │
│                          [ Cancel ]  [ Save ]        │
└──────────────────────────────────────────────────────┘
```

### 4.6 Strengths modal (flat list)
```
┌─────────────────────────────────────────┐
│  Add Strengths                   [ × ]  │
│ ─────────────────────────────────────── │
│  • [API Design                ] [ x ]   │
│  • [Problem Solving           ] [ x ]   │
│  • [Clean Code                ] [ x ]   │
│  [ + add strength ]                     │
│                   [ Cancel ]  [ Save ]  │
└─────────────────────────────────────────┘
```

### 4.7 Key Achievements modal (flat list)
```
┌─────────────────────────────────────────┐
│  Add Key Achievements            [ × ]  │
│ ─────────────────────────────────────── │
│  • [Delivered 7 full‑cycle…   ] [ x ]   │
│  • [Contributed to real‑time… ] [ x ]   │
│  [ + add achievement ]                  │
│                   [ Cancel ]  [ Save ]  │
└─────────────────────────────────────────┘
```

### 4.8 Education modal (repeatable)
```
┌──────────────────────────────────────────────────────┐
│  Add Education                                 [ × ] │
│ ──────────────────────────────────────────────────── │
│  ── Entry #1 ─────────────────────── [ remove ]     │
│  Degree         [B.S. Software Engineering         ] │
│  Institution    [University of Management & Tech.  ] │
│  Start year     [2016]    End year   [2021]          │
│                                                      │
│  [ + add another ]                                   │
│                          [ Cancel ]  [ Save ]        │
└──────────────────────────────────────────────────────┘
```

---

## 5. Filled state (what it looks like after sections are populated)

The same skeleton, but each section now renders the entered data in the blue two‑column PDF style. The `+` icon becomes a small `✎ Edit` button. Clicking `✎ Edit` reopens the same modal pre‑filled with the existing values.

```
┌──────────────────────────────────────────────────────────────────────┐
│  USMAN ARIF                                                  [ ✎ ]  │
│  SOFTWARE ENGINEER | LARAVEL & FULL‑STACK DEVELOPMENT               │
│  ✆ +92 33642...   ✉ usmanarif…@gmail.com   📍 Lahore   ⌂ github…    │
└──────────────────────────────────────────────────────────────────────┘
┌────────────────────────────────┐  ┌────────────────────────────────┐
│ PROFILE                 [ ✎ ]  │  │ SKILLS                  [ ✎ ]  │
│ Software Engineer with 5+ …    │  │ Backend & Frontend             │
│                                │  │  [PHP] [Laravel] [Livewire]…   │
└────────────────────────────────┘  │ Real‑Time                      │
                                    │  [WebSocket] [WebRTC]…         │
┌────────────────────────────────┐  └────────────────────────────────┘
│ WORK EXPERIENCE         [ ✎ ]  │  ┌────────────────────────────────┐
│ JSYS Tech       Aug 2025 – Now │  │ STRENGTHS               [ ✎ ]  │
│ Software Engineer — Full Stack │  │  ★ API Design   ★ Problem Solv │
│  • bullet                      │  │  ★ Clean Code   ★ Real‑Time…   │
│  • bullet                      │  └────────────────────────────────┘
│ Horizam         2022 – Aug 2025│  ┌────────────────────────────────┐
│ …                              │  │ KEY ACHIEVEMENTS        [ ✎ ]  │
└────────────────────────────────┘  │  • Delivered 7 full‑cycle…     │
┌────────────────────────────────┐  │  • Contributed to real‑time…   │
│ KEY PROJECTS            [ ✎ ]  │  └────────────────────────────────┘
│ RehabSuite — Real‑Time Chat…   │  ┌────────────────────────────────┐
│  • bullet                      │  │ EDUCATION               [ ✎ ]  │
│  Tech: Laravel 12, Rust/Axum…  │  │  B.S. Software Engineering     │
└────────────────────────────────┘  │  University of Management…     │
                                    │  2016 – 2021                   │
                                    └────────────────────────────────┘
                       [ Reset all ]  [ Download PDF ]
```

---

## 6. Architecture (follows `CLAUDE.md` rules)

### 6.1 Module placement

The builder is a Portfolio sub‑feature (sits under the Portfolio sidebar group, alongside the existing `Resume Generator` which stays untouched).

| Concern        | Path                                                                  |
|----------------|-----------------------------------------------------------------------|
| Livewire root  | `app/Livewire/Admin/Portfolio/ResumeBuilder/`                         |
| Views root     | `resources/views/livewire/admin/portfolio/resume-builder/`            |
| Service        | `app/Services/ResumeBuilderService.php`                               |
| Routes         | `routes/admin/portfolio/resume-builder.php` (auto‑discovered)         |
| Sidebar entry  | nested inside Portfolio group as "Resume Builder"                     |

### 6.2 Livewire components

| Component                                   | Responsibility                                                                                            |
|---------------------------------------------|-----------------------------------------------------------------------------------------------------------|
| `ResumeBuilderIndex`                        | The page. Loads current resume data, renders empty/filled skeleton, manages which modal is open, action bar |
| `SectionModal`                              | A single modal component that receives a `section` prop (`header`/`profile`/`work`/…) and renders the matching form. Emits `section-saved` event on save. |

### 6.3 Service

`ResumeBuilderService` (single class, all business logic per CLAUDE.md):

```php
public function getResumeFor(User $user): array;
public function saveSection(string $section, User $user, array $data): void;
public function resetAll(User $user): void;
```

Save logic for repeatable sections: transactional **wipe + re‑insert** in `position` order. This keeps the implementation simple and avoids stale‑row bugs from partial updates.

### 6.4 Data model

11 new tables, flat migrations under `database/migrations/`:

| Table                                | Purpose                                |
|--------------------------------------|----------------------------------------|
| `resume_headers`                     | one row per user                       |
| `resume_profiles`                    | one row per user                       |
| `resume_work_experiences`            | many per user (ordered)                |
| `resume_work_experience_bullets`     | many per experience (ordered)          |
| `resume_projects`                    | many per user (ordered)                |
| `resume_project_bullets`             | many per project (ordered)             |
| `resume_skill_groups`                | many per user (ordered)                |
| `resume_skill_tags`                  | many per skill group (ordered)         |
| `resume_strengths`                   | many per user (ordered)                |
| `resume_achievements`                | many per user (ordered)                |
| `resume_education`                   | many per user (ordered)                |

Models grouped under `app/Models/Resume/` (CLAUDE.md rule: ≥2 related models → subfolder).

### 6.5 Live preview

A Blade partial — `_preview.blade.php` — renders the resume in the blue two‑column PDF layout. It is **the same view** the user is editing: the builder page IS the preview. Empty sections show the `+` placeholder; filled sections show the rendered content with `✎ Edit`.

The preview container is wrapped in `bg-white text-slate-900` so it looks like paper even though the rest of the admin UI is dark.

### 6.6 PDF export

- Route: `GET /admin/portfolio/resume-builder/download`
- Handler: a thin controller `ResumeBuilderDownloadController` (binary stream, not a Livewire interaction)
- Template: new print‑optimized blade at `resources/views/resume/templates/builder.blade.php` (A4, blue accent, two‑column)
- Uses the **same PDF library already present in the project** (whichever the existing `ResumeService` uses — left untouched)

---

## 7. Files to create

```
app/
  Livewire/Admin/Portfolio/ResumeBuilder/
    ResumeBuilderIndex.php
    SectionModal.php
  Models/Resume/
    ResumeHeader.php
    ResumeProfile.php
    ResumeWorkExperience.php
    ResumeWorkExperienceBullet.php
    ResumeProject.php
    ResumeProjectBullet.php
    ResumeSkillGroup.php
    ResumeSkillTag.php
    ResumeStrength.php
    ResumeAchievement.php
    ResumeEducation.php
  Services/
    ResumeBuilderService.php
  Http/Controllers/
    ResumeBuilderDownloadController.php

database/migrations/
  2026_05_22_000001_create_resume_headers_table.php
  2026_05_22_000002_create_resume_profiles_table.php
  2026_05_22_000003_create_resume_work_experiences_table.php
  2026_05_22_000004_create_resume_work_experience_bullets_table.php
  2026_05_22_000005_create_resume_projects_table.php
  2026_05_22_000006_create_resume_project_bullets_table.php
  2026_05_22_000007_create_resume_skill_groups_table.php
  2026_05_22_000008_create_resume_skill_tags_table.php
  2026_05_22_000009_create_resume_strengths_table.php
  2026_05_22_000010_create_resume_achievements_table.php
  2026_05_22_000011_create_resume_education_table.php

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

Plus one tiny edit to `resources/views/components/layouts/admin.blade.php` to add a sidebar link inside the existing Portfolio group.

---

## 8. Verification

1. `php artisan migrate` — all 11 tables created without error.
2. Visit `/admin/portfolio/resume-builder` — the empty skeleton renders exactly like §3, all 8 `+` buttons visible.
3. Click `+` on **Header**, fill all fields, save → header renders the filled state and `+` becomes `✎ Edit`.
4. Repeat for all 7 remaining sections; verify each renders in the correct column and visually matches the supplied PDF.
5. Add multiple Work Experiences, Projects, Skill groups, Education entries — confirm ordering stays stable via `position`.
6. Click **Download PDF** → produces an A4 PDF that visually matches the live preview AND the supplied reference PDF.
7. Click **Reset all**, confirm → page returns to empty skeleton; DB rows for that user are gone.
8. Reload page → state persists.
9. `./vendor/bin/pint` passes; `php artisan test` passes.

---

## 9. Out of scope

- Multiple resume templates / template switcher.
- Multi‑user resume management.
- Pre‑filling from existing portfolio tables (Profile, Experience, Project, Skill).
- Touching the existing `ResumeGenerator` component or `ResumeService`.

---

## 10. Open questions for the user

1. **`+` placement** — top‑right corner of each empty section card (most common pattern), or centered in the middle of the empty area (more prominent for first‑time use)? Mockups in §3 show top‑right.
2. **Auto‑save vs explicit Save button** — modals currently use explicit `Save`. OK? (Auto‑save on field blur is also possible.)
3. **Repeatable sections — single big modal vs per‑item modal** — current design: one modal where all jobs/projects/etc. for that section are managed together. Alternative: a list inside the section card with a `+ Add` that opens a single‑item modal. Which feels better?
4. **Delete a section's data** — should each section have its own clear/reset button, or only the global `Reset all`?

---

> ✏️ **Next step:** read this plan and tell me what to change. I will update **this same file** on each iteration. When you are happy, say so explicitly and I will then proceed to implementation.
