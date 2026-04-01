<?php

namespace App\Livewire\Admin\Tasks\QuickCapture;

use App\Models\Task\TaskCategory;
use App\Services\TaskService;
use Illuminate\Support\Carbon;
use Livewire\Component;

class QuickCapture extends Component
{
    public string $title = '';

    public ?int $taskCategoryId = null;

    public string $dueDate = '';

    public bool $showModal = false;

    public function mount(): void
    {
        $this->dueDate = Carbon::today()->toDateString();
    }

    public function openModal(): void
    {
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function save(TaskService $service): void
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'taskCategoryId' => 'nullable|exists:task_categories,id',
            'dueDate' => 'required|date|after_or_equal:today',
        ]);

        $service->create([
            'user_id' => auth()->id(),
            'title' => $this->title,
            'category_id' => $this->taskCategoryId,
            'due_date' => $this->dueDate,
            'status' => 'pending',
            'priority' => 'medium',
            'sort_order' => 0,
        ]);

        $this->resetForm();
        $this->dispatch('task-created');
    }

    public function resetForm(): void
    {
        $this->title = '';
        $this->taskCategoryId = null;
        $this->dueDate = Carbon::today()->toDateString();
        $this->resetValidation();
    }

    public function render()
    {
        $categories = TaskCategory::ordered()->get();

        return view('livewire.admin.tasks.quick-capture.quick-capture', [
            'categories' => $categories,
        ]);
    }
}
