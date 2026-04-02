<?php

namespace App\Livewire\Admin\Personal\GoalsTracker;

use App\Models\Goal;
use App\Services\GoalService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class GoalIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $filterCategory = '';

    #[Url]
    public string $filterStatus = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCategory(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function delete(GoalService $service, int $id): void
    {
        $service->delete(Goal::findOrFail($id));
        session()->flash('success', 'Goal deleted successfully.');
    }

    public function render()
    {
        $service = app(GoalService::class);

        return view('livewire.admin.personal.goals-tracker.index', [
            'goals' => $service->getAll([
                'search' => $this->search,
                'category' => $this->filterCategory,
                'status' => $this->filterStatus,
            ]),
            'stats' => $service->getStats(),
        ]);
    }
}
