<?php

namespace App\Livewire\Admin\JobSearch\ApplicationTracker;

use App\Models\JobSearch\JobApplication;
use App\Services\ApplicationTrackerService;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class ApplicationTrackerIndex extends Component
{
    #[Url]
    public string $search = '';

    public array $statusCounts = [];

    public function mount(): void
    {
        $this->loadStatusCounts();
    }

    public function loadStatusCounts(): void
    {
        $this->statusCounts = (new ApplicationTrackerService)->getStatusCounts();
    }

    public function updateCardStatus(int $applicationId, string $newStatus, int $newSortOrder): void
    {
        Validator::make(
            ['applicationId' => $applicationId, 'newStatus' => $newStatus, 'newSortOrder' => $newSortOrder],
            [
                'applicationId' => 'required|exists:job_applications,id',
                'newStatus' => 'required|string|in:saved,applied,interview,offer,rejected',
                'newSortOrder' => 'required|integer|min:0',
            ]
        )->validate();

        $application = JobApplication::findOrFail($applicationId);
        $service = new ApplicationTrackerService;
        $service->updateStatus($application, $newStatus, $newSortOrder);

        $this->loadStatusCounts();
    }

    public function reorderColumn(string $status, array $orderedIds): void
    {
        $service = new ApplicationTrackerService;
        $service->reorderColumn($status, $orderedIds);
    }

    public function deleteApplication(int $applicationId): void
    {
        $application = JobApplication::findOrFail($applicationId);
        $service = new ApplicationTrackerService;
        $service->deleteApplication($application);

        $this->loadStatusCounts();

        session()->flash('success', 'Application deleted successfully.');
    }

    public function render()
    {
        $service = new ApplicationTrackerService;
        $applications = $service->getApplicationsGroupedByStatus($this->search ?: null);

        return view('livewire.admin.job-search.application-tracker.index', [
            'applications' => $applications,
        ]);
    }
}
