<?php

namespace App\Livewire\Admin;

use App\Models\Technology;
use App\Services\TechnologyService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class TechnologyIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $activeFilter = 'all';

    #[Url]
    public string $categoryFilter = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingActiveFilter(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function delete(TechnologyService $service, int $id): void
    {
        $service->delete(Technology::findOrFail($id));
        session()->flash('success', 'Technology deleted successfully.');
    }

    public function render()
    {
        $query = Technology::query()->ordered();

        if ($this->search) {
            $query->where('name', 'like', '%'.$this->search.'%');
        }

        if ($this->activeFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->activeFilter === 'inactive') {
            $query->where('is_active', false);
        }

        if ($this->categoryFilter !== 'all') {
            $query->where('category', $this->categoryFilter);
        }

        return view('livewire.admin.technology-index', [
            'technologies' => $query->paginate(10),
        ]);
    }
}
