<?php

namespace App\Livewire\Admin\Email\RecruiterAlerts;

use App\Services\RecruiterAlertService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class RecruiterAlertSettings extends Component
{
    public bool $is_enabled = true;

    public bool $alert_on_recruiter = true;

    public bool $alert_on_hiring_manager = true;

    public bool $alert_on_freelance_client = true;

    public int $min_confidence_score = 70;

    public bool $browser_notification = false;

    public bool $email_forward = false;

    public string $forward_email = '';

    public function mount(RecruiterAlertService $service): void
    {
        $settings = $service->getSettings();

        $this->is_enabled = $settings->is_enabled;
        $this->alert_on_recruiter = $settings->alert_on_recruiter;
        $this->alert_on_hiring_manager = $settings->alert_on_hiring_manager;
        $this->alert_on_freelance_client = $settings->alert_on_freelance_client;
        $this->min_confidence_score = $settings->min_confidence_score;
        $this->browser_notification = $settings->browser_notification;
        $this->email_forward = $settings->email_forward;
        $this->forward_email = $settings->forward_email ?? '';
    }

    public function save(RecruiterAlertService $service): void
    {
        $validated = $this->validate([
            'is_enabled' => 'required|boolean',
            'alert_on_recruiter' => 'required|boolean',
            'alert_on_hiring_manager' => 'required|boolean',
            'alert_on_freelance_client' => 'required|boolean',
            'min_confidence_score' => 'required|integer|min:0|max:100',
            'browser_notification' => 'required|boolean',
            'email_forward' => 'required|boolean',
            'forward_email' => 'nullable|required_if:email_forward,true|email|max:255',
        ]);

        $service->updateSettings($validated);

        session()->flash('success', 'Settings saved.');
    }

    public function render()
    {
        return view('livewire.admin.email.recruiter-alerts.settings');
    }
}
