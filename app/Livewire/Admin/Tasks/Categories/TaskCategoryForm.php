<?php

namespace App\Livewire\Admin\Tasks\Categories;

use App\Models\Task\TaskCategory;
use App\Services\TaskCategoryService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class TaskCategoryForm extends Component
{
    public ?TaskCategory $taskCategory = null;

    public string $name = '';

    public string $color = '#7c3aed';

    public int $sort_order = 0;

    public function mount(?TaskCategory $taskCategory = null): void
    {
        if ($taskCategory && $taskCategory->exists) {
            $this->taskCategory = $taskCategory;
            $this->name = $taskCategory->name;
            $this->color = $taskCategory->color;
            $this->sort_order = $taskCategory->sort_order ?? 0;
        }
    }

    public function save(TaskCategoryService $service): void
    {
        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('task_categories', 'name')->ignore($this->taskCategory?->id),
            ],
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'sort_order' => 'required|integer|min:0',
        ]);

        if ($this->taskCategory) {
            $service->update($this->taskCategory, $validated);
            $message = 'Category updated successfully.';
        } else {
            $service->create($validated);
            $message = 'Category created successfully.';
        }

        session()->flash('success', $message);
        $this->redirect(route('admin.tasks.categories.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.tasks.categories.form');
    }
}
