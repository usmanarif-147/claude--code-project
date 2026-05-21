<?php

namespace App\Livewire\Admin\Portfolio\Strengths;

use App\Models\Strength;
use App\Services\StrengthService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class StrengthIndex extends Component
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

    public function delete(StrengthService $service, int $id): void
    {
        $service->delete(Strength::findOrFail($id));
        session()->flash('success', 'Strength deleted successfully.');
    }

    public function render()
    {
        $query = Strength::query()->ordered();

        if ($this->search) {
            $query->where('title', 'like', '%'.$this->search.'%');
        }

        if ($this->activeFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->activeFilter === 'inactive') {
            $query->where('is_active', false);
        }

        return view('livewire.admin.portfolio.strengths.index', [
            'strengths' => $query->paginate(10),
        ]);
    }
}
