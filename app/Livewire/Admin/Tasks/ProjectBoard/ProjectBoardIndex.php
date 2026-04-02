<?php

namespace App\Livewire\Admin\Tasks\ProjectBoard;

use App\Models\Task\ProjectBoard;
use App\Models\Task\ProjectBoardColumn;
use App\Models\Task\ProjectTask;
use App\Models\Task\ProjectTaskImage;
use App\Models\Task\TaskCategory;
use App\Services\ProjectBoardService;
use App\Services\ProjectTaskService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
class ProjectBoardIndex extends Component
{
    use WithFileUploads;

    // --- Board Selection ---
    #[Url]
    public ?int $selectedBoardId = null;

    // --- Search & Filter ---
    public string $search = '';

    public string $priorityFilter = 'all';

    public string $categoryFilter = 'all';

    // --- New Board Modal ---
    public bool $showNewBoardModal = false;

    public string $newBoardName = '';

    public string $newBoardDescription = '';

    // --- Task Modal ---
    public bool $showTaskModal = false;

    public ?int $editingTaskId = null;

    public ?int $taskColumnId = null;

    public string $taskTitle = '';

    public string $taskDescription = '';

    public string $taskPriority = 'medium';

    public ?string $taskTargetDate = null;

    public ?string $taskCategoryId = '';

    public array $taskTags = [];

    public string $tagInput = '';

    public array $taskImages = [];

    public array $existingImages = [];

    // --- Column Management ---
    public bool $showColumnModal = false;

    public string $newColumnName = '';

    public string $newColumnColor = '#7c3aed';

    public function mount(): void
    {
        if (! $this->selectedBoardId) {
            $board = ProjectBoard::query()
                ->forUser(auth()->id())
                ->ordered()
                ->first();

            if ($board) {
                $this->selectedBoardId = $board->id;
            }
        }
    }

    public function selectBoard(int $boardId): void
    {
        $this->selectedBoardId = $boardId;
        $this->reset('search', 'priorityFilter', 'categoryFilter');
    }

    // -- Board CRUD --

    public function openNewBoardModal(): void
    {
        $this->reset('newBoardName', 'newBoardDescription');
        $this->showNewBoardModal = true;
    }

    public function createBoard(ProjectBoardService $service): void
    {
        $this->validate([
            'newBoardName' => 'required|string|max:255',
            'newBoardDescription' => 'nullable|string|max:1000',
        ]);

        $board = $service->createBoard([
            'user_id' => auth()->id(),
            'name' => $this->newBoardName,
            'description' => $this->newBoardDescription,
        ]);

        $this->selectedBoardId = $board->id;
        $this->showNewBoardModal = false;
        $this->reset('newBoardName', 'newBoardDescription');

        session()->flash('success', 'Board created successfully.');
    }

    public function deleteBoard(ProjectBoardService $service, int $boardId): void
    {
        $board = ProjectBoard::where('user_id', auth()->id())->findOrFail($boardId);
        $service->deleteBoard($board);

        // Select the next available board
        $nextBoard = ProjectBoard::query()
            ->forUser(auth()->id())
            ->ordered()
            ->first();

        $this->selectedBoardId = $nextBoard?->id;

        session()->flash('success', 'Board deleted successfully.');
    }

    // -- Column CRUD --

    public function openColumnModal(): void
    {
        $this->reset('newColumnName');
        $this->newColumnColor = '#7c3aed';
        $this->showColumnModal = true;
    }

    public function addColumn(ProjectBoardService $service): void
    {
        $this->validate([
            'newColumnName' => 'required|string|max:100',
            'newColumnColor' => 'nullable|string|max:7',
        ]);

        $service->addColumn($this->selectedBoardId, [
            'name' => $this->newColumnName,
            'color' => $this->newColumnColor,
        ]);

        $this->showColumnModal = false;
        $this->reset('newColumnName');
        $this->newColumnColor = '#7c3aed';

        session()->flash('success', 'Column added successfully.');
    }

    public function deleteColumn(ProjectBoardService $service, int $columnId): void
    {
        $column = ProjectBoardColumn::findOrFail($columnId);
        $result = $service->deleteColumn($column);

        if (! $result) {
            session()->flash('error', 'Move or delete all tasks in this column before removing it.');

            return;
        }

        session()->flash('success', 'Column deleted successfully.');
    }

    // -- Task CRUD --

    public function openTaskModal(?int $columnId = null, ?int $taskId = null): void
    {
        $this->resetTaskForm();

        if ($taskId) {
            $task = ProjectTask::with('images')->findOrFail($taskId);
            $this->editingTaskId = $task->id;
            $this->taskColumnId = $task->column_id;
            $this->taskTitle = $task->title;
            $this->taskDescription = $task->description ?? '';
            $this->taskPriority = $task->priority;
            $this->taskTargetDate = $task->target_date?->format('Y-m-d');
            $this->taskCategoryId = $task->category_id ? (string) $task->category_id : '';
            $this->taskTags = $task->tags ?? [];
            $this->existingImages = $task->images->map(fn ($img) => [
                'id' => $img->id,
                'image_path' => $img->image_path,
            ])->toArray();
        } elseif ($columnId) {
            $this->taskColumnId = $columnId;
        }

        $this->showTaskModal = true;
    }

    public function createTask(ProjectTaskService $service): void
    {
        $this->validate($this->taskValidationRules());

        $task = $service->create([
            'board_id' => $this->selectedBoardId,
            'column_id' => $this->taskColumnId,
            'user_id' => auth()->id(),
            'title' => $this->taskTitle,
            'description' => $this->taskDescription ?: null,
            'priority' => $this->taskPriority,
            'target_date' => $this->taskTargetDate ?: null,
            'category_id' => $this->taskCategoryId ?: null,
            'tags' => ! empty($this->taskTags) ? $this->taskTags : null,
        ]);

        if (! empty($this->taskImages)) {
            $service->uploadImages($task, $this->taskImages);
        }

        $this->closeTaskModal();

        session()->flash('success', 'Task created successfully.');
    }

    public function updateTask(ProjectTaskService $service): void
    {
        $this->validate($this->taskValidationRules());

        $task = ProjectTask::where('user_id', auth()->id())->findOrFail($this->editingTaskId);

        $service->update($task, [
            'title' => $this->taskTitle,
            'description' => $this->taskDescription ?: null,
            'priority' => $this->taskPriority,
            'target_date' => $this->taskTargetDate ?: null,
            'category_id' => $this->taskCategoryId ?: null,
            'tags' => ! empty($this->taskTags) ? $this->taskTags : null,
        ]);

        if (! empty($this->taskImages)) {
            $service->uploadImages($task, $this->taskImages);
        }

        $this->closeTaskModal();

        session()->flash('success', 'Task updated successfully.');
    }

    public function deleteTask(ProjectTaskService $service, int $taskId): void
    {
        $task = ProjectTask::where('user_id', auth()->id())->findOrFail($taskId);
        $service->delete($task);

        session()->flash('success', 'Task deleted successfully.');
    }

    public function closeTaskModal(): void
    {
        $this->resetTaskForm();
        $this->showTaskModal = false;
    }

    // -- Drag-Drop --

    public function moveTask(ProjectTaskService $service, int $taskId, int $columnId, int $position): void
    {
        $service->moveToColumn($taskId, $columnId, $position);
    }

    // -- Cross-Board Move --

    public function moveTaskToBoard(ProjectTaskService $service, int $taskId, int $targetBoardId): void
    {
        $service->moveToBoard($taskId, $targetBoardId);

        session()->flash('success', 'Task moved to another board.');
    }

    // -- Tags --

    public function addTag(): void
    {
        $tag = trim($this->tagInput);

        if ($tag !== '' && count($this->taskTags) < 10 && ! in_array($tag, $this->taskTags)) {
            $this->taskTags[] = $tag;
        }

        $this->tagInput = '';
    }

    public function removeTag(int $index): void
    {
        unset($this->taskTags[$index]);
        $this->taskTags = array_values($this->taskTags);
    }

    // -- Images --

    public function removeExistingImage(ProjectTaskService $service, int $imageId): void
    {
        $image = ProjectTaskImage::findOrFail($imageId);
        $service->deleteImage($image);

        $this->existingImages = array_values(
            array_filter($this->existingImages, fn ($img) => $img['id'] !== $imageId)
        );
    }

    public function render()
    {
        $userId = auth()->id();
        $boards = ProjectBoard::query()->forUser($userId)->ordered()->get();

        $selectedBoard = null;
        if ($this->selectedBoardId) {
            $selectedBoard = ProjectBoard::with([
                'columns.tasks' => function ($query) {
                    if ($this->search) {
                        $query->where(function ($q) {
                            $q->where('title', 'like', '%'.$this->search.'%')
                                ->orWhere('description', 'like', '%'.$this->search.'%');
                        });
                    }
                    if ($this->priorityFilter !== 'all') {
                        $query->where('priority', $this->priorityFilter);
                    }
                    if ($this->categoryFilter !== 'all') {
                        $query->where('category_id', $this->categoryFilter);
                    }
                    $query->orderBy('position');
                },
                'columns.tasks.category',
                'columns.tasks.images',
            ])->find($this->selectedBoardId);
        }

        $categories = TaskCategory::query()->ordered()->get();

        return view('livewire.admin.tasks.project-board.index', [
            'boards' => $boards,
            'selectedBoard' => $selectedBoard,
            'categories' => $categories,
        ]);
    }

    private function taskValidationRules(): array
    {
        return [
            'taskTitle' => 'required|string|max:255',
            'taskDescription' => 'nullable|string|max:5000',
            'taskPriority' => 'required|in:low,medium,high,urgent',
            'taskTargetDate' => 'nullable|date',
            'taskCategoryId' => 'nullable|exists:task_categories,id',
            'taskTags' => 'nullable|array|max:10',
            'taskTags.*' => 'string|max:50',
            'taskImages' => 'nullable|array|max:5',
            'taskImages.*' => 'image|max:2048',
        ];
    }

    private function resetTaskForm(): void
    {
        $this->editingTaskId = null;
        $this->taskColumnId = null;
        $this->taskTitle = '';
        $this->taskDescription = '';
        $this->taskPriority = 'medium';
        $this->taskTargetDate = null;
        $this->taskCategoryId = '';
        $this->taskTags = [];
        $this->tagInput = '';
        $this->taskImages = [];
        $this->existingImages = [];
    }
}
