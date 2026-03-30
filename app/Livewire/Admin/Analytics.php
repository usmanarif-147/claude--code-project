<?php

namespace App\Livewire\Admin;

use App\Services\AnalyticsService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Analytics extends Component
{
    public string $period = '30d';

    public function updatedPeriod(): void
    {
        if (! in_array($this->period, ['7d', '30d', '90d'])) {
            $this->period = '30d';
        }
    }

    public function render(AnalyticsService $service)
    {
        return view('livewire.admin.analytics', [
            'stats' => $service->getVisitorStats($this->period),
            'chartData' => $service->getVisitorsByDay($this->period),
            'topPages' => $service->getTopPages($this->period),
            'devices' => $service->getDeviceBreakdown($this->period),
            'referrers' => $service->getReferrerBreakdown($this->period),
        ]);
    }
}
