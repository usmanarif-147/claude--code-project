# AI Task Prioritization — Spec

Side: **ADMIN**

---

## 1. MODULE OVERVIEW

AI Task Prioritization is a read-and-action dashboard within the Tasks module that uses an AI provider (Claude or OpenAI) to analyze the user's current tasks and recommend an optimal execution order. It reads existing Task records, sends them to the configured AI API, and displays a prioritized list with urgency reasoning, a "start with this" recommendation, and warnings about overdue or forgotten tasks.

### Features
- AI auto-sorts today's tasks by urgency and importance
- Suggests which task to start with (highlighted "Start Here" card)
- Warns about overdue or forgotten tasks (past due date, not completed)
- Allows re-running the AI analysis on demand (manual refresh)
- Shows a graceful setup prompt when no AI API key is configured
- Displays the AI's reasoning for each task's priority ranking
- Lets the user apply the AI-suggested order to their tasks (updates `sort_order` on Task records)

### Admin features
- View AI-prioritized task list for today
- Trigger AI analysis manually via "Prioritize My Tasks" button
- See overdue task warnings with days-overdue count
- Apply suggested order to persist the AI's recommended sort
- Choose AI provider (Claude or OpenAI) based on which API key is configured

---

## 2. DATABASE SCHEMA

No new tables are needed. This feature reads and updates existing tables:

```
Table: tasks (existing — created by the daily-task-planner feature)
Columns used by this feature (read):
  - id
  - title
  - description (nullable)
  - task_category_id (FK -> task_categories.id)
  - priority (string: low, medium, high)
  - status (string: pending, in_progress, completed)
  - due_date (date, nullable)
  - sort_order (integer)
  - created_at, updated_at

Columns updated by this feature:
  - sort_order (integer) — updated when user applies AI-suggested order

Table: api_keys (existing — created by the settings module)
Columns used by this feature (read-only):
  - provider (filter for 'claude' or 'openai')
  - key_value (encrypted API key)
  - is_connected (boolean)

Table: task_categories (existing — created by the task-categories feature)
Columns used by this feature (read-only):
  - id, name, color — included in AI context for better prioritization
```

No migrations required.

---

## 3. FILE MAP

```
MIGRATIONS:
  - None — no new tables

MODELS:
  - None — uses existing Task, ApiKey, and TaskCategory models

SERVICES:
  - app/Services/AiTaskPrioritizationService.php
    - getConfiguredProvider(int $userId): ?string
        — checks api_keys for 'claude' or 'openai' with is_connected=true, returns provider name or null
    - getTodaysTasks(int $userId): Collection
        — returns tasks for today: pending/in_progress, due_date <= today or due_date is null, ordered by sort_order
    - getOverdueTasks(int $userId): Collection
        — returns tasks where due_date < today and status != completed
    - prioritize(int $userId): array
        — builds prompt from tasks, calls AI API, parses response, returns structured result
    - buildPrompt(Collection $tasks, Collection $overdueTasks): string
        — constructs the system+user prompt with task details for the AI
    - callClaudeApi(string $apiKey, string $prompt): string
        — sends HTTP request to Claude API (messages endpoint), returns response text
    - callOpenAiApi(string $apiKey, string $prompt): string
        — sends HTTP request to OpenAI API (chat completions endpoint), returns response text
    - parseAiResponse(string $responseText): array
        — parses the AI JSON response into structured array with prioritized_tasks, start_with, overdue_warnings, reasoning
    - applyOrder(int $userId, array $taskIds): void
        — updates sort_order on Task records in the given order (1, 2, 3, ...)

--- ADMIN FILES ---

LIVEWIRE COMPONENTS:
  - app/Livewire/Admin/Tasks/AiPrioritization/AiPrioritizationIndex.php
    - public properties:
        - $hasApiKey (bool) — whether a Claude or OpenAI key is configured
        - $provider (?string) — which AI provider is active ('claude' or 'openai')
        - $result (?array) — the parsed AI response (null until analysis runs)
        - $isLoading (bool) — true while AI request is in flight
        - $error (?string) — error message from failed AI call
        - $lastAnalyzedAt (?string) — timestamp of last successful analysis
    - methods:
        - mount() — checks for API key, loads initial state
        - analyze() — triggers AI prioritization, sets result or error
        - applyOrder() — persists the AI-suggested sort_order to Task records
        - render() — returns view with tasks and result data

VIEWS:
  - resources/views/livewire/admin/tasks/ai-prioritization/index.blade.php
    - AI prioritization dashboard page

ROUTES (admin):
  - routes/admin/tasks/ai-prioritization.php
    - GET /ai-prioritization → AiPrioritizationIndex → admin.tasks.ai-prioritization.index
```

---

## 4. COMPONENT CONTRACTS

### AiPrioritizationIndex

```
Component: App\Livewire\Admin\Tasks\AiPrioritization\AiPrioritizationIndex
Namespace: App\Livewire\Admin\Tasks\AiPrioritization
Layout:    components.layouts.admin

Properties:
  - $hasApiKey (bool, default false) — true if a Claude or OpenAI key exists and is_connected
  - $provider (?string, default null) — 'claude' or 'openai' or null
  - $result (?array, default null) — parsed AI response with keys: prioritized_tasks, start_with, overdue_warnings, reasoning
  - $isLoading (bool, default false) — loading state during API call
  - $error (?string, default null) — error message from failed AI call or empty task list
  - $lastAnalyzedAt (?string, default null) — human-readable timestamp of last analysis

Methods:
  - mount(AiTaskPrioritizationService $service)
    Input: none (service injected)
    Does:
      1. Checks for configured AI provider via $service->getConfiguredProvider(auth()->id())
      2. Sets $hasApiKey and $provider accordingly
    Output: none

  - analyze(AiTaskPrioritizationService $service)
    Input: none (service injected)
    Does:
      1. Sets $isLoading = true, $error = null
      2. Calls $service->prioritize(auth()->id())
      3. On success: sets $result, $lastAnalyzedAt = now()->format('g:i A')
      4. On failure: catches exception, sets $error with user-friendly message
      5. Sets $isLoading = false
    Output: updates component state (no redirect)

  - applyOrder(AiTaskPrioritizationService $service)
    Input: none (service injected)
    Does:
      1. Extracts task IDs from $result['prioritized_tasks'] in order
      2. Calls $service->applyOrder(auth()->id(), $taskIds)
      3. Flashes success message
    Output: session flash 'success'

  - render()
    Input: none
    Does:
      1. Loads today's tasks and overdue tasks via service for display counts
    Output: returns view with $todaysTasks and $overdueTasks collections
```

---

## 5. VIEW BLUEPRINTS

### Index View (AI Prioritization Dashboard)

```
View: resources/views/livewire/admin/tasks/ai-prioritization/index.blade.php
Layout: components.layouts.admin
Side: ADMIN
Page title: "AI Prioritization"

Design rules:
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:

  1. Breadcrumb: Dashboard > Tasks > AI Prioritization

  2. Page header:
     - Title: "AI PRIORITIZATION"
     - Subtitle: "Let AI analyze your tasks and suggest the optimal order"
     - Right side: "Prioritize My Tasks" button (primary, with loading spinner)
       - Disabled when $isLoading or !$hasApiKey
       - wire:click="analyze"
     - Below button: small text showing provider name + last analyzed time (if available)

  3. No API Key state (shown when $hasApiKey is false):
     - CTA card with gradient border (bg-gradient-to-br from-primary/20 to-fuchsia-600/20 border-primary/30)
     - Icon: key/lock icon in primary/10 circle
     - Heading: "API Key Required"
     - Body text: "Configure a Claude or OpenAI API key in Settings to use AI prioritization."
     - Button: "Go to Settings" linking to admin.settings.api-keys.index

  4. Error state (shown when $error is not null):
     - Red alert banner (bg-red-500/10 border border-red-500/20 text-red-400)
     - Error message text
     - "Try Again" button

  5. Stat cards row (shown when $hasApiKey is true, always visible):
     - 4 cards in a grid (grid-cols-4):
       a. "Today's Tasks" — count of today's tasks, icon: clipboard, bg-primary/10
       b. "Completed" — count of completed today, icon: check-circle, bg-emerald-500/10
       c. "Overdue" — count of overdue tasks, icon: exclamation-triangle, bg-red-500/10
       d. "AI Provider" — provider name (Claude/OpenAI), icon: cpu/spark, bg-fuchsia-500/10

  6. Overdue warnings section (shown when overdue tasks exist, always — not only after analysis):
     - Card with amber/warning styling (bg-amber-500/10 border border-amber-500/20)
     - Heading: "Overdue Tasks" with warning icon
     - List of overdue tasks with:
       - Task title
       - Category badge (colored by category color)
       - Days overdue count (red text)
       - Due date

  7. AI Results section (shown only after successful analysis, when $result is not null):

     a. "Start Here" highlight card:
        - Gradient border card (CTA style from design system)
        - Shows the AI's recommended first task
        - Task title, category, priority badge, AI reasoning
        - Sparkle/star icon

     b. Prioritized task list:
        - Numbered list (1, 2, 3...) in a card
        - Each row shows:
          - Priority rank number (large, text-white)
          - Task title (text-white font-medium)
          - Category badge (color from task_categories)
          - Priority badge (high=red, medium=amber, low=blue)
          - Due date (if set)
          - AI reasoning snippet (text-gray-500, italic)
        - Rows alternate with subtle border separators (divide-y divide-dark-700/50)

     c. "Apply This Order" button at bottom of prioritized list:
        - Primary button with loading state
        - wire:click="applyOrder"
        - Saves the AI-suggested sort_order to task records

  8. Empty state (shown when hasApiKey is true but no tasks exist for today):
     - Standard empty state card
     - Icon: clipboard with check
     - Text: "No tasks for today. Add tasks in the Daily Planner to get AI suggestions."
     - Link to daily planner page

  9. Flash messages: standard success/error flash at top of page
```

---

## 6. VALIDATION RULES

No form validation is needed for this feature. This is a read-and-action dashboard, not a CRUD form. The only user action that modifies data is "Apply Order," which uses task IDs from the AI result (already validated server-side by the service).

Internal validation in the service:
```
- AI API key must exist and be connected (checked before API call)
- AI response must be valid JSON with expected structure (parsed defensively)
- Task IDs in applyOrder must belong to the authenticated user (security check)
```

---

## 7. EDGE CASES & BUSINESS RULES

- **No API key configured**: Show the setup prompt CTA card. The "Prioritize My Tasks" button is disabled. No error is thrown — the state is handled gracefully in the view.
- **API key exists but is_connected is false**: Treat the same as no API key. The key must be both present and connected (tested successfully) to be used.
- **Both Claude and OpenAI keys exist**: Prefer Claude. The service checks for Claude first, falls back to OpenAI.
- **No tasks for today**: Show the empty state. Do not call the AI API with an empty task list.
- **AI API call fails (network error, rate limit, invalid key)**: Catch the exception in the component, set $error with a user-friendly message (e.g., "Failed to reach the AI service. Please try again."). Do not expose raw API errors.
- **AI response is malformed (not valid JSON, missing fields)**: The parseAiResponse method returns a safe fallback structure with an error flag. The component shows a generic error: "AI returned an unexpected response. Please try again."
- **Overdue tasks**: A task is overdue if due_date < today AND status is not 'completed'. Overdue warnings appear always (not only after AI analysis) so the user sees them immediately on page load.
- **Apply order — security**: The applyOrder method must verify that all task IDs belong to the authenticated user before updating. Ignore any IDs that do not belong to the user.
- **Apply order — partial match**: If the AI returns task IDs that no longer exist (deleted between analysis and apply), skip them silently. Update only existing, valid tasks.
- **Concurrent modifications**: If tasks are added/completed/deleted between analysis and applying the order, the apply action works on a best-effort basis — it updates what still exists.
- **API timeout**: Set a 30-second timeout on HTTP requests to the AI provider. If it times out, show a timeout-specific error message.
- **Rate limiting**: No client-side rate limiting for now. The user can re-run analysis as often as they want. The AI provider's own rate limits will apply.
- **No sort_order column yet**: This feature depends on the Task model having a `sort_order` column. If the daily-task-planner migration has not been run yet, this feature cannot function. The mount() method should handle this gracefully (check if tasks table exists).
- **AI prompt engineering**: The prompt instructs the AI to return a JSON object with a specific schema. The prompt includes: task titles, descriptions, categories, priorities, due dates, overdue status, and current date. It asks for a prioritized order, a "start with" recommendation, reasoning per task, and overdue warnings.

---

## 8. IMPLEMENTATION ORDER

```
1. Service — app/Services/AiTaskPrioritizationService.php
   (depends on existing Task, ApiKey, TaskCategory models — no new models needed)
2. Routes — routes/admin/tasks/ai-prioritization.php
3. Livewire component — AiPrioritizationIndex.php
4. Admin view — index.blade.php
5. Sidebar — add "AI Prioritization" link under "Tasks" parent group in admin layout
```

Note: This feature depends on the following being implemented first:
- Task Categories feature (task_categories table + TaskCategory model)
- Daily Task Planner feature (tasks table + Task model)
- API Keys feature from Settings module (api_keys table + ApiKey model — already implemented)
