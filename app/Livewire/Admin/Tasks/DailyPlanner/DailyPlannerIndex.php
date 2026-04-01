<?php

namespace App\Livewire\Admin\Tasks\DailyPlanner;

use App\Models\Task\Task;
use App\Models\Task\TaskCategory;
use App\Services\TaskService;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class DailyPlannerIndex extends Component
{
    #[Url]
    public string $selectedDate = '';

    #[Url]
    public string $statusFilter = 'all';

    #[Url]
    public string $priorityFilter = 'all';

    #[Url]
    public string $categoryFilter = 'all';

    public string $newTaskTitle = '';

    public string $newTaskPriority = 'medium';

    public string $newTaskCategoryId = '';

    public ?int $editingTaskId = null;

    public string $editTitle = '';

    public string $editPriority = 'medium';

    public string $editCategoryId = '';

    public function mount(): void
    {
        if (! $this->selectedDate) {
            $this->selectedDate = now()->format('Y-m-d');
        }
    }

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
        ]);

        $this->reset('newTaskTitle', 'newTaskPriority', 'newTaskCategoryId');
        $this->newTaskPriority = 'medium';

        session()->flash('success', 'Task added successfully.');
    }

    public function toggleComplete(TaskService $service, int $taskId): void
    {
        $task = Task::where('user_id', auth()->id())->findOrFail($taskId);
        $service->toggleComplete($task);
    }

    public function startEditing(int $taskId): void
    {
        $task = Task::where('user_id', auth()->id())->findOrFail($taskId);

        $this->editingTaskId = $task->id;
        $this->editTitle = $task->title;
        $this->editPriority = $task->priority;
        $this->editCategoryId = $task->category_id ? (string) $task->category_id : '';
    }

    public function saveEdit(TaskService $service): void
    {
        $this->validate([
            'editTitle' => 'required|string|max:255',
            'editPriority' => 'required|in:low,medium,high,urgent',
            'editCategoryId' => 'nullable|exists:task_categories,id',
        ]);

        $task = Task::where('user_id', auth()->id())->findOrFail($this->editingTaskId);

        $service->update($task, [
            'title' => $this->editTitle,
            'priority' => $this->editPriority,
            'category_id' => $this->editCategoryId ?: null,
        ]);

        $this->cancelEdit();

        session()->flash('success', 'Task updated successfully.');
    }

    public function cancelEdit(): void
    {
        $this->editingTaskId = null;
        $this->editTitle = '';
        $this->editPriority = 'medium';
        $this->editCategoryId = '';
    }

    public function deleteTask(TaskService $service, int $taskId): void
    {
        $task = Task::where('user_id', auth()->id())->findOrFail($taskId);
        $service->delete($task);

        session()->flash('success', 'Task deleted successfully.');
    }

    public function moveIncompleteToTomorrow(TaskService $service): void
    {
        $fromDate = Carbon::parse($this->selectedDate);
        $toDate = $fromDate->copy()->addDay();

        $count = $service->moveIncompleteTo(auth()->id(), $fromDate, $toDate);

        if ($count > 0) {
            session()->flash('success', "{$count} incomplete task(s) moved to tomorrow.");
        } else {
            session()->flash('info', 'No incomplete tasks to move.');
        }
    }

    public function goToDate(string $date): void
    {
        $this->selectedDate = $date;
    }

    public function goToToday(): void
    {
        $this->selectedDate = now()->format('Y-m-d');
    }

    public function goToPreviousDay(): void
    {
        $this->selectedDate = Carbon::parse($this->selectedDate)->subDay()->format('Y-m-d');
    }

    public function goToNextDay(): void
    {
        $this->selectedDate = Carbon::parse($this->selectedDate)->addDay()->format('Y-m-d');
    }

    public function render()
    {
        $userId = auth()->id();
        $date = Carbon::parse($this->selectedDate);

        $query = Task::query()
            ->forUser($userId)
            ->forDate($date)
            ->with('category');

        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        if ($this->priorityFilter !== 'all') {
            $query->where('priority', $this->priorityFilter);
        }

        if ($this->categoryFilter !== 'all') {
            $query->where('category_id', $this->categoryFilter);
        }

        $tasks = $query
            ->byPriority()
            ->ordered()
            ->orderBy('created_at')
            ->get()
            ->sortBy(fn ($task) => $task->status === 'completed' ? 1 : 0);

        $stats = (new TaskService)->getCompletionStats($userId, $date);
        $categories = TaskCategory::query()->ordered()->get();

        return view('livewire.admin.tasks.daily-planner.index', [
            'tasks' => $tasks,
            'stats' => $stats,
            'categories' => $categories,
        ]);
    }
}
