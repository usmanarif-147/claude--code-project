<?php

namespace App\Livewire\Admin\JobSearch\AiCoverLetter;

use App\Models\JobSearch\CoverLetter;
use App\Services\AiCoverLetterService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class AiCoverLetterIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    public function mount(): void
    {
        // Defaults are sufficient
    }

    public function deleteLetter(int $id): void
    {
        $coverLetter = CoverLetter::query()
            ->forUser(auth()->id())
            ->findOrFail($id);

        $service = app(AiCoverLetterService::class);
        $service->delete($coverLetter);

        session()->flash('success', 'Cover letter deleted successfully.');
    }

    #[Computed]
    public function letters(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $service = app(AiCoverLetterService::class);

        return $service->getLettersForUser(auth()->user(), $this->search, 10);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.admin.job-search.ai-cover-letter.index');
    }
}
