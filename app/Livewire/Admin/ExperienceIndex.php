<?php

namespace App\Livewire\Admin;

use App\Models\Experience;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class ExperienceIndex extends Component
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
        Experience::findOrFail($id)->delete();
        session()->flash('success', 'Experience deleted successfully.');
    }

    public function render()
    {
        $query = Experience::query()->ordered();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('role', 'like', '%' . $this->search . '%')
                  ->orWhere('company', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->activeFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->activeFilter === 'inactive') {
            $query->where('is_active', false);
        }

        return view('livewire.admin.experience-index', [
            'experiences' => $query->paginate(10),
        ]);
    }
}
