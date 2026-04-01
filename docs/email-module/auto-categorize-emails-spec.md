# Auto-Categorize Emails — Spec

Side: ADMIN

---

## 1. MODULE OVERVIEW

This feature uses AI to automatically categorize incoming emails into predefined categories (Job Response, Freelance, Important, Newsletter, Spam/Noise) so the user can quickly filter and focus on what matters. When the AI miscategorizes an email, the user can manually correct it, and those corrections are tracked so the AI can learn and improve over time.

### Features
- Manage email categories (CRUD) with name, color, icon, and sort order
- AI auto-assigns a category to each email based on sender, subject, and snippet
- View/filter emails by category
- Manually reassign an email's category (correction)
- Track manual corrections in a dedicated table for AI learning
- Category stats dashboard showing distribution of emails across categories

### Admin Features
- Create, edit, delete, reorder email categories
- View email inbox filtered by category
- Manually override AI-assigned category on any email
- View correction history to understand AI accuracy
- See category distribution stats (how many emails per category)

---

## 2. DATABASE SCHEMA

### Table: email_categories
```
Columns:
  - id (bigint, primary key, auto increment)
  - name (string 100, required) — display name e.g. "Job Response"
  - slug (string 100, required, unique) — url-friendly e.g. "job-response"
  - color (string 20, required) — tailwind color token e.g. "emerald", "amber", "red", "blue", "gray"
  - icon (string 50, required) — icon identifier e.g. "briefcase", "code", "star", "newspaper", "trash"
  - sort_order (integer, required, default 0) — controls display order
  - created_at, updated_at (timestamps)

Indexes:
  - unique index on slug
  - index on sort_order

Default seed data:
  1. Job Response — slug: job-response, color: emerald, icon: briefcase, sort_order: 1
  2. Freelance — slug: freelance, color: blue, icon: code, sort_order: 2
  3. Important — slug: important, color: amber, icon: star, sort_order: 3
  4. Newsletter — slug: newsletter, color: primary, icon: newspaper, sort_order: 4
  5. Spam/Noise — slug: spam-noise, color: gray, icon: trash, sort_order: 5
```

### Table: emails (existing — add column via migration)
```
New column:
  - category_id (bigint unsigned, nullable, foreign key → email_categories.id, ON DELETE SET NULL)

Indexes:
  - index on category_id
```

### Table: email_category_corrections
```
Columns:
  - id (bigint, primary key, auto increment)
  - email_id (bigint unsigned, required, foreign key → emails.id, ON DELETE CASCADE)
  - from_category_id (bigint unsigned, nullable, foreign key → email_categories.id, ON DELETE SET NULL) — previous category (null if uncategorized)
  - to_category_id (bigint unsigned, required, foreign key → email_categories.id, ON DELETE CASCADE) — new manually-assigned category
  - corrected_at (timestamp, required) — when the correction was made
  - created_at, updated_at (timestamps)

Indexes:
  - index on email_id
  - index on to_category_id
  - index on corrected_at
```

---

## 3. FILE MAP

### MIGRATIONS
```
database/migrations/2026_04_01_300001_create_email_categories_table.php
database/migrations/2026_04_01_300002_add_category_id_to_emails_table.php
database/migrations/2026_04_01_300003_create_email_category_corrections_table.php
```

### MODELS
```
app/Models/Email/EmailCategory.php
  - fillable: name, slug, color, icon, sort_order
  - relationships: emails() hasMany, corrections() hasManyThrough
  - casts: sort_order → integer

app/Models/Email/EmailCategoryCorrection.php
  - fillable: email_id, from_category_id, to_category_id, corrected_at
  - relationships: email() belongsTo, fromCategory() belongsTo, toCategory() belongsTo
  - casts: corrected_at → datetime
```

Note: The existing Email model (assumed at `app/Models/Email.php` or `app/Models/Email/Email.php`, created by the morning-email-digest feature) will need these additions:
  - relationship: category() belongsTo(EmailCategory)
  - fillable: add category_id

### SERVICES
```
app/Services/EmailCategorizationService.php
  - getCategories(): Collection — returns all categories ordered by sort_order
  - getCategoryById(int $id): EmailCategory — find or fail
  - createCategory(array $data): EmailCategory — create with auto-slug
  - updateCategory(int $id, array $data): EmailCategory — update with slug regeneration
  - deleteCategory(int $id): void — delete category (emails set to null via ON DELETE SET NULL)
  - reorderCategories(array $orderedIds): void — bulk update sort_order
  - categorizeEmail(Email $email): EmailCategory|null — AI categorization logic using from_email, from_name, subject, snippet
  - categorizeUncategorized(): int — batch-categorize all emails without a category, returns count
  - reassignCategory(Email $email, int $newCategoryId): void — manually reassign and log correction
  - getCategoryStats(): array — count of emails per category
  - getCorrections(int $perPage = 15): LengthAwarePaginator — paginated correction history
  - getAccuracyRate(): float — percentage of emails that were NOT manually corrected
```

### LIVEWIRE COMPONENTS (ADMIN)
```
app/Livewire/Admin/Email/Categories/EmailCategoryIndex.php
  - list all categories with email counts, reorder support
app/Livewire/Admin/Email/Categories/EmailCategoryForm.php
  - create/edit a category

app/Livewire/Admin/Email/Categorize/CategorizeDashboard.php
  - view emails filtered by category, reassign categories, see stats
```

### VIEWS (ADMIN)
```
resources/views/livewire/admin/email/categories/index.blade.php
  - category list with drag-to-reorder, color badges, email counts
resources/views/livewire/admin/email/categories/form.blade.php
  - create/edit form for a category

resources/views/livewire/admin/email/categorize/dashboard.blade.php
  - category stats cards, email list filtered by category, correction UI
```

### ROUTES (ADMIN)
```
routes/admin/email/categorize.php
  - GET  /admin/email/categories           → EmailCategoryIndex     → admin.email.categories.index
  - GET  /admin/email/categories/create    → EmailCategoryForm      → admin.email.categories.create
  - GET  /admin/email/categories/{emailCategory}/edit → EmailCategoryForm → admin.email.categories.edit
  - GET  /admin/email/categorize           → CategorizeDashboard    → admin.email.categorize.index
```

### SEEDER
```
database/seeders/EmailCategorySeeder.php
  - seeds the 5 default categories
```

---

## 4. COMPONENT CONTRACTS

### Component: App\Livewire\Admin\Email\Categories\EmailCategoryIndex

```
Namespace: App\Livewire\Admin\Email\Categories
Layout: #[Layout('components.layouts.admin')]

Properties:
  - $categories (Collection) — all categories with email counts
  - $search (string, #[Url]) — filter categories by name

Methods:
  - mount()
    Input: none
    Does: loads categories via service
    Output: populates $categories

  - delete(int $id)
    Input: category ID
    Does: calls service deleteCategory, reloads list
    Output: flash success message

  - updateOrder(array $orderedIds)
    Input: array of category IDs in new order
    Does: calls service reorderCategories
    Output: flash success message
```

### Component: App\Livewire\Admin\Email\Categories\EmailCategoryForm

```
Namespace: App\Livewire\Admin\Email\Categories
Layout: #[Layout('components.layouts.admin')]

Properties:
  - $emailCategoryId (int|null) — null for create, set for edit
  - $name (string) — category name
  - $color (string) — tailwind color token
  - $icon (string) — icon identifier
  - $sort_order (int) — display order

Methods:
  - mount(EmailCategory $emailCategory = null)
    Input: optional EmailCategory for edit
    Does: populates fields if editing
    Output: sets properties

  - save()
    Input: none
    Does: validates, calls service create or update
    Output: redirect to categories index with flash success

Validation Rules:
  - name: required|string|max:100
  - color: required|string|max:20
  - icon: required|string|max:50
  - sort_order: required|integer|min:0
```

### Component: App\Livewire\Admin\Email\Categorize\CategorizeDashboard

```
Namespace: App\Livewire\Admin\Email\Categorize
Layout: #[Layout('components.layouts.admin')]

Properties:
  - $categories (Collection) — all categories for filter tabs
  - $categoryStats (array) — email count per category
  - $selectedCategoryId (int|null, #[Url]) — filter emails by category (null = all)
  - $emails (LengthAwarePaginator) — paginated emails for selected category
  - $search (string, #[Url]) — search emails by subject/sender
  - $accuracyRate (float) — AI accuracy percentage
  - $uncategorizedCount (int) — emails without a category

Methods:
  - mount()
    Input: none
    Does: loads categories, stats, initial email list
    Output: populates all properties

  - filterByCategory(int|null $categoryId)
    Input: category ID or null for all
    Does: sets $selectedCategoryId, reloads emails
    Output: updates email list

  - reassignCategory(int $emailId, int $newCategoryId)
    Input: email ID and new category ID
    Does: calls service reassignCategory, reloads emails and stats
    Output: flash success message

  - categorizeAll()
    Input: none
    Does: calls service categorizeUncategorized for batch AI processing
    Output: flash message with count of categorized emails

  - updatedSearch()
    Input: none
    Does: reloads emails with search filter
    Output: updates email list
```

---

## 5. VIEW BLUEPRINTS

### View: resources/views/livewire/admin/email/categories/index.blade.php
```
Layout: components.layouts.admin
Side: ADMIN
Page title: "Email Categories"

Design rules:
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:
  - Breadcrumb: Dashboard > Email > Categories
  - Page header: "Email Categories" title + "Add Category" button
  - Flash messages
  - Category list as sortable cards (not a table — drag handle, name, color badge, icon preview, email count, edit/delete actions)
  - Each category card shows:
    - Drag handle icon (left)
    - Color dot + category name
    - Icon preview
    - Email count badge
    - Edit and delete action buttons (right)
  - Empty state: "No categories found" with "Add First Category" button
```

### View: resources/views/livewire/admin/email/categories/form.blade.php
```
Layout: components.layouts.admin
Side: ADMIN
Page title: "Create Category" or "Edit Category"

Design rules:
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - 2/3 + 1/3 grid layout

Sections:
  - Breadcrumb: Dashboard > Email > Categories > Create/Edit
  - Page header with title and Back button
  - Main content (2/3):
    - "Category Details" section card:
      - Name input (text, required)
      - Color select (dropdown with color preview: emerald, blue, amber, primary, gray, red, fuchsia, cyan)
      - Icon select (dropdown with icon preview: briefcase, code, star, newspaper, trash, envelope, bell, flag)
  - Sidebar (1/3):
    - "Preview" card showing how the category badge will look
    - "Settings" card with sort_order number input
    - "Actions" card with Save button (primary) and Cancel button (secondary)
```

### View: resources/views/livewire/admin/email/categorize/dashboard.blade.php
```
Layout: components.layouts.admin
Side: ADMIN
Page title: "Email Categorization"

Design rules:
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:
  - Breadcrumb: Dashboard > Email > Categorization
  - Page header: "Email Categorization" title + "Categorize All" button (runs AI on uncategorized emails)
  - Flash messages

  - Stat cards row (4 columns):
    - Total Categorized (emerald icon bg) — count of emails with a category
    - Uncategorized (amber icon bg) — count of emails without a category
    - AI Accuracy (blue icon bg) — percentage of emails not manually corrected
    - Total Corrections (fuchsia icon bg) — count of manual corrections

  - Category filter tabs:
    - Horizontal row of pill buttons, one per category + "All" tab
    - Each tab shows category color dot + name + count
    - Active tab uses bg-primary/10 text-primary-light style

  - Search bar: filter emails by subject or sender

  - Email list table:
    - Columns: From (name + email), Subject, Received, Category (dropdown to reassign), Actions
    - Category column: select dropdown with all categories, wire:change triggers reassignCategory
    - Each row shows the email with its current category badge
    - Pagination footer with record count

  - Empty state: "No emails found" when filter returns nothing
```

---

## 6. VALIDATION RULES

### Form: EmailCategoryForm
```
  - name: required|string|max:100
  - color: required|string|in:emerald,blue,amber,primary,gray,red,fuchsia,cyan
  - icon: required|string|in:briefcase,code,star,newspaper,trash,envelope,bell,flag
  - sort_order: required|integer|min:0
```

### Inline: CategorizeDashboard (reassignCategory)
```
  - newCategoryId: required|exists:email_categories,id
```

---

## 7. EDGE CASES & BUSINESS RULES

- **Delete category:** ON DELETE SET NULL on emails.category_id -- emails become uncategorized, not deleted. The email_category_corrections table uses ON DELETE SET NULL for from_category_id and ON DELETE CASCADE for to_category_id (if the target category is deleted, the correction record is meaningless).
- **Unique slug:** Auto-generated from name using Str::slug(). On update, regenerate slug. Enforce unique constraint at DB level.
- **Sort order:** Default to max(sort_order) + 1 on create. Reorder updates all sort_order values in a single transaction.
- **AI categorization logic:** Uses keyword matching on from_email, from_name, subject, and snippet fields. Categories are matched by configurable keyword sets stored in the service. Falls back to null (uncategorized) if no confident match. This is a rule-based approach initially; can be swapped for LLM-based classification later.
- **Correction tracking:** Every manual reassignment logs the old and new category. If an email is reassigned multiple times, each reassignment creates a new correction record.
- **Uncategorized emails:** Emails with category_id = null appear under an "Uncategorized" virtual tab in the dashboard. The "Categorize All" button processes only these emails.
- **Category color/icon:** Stored as string tokens (e.g., "emerald", "briefcase") and rendered in Blade using conditional class maps. Never store raw CSS classes in the database.
- **Batch categorization:** The categorizeUncategorized method processes emails in chunks of 100 to avoid memory issues. Returns total count processed.
- **Accuracy rate:** Calculated as (total categorized emails - total unique corrected emails) / total categorized emails * 100. Only counts the most recent correction per email to avoid double-counting.

---

## 8. IMPLEMENTATION ORDER

```
1. database/migrations/2026_04_01_300001_create_email_categories_table.php
2. database/migrations/2026_04_01_300002_add_category_id_to_emails_table.php
3. database/migrations/2026_04_01_300003_create_email_category_corrections_table.php
4. database/seeders/EmailCategorySeeder.php
5. app/Models/Email/EmailCategory.php
6. app/Models/Email/EmailCategoryCorrection.php
7. Update existing Email model — add category() relationship and category_id to fillable
8. app/Services/EmailCategorizationService.php
9. routes/admin/email/categorize.php
10. app/Livewire/Admin/Email/Categories/EmailCategoryIndex.php
11. resources/views/livewire/admin/email/categories/index.blade.php
12. app/Livewire/Admin/Email/Categories/EmailCategoryForm.php
13. resources/views/livewire/admin/email/categories/form.blade.php
14. app/Livewire/Admin/Email/Categorize/CategorizeDashboard.php
15. resources/views/livewire/admin/email/categorize/dashboard.blade.php
16. Update sidebar (components/layouts/admin.blade.php) — add Email group with Categories and Categorization links
```
