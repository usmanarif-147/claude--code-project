# Shared Features Spec: TXT File Upload, PDF Download, AI Category Identification

## 1. UPDATE OVERVIEW

Three shared features are being added to the Tasks module. All three are "shared" because they will be consumed by both DailyPlannerIndex (personal tasks) and ProjectBoardIndex (project tasks, created by a separate spec). None of these features require database migrations.

| Feature | Purpose | New Service | Touches View | Touches Component |
|---|---|---|---|---|
| TXT File Upload | Bulk-import tasks from a plain-text file | `TaskImportService` | daily-planner `index.blade.php` | `DailyPlannerIndex.php` |
| PDF Download | Export tasks for a period as a downloadable PDF | `TaskPdfService` | daily-planner `index.blade.php` | (none -- uses a controller route) |
| AI Category ID | Auto-assign or create categories via AI | `AiCategoryIdentificationService` | daily-planner `index.blade.php` | `DailyPlannerIndex.php` |

---

## 2. CURRENT STATE (BEFORE)

### 2.1 TaskService (`app/Services/TaskService.php`)

Current method signatures:

```php
public function create(array $data): Task
public function update(Task $task, array $data): Task
public function delete(Task $task): void
public function toggleComplete(Task $task): Task
public function markComplete(Task $task): Task
public function markPending(Task $task): Task
public function moveIncompleteTo(int $userId, Carbon $fromDate, Carbon $toDate): int
public function getTasksForDate(int $userId, Carbon $date): Collection
public function getCompletionStats(int $userId, Carbon $date): array
```

The `create()` method accepts a flat `$data` array and calls `Task::create($data)` directly. It has no awareness of AI categorization.

### 2.2 DailyPlannerIndex Component (`app/Livewire/Admin/Tasks/DailyPlanner/DailyPlannerIndex.php`)

Current public properties:

```php
#[Url] public string $selectedDate = '';
#[Url] public string $statusFilter = 'all';
#[Url] public string $priorityFilter = 'all';
#[Url] public string $categoryFilter = 'all';
public string $newTaskTitle = '';
public string $newTaskPriority = 'medium';
public string $newTaskCategoryId = '';
public ?int $editingTaskId = null;
public string $editTitle = '';
public string $editPriority = 'medium';
public string $editCategoryId = '';
```

Current methods: `mount`, `addTask`, `toggleComplete`, `startEditing`, `saveEdit`, `cancelEdit`, `deleteTask`, `moveIncompleteToTomorrow`, `goToDate`, `goToToday`, `goToPreviousDay`, `goToNextDay`, `render`.

The component does NOT use `WithFileUploads`. It has no import modal state, no PDF download controls, and no AI categorize toggle.

### 2.3 Daily Planner View (`resources/views/livewire/admin/tasks/daily-planner/index.blade.php`)

Current sections in order:
1. Breadcrumb
2. Page header (title + "Move Incomplete to Tomorrow" button)
3. Flash messages
4. Date navigation bar
5. Progress section
6. Filter bar (status, priority, category selects)
7. Inline add-task form (title input, priority select, category select, Add button)
8. Task list (each row: checkbox, title, priority badge, category badge, edit/delete buttons)
9. Empty state

No import button, no PDF download button, no AI toggle exists in the current view.

### 2.4 AI Pattern (from `AiTaskPrioritizationService`)

The established AI call pattern:
- `getConfiguredProvider(int $userId): ?string` -- checks Claude first, then OpenAI
- `callClaudeApi(string $apiKey, string $prompt): string` -- POST to `https://api.anthropic.com/v1/messages`, model `claude-sonnet-4-20250514`, max_tokens 1024, anthropic-version `2023-06-01`
- `callOpenAiApi(string $apiKey, string $prompt): string` -- POST to `https://api.openai.com/v1/chat/completions`, model `gpt-4o-mini`, max_tokens 1024
- Response text extraction: Claude = `$body['content'][0]['text']`, OpenAI = `$body['choices'][0]['message']['content']`
- JSON parsing: strip markdown code fences, `json_decode`, validate structure

### 2.5 PDF Pattern (from `ResumeService`)

The established PDF generation pattern:
- Uses `Barryvdh\DomPDF\Facade\Pdf` (already installed via Composer)
- `Pdf::loadView('view.path', $data)->setPaper('a4')->setOption('isRemoteEnabled', true)`
- Templates live in `resources/views/` with full HTML document structure, inline `<style>`, and DomPDF-compatible CSS (no Tailwind, no external stylesheets)
- Font: `font-family: DejaVu Sans, sans-serif` (bundled with DomPDF)
- Downloads via `$pdf->download($filename)` returning a `Symfony\Component\HttpFoundation\Response`

### 2.6 Task Model (`app/Models/Task/Task.php`)

Fillable: `user_id`, `category_id`, `title`, `description`, `due_date`, `priority`, `status`, `completed_at`, `sort_order`.

Scopes: `forUser`, `forDate`, `forToday`, `ordered`, `byPriority`, `pending`, `completed`.

### 2.7 TaskCategory Model (`app/Models/Task/TaskCategory.php`)

Fillable: `name`, `color`, `sort_order`.

Scopes: `ordered`, `search`.

Relationships: `tasks()` hasMany.

Note: TaskCategory does NOT have a `user_id` column -- categories are global (shared across users).

### 2.8 ApiKey Model (`app/Models/ApiKey.php`)

Providers: `PROVIDER_CLAUDE = 'claude'`, `PROVIDER_OPENAI = 'openai'`.

Scopes: `forUser`, `forProvider`, `connected`.

The `key_value` field is cast as `encrypted`.

---

## 3. TARGET STATE (AFTER)

### 3.1 New Services

#### `app/Services/TaskImportService.php`

```php
<?php

namespace App\Services;

use App\Models\Task\Task;
use App\Models\Task\TaskCategory;

class TaskImportService
{
    public function __construct(
        private TaskService $taskService,
        private AiCategoryIdentificationService $aiCategoryService
    ) {}

    /**
     * Import tasks from a TXT file's contents.
     *
     * @param int    $userId           The authenticated user's ID
     * @param string $fileContents     Raw text content of the uploaded .txt file
     * @param string $dueDate          Date in Y-m-d format to assign to all imported tasks
     * @param string $defaultPriority  Fallback priority if line has no #priority tag (default: 'medium')
     * @param bool   $aiCategorize     Whether to use AI to categorize tasks without a @category tag
     * @param array  $extraData        Additional fields to merge into every created task (e.g., project_id for project tasks)
     *
     * @return array{imported: int, skipped: int, errors: string[]}
     */
    public function importFromTxt(
        int $userId,
        string $fileContents,
        string $dueDate,
        string $defaultPriority = 'medium',
        bool $aiCategorize = false,
        array $extraData = []
    ): array {
        $lines = explode("\n", $fileContents);
        $imported = 0;
        $skipped = 0;
        $errors = [];

        // Collect tasks that need AI categorization for batch processing
        $pendingAiTasks = [];

        foreach ($lines as $lineNumber => $line) {
            $parsed = $this->parseLine($line);

            if ($parsed === null) {
                $skipped++;
                continue;
            }

            $categoryId = null;

            // If line had @category hint, find or create the category
            if ($parsed['category_hint'] !== null) {
                $category = TaskCategory::query()
                    ->whereRaw('LOWER(name) = ?', [strtolower($parsed['category_hint'])])
                    ->first();

                if (!$category) {
                    $category = TaskCategory::create([
                        'name' => ucfirst($parsed['category_hint']),
                        'color' => $this->generateCategoryColor(),
                        'sort_order' => TaskCategory::max('sort_order') + 1,
                    ]);
                }

                $categoryId = $category->id;
            }

            $taskData = array_merge([
                'user_id' => $userId,
                'title' => $parsed['title'],
                'priority' => $parsed['priority'] ?? $defaultPriority,
                'category_id' => $categoryId,
                'due_date' => $dueDate,
                'status' => 'pending',
            ], $extraData);

            try {
                $task = $this->taskService->create($taskData);
                $imported++;

                // If no category was found/set and AI categorize is enabled, queue for batch
                if ($categoryId === null && $aiCategorize) {
                    $pendingAiTasks[] = [
                        'task' => $task,
                        'title' => $parsed['title'],
                    ];
                }
            } catch (\Throwable $e) {
                $errors[] = "Line " . ($lineNumber + 1) . ": " . $e->getMessage();
                $skipped++;
            }
        }

        // Batch AI categorization for tasks without categories
        if (!empty($pendingAiTasks) && $aiCategorize) {
            try {
                $taskTitles = array_map(fn($item) => [
                    'title' => $item['title'],
                    'description' => '',
                ], $pendingAiTasks);

                $categories = $this->aiCategoryService->identifyCategoriesBatch($userId, $taskTitles);

                foreach ($pendingAiTasks as $index => $item) {
                    if (isset($categories[$index]) && $categories[$index] !== null) {
                        $item['task']->update(['category_id' => $categories[$index]->id]);
                    }
                }
            } catch (\Throwable $e) {
                // AI categorization failure is non-fatal -- tasks are already created
                $errors[] = "AI categorization failed: " . $e->getMessage();
            }
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }

    /**
     * Parse a single line from the TXT file.
     *
     * Rules:
     * 1. Trim whitespace
     * 2. Skip empty lines
     * 3. Strip list prefixes: "- ", "* ", "[ ] ", "[x] "
     * 4. Extract @categoryname tag -> category_hint
     * 5. Extract #priority tag (low|medium|high|urgent) -> priority
     * 6. Remaining text = title
     *
     * @return array{title: string, category_hint: ?string, priority: ?string}|null
     */
    public function parseLine(string $line): ?array
    {
        $line = trim($line);

        // Skip empty lines
        if ($line === '') {
            return null;
        }

        // Strip common list prefixes
        $line = preg_replace('/^\s*[-*]\s+/', '', $line);         // "- " or "* "
        $line = preg_replace('/^\s*\[[xX ]\]\s+/', '', $line);    // "[ ] " or "[x] " or "[X] "

        $line = trim($line);

        if ($line === '') {
            return null;
        }

        // Extract @category tag (case-insensitive, supports multi-word with hyphens/underscores)
        $categoryHint = null;
        if (preg_match('/@([a-zA-Z][a-zA-Z0-9_-]*)/', $line, $matches)) {
            $categoryHint = str_replace(['-', '_'], ' ', $matches[1]);
            $line = trim(str_replace($matches[0], '', $line));
        }

        // Extract #priority tag
        $priority = null;
        if (preg_match('/#(low|medium|high|urgent)\b/i', $line, $matches)) {
            $priority = strtolower($matches[1]);
            $line = trim(str_replace($matches[0], '', $line));
        }

        $title = trim($line);

        // After stripping tags, if nothing remains, skip
        if ($title === '') {
            return null;
        }

        return [
            'title' => $title,
            'category_hint' => $categoryHint,
            'priority' => $priority,
        ];
    }

    /**
     * Generate a random hex color for new categories created during import.
     */
    private function generateCategoryColor(): string
    {
        $colors = [
            '#7c3aed', '#3b82f6', '#22c55e', '#f59e0b', '#ef4444',
            '#ec4899', '#8b5cf6', '#06b6d4', '#14b8a6', '#f97316',
        ];

        return $colors[array_rand($colors)];
    }
}
```

#### `app/Services/TaskPdfService.php`

```php
<?php

namespace App\Services;

use App\Models\Task\Task;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPDF;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class TaskPdfService
{
    /**
     * Generate a PDF of tasks for a given period.
     *
     * @param int    $userId   The authenticated user's ID
     * @param string $period   One of: 'day', 'week', 'month'
     * @param string $date     Reference date in Y-m-d format
     * @param string $taskType One of: 'all', 'personal', 'project'
     *
     * @return DomPDF
     */
    public function generatePdf(
        int $userId,
        string $period,
        string $date,
        string $taskType = 'all'
    ): DomPDF {
        $dateRange = $this->getDateRange($period, $date);

        $personalTasks = collect();
        $projectTasks = collect();

        if ($taskType === 'all' || $taskType === 'personal') {
            $personalTasks = $this->getPersonalTasksForPeriod($userId, $dateRange['start'], $dateRange['end']);
        }

        // Project tasks placeholder -- will be implemented when ProjectTask model exists
        // if ($taskType === 'all' || $taskType === 'project') {
        //     $projectTasks = $this->getProjectTasksForPeriod($userId, $dateRange['start'], $dateRange['end']);
        // }

        $data = [
            'personalTasks' => $personalTasks,
            'projectTasks' => $projectTasks,
            'period' => $period,
            'dateRange' => $dateRange,
            'taskType' => $taskType,
            'generatedAt' => now()->format('M j, Y g:i A'),
        ];

        return Pdf::loadView('tasks.pdf.task-list', $data)
            ->setPaper('a4')
            ->setOption('isRemoteEnabled', true);
    }

    /**
     * Calculate start/end dates and a human-readable label for the given period.
     *
     * @return array{start: Carbon, end: Carbon, label: string}
     */
    public function getDateRange(string $period, string $date): array
    {
        $reference = Carbon::parse($date);

        return match ($period) {
            'day' => [
                'start' => $reference->copy()->startOfDay(),
                'end' => $reference->copy()->endOfDay(),
                'label' => $reference->format('l, F j, Y'),
            ],
            'week' => [
                'start' => $reference->copy()->startOfWeek(),
                'end' => $reference->copy()->endOfWeek(),
                'label' => $reference->copy()->startOfWeek()->format('M j') . ' - ' . $reference->copy()->endOfWeek()->format('M j, Y'),
            ],
            'month' => [
                'start' => $reference->copy()->startOfMonth(),
                'end' => $reference->copy()->endOfMonth(),
                'label' => $reference->format('F Y'),
            ],
            default => throw new \InvalidArgumentException("Invalid period: {$period}. Must be 'day', 'week', or 'month'."),
        };
    }

    /**
     * Get personal tasks grouped by date for the given period.
     *
     * @return Collection  Keyed by date string (Y-m-d), each value is a Collection of Task models
     */
    public function getPersonalTasksForPeriod(int $userId, Carbon $start, Carbon $end): Collection
    {
        return Task::query()
            ->forUser($userId)
            ->whereBetween('due_date', [$start->toDateString(), $end->toDateString()])
            ->with('category')
            ->byPriority()
            ->ordered()
            ->orderBy('created_at')
            ->get()
            ->groupBy(fn (Task $task) => $task->due_date->format('Y-m-d'));
    }

    /**
     * Get project tasks grouped by date for the given period.
     * Placeholder -- returns empty collection until ProjectTask model is created.
     *
     * @return Collection
     */
    public function getProjectTasksForPeriod(int $userId, Carbon $start, Carbon $end): Collection
    {
        // TODO: Implement when ProjectTask model exists
        return collect();
    }

    /**
     * Generate the PDF and return a download response.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function download(int $userId, string $period, string $date, string $taskType = 'all'): \Symfony\Component\HttpFoundation\Response
    {
        $pdf = $this->generatePdf($userId, $period, $date, $taskType);
        $dateRange = $this->getDateRange($period, $date);

        $filename = 'Tasks_' . str_replace([' ', ',', '-'], '_', $dateRange['label']) . '.pdf';

        return $pdf->download($filename);
    }
}
```

#### `app/Services/AiCategoryIdentificationService.php`

```php
<?php

namespace App\Services;

use App\Models\ApiKey;
use App\Models\Task\TaskCategory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiCategoryIdentificationService
{
    /**
     * Identify the best category for a single task using AI.
     *
     * @param int    $userId      The authenticated user's ID (to fetch their API key)
     * @param string $taskTitle   The task's title text
     * @param string $description Optional task description for better context
     *
     * @return ?TaskCategory  The matched or newly created TaskCategory, or null on failure
     */
    public function identifyCategory(int $userId, string $taskTitle, string $description = ''): ?TaskCategory
    {
        $result = $this->identifyCategoriesBatch($userId, [
            ['title' => $taskTitle, 'description' => $description],
        ]);

        return $result[0] ?? null;
    }

    /**
     * Identify categories for multiple tasks in a single AI call (batch).
     *
     * @param int   $userId  The authenticated user's ID
     * @param array $tasks   Array of ['title' => string, 'description' => string]
     *
     * @return array  Array of ?TaskCategory (same order as input), null for any task that could not be categorized
     */
    public function identifyCategoriesBatch(int $userId, array $tasks): array
    {
        if (empty($tasks)) {
            return [];
        }

        $provider = $this->getConfiguredProvider($userId);

        if ($provider === null) {
            Log::info('AI Category Identification: No API key configured for user ' . $userId);
            return array_fill(0, count($tasks), null);
        }

        $existingCategories = TaskCategory::query()->ordered()->pluck('name')->toArray();
        $prompt = $this->buildPrompt($tasks, $existingCategories);

        $apiKey = ApiKey::query()
            ->forUser($userId)
            ->forProvider($provider === 'claude' ? ApiKey::PROVIDER_CLAUDE : ApiKey::PROVIDER_OPENAI)
            ->connected()
            ->first();

        try {
            $responseText = $provider === 'claude'
                ? $this->callClaudeApi($apiKey->key_value, $prompt)
                : $this->callOpenAiApi($apiKey->key_value, $prompt);

            $categoryNames = $this->parseAiResponse($responseText, count($tasks));

            $result = [];
            foreach ($categoryNames as $name) {
                if ($name === null || trim($name) === '') {
                    $result[] = null;
                } else {
                    $result[] = $this->findOrCreateCategory(trim($name));
                }
            }

            return $result;
        } catch (\Throwable $e) {
            Log::warning('AI Category Identification failed: ' . $e->getMessage());
            return array_fill(0, count($tasks), null);
        }
    }

    /**
     * Find an existing category by name (case-insensitive) or create a new one.
     */
    public function findOrCreateCategory(string $categoryName): TaskCategory
    {
        $category = TaskCategory::query()
            ->whereRaw('LOWER(name) = ?', [strtolower($categoryName)])
            ->first();

        if ($category) {
            return $category;
        }

        return TaskCategory::create([
            'name' => ucfirst($categoryName),
            'color' => $this->generateCategoryColor(),
            'sort_order' => (TaskCategory::max('sort_order') ?? 0) + 1,
        ]);
    }

    /**
     * Check which AI provider the user has configured.
     * Prefers Claude, falls back to OpenAI. Returns null if neither is available.
     */
    public function getConfiguredProvider(int $userId): ?string
    {
        $claudeKey = ApiKey::query()
            ->forUser($userId)
            ->forProvider(ApiKey::PROVIDER_CLAUDE)
            ->connected()
            ->first();

        if ($claudeKey) {
            return 'claude';
        }

        $openaiKey = ApiKey::query()
            ->forUser($userId)
            ->forProvider(ApiKey::PROVIDER_OPENAI)
            ->connected()
            ->first();

        if ($openaiKey) {
            return 'openai';
        }

        return null;
    }

    /**
     * Build the AI prompt for category identification.
     *
     * @param array $tasks              Array of ['title' => string, 'description' => string]
     * @param array $existingCategories Array of existing category name strings
     */
    public function buildPrompt(array $tasks, array $existingCategories): string
    {
        $taskList = '';
        foreach ($tasks as $index => $task) {
            $desc = !empty($task['description']) ? " | Description: {$task['description']}" : '';
            $taskList .= ($index + 1) . ". Title: {$task['title']}{$desc}\n";
        }

        $categoryList = !empty($existingCategories)
            ? "Existing categories (prefer these when appropriate):\n" . implode(', ', $existingCategories) . "\n\n"
            : "No existing categories yet. Create appropriate category names.\n\n";

        $count = count($tasks);

        return <<<PROMPT
You are a task organization assistant. Your job is to assign the best category to each task.

{$categoryList}Here are the tasks to categorize:
{$taskList}
Rules:
- Use an existing category if it fits well (match by meaning, not just exact name)
- If no existing category fits, suggest a new short category name (1-3 words, capitalize first letter)
- Category names should be general enough to group related tasks (e.g., "Development", "Design", "Health", "Finance", "Meetings", "Personal", "Shopping", "Errands")
- Do NOT create overly specific categories (e.g., "Buy Groceries" is too specific -- use "Shopping" instead)
- Every task MUST get a category -- never leave one blank

Respond with ONLY a valid JSON array of exactly {$count} strings, one category name per task, in the same order as the input. No markdown, no code fences, no explanation.

Example response for 3 tasks:
["Development", "Health", "Finance"]
PROMPT;
    }

    public function callClaudeApi(string $apiKey, string $prompt): string
    {
        $response = Http::timeout(30)
            ->withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => 'claude-sonnet-4-20250514',
                'max_tokens' => 1024,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Failed to reach the AI service. Please try again.');
        }

        $body = $response->json();

        return $body['content'][0]['text'] ?? '';
    }

    public function callOpenAiApi(string $apiKey, string $prompt): string
    {
        $response = Http::timeout(30)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'max_tokens' => 1024,
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Failed to reach the AI service. Please try again.');
        }

        $body = $response->json();

        return $body['choices'][0]['message']['content'] ?? '';
    }

    /**
     * Parse the AI response into an array of category name strings.
     *
     * @param string $responseText  Raw AI response text
     * @param int    $expectedCount How many category names we expect
     *
     * @return array  Array of ?string category names
     */
    public function parseAiResponse(string $responseText, int $expectedCount): array
    {
        // Strip markdown code fences if present
        $responseText = preg_replace('/^```(?:json)?\s*/m', '', $responseText);
        $responseText = preg_replace('/```\s*$/m', '', $responseText);
        $responseText = trim($responseText);

        $parsed = json_decode($responseText, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($parsed)) {
            throw new \RuntimeException('AI returned an unexpected response format.');
        }

        // Ensure we have the right number of results
        $result = [];
        for ($i = 0; $i < $expectedCount; $i++) {
            $result[] = isset($parsed[$i]) && is_string($parsed[$i]) ? $parsed[$i] : null;
        }

        return $result;
    }

    private function generateCategoryColor(): string
    {
        $colors = [
            '#7c3aed', '#3b82f6', '#22c55e', '#f59e0b', '#ef4444',
            '#ec4899', '#8b5cf6', '#06b6d4', '#14b8a6', '#f97316',
        ];

        return $colors[array_rand($colors)];
    }
}
```

### 3.2 New Controller

#### `app/Http/Controllers/TaskPdfController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Services\TaskPdfService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TaskPdfController extends Controller
{
    public function download(Request $request, TaskPdfService $service): Response
    {
        $request->validate([
            'period' => 'required|in:day,week,month',
            'date' => 'required|date_format:Y-m-d',
            'type' => 'nullable|in:all,personal,project',
        ]);

        return $service->download(
            userId: auth()->id(),
            period: $request->input('period'),
            date: $request->input('date'),
            taskType: $request->input('type', 'all')
        );
    }
}
```

### 3.3 New Route File

#### `routes/admin/tasks/pdf-download.php`

```php
<?php

use App\Http\Controllers\TaskPdfController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/admin/tasks/pdf/download', [TaskPdfController::class, 'download'])
        ->name('admin.tasks.pdf.download');
});
```

### 3.4 New PDF Template

#### `resources/views/tasks/pdf/task-list.blade.php`

Full HTML document with inline CSS. Light theme (white background, dark text) for printability. Structure:

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Task List — {{ $dateRange['label'] }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #2d2d2d;
            background: #ffffff;
        }
        .container { width: 100%; padding: 0; }

        /* Header */
        .header {
            background-color: #1a1a2e;
            color: #ffffff;
            padding: 20px 30px;
        }
        .header h1 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }
        .header .subtitle {
            font-size: 11px;
            color: #a5b4fc;
            margin-bottom: 2px;
        }
        .header .meta {
            font-size: 9px;
            color: #d1d5db;
        }

        /* Content */
        .content { padding: 20px 30px; }

        /* Date group */
        .date-group { margin-bottom: 16px; }
        .date-heading {
            font-size: 12px;
            font-weight: 700;
            color: #4f46e5;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }

        /* Task table */
        .task-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .task-table th {
            background-color: #f3f4f6;
            text-align: left;
            padding: 6px 10px;
            font-size: 9px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e5e7eb;
        }
        .task-table td {
            padding: 6px 10px;
            font-size: 9.5px;
            color: #374151;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: top;
        }
        .task-table tr.completed td {
            color: #9ca3af;
            text-decoration: line-through;
        }

        /* Priority badges (inline) */
        .priority-urgent { color: #dc2626; font-weight: 700; }
        .priority-high { color: #d97706; font-weight: 600; }
        .priority-medium { color: #2563eb; }
        .priority-low { color: #6b7280; }

        /* Status */
        .status-completed { color: #16a34a; font-weight: 600; }
        .status-pending { color: #d97706; }
        .status-in-progress { color: #2563eb; }

        /* Category */
        .category {
            display: inline-block;
            padding: 1px 6px;
            background-color: #ede9fe;
            color: #6d28d9;
            border-radius: 3px;
            font-size: 8.5px;
            font-weight: 600;
        }

        /* Summary section */
        .summary {
            margin-top: 20px;
            padding: 12px 16px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
        }
        .summary h3 {
            font-size: 11px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 6px;
        }
        .summary-row {
            font-size: 9.5px;
            color: #4b5563;
            margin-bottom: 3px;
        }
        .summary-value {
            font-weight: 700;
            color: #1f2937;
        }

        /* Footer */
        .footer {
            margin-top: 24px;
            padding-top: 8px;
            border-top: 1px solid #e5e7eb;
            font-size: 8px;
            color: #9ca3af;
            text-align: center;
        }

        /* Section type header */
        .section-type {
            font-size: 13px;
            font-weight: 700;
            color: #1f2937;
            margin-top: 16px;
            margin-bottom: 10px;
            padding-bottom: 4px;
            border-bottom: 1px solid #e5e7eb;
        }

        /* Empty state */
        .empty-state {
            padding: 20px;
            text-align: center;
            color: #9ca3af;
            font-size: 10px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Header --}}
        <div class="header">
            <h1>Task List</h1>
            <div class="subtitle">{{ $dateRange['label'] }}</div>
            <div class="meta">Generated on {{ $generatedAt }} | Period: {{ ucfirst($period) }}{{ $taskType !== 'all' ? ' | Type: ' . ucfirst($taskType) : '' }}</div>
        </div>

        <div class="content">
            {{-- Personal Tasks --}}
            @if($taskType === 'all' || $taskType === 'personal')
                @if($taskType === 'all')
                    <div class="section-type">Personal Tasks</div>
                @endif

                @if($personalTasks->isNotEmpty())
                    @foreach($personalTasks as $date => $dateTasks)
                        <div class="date-group">
                            <div class="date-heading">{{ \Carbon\Carbon::parse($date)->format('l, M j, Y') }}</div>
                            <table class="task-table">
                                <thead>
                                    <tr>
                                        <th style="width: 5%;">#</th>
                                        <th style="width: 40%;">Task</th>
                                        <th style="width: 15%;">Priority</th>
                                        <th style="width: 15%;">Status</th>
                                        <th style="width: 25%;">Category</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dateTasks as $index => $task)
                                        <tr class="{{ $task->status === 'completed' ? 'completed' : '' }}">
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $task->title }}</td>
                                            <td>
                                                <span class="priority-{{ $task->priority }}">{{ ucfirst($task->priority) }}</span>
                                            </td>
                                            <td>
                                                <span class="status-{{ $task->status }}">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                                            </td>
                                            <td>
                                                @if($task->category)
                                                    <span class="category">{{ $task->category->name }}</span>
                                                @else
                                                    <span style="color: #9ca3af;">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                @else
                    <div class="empty-state">No personal tasks found for this period.</div>
                @endif
            @endif

            {{-- Project Tasks --}}
            @if($taskType === 'all' || $taskType === 'project')
                @if($taskType === 'all')
                    <div class="section-type">Project Tasks</div>
                @endif

                @if($projectTasks->isNotEmpty())
                    {{-- Same table structure as personal tasks, rendered when ProjectTask model exists --}}
                @else
                    <div class="empty-state">No project tasks found for this period.</div>
                @endif
            @endif

            {{-- Summary --}}
            @php
                $allTasks = $personalTasks->flatten(1)->merge($projectTasks->flatten(1));
                $totalCount = $allTasks->count();
                $completedCount = $allTasks->where('status', 'completed')->count();
                $pendingCount = $allTasks->where('status', '!=', 'completed')->count();
            @endphp
            @if($totalCount > 0)
                <div class="summary">
                    <h3>Summary</h3>
                    <div class="summary-row">Total tasks: <span class="summary-value">{{ $totalCount }}</span></div>
                    <div class="summary-row">Completed: <span class="summary-value" style="color: #16a34a;">{{ $completedCount }}</span></div>
                    <div class="summary-row">Pending: <span class="summary-value" style="color: #d97706;">{{ $pendingCount }}</span></div>
                    @if($totalCount > 0)
                        <div class="summary-row">Completion rate: <span class="summary-value">{{ round(($completedCount / $totalCount) * 100) }}%</span></div>
                    @endif
                </div>
            @endif

            <div class="footer">
                Generated from Task Manager | {{ $generatedAt }}
            </div>
        </div>
    </div>
</body>
</html>
```

### 3.5 Modified TaskService

Add an `$aiCategorize` parameter to the `create()` method:

```php
// BEFORE:
public function create(array $data): Task
{
    return Task::create($data);
}

// AFTER:
public function create(array $data, bool $aiCategorize = false, ?int $aiUserId = null): Task
{
    $task = Task::create($data);

    // If AI categorization is requested and no category was provided
    if ($aiCategorize && empty($data['category_id']) && $aiUserId !== null) {
        try {
            $aiService = app(AiCategoryIdentificationService::class);
            $category = $aiService->identifyCategory($aiUserId, $data['title'], $data['description'] ?? '');

            if ($category !== null) {
                $task->update(['category_id' => $category->id]);
                $task->refresh();
            }
        } catch (\Throwable $e) {
            // AI failure is non-fatal -- task is already created without category
            \Illuminate\Support\Facades\Log::warning('AI categorization failed during task create: ' . $e->getMessage());
        }
    }

    return $task;
}
```

### 3.6 Modified DailyPlannerIndex Component

New properties to add:

```php
use Livewire\WithFileUploads;

// Add trait
class DailyPlannerIndex extends Component
{
    use WithFileUploads;

    // ... existing properties ...

    // --- TXT Import Modal ---
    public bool $showImportModal = false;
    public $taskFile = null;  // TemporaryUploadedFile (Livewire file upload)
    public bool $aiCategorizeImport = false;
    public string $importDefaultPriority = 'medium';

    // --- AI Categorize Toggle (for single task add) ---
    public bool $aiCategorizeNewTask = false;
}
```

New/modified methods:

```php
/**
 * Open the import modal.
 */
public function openImportModal(): void
{
    $this->showImportModal = true;
    $this->reset('taskFile', 'aiCategorizeImport', 'importDefaultPriority');
    $this->importDefaultPriority = 'medium';
}

/**
 * Close the import modal.
 */
public function closeImportModal(): void
{
    $this->showImportModal = false;
    $this->reset('taskFile', 'aiCategorizeImport', 'importDefaultPriority');
    $this->importDefaultPriority = 'medium';
}

/**
 * Import tasks from the uploaded TXT file.
 */
public function importTasks(TaskImportService $importService): void
{
    $this->validate([
        'taskFile' => 'required|file|mimes:txt|max:1024', // max 1MB
        'importDefaultPriority' => 'required|in:low,medium,high,urgent',
    ]);

    $fileContents = $this->taskFile->get();

    $result = $importService->importFromTxt(
        userId: auth()->id(),
        fileContents: $fileContents,
        dueDate: $this->selectedDate,
        defaultPriority: $this->importDefaultPriority,
        aiCategorize: $this->aiCategorizeImport,
    );

    $this->closeImportModal();

    if ($result['imported'] > 0) {
        $message = "{$result['imported']} task(s) imported successfully.";
        if ($result['skipped'] > 0) {
            $message .= " {$result['skipped']} line(s) skipped.";
        }
        session()->flash('success', $message);
    } else {
        session()->flash('error', 'No tasks could be imported. Check your file format.');
    }
}

// Modify existing addTask to support AI categorization:
public function addTask(TaskService $service): void
{
    $this->validate([
        'newTaskTitle' => 'required|string|max:255',
        'newTaskPriority' => 'required|in:low,medium,high,urgent',
        'newTaskCategoryId' => 'nullable|exists:task_categories,id',
    ]);

    $service->create([
        'user_id' => auth()->id(),
        'title' => $this->newTaskTitle,
        'priority' => $this->newTaskPriority,
        'category_id' => $this->newTaskCategoryId ?: null,
        'due_date' => $this->selectedDate,
        'status' => 'pending',
    ], aiCategorize: $this->aiCategorizeNewTask, aiUserId: auth()->id());

    $this->reset('newTaskTitle', 'newTaskPriority', 'newTaskCategoryId', 'aiCategorizeNewTask');
    $this->newTaskPriority = 'medium';

    session()->flash('success', 'Task added successfully.');
}
```

### 3.7 Modified Daily Planner View

Changes to `resources/views/livewire/admin/tasks/daily-planner/index.blade.php`:

#### A. Page Header -- Add Import and PDF buttons next to "Move Incomplete to Tomorrow"

Insert after the existing `moveIncompleteToTomorrow` button, inside the page header `<div class="flex items-center justify-between mb-8">`:

```blade
<div class="flex items-center gap-2">
    {{-- Import TXT Button --}}
    <button wire:click="openImportModal"
            class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 text-gray-300 text-sm font-medium rounded-lg px-4 py-2.5 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
        Import
    </button>

    {{-- PDF Download Dropdown --}}
    <div x-data="{ open: false }" class="relative">
        <button @click="open = !open"
                class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 text-gray-300 text-sm font-medium rounded-lg px-4 py-2.5 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            PDF
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div x-show="open" @click.outside="open = false"
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="absolute right-0 mt-2 w-48 bg-dark-800 border border-dark-700 rounded-lg shadow-xl z-50 py-1">
            <a href="{{ route('admin.tasks.pdf.download', ['period' => 'day', 'date' => $selectedDate]) }}"
               class="block px-4 py-2 text-sm text-gray-300 hover:bg-dark-700 hover:text-white transition-colors">
                Download Day
            </a>
            <a href="{{ route('admin.tasks.pdf.download', ['period' => 'week', 'date' => $selectedDate]) }}"
               class="block px-4 py-2 text-sm text-gray-300 hover:bg-dark-700 hover:text-white transition-colors">
                Download Week
            </a>
            <a href="{{ route('admin.tasks.pdf.download', ['period' => 'month', 'date' => $selectedDate]) }}"
               class="block px-4 py-2 text-sm text-gray-300 hover:bg-dark-700 hover:text-white transition-colors">
                Download Month
            </a>
        </div>
    </div>

    {{-- Existing Move Incomplete button --}}
    @if($stats['total'] > 0 && $stats['completed'] < $stats['total'])
        <button wire:click="moveIncompleteToTomorrow" ...>
            ...
        </button>
    @endif
</div>
```

#### B. AI Categorize Toggle -- Add to the inline add-task form

Insert between the category select and the Add button in section 6:

```blade
{{-- AI Auto-Categorize Toggle --}}
<label class="inline-flex items-center gap-2 cursor-pointer shrink-0" title="Use AI to auto-assign a category">
    <input type="checkbox" wire:model="aiCategorizeNewTask" class="sr-only peer">
    <div class="w-9 h-5 bg-dark-600 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary relative"></div>
    <span class="text-xs text-gray-400 whitespace-nowrap">
        <svg class="w-3.5 h-3.5 inline -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        AI
    </span>
</label>
```

#### C. Import Modal -- Add at the bottom of the view, before closing `</div>`

```blade
{{-- IMPORT MODAL --}}
@if($showImportModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-data x-init="document.body.classList.add('overflow-hidden')"
         x-destroy="document.body.classList.remove('overflow-hidden')">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-dark-950/80" wire:click="closeImportModal"></div>

        {{-- Modal --}}
        <div class="relative bg-dark-800 border border-dark-700 rounded-xl w-full max-w-lg shadow-2xl z-10"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-dark-700">
                <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider">Import Tasks from TXT</h2>
                <button wire:click="closeImportModal" class="text-gray-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="p-6 space-y-5">
                {{-- File Upload --}}
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        TXT File <span class="text-red-400">*</span>
                    </label>
                    <input type="file" wire:model="taskFile" accept=".txt"
                           class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary file:text-white hover:file:bg-primary-hover file:cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                    @error('taskFile')
                        <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                    <p class="mt-1.5 text-xs text-gray-500">Max 1MB. One task per line.</p>
                </div>

                {{-- Default Priority --}}
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Default Priority</label>
                    <select wire:model="importDefaultPriority"
                            class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                    <p class="mt-1.5 text-xs text-gray-500">Applied to lines without a #priority tag.</p>
                </div>

                {{-- AI Categorize Toggle --}}
                <div class="flex items-center justify-between p-4 bg-dark-700 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-300">AI Auto-Categorize</p>
                        <p class="text-xs text-gray-500 mt-0.5">Use AI to assign categories to tasks without @category tags</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="aiCategorizeImport" class="sr-only peer">
                        <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>

                {{-- Format Guide --}}
                <div class="p-4 bg-dark-700/50 rounded-lg border border-dark-600">
                    <p class="text-xs font-mono font-medium text-gray-400 uppercase tracking-wider mb-2">File Format Guide</p>
                    <div class="text-xs text-gray-500 space-y-1 font-mono">
                        <p>- Buy groceries @shopping #high</p>
                        <p>* Review pull request @development</p>
                        <p>[ ] Call dentist @health #urgent</p>
                        <p>Write blog post</p>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        <span class="text-gray-400">@tag</span> = category &nbsp;|&nbsp;
                        <span class="text-gray-400">#priority</span> = low/medium/high/urgent
                    </p>
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-dark-700">
                <button wire:click="closeImportModal"
                        class="bg-dark-700 hover:bg-dark-600 text-gray-300 text-sm font-medium rounded-lg px-5 py-2.5 transition-colors">
                    Cancel
                </button>
                <button wire:click="importTasks"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20 disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg wire:loading wire:target="importTasks" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <svg wire:loading.remove wire:target="importTasks" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Import Tasks
                </button>
            </div>
        </div>
    </div>
@endif
```

---

## 4. MIGRATION PATH

No database migrations are required for any of these three features.

- TXT Import creates Task records using existing `tasks` table schema and TaskCategory records using existing `task_categories` table schema.
- PDF Download reads existing Task records -- no schema changes.
- AI Category Identification creates/reads TaskCategory records using existing schema, and updates the existing `category_id` column on tasks.

---

## 5. FILES TO CREATE

| # | File Path | Description |
|---|---|---|
| 1 | `app/Services/TaskImportService.php` | TXT file parsing and bulk task creation service. Depends on TaskService and AiCategoryIdentificationService. Contains `importFromTxt()` and `parseLine()` methods. See section 3.1 for full implementation. |
| 2 | `app/Services/TaskPdfService.php` | PDF generation service for task lists. Uses DomPDF. Contains `generatePdf()`, `getDateRange()`, `getPersonalTasksForPeriod()`, `getProjectTasksForPeriod()` (placeholder), and `download()`. See section 3.1 for full implementation. |
| 3 | `app/Services/AiCategoryIdentificationService.php` | AI-powered category identification service. Follows same API call pattern as AiTaskPrioritizationService. Contains `identifyCategory()`, `identifyCategoriesBatch()`, `findOrCreateCategory()`, `buildPrompt()`, `callClaudeApi()`, `callOpenAiApi()`, `parseAiResponse()`. See section 3.1 for full implementation. |
| 4 | `app/Http/Controllers/TaskPdfController.php` | Thin controller for PDF download route. Validates period/date/type params, delegates to TaskPdfService. See section 3.2 for full implementation. |
| 5 | `routes/admin/tasks/pdf-download.php` | Route file: `GET /admin/tasks/pdf/download` with `auth` middleware, named `admin.tasks.pdf.download`. Auto-discovered by glob in bootstrap/app.php. See section 3.3. |
| 6 | `resources/views/tasks/pdf/task-list.blade.php` | DomPDF-compatible HTML template for task list PDF. Light theme with inline CSS. Contains header, date-grouped task tables, priority/status badges, category labels, summary stats, and footer. See section 3.4 for full template. |

---

## 6. FILES TO MODIFY

### 6.1 `app/Services/TaskService.php`

**What changes**: Add optional `$aiCategorize` and `$aiUserId` parameters to `create()` method.

**Before**:
```php
public function create(array $data): Task
{
    return Task::create($data);
}
```

**After**:
```php
public function create(array $data, bool $aiCategorize = false, ?int $aiUserId = null): Task
{
    $task = Task::create($data);

    if ($aiCategorize && empty($data['category_id']) && $aiUserId !== null) {
        try {
            $aiService = app(AiCategoryIdentificationService::class);
            $category = $aiService->identifyCategory($aiUserId, $data['title'], $data['description'] ?? '');

            if ($category !== null) {
                $task->update(['category_id' => $category->id]);
                $task->refresh();
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('AI categorization failed during task create: ' . $e->getMessage());
        }
    }

    return $task;
}
```

**Impact**: Backward compatible. All existing callers pass no extra args, so behavior is unchanged.

### 6.2 `app/Livewire/Admin/Tasks/DailyPlanner/DailyPlannerIndex.php`

**What changes**:
1. Add `use Livewire\WithFileUploads;` import and the trait
2. Add `use App\Services\TaskImportService;` import
3. Add 5 new public properties: `$showImportModal`, `$taskFile`, `$aiCategorizeImport`, `$importDefaultPriority`, `$aiCategorizeNewTask`
4. Add 3 new methods: `openImportModal()`, `closeImportModal()`, `importTasks()`
5. Modify `addTask()` to pass AI categorize params to service

See section 3.6 for all details.

### 6.3 `resources/views/livewire/admin/tasks/daily-planner/index.blade.php`

**What changes**:
1. **Page header area** (section 2): Wrap the "Move Incomplete" button and add "Import" button and "PDF" dropdown before it. The right side of the header becomes a flex container with 3 buttons.
2. **Add-task form** (section 6): Insert AI auto-categorize toggle (small toggle + "AI" label) between the category select and the Add button.
3. **Bottom of view**: Add the import modal markup (rendered conditionally with `@if($showImportModal)`).

See section 3.7 for all view markup details.

---

## 7. CROSS-MODULE IMPACT

### 7.1 TaskImportService

Used by:
- **DailyPlannerIndex** (personal tasks) -- passes no `$extraData`
- **ProjectBoardIndex** (project tasks, created by separate spec) -- will pass `['project_id' => $projectId]` via the `$extraData` parameter

The `$extraData` array merge in `importFromTxt()` is specifically designed for this cross-module use case. ProjectBoardIndex will call:

```php
$importService->importFromTxt(
    userId: auth()->id(),
    fileContents: $contents,
    dueDate: $date,
    defaultPriority: 'medium',
    aiCategorize: true,
    extraData: ['project_id' => $this->projectId],
);
```

### 7.2 AiCategoryIdentificationService

Used by:
- **TaskImportService** (batch categorization during import) -- both personal and project imports
- **TaskService::create()** (single-task categorization) -- both DailyPlannerIndex and ProjectBoardIndex
- Potentially called directly by ProjectBoardIndex for bulk operations

### 7.3 TaskPdfService

Used by:
- **TaskPdfController** (GET route, triggered from DailyPlannerIndex view)
- ProjectBoardIndex will use the same route, potentially with `?type=project` query param

The `getProjectTasksForPeriod()` method is currently a placeholder returning an empty collection. It will be implemented when the ProjectTask model is created by the separate project-board spec.

### 7.4 PDF Template

The template (`resources/views/tasks/pdf/task-list.blade.php`) already has sections for both personal and project tasks. The project tasks section will render once `$projectTasks` is populated by the service.

---

## 8. VALIDATION RULES

### 8.1 TXT File Upload

| Field | Rule | Error Message |
|---|---|---|
| `taskFile` | `required\|file\|mimes:txt\|max:1024` | File is required, must be .txt, max 1MB |
| `importDefaultPriority` | `required\|in:low,medium,high,urgent` | Must be a valid priority level |

### 8.2 PDF Download

| Field | Rule | Error Message |
|---|---|---|
| `period` | `required\|in:day,week,month` | Must be day, week, or month |
| `date` | `required\|date_format:Y-m-d` | Must be a valid date in Y-m-d format |
| `type` | `nullable\|in:all,personal,project` | Must be all, personal, or project |

### 8.3 Add Task (modified)

| Field | Rule | Notes |
|---|---|---|
| `newTaskTitle` | `required\|string\|max:255` | Unchanged |
| `newTaskPriority` | `required\|in:low,medium,high,urgent` | Unchanged |
| `newTaskCategoryId` | `nullable\|exists:task_categories,id` | Unchanged |
| `aiCategorizeNewTask` | (bool property, no validation needed) | Toggle state, not user-submitted text |

### 8.4 Parse Line Validation (within TaskImportService)

- Empty/whitespace-only lines: skipped (return null)
- Lines that become empty after stripping prefixes and tags: skipped (return null)
- `@category` tag: must match pattern `[a-zA-Z][a-zA-Z0-9_-]*` (letters, digits, hyphens, underscores)
- `#priority` tag: must be one of `low`, `medium`, `high`, `urgent` (case-insensitive)
- Title max length: not enforced at parse level (enforced by database column `varchar(255)`)

---

## 9. EDGE CASES & RISKS

### 9.1 TXT File Upload

| Edge Case | Handling |
|---|---|
| Empty file | All lines skipped. Result: `{imported: 0, skipped: 0, errors: []}`. Flash: "No tasks could be imported." |
| File with only empty lines | Same as empty file |
| File with Windows line endings (`\r\n`) | `explode("\n", ...)` handles this -- `\r` is trimmed by `trim()` |
| File with mixed encodings | DomPDF font handles UTF-8. Non-UTF8 chars may cause title issues but won't crash |
| Very large file (approaching 1MB limit) | Could create hundreds of tasks. Each is a separate DB insert. Consider: if >500 lines, this could be slow. Acceptable for personal use. |
| Duplicate task titles | Allowed. No uniqueness constraint exists on task titles. |
| Line with only `@category #priority` (no title text) | Stripped to empty string, returns null, skipped |
| Multiple `@category` tags on one line | Only first match is captured by regex. Remaining `@tag` text stays in title. |
| Multiple `#priority` tags on one line | Only first match is captured. Remaining `#tag` text stays in title. |
| `@` or `#` in natural text (e.g., "Email john@example.com") | The regex `/@([a-zA-Z][a-zA-Z0-9_-]*)/` would match `@example` as a category. This is a known limitation. Mitigation: document the format clearly in the UI. |
| AI categorization fails mid-batch | Tasks are already created without categories. Error is logged and included in result errors array. Non-fatal. |

### 9.2 PDF Download

| Edge Case | Handling |
|---|---|
| No tasks for the period | Template shows "No personal/project tasks found" empty state. PDF still generates. |
| Very large number of tasks (100+ in a month) | DomPDF handles multi-page. Table rows flow to next page. No explicit page breaks needed. |
| Date parameter in the future | Valid. Shows tasks scheduled for future dates (likely empty). |
| Date parameter far in the past | Valid. Shows historical tasks. |
| Invalid period value | Controller validation rejects with 422. |
| Unauthenticated access | Route has `auth` middleware -- redirects to login. |

### 9.3 AI Category Identification

| Edge Case | Handling |
|---|---|
| No API key configured | `getConfiguredProvider()` returns null. `identifyCategory()` returns null. `identifyCategoriesBatch()` returns array of nulls. Task created without category. |
| API key exists but is invalid/expired | HTTP call fails. Exception caught, logged. Returns null. Task created without category. |
| API returns malformed JSON | `parseAiResponse()` throws RuntimeException. Caught by caller. Returns null. |
| API returns fewer categories than tasks | `parseAiResponse()` pads with null for missing indices. |
| API returns extra categories | Ignored -- only first N are used. |
| API suggests a very long category name | `findOrCreateCategory()` creates it as-is. TaskCategory `name` column is varchar(255), so this is unlikely to overflow. |
| AI suggests an inappropriate/nonsensical category | Accepted. User can manually recategorize later. This is a trade-off of AI-assisted features. |
| Concurrent requests creating the same category | `whereRaw('LOWER(name) = ?', ...)` + create could race. Worst case: duplicate category created. Acceptable for personal use. |
| Rate limiting by AI provider | HTTP timeout (30s) or 429 error. Exception caught, logged. Returns null. |
| Batch with 50+ tasks | Single API call with all tasks in prompt. Token limit could be reached. For very large batches, consider chunking. Current implementation sends all at once -- acceptable for typical import sizes (< 100 tasks). |

### 9.4 General Risks

| Risk | Severity | Mitigation |
|---|---|---|
| AI API costs from frequent categorization | Low | Toggle is opt-in per task/import. User controls when AI is used. |
| Slow import with AI categorization enabled | Medium | Batch API call (one request for all tasks) instead of per-task calls. Still adds ~2-5s of latency. |
| Livewire file upload temp file cleanup | Low | Livewire auto-cleans temp uploads. No manual cleanup needed. |
| PDF generation memory usage | Low | DomPDF is lightweight for simple tables. Only risk is with very large task lists (1000+), which is unlikely for personal use. |

---

## 10. IMPLEMENTATION ORDER

Execute in this exact sequence. Each step should be a separate commit.

### Step 1: AiCategoryIdentificationService (no dependencies)

**Create**: `app/Services/AiCategoryIdentificationService.php`

This service has no dependencies on other new files. It only uses existing models (ApiKey, TaskCategory) and the established AI API call pattern. Test by calling `identifyCategory()` with a known API key.

### Step 2: TaskImportService (depends on Step 1)

**Create**: `app/Services/TaskImportService.php`

Depends on AiCategoryIdentificationService (injected via constructor) and TaskService (existing). Test `parseLine()` independently with various input formats first. Then test `importFromTxt()` with a sample file.

### Step 3: Modify TaskService (depends on Step 1)

**Modify**: `app/Services/TaskService.php`

Add the `$aiCategorize` and `$aiUserId` parameters to `create()`. This is backward compatible -- existing callers are unaffected. Test that existing task creation still works, then test with AI categorization enabled.

### Step 4: TaskPdfService + Controller + Route + Template (no new-file dependencies)

**Create** (in order):
1. `app/Services/TaskPdfService.php`
2. `resources/views/tasks/pdf/task-list.blade.php`
3. `app/Http/Controllers/TaskPdfController.php`
4. `routes/admin/tasks/pdf-download.php`

These files depend only on existing models and the DomPDF package. Test by hitting the route directly: `GET /admin/tasks/pdf/download?period=day&date=2026-04-03`.

### Step 5: Modify DailyPlannerIndex Component (depends on Steps 1-3)

**Modify**: `app/Livewire/Admin/Tasks/DailyPlanner/DailyPlannerIndex.php`

Add WithFileUploads trait, new properties, new methods, and modify `addTask()`. This is the integration point that connects all three features to the UI.

### Step 6: Modify Daily Planner View (depends on Step 5)

**Modify**: `resources/views/livewire/admin/tasks/daily-planner/index.blade.php`

Add Import button + PDF dropdown to page header, AI toggle to add-task form, and import modal at bottom of view. This is purely view markup -- no logic changes.

### Step 7: Test all three features end-to-end

1. Upload a TXT file with mixed formats (prefixes, @categories, #priorities, empty lines)
2. Verify tasks are created with correct categories and priorities
3. Enable AI categorize on import and verify uncategorized tasks get AI-assigned categories
4. Download PDF for day/week/month and verify output
5. Add a single task with AI categorize toggle on -- verify category is assigned
6. Test all failure paths: no API key, invalid file, empty file

---

## APPENDIX A: Sample TXT File for Testing

```
- Buy groceries @shopping #high
* Review PR for auth module @development
[ ] Call dentist @health #urgent
[x] Send invoice to client @finance
Write blog post about Laravel
Clean the house #low
- Prepare presentation @work #high
Update portfolio website @development #medium

- @personal Read 30 pages
Team standup meeting @meetings
```

Expected parse results:

| Line | Title | Category Hint | Priority |
|---|---|---|---|
| 1 | Buy groceries | shopping | high |
| 2 | Review PR for auth module | development | null (uses default) |
| 3 | Call dentist | health | urgent |
| 4 | Send invoice to client | finance | null (uses default) |
| 5 | Write blog post about Laravel | null (AI or none) | null (uses default) |
| 6 | Clean the house | null (AI or none) | low |
| 7 | Prepare presentation | work | high |
| 8 | Update portfolio website | development | medium |
| 9 | (empty line -- skipped) | -- | -- |
| 10 | Read 30 pages | personal | null (uses default) |
| 11 | Team standup meeting | meetings | null (uses default) |

## APPENDIX B: AI Prompt for Category Identification (Exact Text)

```
You are a task organization assistant. Your job is to assign the best category to each task.

Existing categories (prefer these when appropriate):
Development, Health, Shopping, Finance, Work, Meetings, Personal

Here are the tasks to categorize:
1. Title: Write blog post about Laravel
2. Title: Clean the house

Rules:
- Use an existing category if it fits well (match by meaning, not just exact name)
- If no existing category fits, suggest a new short category name (1-3 words, capitalize first letter)
- Category names should be general enough to group related tasks (e.g., "Development", "Design", "Health", "Finance", "Meetings", "Personal", "Shopping", "Errands")
- Do NOT create overly specific categories (e.g., "Buy Groceries" is too specific -- use "Shopping" instead)
- Every task MUST get a category -- never leave one blank

Respond with ONLY a valid JSON array of exactly 2 strings, one category name per task, in the same order as the input. No markdown, no code fences, no explanation.

Example response for 3 tasks:
["Development", "Health", "Finance"]
```

Expected AI response:
```json
["Development", "Personal"]
```
