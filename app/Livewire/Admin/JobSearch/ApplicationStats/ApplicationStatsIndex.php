<?php

namespace App\Livewire\Admin\JobSearch\ApplicationStats;

use App\Services\ApplicationStatsService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class ApplicationStatsIndex extends Component
{
    public string $period = '30d';

    public function mount(): void
    {
        $this->period = '30d';
    }

    public function updatedPeriod(): void
    {
        if (! in_array($this->period, ['7d', '30d', '90d'])) {
            $this->period = '30d';
        }
    }

    public function render(ApplicationStatsService $service)
    {
        return view('livewire.admin.job-search.application-stats.index', [
            'stats' => $service->getSummaryStats($this->period),
            'chartData' => $service->getApplicationsByDay($this->period),
            'statusBreakdown' => $service->getStatusBreakdown(),
            'topRejected' => $service->getTopRejectedCompanies($this->period),
            'recentActivity' => $service->getRecentActivity(),
            'pipeline' => $service->getPipelineConversion(),
        ]);
    }
}
