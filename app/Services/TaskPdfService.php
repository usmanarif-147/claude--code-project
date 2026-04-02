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
     * @param  int  $userId  The authenticated user's ID
     * @param  string  $period  One of: 'day', 'week', 'month'
     * @param  string  $date  Reference date in Y-m-d format
     * @param  string  $taskType  One of: 'all', 'personal', 'project'
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
                'label' => $reference->copy()->startOfWeek()->format('M j').' - '.$reference->copy()->endOfWeek()->format('M j, Y'),
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
     * @return Collection Keyed by date string (Y-m-d), each value is a Collection of Task models
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
     */
    public function getProjectTasksForPeriod(int $userId, Carbon $start, Carbon $end): Collection
    {
        // TODO: Implement when ProjectTask model exists
        return collect();
    }

    /**
     * Generate the PDF and return a download response.
     */
    public function download(int $userId, string $period, string $date, string $taskType = 'all'): \Symfony\Component\HttpFoundation\Response
    {
        $pdf = $this->generatePdf($userId, $period, $date, $taskType);
        $dateRange = $this->getDateRange($period, $date);

        $filename = 'Tasks_'.str_replace([' ', ',', '-'], '_', $dateRange['label']).'.pdf';

        return $pdf->download($filename);
    }
}
