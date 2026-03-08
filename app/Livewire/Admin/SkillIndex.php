<?php

namespace App\Livewire\Admin;

use App\Models\Skill;
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

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingActiveFilter(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        Skill::findOrFail($id)->delete();
        session()->flash('success', 'Skill deleted successfully.');
    }

    public function render()
    {
        $query = Skill::query()->ordered();

        if ($this->search) {
            $query->where('title', 'like', '%' . $this->search . '%');
        }

        if ($this->activeFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->activeFilter === 'inactive') {
            $query->where('is_active', false);
        }

        return view('livewire.admin.skill-index', [
            'skills' => $query->paginate(10),
        ]);
    }
}
