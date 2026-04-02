<?php

namespace App\Services;

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
     * @param  int  $userId  The authenticated user's ID
     * @param  string  $fileContents  Raw text content of the uploaded .txt file
     * @param  string  $dueDate  Date in Y-m-d format to assign to all imported tasks
     * @param  string  $defaultPriority  Fallback priority if line has no #priority tag (default: 'medium')
     * @param  bool  $aiCategorize  Whether to use AI to categorize tasks without a @category tag
     * @param  array  $extraData  Additional fields to merge into every created task (e.g., project_id for project tasks)
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

                if (! $category) {
                    $category = TaskCategory::create([
                        'name' => ucfirst($parsed['category_hint']),
                        'color' => $this->generateCategoryColor(),
                        'sort_order' => (TaskCategory::max('sort_order') ?? 0) + 1,
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
                $errors[] = 'Line '.($lineNumber + 1).': '.$e->getMessage();
                $skipped++;
            }
        }

        // Batch AI categorization for tasks without categories
        if (! empty($pendingAiTasks) && $aiCategorize) {
            try {
                $taskTitles = array_map(fn ($item) => [
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
                $errors[] = 'AI categorization failed: '.$e->getMessage();
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
