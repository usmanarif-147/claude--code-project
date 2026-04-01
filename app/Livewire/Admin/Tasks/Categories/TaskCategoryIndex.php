<?php

namespace App\Livewire\Admin\Tasks\Categories;

use App\Models\Task\TaskCategory;
use App\Services\TaskCategoryService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class TaskCategoryIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(TaskCategoryService $service, int $id): void
    {
        $category = TaskCategory::findOrFail($id);

        try {
            $service->delete($category);
            session()->flash('success', 'Category deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $query = TaskCategory::query()->ordered();

        if ($this->search) {
            $query->search($this->search);
        }

        return view('livewire.admin.tasks.categories.index', [
            'categories' => $query->paginate(10),
        ]);
    }
}
