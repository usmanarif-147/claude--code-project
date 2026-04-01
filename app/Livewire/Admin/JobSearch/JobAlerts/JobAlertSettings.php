<?php

namespace App\Livewire\Admin\JobSearch\JobAlerts;

use App\Services\JobAlertService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class JobAlertSettings extends Component
{
    public bool $isEnabled = true;

    public int $minScoreThreshold = 80;

    public string $frequency = 'instant';

    public bool $notifyDashboard = true;

    public bool $notifyEmail = false;

    public function mount(): void
    {
        $service = app(JobAlertService::class);
        $config = $service->getOrCreateConfig(auth()->user());

        $this->isEnabled = $config->is_enabled;
        $this->minScoreThreshold = $config->min_score_threshold;
        $this->frequency = $config->frequency;
        $this->notifyDashboard = $config->notify_dashboard;
        $this->notifyEmail = $config->notify_email;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'isEnabled' => 'required|boolean',
            'minScoreThreshold' => 'required|integer|min:0|max:100',
            'frequency' => 'required|string|in:instant,daily,weekly',
            'notifyDashboard' => 'required|boolean',
            'notifyEmail' => 'required|boolean',
        ]);

        try {
            $service = app(JobAlertService::class);
            $service->updateConfig(auth()->user(), [
                'is_enabled' => $validated['isEnabled'],
                'min_score_threshold' => $validated['minScoreThreshold'],
                'frequency' => $validated['frequency'],
                'notify_dashboard' => $validated['notifyDashboard'],
                'notify_email' => $validated['notifyEmail'],
            ]);

            session()->flash('success', 'Alert settings saved.');
        } catch (\Throwable $e) {
            session()->flash('error', 'Failed to save alert settings.');
        }
    }

    public function render()
    {
        return view('livewire.admin.job-search.job-alerts.settings');
    }
}
