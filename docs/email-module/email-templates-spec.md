# Email Templates — Spec

Side: ADMIN

---

## 1. MODULE OVERVIEW

The Email Templates module lets the admin create, edit, and manage reusable email templates for common professional situations such as interview follow-ups, freelance proposals, thank-you notes, and cold outreach. Templates can be quickly copied to the clipboard and used as a starting point for AI-powered smart replies.

**Features:**
- Create email templates with a name, category, subject line, and body
- Edit and delete existing templates
- List all templates with search and category filter
- Quick copy template body (or subject + body) to clipboard
- Use a template as the starting point for an AI smart reply (future integration point)

**Admin features:**
- Full CRUD for email templates
- Categorize templates (interview follow-up, freelance proposal, thank you, cold outreach, custom)
- Search and filter templates by category
- One-click copy to clipboard from the index page
- Mark templates as favorites for quick access

---

## 2. DATABASE SCHEMA

```
Table: email_templates
Columns:
  - id (bigint, primary key, auto increment)
  - user_id (bigint, required, foreign key -> users.id)
  - name (varchar 255, required) — template display name, e.g. "Interview Follow-Up"
  - category (varchar 100, required) — one of: interview_follow_up, freelance_proposal, thank_you, cold_outreach, custom
  - subject (varchar 500, nullable) — email subject line template
  - body (text, required) — email body content (plain text with optional placeholders like {company}, {name})
  - is_favorite (boolean, default false) — pinned to top of list
  - sort_order (integer, default 0) — manual sort within category
  - last_used_at (timestamp, nullable) — tracks when template was last copied/used
  - created_at, updated_at (timestamps)

Indexes:
  - email_templates_user_id_index (user_id)
  - email_templates_category_index (category)
  - email_templates_is_favorite_index (is_favorite)

Foreign keys:
  - user_id references users(id) on delete cascade
```

---

## 3. FILE MAP

```
MIGRATIONS:
  - database/migrations/2026_04_01_200005_create_email_templates_table.php

MODELS:
  - app/Models/EmailTemplate.php  (single model, no subfolder needed)
    - fillable: name, category, subject, body, is_favorite, sort_order, last_used_at, user_id
    - relationships: belongsTo(User)
    - casts: is_favorite -> boolean, last_used_at -> datetime

SERVICES:
  - app/Services/EmailTemplateService.php
    - getAll(search, category, perPage): LengthAwarePaginator — list with optional search/filter
    - getFavorites(): Collection — get all favorite templates
    - getById(id): EmailTemplate — find by ID or fail
    - create(data): EmailTemplate — create new template
    - update(id, data): EmailTemplate — update existing template
    - delete(id): void — delete template
    - toggleFavorite(id): EmailTemplate — toggle is_favorite flag
    - markUsed(id): void — update last_used_at to now
    - getCategories(): array — return list of available categories

--- ADMIN FILES ---

LIVEWIRE COMPONENTS:
  - app/Livewire/Admin/Email/Templates/EmailTemplateIndex.php
    - public properties: $search, $filterCategory, $perPage
    - methods:
      - render() — paginated list with search/filter
      - delete(id) — delete template with confirmation
      - toggleFavorite(id) — toggle favorite status
      - copyToClipboard(id) — mark template as used (JS handles actual clipboard)
  - app/Livewire/Admin/Email/Templates/EmailTemplateForm.php
    - public properties: $emailTemplateId, $name, $category, $subject, $body, $is_favorite, $sort_order
    - methods:
      - mount(emailTemplate?) — load existing template or set defaults
      - save() — validate and create/update template
      - render()

VIEWS:
  - resources/views/livewire/admin/email/templates/index.blade.php
    - template list with search bar, category filter, table, copy-to-clipboard buttons, favorite toggle
  - resources/views/livewire/admin/email/templates/form.blade.php
    - create/edit form with name, category, subject, body, favorite toggle

ROUTES (admin):
  - routes/admin/email/templates.php
    - GET /admin/email/templates → EmailTemplateIndex → admin.email.templates.index
    - GET /admin/email/templates/create → EmailTemplateForm → admin.email.templates.create
    - GET /admin/email/templates/{emailTemplate}/edit → EmailTemplateForm → admin.email.templates.edit
```

---

## 4. COMPONENT CONTRACTS

### Admin Components

```
Component: App\Livewire\Admin\Email\Templates\EmailTemplateIndex
Namespace: App\Livewire\Admin\Email\Templates
Layout: #[Layout('components.layouts.admin')]

Properties:
  - $search (string, '') — #[Url] search query for template name/body
  - $filterCategory (string, '') — #[Url] filter by category
  - $perPage (int, 10) — pagination size

Methods:
  - render()
    Input: none
    Does: calls EmailTemplateService::getAll($search, $filterCategory, $perPage)
    Output: renders view with paginated templates

  - delete(int $id)
    Input: template ID
    Does: calls EmailTemplateService::delete($id)
    Output: flash success message

  - toggleFavorite(int $id)
    Input: template ID
    Does: calls EmailTemplateService::toggleFavorite($id)
    Output: no flash, UI updates reactively

  - copyToClipboard(int $id)
    Input: template ID
    Does: calls EmailTemplateService::markUsed($id), dispatches browser event with template content
    Output: dispatches 'copy-to-clipboard' browser event with subject+body payload
```

```
Component: App\Livewire\Admin\Email\Templates\EmailTemplateForm
Namespace: App\Livewire\Admin\Email\Templates
Layout: #[Layout('components.layouts.admin')]

Properties:
  - $emailTemplateId (int|null) — null for create, set for edit
  - $name (string, '')
  - $category (string, 'custom')
  - $subject (string, '')
  - $body (string, '')
  - $is_favorite (bool, false)
  - $sort_order (int, 0)

Methods:
  - mount(?EmailTemplate $emailTemplate)
    Input: optional EmailTemplate model (route model binding)
    Does: if editing, populates all properties from model
    Output: properties set

  - save()
    Input: none (uses $this properties)
    Does:
      1. Validates all fields
      2. If $emailTemplateId: calls EmailTemplateService::update()
      3. Else: calls EmailTemplateService::create()
    Output: flash success, redirect to admin.email.templates.index

  - render()
    Input: none
    Does: passes categories list to view
    Output: renders form view

Validation Rules:
  - name: required|string|max:255
  - category: required|string|in:interview_follow_up,freelance_proposal,thank_you,cold_outreach,custom
  - subject: nullable|string|max:500
  - body: required|string|max:10000
  - is_favorite: boolean
  - sort_order: integer|min:0|max:999
```

---

## 5. VIEW BLUEPRINTS

### Index View

```
View: resources/views/livewire/admin/email/templates/index.blade.php
Layout: components.layouts.admin
Side: ADMIN
Page title: "Email Templates"

Design rules:
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:
  - Breadcrumb: Dashboard > Email > Templates
  - Page header: "Email Templates" title + "Add Template" button (right)
  - Flash messages (success/error)
  - Filter bar card:
    - Search input (left, searches name and body)
    - Category dropdown filter (right): All, Interview Follow-Up, Freelance Proposal, Thank You, Cold Outreach, Custom
  - Table card:
    - Columns: Favorite (star icon toggle), Name (with category badge below), Subject (truncated), Last Used (relative time or "Never"), Actions
    - Actions per row: Copy (clipboard icon), Edit (pencil icon), Delete (trash icon)
    - Copy button: uses Alpine.js @click to copy subject+body to clipboard, shows brief "Copied!" tooltip
    - Favorite toggle: star icon, filled if favorite, outline if not, wire:click="toggleFavorite(id)"
    - Empty state: "No email templates found. Create your first template to get started."
    - Pagination footer with record count
```

### Form View

```
View: resources/views/livewire/admin/email/templates/form.blade.php
Layout: components.layouts.admin
Side: ADMIN
Page title: "Create Template" / "Edit Template"

Design rules:
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider
  - Full-width form layout: 2/3 main + 1/3 sidebar

Sections:
  - Breadcrumb: Dashboard > Email > Templates > Create/Edit
  - Page header: dynamic title + "Back" button (right)
  - Main content (2/3 left):
    - "Template Details" section card:
      - Name (text input, required, full width)
      - Subject (text input, optional, full width, placeholder: "Email subject line...")
      - Body (textarea, required, 10 rows, full width, placeholder: "Write your email template... Use {name}, {company}, {position} as placeholders.")
  - Sidebar (1/3 right):
    - "Settings" section card:
      - Category (select dropdown: Interview Follow-Up, Freelance Proposal, Thank You, Cold Outreach, Custom)
      - Sort Order (number input, default 0)
      - Favorite toggle switch
    - "Actions" section card:
      - Save button (primary, full width, with loading state)
      - Cancel button (secondary, full width, links back to index)
```

---

## 6. VALIDATION RULES

```
Form: EmailTemplateForm (create/edit)
  - name: required|string|max:255
  - category: required|string|in:interview_follow_up,freelance_proposal,thank_you,cold_outreach,custom
  - subject: nullable|string|max:500
  - body: required|string|max:10000
  - is_favorite: boolean
  - sort_order: integer|min:0|max:999
```

---

## 7. EDGE CASES & BUSINESS RULES

- **Delete:** Hard delete (no soft delete). No cascading needed since email_templates has no child tables.
- **Unique constraints:** No unique constraint on name — user may have similarly named templates.
- **Null handling:** Subject is nullable. When copying to clipboard, if subject is null, only copy the body.
- **Sort order:** Templates are sorted by: is_favorite DESC, then sort_order ASC, then updated_at DESC.
- **Category values:** Stored as snake_case strings. Display as human-readable labels in the UI (e.g., `interview_follow_up` -> "Interview Follow-Up").
- **Copy to clipboard:** Uses Alpine.js `navigator.clipboard.writeText()`. The Livewire component dispatches a browser event with the template content; Alpine handles the actual clipboard write and shows a temporary "Copied!" tooltip.
- **last_used_at:** Updated every time the user clicks "Copy" on a template. This enables sorting by recently used in the future.
- **User scoping:** All queries are scoped to `auth()->id()`. Only the authenticated user can see/edit their own templates.
- **Placeholder tokens:** The body field supports informal placeholders like `{name}`, `{company}`, `{position}`. These are NOT automatically replaced — they serve as visual reminders for the user when pasting. No server-side placeholder processing is needed.
- **Max templates:** No hard limit. Pagination handles large lists.
- **AI smart reply integration:** The "Use as AI starting point" feature is a future concern. For now, the spec only covers CRUD + copy. The template body will be passable to a future AI service.

---

## 8. IMPLEMENTATION ORDER

```
1. database/migrations/2026_04_01_200005_create_email_templates_table.php
2. app/Models/EmailTemplate.php
3. app/Services/EmailTemplateService.php
4. routes/admin/email/templates.php
5. app/Livewire/Admin/Email/Templates/EmailTemplateIndex.php
6. app/Livewire/Admin/Email/Templates/EmailTemplateForm.php
7. resources/views/livewire/admin/email/templates/index.blade.php
8. resources/views/livewire/admin/email/templates/form.blade.php
9. Update sidebar in resources/views/components/layouts/admin.blade.php — add "Email" module group with "Templates" sub-link
10. Update docs/PROJECT-STATUS.md — add Email module entry
```
