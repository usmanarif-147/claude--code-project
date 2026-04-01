<?php

namespace App\Livewire\Admin\JobSearch\JobAlerts;

use App\Models\JobSearch\JobAlertNotification;
use App\Services\JobAlertService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class JobAlertIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $filterStatus = '';

    #[Url]
    public string $filterDateFrom = '';

    #[Url]
    public string $filterDateTo = '';

    public function mount(): void
    {
        //
    }

    public function markAsRead(int $notificationId): void
    {
        $notification = JobAlertNotification::query()
            ->forUser(auth()->id())
            ->findOrFail($notificationId);

        $service = app(JobAlertService::class);
        $service->markAsRead($notification);
    }

    public function markAsUnread(int $notificationId): void
    {
        $notification = JobAlertNotification::query()
            ->forUser(auth()->id())
            ->findOrFail($notificationId);

        $service = app(JobAlertService::class);
        $service->markAsUnread($notification);
    }

    public function markAllAsRead(): void
    {
        $service = app(JobAlertService::class);
        $count = $service->markAllAsRead(auth()->user());

        session()->flash('success', 'All notifications marked as read.');
    }

    public function dismiss(int $notificationId): void
    {
        $notification = JobAlertNotification::query()
            ->forUser(auth()->id())
            ->findOrFail($notificationId);

        $service = app(JobAlertService::class);
        $service->dismissNotification($notification);
    }

    #[Computed]
    public function notifications(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $service = app(JobAlertService::class);

        return $service->getNotifications(auth()->user(), [
            'status' => $this->filterStatus,
            'date_from' => $this->filterDateFrom,
            'date_to' => $this->filterDateTo,
        ], 15);
    }

    #[Computed]
    public function stats(): array
    {
        $service = app(JobAlertService::class);

        return $service->getStats(auth()->user());
    }

    #[Computed]
    public function unreadCount(): int
    {
        $service = app(JobAlertService::class);

        return $service->getUnreadCount(auth()->user());
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingFilterDateTo(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.admin.job-search.job-alerts.index');
    }
}
