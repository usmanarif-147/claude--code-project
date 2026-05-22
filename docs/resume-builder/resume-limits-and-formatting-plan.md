# Resume Builder — Phase 2 Plan: Limits & Formatting

> Status: **Draft v2** — open questions answered, awaiting final approval. Iterate this file until aligned before any implementation begins.
> Scope: **In-memory only**, same as Phase 1. This plan does **not** touch the database, migrations, or any other portfolio module.

---

## 1. Current Problem

The Resume Builder works, the preview is locked at A4, and the PDF downloads correctly. But there is **no constraint on what the user can type**, which leads to overflow off the bottom of the A4 page (see the screenshot you shared):

- The user can add **unlimited** projects, jobs, skills, bullets, etc.
- The user can type **arbitrarily long** strings into every field.
- As content grows, it spills past the bottom of the A4 paper. No way to know in advance how much is "too much."

**A resume is, by definition, a one-page document.** The Resume Builder should make it **structurally impossible** to produce something that exceeds one page. The user should also be able to **format** the resume — fonts, colors, alignment, spacing — and apply formatting to **the entire resume, a single section, or just one element type (like titles)** — like Microsoft Word but minimal.

---

## 2. Goal

A guided one-page editor with three layers of control:

1. **Hard structural limits** — caps on how many jobs / projects / bullets / etc. the user can add.
2. **Hard text limits per field** — every input has a max word count **AND** max character count. Both apply (the stricter wins). Live counters under each input.
3. **Word-like formatting model** — the user clicks an element in the preview (an entire section, or a title, or a subtitle, or the whole resume) and the toolbar shows the formatting controls for that selection. Apply font/size/bold/color/alignment/spacing scoped to what's selected.

Everything is **limited choice** — no free-form values. The user wants control, not infinite freedom.

---

## 3. Approach in three layers

```
┌─────────────────────────────────────────────────────────────┐
│  Layer 1 — STRUCTURAL LIMITS (count caps)                   │
│    • Max jobs, projects, bullets, skill groups, tags, etc.  │
│    • "+ add another …" buttons disable when cap reached.    │
├─────────────────────────────────────────────────────────────┤
│  Layer 2 — TEXT LIMITS (length caps per field)              │
│    • Every input has a (max_words, max_chars) pair.         │
│    • Live counter under the input. Save disables at 100%.   │
├─────────────────────────────────────────────────────────────┤
│  Layer 3 — WORD-LIKE FORMATTING                             │
│    • Click anything in the preview → it becomes selected.   │
│    • Toolbar appears with the format controls for that      │
│      selection (Entire Resume / Section / Element-type).    │
│    • Limited dropdown choices only, no free-form values.    │
└─────────────────────────────────────────────────────────────┘
```

These three layers ship as **separate phases** — see §10.

---

## 4. Layer 1 — Structural limits ✅ confirmed

Caps on **how many items** the user can add inside each repeatable section. Once the cap is reached, the `+ add another …` button is greyed out with a small hint like *"max 3 reached."*

| Section            | Limit                                |
|--------------------|--------------------------------------|
| Work Experience    | max **3** jobs  ← user confirmed "standard"   |
| Bullets per job    | max **3** bullets                    |
| Key Projects       | max **3** projects  ← user confirmed "standard"|
| Bullets per project| max **3** bullets                    |
| Skills             | max **5** categories                 |
| Tags per category  | max **8** tags                       |
| Strengths          | max **6** items                      |
| Key Achievements   | max **4** items                      |
| Education          | max **2** entries                    |

These caps come from the rough math of "what fits on A4 at our default font size." Conservative on purpose.

---

## 5. Layer 2 — Text limits per field ✅ confirmed

Every input has a **`max_words` AND `max_chars`** pair (both apply, stricter wins).

### Field limits table

| Field                  | Max words | Max chars |
|------------------------|-----------|-----------|
| Name                   | 5         | 40        |
| Tagline                | 9         | 70        |
| Phone                  | —         | 20        |
| Email                  | —         | 50        |
| Location               | 4         | 30        |
| GitHub URL             | —         | 50        |
| Profile summary        | 50        | 350       |
| Job company            | 5         | 40        |
| Job role               | 7         | 50        |
| Job bullet             | 20        | 130       |
| Project title          | 7         | 60        |
| Project subtitle       | 9         | 70        |
| Project bullet         | 20        | 130       |
| Project tech list      | 15        | 100       |
| Skill category name    | 4         | 30        |
| Skill tag              | 3         | 25        |
| Strength               | 4         | 30        |
| Achievement            | 16        | 110       |
| Degree                 | 9         | 70        |
| Institution            | 9         | 70        |
| Year (start / end)     | 1         | 9         |

These are first-draft. We can tune them after seeing real resumes in the builder.

### Live counter UX (per input field)

Below every `<input>` and `<textarea>` inside the modals:

```
┌───────────────────────────────────────────────────────────┐
│  Tagline                                                  │
│  [ Software Engineer | Laravel & Full-Stack             ] │
│  44 / 70 chars · 5 / 9 words                              │
└───────────────────────────────────────────────────────────┘
```

**Visual states:**

| % of limit | Counter color | Notes                                    |
|------------|---------------|------------------------------------------|
| 0 – 79%    | grey          | normal                                   |
| 80 – 99%   | yellow / amber| "getting close" warning                  |
| 100%       | red           | at limit — input stops accepting chars   |
| > 100%     | red bold      | over limit — Save button disabled        |

**Save behavior:** if any field exceeds its limit, **Save** is disabled and a small footer banner appears: *"Some fields exceed the limit. Trim them to save."*

**Modal title shows count summary**, e.g. *"Work Experience — 2 of 3 jobs."*

---

## 6. Layer 3 — Word-like formatting (the big idea)

Inspired by Microsoft Word but stripped down to the essentials. The model is **"click → select → format"**.

### 6.1 What the user can select

When the user clicks anywhere on the preview, the system figures out **what they meant to select** — at one of three levels:

```
SCOPE TREE
│
├── ENTIRE RESUME          (click on empty paper area / "Select All" button)
│
├── SECTION                (click on a section heading like "WORK EXPERIENCE")
│   ├── Header
│   ├── Profile
│   ├── Work Experience
│   ├── Key Projects
│   ├── Skills
│   ├── Strengths
│   ├── Key Achievements
│   └── Education
│
└── ELEMENT TYPE           (click on a specific element inside a section)
    ├── Section Title      (the uppercase blue heading itself)
    ├── Item Title         (e.g. company name, project title, degree)
    ├── Item Subtitle      (e.g. role, project subtitle, dates row)
    ├── Body Text          (bullets, profile paragraph, achievement lines)
    └── Inline Pill        (skill tag, strength item)
```

So if the user clicks a project's company name (e.g. "JSYS Tech · Multi-tenant Physiotherapy SaaS Platform"), the system identifies it as an **Item Title inside Key Projects** and gives them three scoping choices in the toolbar:

```
Selected: Item Title in Key Projects
Apply to:  ( ) Just this element type in this section  (default)
           ( ) All Item Titles across the resume
           ( ) Entire Resume
```

### 6.2 What the toolbar looks like

Always visible above the resume preview. Shows the current selection and the controls for that selection.

```
┌──────────────────────────────────────────────────────────────────────────────┐
│  ◉ Selected: Item Title in Work Experience                  [ Clear ]        │
│  Apply to:  ( ) This element type in this section  (●) Same across resume   │
│                                                                              │
│  Font: [▼ Inter]   Size: [▼ 10pt]   [ B ]   Color: [⬛][🟤][🟦][🟥][🟩]      │
│  Align: [⇤][≡][⇥][⇔]   Line spacing: [▼ Normal]   Section spacing: [▼ N/A] │
│                                                                              │
│  [ Reset this selection to defaults ]                                        │
└──────────────────────────────────────────────────────────────────────────────┘
```

When nothing is clicked:

```
┌──────────────────────────────────────────────────────────────────────────────┐
│  ◉ Selected: Entire Resume                              [ Select something ] │
│  Font: [▼ Inter]   Size: [▼ 10pt]   [ B ]   Color: [⬛][🟤][🟦][🟥][🟩]      │
│  Align: [⇤][≡][⇥][⇔]   Line spacing: [▼ Normal]   Section spacing: [▼ Norm]│
└──────────────────────────────────────────────────────────────────────────────┘
```

### 6.3 Controls — confirmed by user ✅

All limited to a small set of options. No free input.

| Control          | Options                                                            | Default     |
|------------------|--------------------------------------------------------------------|-------------|
| Font size        | **9pt · 10pt · 11pt · 12pt**                                       | 10pt        |
| Font family      | **Inter · Roboto · Lato · Georgia**                                | Inter       |
| Bold             | **Off · On**                                                       | Off         |
| Text color       | **Black · Dark Gray · Blue (#1d4ed8) · Dark Red · Dark Green**     | Black       |
| Alignment        | **Left · Center · Right · Justify**                                | Left        |
| Line spacing     | **Tight (1.2) · Normal (1.4) · Loose (1.6)**                       | Normal      |
| Section spacing  | **Compact (8mm) · Normal (12mm) · Spacious (16mm)**                | Normal      |

Section spacing only applies when scope is `Section` or `Entire Resume` (it has no meaning for an element type like "Item Title").

### 6.4 How the rules compose (cascade)

Three levels of overrides, just like Word's character / paragraph styles:

```
   most specific  →  least specific
   ┌────────────────────────────────────┐
   │  1.  Element-type override         │  e.g. "Item Title in Work Experience" — Bold On
   │      (per section)                 │
   ├────────────────────────────────────┤
   │  2.  Section override              │  e.g. "Work Experience" — Font size 11pt
   ├────────────────────────────────────┤
   │  3.  Entire-Resume default         │  e.g. all text — Font family Inter
   └────────────────────────────────────┘
```

When the system renders, it walks the chain from top to bottom and picks the first level where a value is set. Defaults at level 3 always apply unless overridden.

### 6.5 Concrete examples (matches your description)

| What the user wants                                                                     | How it works in this model                                                                                              |
|-----------------------------------------------------------------------------------------|-------------------------------------------------------------------------------------------------------------------------|
| "Bold only the titles"                                                                  | Click any Section Title → toolbar shows "Apply to: All Section Titles across resume" → toggle Bold On. Done.            |
| "Increase font size of just sub-titles"                                                 | Click any Item Subtitle (e.g. a role line) → "Apply to: All Item Subtitles across resume" → pick Size 11pt. Done.       |
| "Change font family of just one section"                                                | Click any Section heading (e.g. "PROFILE") → "Apply to: This section" → pick Font Roboto. Only Profile changes.         |
| "Change font family of entire resume"                                                   | Click the empty paper area (or use "Select All") → "Apply to: Entire Resume" → pick Font Roboto. Everything updates.    |

### 6.6 What's intentionally NOT supported (to keep it minimal)

- **Selecting an arbitrary word / arbitrary text range.** The smallest selectable unit is an element type (e.g. "All Item Titles"), not a single word. A true rich-text-editor would be needed for arbitrary text selection — that's far beyond "minimal."
- **Per-individual-item overrides.** You can format "all subtitles in Work Experience" but not "the subtitle of just the second job." If you need that, we'd add a fourth scope level later.
- **Strikethrough, underline, italic, highlight.** Bold is the only weight toggle in v1.
- **Custom fonts / custom colors.** Strictly the four font families and five colors listed in §6.3.

---

## 7. Page-fill indicator

A small bar above the preview shows roughly how much of the A4 page is used.

```
┌──────────────────────────────────────────────────────────────────────────────┐
│  Page fill:  ████████████████████░░░░░░░░  72%       (estimate)              │
└──────────────────────────────────────────────────────────────────────────────┘
```

- 0 – 85% → green
- 86 – 99% → yellow
- 100%+ → red, plus *"Content may overflow"* warning text

A hint, not a hard truth — the actual overflow is what the user sees in the fixed-A4 preview below.

---

## 8. Visual summary

```
┌──────────────────────────────────────────────────────────────────────────────┐
│  RESUME BUILDER                                              [Download PDF] │
│                                                                              │
│  ╔══════════════════════════════════════════════════════════════════════╗   │
│  ║  ◉ Selected: Entire Resume                       [ Select something ] ║   │
│  ║  Font: [▼ Inter]  Size: [▼ 10pt]  [B]  Color: [⬛][🟤][🟦][🟥][🟩]    ║   │
│  ║  Align: [⇤][≡][⇥][⇔]  Line: [▼ Normal]   Section: [▼ Normal]        ║   │
│  ╚══════════════════════════════════════════════════════════════════════╝   │
│                                                                              │
│  Page fill:  ████████████████░░░░░░░░░░░░  56%                              │
│                                                                              │
│  ┌──────────────────────────────────────────────────┐ ←  A4 paper (fixed)   │
│  │  …                                               │                        │
│  │  …            (resume content)                   │                        │
│  │  …                                               │                        │
│  │  …                                               │                        │
│  └──────────────────────────────────────────────────┘                        │
└──────────────────────────────────────────────────────────────────────────────┘
```

And an editing modal with counters:

```
┌────────────────────────────────────────────────────────┐
│  Work Experience — 2 of 3 jobs                  [ × ]  │
│ ────────────────────────────────────────────────────── │
│   ── Job #1 ───────────────────────── [ Remove ]      │
│   Company   [ JSYS Tech                  ]             │
│             18 / 40 chars · 2 / 5 words                │
│                                                        │
│   Role      [ Software Engineer — Full Stack         ] │
│             30 / 50 chars · 4 / 7 words                │
│                                                        │
│   Bullets (2 of 3):                                    │
│   • [ Worked on a real-time chat …               ]     │
│     115 / 130 chars · 18 / 20 words                    │
│   • [ Developed features across PHP/Laravel …    ]     │
│     102 / 130 chars · 16 / 20 words                    │
│   [ + add bullet ]                                     │
│                                                        │
│  [ + add another job ]    ← disabled when 3 jobs added │
│                                                        │
│                            [ Cancel ]  [ Save ]        │
└────────────────────────────────────────────────────────┘
```

---

## 9. Defaults on a fresh resume

| Item                  | Default        |
|-----------------------|----------------|
| Font family           | Inter          |
| Font size             | 10pt           |
| Bold                  | Off            |
| Text color            | Black          |
| Alignment             | Left           |
| Line spacing          | Normal (1.4)   |
| Section spacing       | Normal (12mm)  |

---

## 10. Implementation phasing ✅ multiple small phases

You said: *"divide into multiple phases. its complicated task so best thing is to divide large task into small task."* Agreed. Here is the breakdown — each phase ships independent value and can be tested in isolation.

| Phase  | Title                                          | Scope                                                                                                                       | Risk    |
|--------|------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------|---------|
| **2A** | Structural caps + text limits + live counters  | §4, §5 in this plan. Caps on item counts. `+ add` buttons disable at cap. Per-field counters under every input. Save gates. | low     |
| **2B** | Page-fill indicator                            | §7 in this plan. Heuristic estimate based on item counts and text lengths. Small bar above the preview.                     | low     |
| **2C** | Global formatting toolbar (Entire Resume only) | The toolbar at the top of §6, but **only the "Entire Resume" scope**. One global font / size / color / alignment / spacing. | medium  |
| **2D** | Section-level scope                            | Toolbar can apply to one specific section (Header / Profile / Work Experience / etc.). Cascade level 2.                     | medium  |
| **2E** | Element-type scope                             | Toolbar can apply to an element type (Section Title / Item Title / Item Subtitle / Body / Inline Pill). Cascade level 1.    | higher  |

**Recommendation:** ship 2A first, then 2B (small follow-on), then 2C. Stop and use the builder for a few real resumes. 2D and 2E only if you genuinely need them.

Each phase will get its own brief sub-plan when its turn comes (so we don't overload one giant document).

---

## 11. Out of scope (intentionally not in this plan)

- **Database persistence** — still in-memory only, same as Phase 1.
- **Linking the resume to existing portfolio data** (Profile / Experience / Project / Skill tables) — not touched.
- **Multi-page resumes / pagination** — explicitly one page, always.
- **Arbitrary text-range selection** (single word, single line of a paragraph) — minimum selectable unit is an element type.
- **Custom font upload** — only the four built-in font families.
- **Free-form color picker** — only the five swatches.
- **Per-individual-item overrides** ("only the second job's subtitle") — formatting is per *type*, not per *instance*.
- **Italic / underline / strikethrough / highlight** — bold only in v1.
- **Drag-to-reorder sections / items** — order is fixed.
- **Spell check, AI suggestions, grammar help** — not in scope.

---

## 12. Open questions for you — all answered ✅

| # | Question                                       | User's answer                                                                                                                  |
|---|------------------------------------------------|--------------------------------------------------------------------------------------------------------------------------------|
| 1 | Item caps comfortable?                         | "It will be standard. Resume should contain 3 work experience and 3 key projects." → §4 caps confirmed.                          |
| 2 | Char/word limits comfortable?                  | Yes. → §5 limits confirmed (we tune after live use).                                                                            |
| 3 | Four font families OK?                         | Yes. → Inter / Roboto / Lato / Georgia confirmed.                                                                               |
| 4 | Five color swatches OK?                        | Yes. → Black / Dark Gray / Blue / Dark Red / Dark Green confirmed.                                                              |
| 5 | Need section-level / element-level scope?      | **Both.** Like Microsoft Word but minimal — click an element, format the selection (entire resume / section / element type). → §6 redesigned around this. |
| 6 | Phasing?                                       | "Divide into multiple phases." → §10 split into 5 small phases (2A–2E).                                                          |

---

## 13. Final open questions — answered ✅

| # | Question                                                         | User's answer                                                       |
|---|------------------------------------------------------------------|---------------------------------------------------------------------|
| 7 | OK to ship 2A → 2B → 2C and pause to evaluate before 2D/2E?      | Yes, agreed.                                                        |
| 8 | Section spacing only applies at Section + Entire-Resume scopes?  | Yes. The Section-spacing control is greyed-out at element-type scope.|

So the path is: **2A → 2B → 2C → pause → evaluate whether 2D / 2E are needed.**

---

## 14. Plan status

All open questions answered. Plan is **ready for approval**.

> ✏️ **Next step:** read this v2 and tell me **"approved"**. The moment you do, I will start implementation of **Phase 2A only** (structural caps + text limits + live counters + Save gating + modal count summaries). 2B and 2C get their own short sub-plans when their turn arrives.
