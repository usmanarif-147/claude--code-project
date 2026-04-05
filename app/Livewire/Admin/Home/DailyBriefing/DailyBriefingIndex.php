<?php

namespace App\Livewire\Admin\Home\DailyBriefing;

use App\Models\Email\RecruiterAlert;
use App\Models\ProjectManagement\ProjectTask;
use App\Services\DailyBriefingService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class DailyBriefingIndex extends Component
{
    public string $greeting = '';

    public array $quickStats = [];

    public Collection $todayTasks;

    public Collection $newJobMatches;

    public array $emailSummary = [];

    public Collection $pendingAlerts;

    public Collection $activeGoals;

    public array $jobSearchStats = [];

    public function mount(DailyBriefingService $service): void
    {
        $userId = auth()->id();

        $this->greeting = $service->getGreeting();
        $this->quickStats = $service->getQuickStats($userId);
        $this->todayTasks = $service->getTodayTasks($userId);
        $this->newJobMatches = $service->getNewJobMatches($userId);
        $this->emailSummary = $service->getEmailSummary();
        $this->pendingAlerts = $service->getPendingRecruiterAlerts();
        $this->activeGoals = $service->getActiveGoals();
        $this->jobSearchStats = $service->getJobSearchStats();
    }

    public function completeTask(DailyBriefingService $briefingService, int $taskId): void
    {
        $task = ProjectTask::where('user_id', auth()->id())->findOrFail($taskId);
        $task->update(['completed_at' => $task->completed_at ? null : now()]);

        $userId = auth()->id();
        $this->todayTasks = $briefingService->getTodayTasks($userId);
        $this->quickStats = $briefingService->getQuickStats($userId);

        session()->flash('success', 'Task updated successfully.');
    }

    public function dismissAlert(DailyBriefingService $briefingService, int $alertId): void
    {
        $alert = RecruiterAlert::findOrFail($alertId);
        $alert->update(['is_dismissed' => true]);

        $this->pendingAlerts = $briefingService->getPendingRecruiterAlerts();

        session()->flash('success', 'Alert dismissed.');
    }

    public function render()
    {
        return view('livewire.admin.home.daily-briefing.index');
    }
}
