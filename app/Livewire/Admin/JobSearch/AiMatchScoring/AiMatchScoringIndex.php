<?php

namespace App\Livewire\Admin\JobSearch\AiMatchScoring;

use App\Models\JobSearch\JobListing;
use App\Services\AiJobMatchService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class AiMatchScoringIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $filterPlatform = '';

    #[Url]
    public string $filterLocationType = '';

    #[Url]
    public ?int $filterMinScore = null;

    #[Url]
    public string $sortBy = 'score';

    public function mount(): void
    {
        //
    }

    public function scoreAllUnscored(AiJobMatchService $service): void
    {
        if (! $this->provider) {
            session()->flash('error', 'No AI provider configured. Add a Claude or OpenAI API key in Settings.');

            return;
        }

        try {
            $summary = $service->scoreUnscored(auth()->user());
            session()->flash('success', "Scored {$summary['scored']} jobs, {$summary['failed']} failed, {$summary['skipped']} skipped.");
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function rescoreAll(AiJobMatchService $service): void
    {
        if (! $this->provider) {
            session()->flash('error', 'No AI provider configured. Add a Claude or OpenAI API key in Settings.');

            return;
        }

        try {
            $summary = $service->rescoreAll(auth()->user());
            session()->flash('success', "Re-scored {$summary['scored']} jobs, {$summary['failed']} failed.");
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function rescoreJob(int $jobId, AiJobMatchService $service): void
    {
        if (! $this->provider) {
            session()->flash('error', 'No AI provider configured. Add a Claude or OpenAI API key in Settings.');

            return;
        }

        try {
            $job = JobListing::query()
                ->forUser(auth()->id())
                ->findOrFail($jobId);

            $score = $service->rescoreJob($job, auth()->user());
            session()->flash('success', "Job re-scored: {$score->score}% match.");
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    #[Computed]
    public function jobs(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $service = app(AiJobMatchService::class);

        return $service->getScoredFeed(auth()->user(), [
            'search' => $this->search,
            'platform' => $this->filterPlatform,
            'locationType' => $this->filterLocationType,
            'minScore' => $this->filterMinScore,
            'sortBy' => $this->sortBy,
        ], 15);
    }

    #[Computed]
    public function stats(): array
    {
        $service = app(AiJobMatchService::class);

        return $service->getScoreStats(auth()->user());
    }

    #[Computed]
    public function provider(): ?string
    {
        $service = app(AiJobMatchService::class);

        return $service->getConfiguredProvider(auth()->id());
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterPlatform(): void
    {
        $this->resetPage();
    }

    public function updatingFilterLocationType(): void
    {
        $this->resetPage();
    }

    public function updatingFilterMinScore(): void
    {
        $this->resetPage();
    }

    public function updatingSortBy(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.admin.job-search.ai-match-scoring.index');
    }
}
