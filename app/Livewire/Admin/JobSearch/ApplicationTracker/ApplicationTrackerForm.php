<?php

namespace App\Livewire\Admin\JobSearch\ApplicationTracker;

use App\Models\JobSearch\JobApplication;
use App\Models\JobSearch\JobListing;
use App\Services\ApplicationTrackerService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class ApplicationTrackerForm extends Component
{
    public ?int $applicationId = null;

    public ?int $job_listing_id = null;

    public string $company = '';

    public string $position = '';

    public string $status = 'saved';

    public ?string $applied_date = null;

    public string $notes = '';

    public string $salary_offered = '';

    public string $url = '';

    public function mount(?JobApplication $jobApplication = null): void
    {
        if ($jobApplication && $jobApplication->exists) {
            $this->applicationId = $jobApplication->id;
            $this->job_listing_id = $jobApplication->job_listing_id;
            $this->company = $jobApplication->company;
            $this->position = $jobApplication->position;
            $this->status = $jobApplication->status;
            $this->applied_date = $jobApplication->applied_date?->format('Y-m-d');
            $this->notes = $jobApplication->notes ?? '';
            $this->salary_offered = $jobApplication->salary_offered ?? '';
            $this->url = $jobApplication->url ?? '';
        }
    }

    public function save(ApplicationTrackerService $service): void
    {
        $validated = $this->validate([
            'company' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'status' => 'required|string|in:saved,applied,interview,offer,rejected',
            'applied_date' => 'nullable|date',
            'notes' => 'nullable|string|max:5000',
            'salary_offered' => 'nullable|string|max:100',
            'url' => 'nullable|url|max:2048',
            'job_listing_id' => 'nullable|exists:job_listings,id',
        ]);

        // Clean empty strings to null
        $validated['job_listing_id'] = $this->job_listing_id;
        $validated['notes'] = $validated['notes'] ?: null;
        $validated['salary_offered'] = $validated['salary_offered'] ?: null;
        $validated['url'] = $validated['url'] ?: null;
        $validated['applied_date'] = $validated['applied_date'] ?: null;

        if ($this->applicationId) {
            $application = JobApplication::findOrFail($this->applicationId);
            $service->updateApplication($application, $validated);
            $message = 'Application updated successfully.';
        } else {
            $service->createApplication($validated);
            $message = 'Application created successfully.';
        }

        session()->flash('success', $message);
        $this->redirect(route('admin.job-search.applications.index'), navigate: true);
    }

    public function render()
    {
        $jobListings = JobListing::query()
            ->select('id', 'title', 'company_name')
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        return view('livewire.admin.job-search.application-tracker.form', [
            'jobListings' => $jobListings,
        ]);
    }
}
