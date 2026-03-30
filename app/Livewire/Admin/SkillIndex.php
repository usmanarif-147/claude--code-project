<?php

namespace App\Livewire\Admin;

use App\Models\Skill;
use App\Services\SkillService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class SkillIndex extends Component
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

    public function delete(SkillService $service, int $id): void
    {
        $service->delete(Skill::findOrFail($id));
        session()->flash('success', 'Skill deleted successfully.');
    }

    public function render()
    {
        $query = Skill::query()->ordered();

        if ($this->search) {
            $query->where('title', 'like', '%'.$this->search.'%');
        }

        if ($this->activeFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->activeFilter === 'inactive') {
            $query->where('is_active', false);
        }

        if ($this->categoryFilter !== 'all') {
            $query->where('category', $this->categoryFilter);
        }

        $categories = Skill::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category');

        return view('livewire.admin.skill-index', [
            'skills' => $query->paginate(10),
            'categories' => $categories,
        ]);
    }
}
