<?php

namespace App\Livewire\Admin\JobSearch\SavedSearches;

use App\Models\JobSearch\SavedSearch;
use App\Services\SavedSearchService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class SavedSearchIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $filterStatus = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function toggleActive(SavedSearchService $service, int $id): void
    {
        $savedSearch = SavedSearch::query()
            ->forUser(auth()->id())
            ->findOrFail($id);

        $result = $service->toggleActive($savedSearch);

        $state = $result->is_active ? 'activated' : 'deactivated';
        session()->flash('success', "Search {$state}.");
    }

    public function delete(SavedSearchService $service, int $id): void
    {
        $savedSearch = SavedSearch::query()
            ->forUser(auth()->id())
            ->findOrFail($id);

        $service->delete($savedSearch);
        session()->flash('success', 'Saved search deleted successfully.');
    }

    public function render(SavedSearchService $service)
    {
        return view('livewire.admin.job-search.saved-searches.index', [
            'savedSearches' => $service->list(auth()->id(), $this->search, $this->filterStatus),
        ]);
    }
}
