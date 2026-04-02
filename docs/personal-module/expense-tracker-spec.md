# Expense Tracker — Spec

Side: ADMIN

---

## 1. MODULE OVERVIEW

The Expense Tracker lets you log daily expenses with an amount, category, and optional note so you can see where your money goes. It provides daily, weekly, and monthly spending totals, a category breakdown chart, and a monthly budget system that shows how much you have left to spend.

**Features:**
- Log expenses with amount, category, and note
- Default categories (Food, Transport, Bills, Shopping, Other) seeded on first use
- Add and manage custom expense categories
- View expenses filtered by date range and category
- Daily, weekly, and monthly spending totals
- Spending chart broken down by category
- Set a monthly budget and see remaining amount with progress bar

**Admin features (what the admin can do):**
- Add, edit, and delete expenses
- Create, edit, and delete custom expense categories
- View expense list with search, date range filter, and category filter
- See stat cards for today's spending, this week's spending, this month's spending, and remaining budget
- View a doughnut/bar chart of spending by category for the selected month
- Set and update a monthly budget amount
- See a budget progress bar showing spent vs. remaining

---

## 2. DATABASE SCHEMA

```
Table: expense_categories
Columns:
  - id (bigint, primary key, auto increment)
  - name (string 100, required) — category name (e.g. "Food", "Transport")
  - color (string 7, required, default: '#7c3aed') — hex color for chart segments and badges
  - icon (string 50, nullable) — optional icon identifier for display
  - is_default (boolean, required, default: false) — true for seeded default categories
  - sort_order (integer, required, default: 0) — display ordering
  - created_at, updated_at (timestamps)

Indexes:
  - unique index on name (no duplicate category names)
  - index on sort_order

Foreign keys: none (single-user app)
```

```
Table: expenses
Columns:
  - id (bigint, primary key, auto increment)
  - expense_category_id (bigint unsigned, required) — FK to expense_categories
  - amount (decimal 10,2, required) — expense amount in user's currency
  - note (string 500, nullable) — optional description of the expense
  - spent_at (date, required) — date the expense occurred
  - created_at, updated_at (timestamps)

Indexes:
  - index on spent_at (date range queries for daily/weekly/monthly)
  - index on expense_category_id (category filtering and joins)
  - composite index on (spent_at, expense_category_id) for grouped queries

Foreign keys:
  - expense_category_id → expense_categories.id (cascade on delete)
```

```
Table: monthly_budgets
Columns:
  - id (bigint, primary key, auto increment)
  - year (smallint, required) — budget year (e.g. 2026)
  - month (tinyint, required) — budget month (1-12)
  - amount (decimal 10,2, required) — budget limit for the month
  - created_at, updated_at (timestamps)

Indexes:
  - unique composite index on (year, month) — one budget per month

Foreign keys: none
```

---

## 3. FILE MAP

```
MIGRATIONS:
  - database/migrations/2026_04_02_000001_create_expense_categories_table.php
  - database/migrations/2026_04_02_000002_create_expenses_table.php
  - database/migrations/2026_04_02_000003_create_monthly_budgets_table.php

SEEDERS:
  - database/seeders/ExpenseCategorySeeder.php
    - Seeds default categories: Food (#22c55e), Transport (#3b82f6), Bills (#f59e0b), Shopping (#a78bfa), Other (#6b7280)

MODELS:
  - app/Models/Expense/ExpenseCategory.php (2+ related models → subfolder)
    - fillable: name, color, icon, is_default, sort_order
    - casts: is_default → boolean
    - relationships: expenses() → hasMany(Expense::class)
    - scopes: scopeOrdered() — order by sort_order asc

  - app/Models/Expense/Expense.php
    - fillable: expense_category_id, amount, note, spent_at
    - casts: amount → decimal:2, spent_at → date
    - relationships: category() → belongsTo(ExpenseCategory::class, 'expense_category_id')
    - scopes: scopeForDate($date), scopeForDateRange($start, $end), scopeForMonth($year, $month), scopeForWeek($date), scopeForCategory($categoryId)

  - app/Models/Expense/MonthlyBudget.php
    - fillable: year, month, amount
    - casts: amount → decimal:2

SERVICES:
  - app/Services/ExpenseCategoryService.php
    - getAllOrdered(): Collection — all categories ordered by sort_order
    - createCategory(array $data): ExpenseCategory — create a custom category
    - updateCategory(ExpenseCategory $category, array $data): ExpenseCategory — update category
    - deleteCategory(ExpenseCategory $category): void — delete category (only if not default and no expenses attached, or reassign to "Other")
    - seedDefaultsIfEmpty(): void — seed default categories if table is empty

  - app/Services/ExpenseService.php
    - getExpenses(?string $search, ?int $categoryId, ?string $dateFrom, ?string $dateTo): Builder — returns query builder for paginated list
    - createExpense(array $data): Expense — create a new expense
    - updateExpense(Expense $expense, array $data): Expense — update an expense
    - deleteExpense(Expense $expense): void — delete an expense
    - getTodayTotal(): float — sum of today's expenses
    - getWeekTotal(?Carbon $date): float — sum of current week's expenses (Mon-Sun)
    - getMonthTotal(int $year, int $month): float — sum of a given month's expenses
    - getCategoryBreakdown(int $year, int $month): Collection — grouped totals per category for chart
    - getMonthlyBudget(int $year, int $month): ?MonthlyBudget — get budget for a month
    - setMonthlyBudget(int $year, int $month, float $amount): MonthlyBudget — create or update budget
    - getBudgetRemaining(int $year, int $month): ?float — budget amount minus month total (null if no budget set)
    - getDailyTotals(int $year, int $month): Collection — daily totals for the month (for sparkline/chart)

--- ADMIN FILES ---

LIVEWIRE COMPONENTS:
  - app/Livewire/Admin/Personal/ExpenseTracker/ExpenseIndex.php
    - Index page with expense list, stats, chart, and budget
  - app/Livewire/Admin/Personal/ExpenseTracker/ExpenseForm.php
    - Create/edit expense form
  - app/Livewire/Admin/Personal/ExpenseTracker/ExpenseCategoryIndex.php
    - Manage expense categories

VIEWS:
  - resources/views/livewire/admin/personal/expense-tracker/index.blade.php
    - Main expense dashboard: stat cards, category chart, expense table, budget section
  - resources/views/livewire/admin/personal/expense-tracker/form.blade.php
    - Create/edit expense form
  - resources/views/livewire/admin/personal/expense-tracker/categories.blade.php
    - Category management page

ROUTES (admin):
  - routes/admin/personal/expense-tracker.php
    - GET  /admin/personal/expense-tracker             → ExpenseIndex          → admin.personal.expense-tracker.index
    - GET  /admin/personal/expense-tracker/create       → ExpenseForm           → admin.personal.expense-tracker.create
    - GET  /admin/personal/expense-tracker/{expense}/edit → ExpenseForm         → admin.personal.expense-tracker.edit
    - GET  /admin/personal/expense-tracker/categories   → ExpenseCategoryIndex  → admin.personal.expense-tracker.categories
```

---

## 4. COMPONENT CONTRACTS

### Component: App\Livewire\Admin\Personal\ExpenseTracker\ExpenseIndex
Namespace: App\Livewire\Admin\Personal\ExpenseTracker

```
Properties:
  - $search (string, '') — search filter for expense notes
  - $filterCategory (int|string, '') — category filter, #[Url]
  - $filterMonth (string, '') — month filter in 'YYYY-MM' format, #[Url], defaults to current month
  - $todayTotal (float) — today's spending total
  - $weekTotal (float) — this week's spending total
  - $monthTotal (float) — selected month's spending total
  - $budgetAmount (float|null) — current month's budget amount
  - $budgetRemaining (float|null) — remaining budget
  - $newBudgetAmount (string, '') — input field for setting/updating budget
  - $categoryBreakdown (array) — category totals for chart [{name, color, total}]
  - $categories (Collection) — all categories for filter dropdown

Methods:
  - mount()
    Input: none
    Does: loads categories, computes stats for the current month, seeds default categories if empty
    Output: sets all computed properties

  - updatedFilterMonth()
    Input: none
    Does: recomputes stats and chart data when month changes
    Output: refreshes stat properties

  - setBudget()
    Input: uses $newBudgetAmount
    Does: validates amount, calls ExpenseService::setMonthlyBudget(), flashes success
    Output: flash message, refreshes budget properties

  - delete(int $id)
    Input: expense ID
    Does: finds expense, calls ExpenseService::deleteExpense(), flashes success
    Output: flash message, refreshes stats

  - rendering lifecycle
    Does: passes paginated expenses to view via ExpenseService::getExpenses()
```

### Component: App\Livewire\Admin\Personal\ExpenseTracker\ExpenseForm
Namespace: App\Livewire\Admin\Personal\ExpenseTracker

```
Properties:
  - $expenseId (int|null) — null for create, set for edit
  - $expense_category_id (string, '') — selected category
  - $amount (string, '') — expense amount
  - $note (string, '') — optional note
  - $spent_at (string, '') — date of expense, defaults to today
  - $categories (Collection) — all categories for dropdown

Methods:
  - mount(?Expense $expense = null)
    Input: optional Expense model (for edit)
    Does: if editing, populates properties from model; loads categories; defaults spent_at to today
    Output: sets properties

  - save()
    Input: uses component properties
    Does: validates, calls ExpenseService::createExpense() or updateExpense(), flashes success, redirects to index
    Output: redirect to admin.personal.expense-tracker.index

Validation Rules:
  - expense_category_id: required|exists:expense_categories,id
  - amount: required|numeric|min:0.01|max:99999999.99
  - note: nullable|string|max:500
  - spent_at: required|date|before_or_equal:today
```

### Component: App\Livewire\Admin\Personal\ExpenseTracker\ExpenseCategoryIndex
Namespace: App\Livewire\Admin\Personal\ExpenseTracker

```
Properties:
  - $categories (Collection) — all categories ordered
  - $editingCategoryId (int|null) — ID of category being edited inline, null when not editing
  - $name (string, '') — category name input
  - $color (string, '#7c3aed') — category color input
  - $showForm (bool, false) — toggles inline add form visibility

Methods:
  - mount()
    Input: none
    Does: loads all categories, seeds defaults if empty
    Output: sets $categories

  - addCategory()
    Input: uses $name, $color
    Does: validates, calls ExpenseCategoryService::createCategory(), refreshes list, resets form
    Output: flash message

  - startEdit(int $id)
    Input: category ID
    Does: sets $editingCategoryId, populates $name and $color from the category
    Output: shows inline edit mode for that row

  - updateCategory()
    Input: uses $editingCategoryId, $name, $color
    Does: validates, calls ExpenseCategoryService::updateCategory(), refreshes list, clears edit state
    Output: flash message

  - cancelEdit()
    Input: none
    Does: resets edit state
    Output: clears editing mode

  - deleteCategory(int $id)
    Input: category ID
    Does: checks if default (blocks), checks expense count, calls ExpenseCategoryService::deleteCategory()
    Output: flash message (success or error if default)

Validation Rules:
  - name: required|string|max:100|unique:expense_categories,name (ignore current when editing)
  - color: required|string|regex:/^#[0-9A-Fa-f]{6}$/
```

---

## 5. VIEW BLUEPRINTS

### View: resources/views/livewire/admin/personal/expense-tracker/index.blade.php
Layout: components.layouts.admin
Side: ADMIN
Page title: "Expense Tracker"

```
Design rules (from CLAUDE.md admin side):
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:
  - Breadcrumb: Dashboard > Personal > Expense Tracker
  - Page header: "Expense Tracker" title + "Add Expense" button (links to create) + "Categories" button (links to categories page)

  - Stat cards row (4 columns):
    1. Today's Spending — dollar icon, bg-emerald-500/10, shows $todayTotal
    2. This Week — calendar icon, bg-blue-500/10, shows $weekTotal
    3. This Month — chart icon, bg-primary/10, shows $monthTotal
    4. Budget Remaining — wallet icon, bg-amber-500/10 or bg-red-500/10 (red if overspent), shows $budgetRemaining or "No budget set"

  - Two-column layout below stats:
    - Left (2/3): Expense table
    - Right (1/3): Category breakdown chart + Budget card

  - Category chart card:
    - Doughnut chart using Alpine.js + canvas (or simple CSS-based bar chart)
    - Shows spending per category with matching colors
    - Month selector dropdown

  - Budget card:
    - Shows current month's budget amount (editable)
    - Progress bar: spent / budget (gradient from-primary to-fuchsia-500)
    - Text: "$X spent of $Y" and "$Z remaining"
    - Input + button to set/update budget amount
    - If over budget: progress bar turns red, remaining shows negative in red

  - Filter bar above table:
    - Search input (searches notes)
    - Category dropdown filter
    - Month picker (YYYY-MM input)

  - Table columns: Date, Category (badge with color), Amount, Note, Actions (edit/delete)
  - Sorted by spent_at desc, then created_at desc
  - Pagination: 20 per page

  - Empty state: "No expenses logged yet. Start tracking your spending!" with Add Expense button
```

### View: resources/views/livewire/admin/personal/expense-tracker/form.blade.php
Layout: components.layouts.admin
Side: ADMIN
Page title: "Add Expense" / "Edit Expense"

```
Design rules:
  - Full-width form layout
  - Cards: rounded-xl, bg-dark-800 border border-dark-700

Sections:
  - Breadcrumb: Dashboard > Personal > Expense Tracker > Add/Edit Expense
  - Page header: dynamic title + Back button

  - Form section card "Expense Details":
    - Two-column grid:
      - Amount: number input with step="0.01", required, placeholder "0.00"
      - Category: select dropdown with all categories, required
      - Date: date input, defaults to today, required
      - Note: text input (single line), optional, placeholder "What was this for?"

  - Submit card:
    - Save button (primary) with loading state
    - Cancel button (secondary, navigates back)
```

### View: resources/views/livewire/admin/personal/expense-tracker/categories.blade.php
Layout: components.layouts.admin
Side: ADMIN
Page title: "Expense Categories"

```
Design rules:
  - Cards: rounded-xl, bg-dark-800 border border-dark-700

Sections:
  - Breadcrumb: Dashboard > Personal > Expense Tracker > Categories
  - Page header: "Expense Categories" title + Back to Expense Tracker button

  - Add category inline form (toggleable):
    - Name input + color picker + Add button, all in a single row

  - Categories list (card-based, not table):
    - Each category row shows: color dot, name, expense count badge, default badge (if is_default)
    - Inline edit: clicking edit replaces name/color with inputs
    - Delete button (disabled/hidden for default categories)
    - Drag handle for reordering (optional — can be deferred)

  - Empty state: "No categories found. Default categories will be created automatically."
```

---

## 6. VALIDATION RULES

```
Form: ExpenseForm (create/edit expense)
  - expense_category_id: required|exists:expense_categories,id
  - amount: required|numeric|min:0.01|max:99999999.99
  - note: nullable|string|max:500
  - spent_at: required|date|before_or_equal:today

Form: ExpenseCategoryIndex (add/edit category)
  - name: required|string|max:100|unique:expense_categories,name (ignore $editingCategoryId on update)
  - color: required|string|regex:/^#[0-9A-Fa-f]{6}$/

Form: ExpenseIndex (set budget)
  - newBudgetAmount: required|numeric|min:0.01|max:99999999.99
```

---

## 7. EDGE CASES & BUSINESS RULES

- **Default categories:** The 5 default categories (Food, Transport, Bills, Shopping, Other) are seeded via ExpenseCategorySeeder. The service also calls seedDefaultsIfEmpty() on mount of index/form to auto-create them if the table is empty (handles fresh installs without running seeder).
- **Delete category:** Default categories (is_default = true) cannot be deleted. Custom categories with existing expenses: cascade delete removes all expenses in that category (user is warned via wire:confirm). Alternatively, expenses can be reassigned to "Other" before deletion — implementation should use cascade for simplicity since this is a personal tool.
- **Delete expense:** Hard delete, no soft delete needed for a personal expense tracker.
- **Unique category name:** Enforced at both DB (unique index) and validation level. Case-insensitive check not required at DB level but validation should use unique rule.
- **Budget per month:** Only one budget per (year, month) pair, enforced by unique composite index. Setting a budget for a month that already has one performs an upsert (updateOrCreate).
- **Budget remaining:** Can go negative if spending exceeds budget. Display negative values in red.
- **Date constraint:** Expenses can only be logged for today or past dates (before_or_equal:today). No future-dating expenses.
- **Amount precision:** Stored as decimal(10,2). Display with 2 decimal places and currency symbol ($ prefix).
- **Sort order:** Expenses sorted by spent_at desc, then id desc. Categories sorted by sort_order asc.
- **Month filter default:** Defaults to current month (Carbon::now()->format('Y-m')). Stats always reflect the selected month.
- **Week calculation:** Week runs Monday to Sunday. Uses Carbon's startOfWeek(Carbon::MONDAY) and endOfWeek(Carbon::SUNDAY).
- **Chart data:** Category breakdown only includes categories that have expenses in the selected month (zero-amount categories excluded from chart).
- **Sidebar placement:** Expense Tracker appears under the "Personal" parent group in the sidebar navigation, following the module grouping rules.

---

## 8. IMPLEMENTATION ORDER

```
1. database/migrations/2026_04_02_000001_create_expense_categories_table.php
2. database/migrations/2026_04_02_000002_create_expenses_table.php
3. database/migrations/2026_04_02_000003_create_monthly_budgets_table.php
4. database/seeders/ExpenseCategorySeeder.php
5. app/Models/Expense/ExpenseCategory.php
6. app/Models/Expense/Expense.php
7. app/Models/Expense/MonthlyBudget.php
8. app/Services/ExpenseCategoryService.php
9. app/Services/ExpenseService.php
10. routes/admin/personal/expense-tracker.php
11. app/Livewire/Admin/Personal/ExpenseTracker/ExpenseCategoryIndex.php
12. resources/views/livewire/admin/personal/expense-tracker/categories.blade.php
13. app/Livewire/Admin/Personal/ExpenseTracker/ExpenseForm.php
14. resources/views/livewire/admin/personal/expense-tracker/form.blade.php
15. app/Livewire/Admin/Personal/ExpenseTracker/ExpenseIndex.php
16. resources/views/livewire/admin/personal/expense-tracker/index.blade.php
17. Sidebar: add "Personal" group with "Expense Tracker" and "Expense Categories" links
```
