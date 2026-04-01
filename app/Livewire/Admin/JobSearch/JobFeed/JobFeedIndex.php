<?php

namespace App\Livewire\Admin\JobSearch\JobFeed;

use App\Models\JobSearch\JobListing;
use App\Services\JobFeedService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class JobFeedIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $filterPlatform = '';

    #[Url]
    public string $filterLocationType = '';

    #[Url]
    public string $filterCountry = '';

    #[Url]
    public string $filterStatus = '';

    #[Url]
    public ?int $filterSalaryMin = null;

    #[Url]
    public ?int $filterSalaryMax = null;

    public function mount(): void
    {
        // Defaults are sufficient
    }

    public function fetchJobs(JobFeedService $service): void
    {
        try {
            $result = $service->fetchAllPlatforms(auth()->user());
            $duplicates = $service->deduplicateJobs(auth()->user());

            $message = "Fetched {$result['total_fetched']} new jobs from {$result['platforms_fetched']} platforms";

            if ($duplicates > 0) {
                $message .= ", {$duplicates} duplicates removed";
            }

            if (! empty($result['errors'])) {
                $message .= '. Warnings: '.implode('; ', $result['errors']);
            }

            session()->flash('success', $message.'.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to fetch jobs: '.$e->getMessage());
        }
    }

    public function updateStatus(int $jobId, ?string $status, JobFeedService $service): void
    {
        $job = JobListing::where('user_id', auth()->id())->findOrFail($jobId);

        // Toggle behavior: if already set to this status, clear it
        $newStatus = $job->user_status === $status ? null : $status;

        $service->updateJobStatus($job, $newStatus);
    }

    public function hideJob(int $jobId, JobFeedService $service): void
    {
        $job = JobListing::where('user_id', auth()->id())->findOrFail($jobId);
        $service->hideJob($job);
    }

    #[Computed]
    public function jobs(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $service = app(JobFeedService::class);

        return $service->getFilteredFeed(auth()->user(), [
            'search' => $this->search,
            'platform' => $this->filterPlatform,
            'location_type' => $this->filterLocationType,
            'country' => $this->filterCountry,
            'status' => $this->filterStatus,
            'salary_min' => $this->filterSalaryMin,
            'salary_max' => $this->filterSalaryMax,
        ], 15);
    }

    #[Computed]
    public function stats(): array
    {
        $service = app(JobFeedService::class);

        return $service->getStats(auth()->user());
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

    public function updatingFilterCountry(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterSalaryMin(): void
    {
        $this->resetPage();
    }

    public function updatingFilterSalaryMax(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.admin.job-search.job-feed.index');
    }
}
