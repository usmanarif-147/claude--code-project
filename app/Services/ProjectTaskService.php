<?php

namespace App\Services;

use App\Models\Task\ProjectBoardColumn;
use App\Models\Task\ProjectTask;
use App\Models\Task\ProjectTaskImage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProjectTaskService
{
    /**
     * Create a project task.
     * Sets position to max(position in target column) + 1.
     */
    public function create(array $data): ProjectTask
    {
        $maxPosition = ProjectTask::where('column_id', $data['column_id'])->max('position') ?? -1;
        $data['position'] = $maxPosition + 1;

        return ProjectTask::create($data);
    }

    /**
     * Update a project task. Does NOT change column/position.
     */
    public function update(ProjectTask $task, array $data): ProjectTask
    {
        $task->update($data);

        return $task;
    }

    /**
     * Delete a project task and its images from storage.
     */
    public function delete(ProjectTask $task): void
    {
        // Delete image files from storage
        foreach ($task->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }

        // Delete the entire task directory if it exists
        $directory = 'project-tasks/'.$task->id;
        if (Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->deleteDirectory($directory);
        }

        $task->delete();
    }

    /**
     * Move a task to a target column at a given position.
     */
    public function moveToColumn(int $taskId, int $targetColumnId, int $position): ProjectTask
    {
        return DB::transaction(function () use ($taskId, $targetColumnId, $position) {
            $task = ProjectTask::findOrFail($taskId);
            $oldColumnId = $task->column_id;

            // Reorder tasks in the old column (close the gap)
            if ($oldColumnId !== $targetColumnId) {
                ProjectTask::where('column_id', $oldColumnId)
                    ->where('position', '>', $task->position)
                    ->decrement('position');
            }

            // Make room in the target column
            ProjectTask::where('column_id', $targetColumnId)
                ->where('position', '>=', $position)
                ->increment('position');

            // Move the task
            $task->column_id = $targetColumnId;
            $task->position = $position;

            // Handle completed_at based on is_completed_column
            $targetColumn = ProjectBoardColumn::findOrFail($targetColumnId);
            if ($targetColumn->is_completed_column) {
                $task->completed_at = now();
            } else {
                $task->completed_at = null;
            }

            $task->save();

            return $task;
        });
    }

    /**
     * Reorder tasks within a single column.
     * Accepts array of [task_id => position].
     */
    public function reorderInColumn(int $columnId, array $taskOrder): void
    {
        foreach ($taskOrder as $taskId => $position) {
            ProjectTask::where('id', $taskId)
                ->where('column_id', $columnId)
                ->update(['position' => $position]);
        }
    }

    /**
     * Get project tasks for a specific date (by target_date).
     */
    public function getTasksForDate(int $userId, Carbon $date): Collection
    {
        return ProjectTask::query()
            ->forUser($userId)
            ->forDate($date)
            ->with(['board', 'column', 'category', 'images'])
            ->ordered()
            ->get();
    }

    /**
     * Get project tasks for a date range (by target_date).
     */
    public function getTasksForDateRange(int $userId, Carbon $start, Carbon $end): Collection
    {
        return ProjectTask::query()
            ->forUser($userId)
            ->forDateRange($start, $end)
            ->with(['board', 'column', 'category', 'images'])
            ->ordered()
            ->get();
    }

    /**
     * Upload images for a task.
     *
     * @param  \Illuminate\Http\UploadedFile[]  $files
     * @return ProjectTaskImage[]
     */
    public function uploadImages(ProjectTask $task, array $files): array
    {
        $images = [];
        $maxSortOrder = $task->images()->max('sort_order') ?? -1;

        foreach ($files as $file) {
            $path = $file->store('project-tasks/'.$task->id, 'public');
            $maxSortOrder++;

            $images[] = $task->images()->create([
                'image_path' => $path,
                'sort_order' => $maxSortOrder,
            ]);
        }

        return $images;
    }

    /**
     * Delete a single image from storage and database.
     */
    public function deleteImage(ProjectTaskImage $image): void
    {
        Storage::disk('public')->delete($image->image_path);
        $image->delete();
    }
}
