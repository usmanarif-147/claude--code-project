<?php

namespace App\Livewire\Admin\Portfolio\Categories;

use App\Models\Category;
use App\Services\CategoryService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class CategoryIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $activeFilter = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingActiveFilter(): void
    {
        $this->resetPage();
    }

    public function delete(CategoryService $service, int $id): void
    {
        $category = Category::findOrFail($id);

        if ($category->skills()->exists() || $category->technologies()->exists()) {
            session()->flash('error', 'Cannot delete a category that still has skills or technologies assigned to it.');

            return;
        }

        $service->delete($category);
        session()->flash('success', 'Category deleted successfully.');
    }

    public function render()
    {
        $query = Category::query()
            ->withCount(['skills', 'technologies'])
            ->ordered();

        if ($this->search) {
            $query->where('name', 'like', '%'.$this->search.'%');
        }

        if ($this->activeFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->activeFilter === 'inactive') {
            $query->where('is_active', false);
        }

        return view('livewire.admin.portfolio.categories.index', [
            'categories' => $query->paginate(10),
        ]);
    }
}
