<?php

namespace App\Services;

use App\Models\ProjectManagement\ProjectBoard;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectBoardExportService
{
    /**
     * Export board as PDF download.
     */
    public function exportPdf(int $boardId, int $userId): \Symfony\Component\HttpFoundation\Response
    {
        $board = $this->getBoardWithTasks($boardId, $userId);

        $allTasks = $board->columns->flatMap->tasks;
        $totalCount = $allTasks->count();
        $completedCount = $allTasks->filter(fn ($task) => $task->completed_at !== null)->count();
        $pendingCount = $totalCount - $completedCount;
        $completionRate = $totalCount > 0 ? round(($completedCount / $totalCount) * 100) : 0;

        $data = [
            'board' => $board,
            'totalCount' => $totalCount,
            'completedCount' => $completedCount,
            'pendingCount' => $pendingCount,
            'completionRate' => $completionRate,
            'generatedAt' => now()->format('M j, Y g:i A'),
        ];

        $filename = str_replace(' ', '_', $board->name).'_Export.pdf';

        return Pdf::loadView('project-management.pdf.project-board', $data)
            ->setPaper('a4')
            ->setOption('isRemoteEnabled', true)
            ->download($filename);
    }

    /**
     * Export board as CSV download.
     */
    public function exportCsv(int $boardId, int $userId): StreamedResponse
    {
        $board = $this->getBoardWithTasks($boardId, $userId);

        $filename = str_replace(' ', '_', $board->name).'_Export.csv';

        return new StreamedResponse(function () use ($board) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Title', 'Status (Column)', 'Priority', 'Target Date', 'Tags', 'Completed']);

            foreach ($board->columns as $column) {
                foreach ($column->tasks as $task) {
                    fputcsv($handle, [
                        $task->title,
                        $column->name,
                        ucfirst($task->priority ?? ''),
                        $task->target_date?->format('Y-m-d') ?? '',
                        is_array($task->tags) ? implode(', ', $task->tags) : '',
                        $task->completed_at !== null ? 'Yes' : 'No',
                    ]);
                }
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * Export board as Markdown download.
     */
    public function exportMarkdown(int $boardId, int $userId): Response
    {
        $board = $this->getBoardWithTasks($boardId, $userId);

        $allTasks = $board->columns->flatMap->tasks;
        $totalCount = $allTasks->count();
        $completedCount = $allTasks->filter(fn ($task) => $task->completed_at !== null)->count();
        $pendingCount = $totalCount - $completedCount;

        $lines = [];
        $lines[] = '# '.$board->name;
        $lines[] = '_Exported on '.now()->format('M j, Y').'_';
        $lines[] = '';

        foreach ($board->columns as $column) {
            $taskCount = $column->tasks->count();
            $lines[] = '## '.$column->name.' ('.$taskCount.' tasks)';

            if ($taskCount === 0) {
                $lines[] = '_No tasks in this column_';
            } else {
                foreach ($column->tasks as $task) {
                    $isCompleted = $task->completed_at !== null;
                    $checkbox = $isCompleted ? '[x]' : '[ ]';

                    $parts = [];
                    $parts[] = '- '.$checkbox.' **'.$task->title.'**';

                    $details = [];
                    if ($task->priority) {
                        $details[] = 'Priority: '.ucfirst($task->priority);
                    }
                    if ($task->target_date) {
                        $details[] = 'Due: '.$task->target_date->format('Y-m-d');
                    }
                    if (is_array($task->tags) && count($task->tags) > 0) {
                        $details[] = 'Tags: '.implode(', ', $task->tags);
                    }

                    $line = $parts[0];
                    if (count($details) > 0) {
                        $line .= ' | '.implode(' | ', $details);
                    }

                    $lines[] = $line;
                }
            }

            $lines[] = '';
        }

        $lines[] = '---';
        $lines[] = '**Summary:** '.$totalCount.' tasks, '.$completedCount.' completed, '.$pendingCount.' pending';

        $content = implode("\n", $lines)."\n";
        $filename = str_replace(' ', '_', $board->name).'_Export.md';

        return new Response($content, 200, [
            'Content-Type' => 'text/markdown',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * Load board with columns and tasks, scoped by user.
     *
     * @throws ModelNotFoundException
     */
    private function getBoardWithTasks(int $boardId, int $userId): ProjectBoard
    {
        return ProjectBoard::query()
            ->forUser($userId)
            ->with([
                'columns' => fn ($q) => $q->orderBy('sort_order'),
                'columns.tasks' => fn ($q) => $q->orderBy('position'),
                'columns.tasks',
            ])
            ->findOrFail($boardId);
    }
}
