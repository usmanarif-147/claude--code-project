<?php

namespace App\Livewire\Admin\Email\RecruiterAlerts;

use App\Services\RecruiterAlertService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class RecruiterAlertIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $filterType = '';

    #[Url]
    public string $filterUrgency = '';

    #[Url]
    public string $filterStatus = '';

    public array $stats = [];

    public function mount(RecruiterAlertService $service): void
    {
        $this->stats = $service->getStats();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    public function updatingFilterUrgency(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function markAsRead(int $id, RecruiterAlertService $service): void
    {
        $service->markAsRead($id);
        $this->refreshStats($service);
        session()->flash('success', 'Alert marked as read.');
    }

    public function markAsUnread(int $id, RecruiterAlertService $service): void
    {
        $service->markAsUnread($id);
        $this->refreshStats($service);
        session()->flash('success', 'Alert marked as unread.');
    }

    public function dismiss(int $id, RecruiterAlertService $service): void
    {
        $service->dismissAlert($id);
        $this->refreshStats($service);
        session()->flash('success', 'Alert dismissed.');
    }

    public function markAllAsRead(RecruiterAlertService $service): void
    {
        $count = $service->markAllAsRead();
        $this->refreshStats($service);
        session()->flash('success', "{$count} alerts marked as read.");
    }

    public function dismissAll(RecruiterAlertService $service): void
    {
        $count = $service->dismissAll();
        $this->refreshStats($service);
        session()->flash('success', "{$count} alerts dismissed.");
    }

    public function scanEmails(RecruiterAlertService $service): void
    {
        $count = $service->scanEmails();
        $this->refreshStats($service);
        session()->flash('success', "{$count} new alerts found.");
    }

    public function deleteAlert(int $id, RecruiterAlertService $service): void
    {
        $service->deleteAlert($id);
        $this->refreshStats($service);
        session()->flash('success', 'Alert deleted.');
    }

    public function refreshStats(?RecruiterAlertService $service = null): void
    {
        $service = $service ?? app(RecruiterAlertService::class);
        $this->stats = $service->getStats();
    }

    public function render(RecruiterAlertService $service)
    {
        return view('livewire.admin.email.recruiter-alerts.index', [
            'alerts' => $service->getAlerts([
                'search' => $this->search,
                'type' => $this->filterType,
                'urgency' => $this->filterUrgency,
                'status' => $this->filterStatus,
            ]),
        ]);
    }
}
