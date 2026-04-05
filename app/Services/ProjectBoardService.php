<?php

namespace App\Services;

use App\Models\ProjectManagement\ProjectBoard;
use App\Models\ProjectManagement\ProjectBoardColumn;
use Illuminate\Support\Collection;

class ProjectBoardService
{
    /**
     * Get all boards for a user, ordered by sort_order.
     */
    public function getBoards(int $userId): Collection
    {
        return ProjectBoard::query()
            ->forUser($userId)
            ->ordered()
            ->get();
    }

    /**
     * Get a single board with columns and tasks eager-loaded.
     */
    public function getBoard(int $boardId): ProjectBoard
    {
        return ProjectBoard::with([
            'columns.tasks',
            'columns.tasks.images',
        ])->findOrFail($boardId);
    }

    /**
     * Create a board and 5 default columns.
     */
    public function createBoard(array $data): ProjectBoard
    {
        $board = ProjectBoard::create($data);

        $defaultColumns = [
            ['name' => 'New', 'color' => '#8b5cf6', 'sort_order' => 0, 'is_completed_column' => false],
            ['name' => 'Todo', 'color' => '#f59e0b', 'sort_order' => 1, 'is_completed_column' => false],
            ['name' => 'On Going', 'color' => '#3b82f6', 'sort_order' => 2, 'is_completed_column' => false],
            ['name' => 'In Review', 'color' => '#f97316', 'sort_order' => 3, 'is_completed_column' => false],
            ['name' => 'Completed', 'color' => '#22c55e', 'sort_order' => 4, 'is_completed_column' => true],
        ];

        foreach ($defaultColumns as $column) {
            $board->columns()->create($column);
        }

        return $board->load('columns');
    }

    /**
     * Update board name/description.
     */
    public function updateBoard(ProjectBoard $board, array $data): ProjectBoard
    {
        $board->update($data);

        return $board;
    }

    /**
     * Delete a board (cascades to columns and tasks).
     */
    public function deleteBoard(ProjectBoard $board): void
    {
        $board->delete();
    }

    /**
     * Add a custom column to a board.
     */
    public function addColumn(int $boardId, array $data): ProjectBoardColumn
    {
        $maxSortOrder = ProjectBoardColumn::where('board_id', $boardId)->max('sort_order') ?? -1;

        return ProjectBoardColumn::create(array_merge($data, [
            'board_id' => $boardId,
            'sort_order' => $maxSortOrder + 1,
        ]));
    }

    /**
     * Update column name, color, or is_completed_column flag.
     */
    public function updateColumn(ProjectBoardColumn $column, array $data): ProjectBoardColumn
    {
        $column->update($data);

        return $column;
    }

    /**
     * Delete a column. Fails if column has tasks or is the last column.
     */
    public function deleteColumn(ProjectBoardColumn $column): bool
    {
        if ($column->tasks()->count() > 0) {
            return false;
        }

        $columnCount = ProjectBoardColumn::where('board_id', $column->board_id)->count();
        if ($columnCount <= 1) {
            return false;
        }

        $column->delete();

        return true;
    }

    /**
     * Reorder columns by accepting an array of [column_id => sort_order].
     */
    public function reorderColumns(array $columnOrder): void
    {
        foreach ($columnOrder as $columnId => $sortOrder) {
            ProjectBoardColumn::where('id', $columnId)->update(['sort_order' => $sortOrder]);
        }
    }
}
