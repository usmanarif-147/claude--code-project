<?php

namespace App\Livewire\Admin\Email\Inbox;

use App\Services\EmailInboxService;
use App\Services\GmailSyncService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class EmailInboxIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $filterCategory = '';

    #[Url]
    public string $filterRead = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCategory(): void
    {
        $this->resetPage();
    }

    public function updatingFilterRead(): void
    {
        $this->resetPage();
    }

    public function syncNow(GmailSyncService $service): void
    {
        try {
            $result = $service->syncEmails();
            session()->flash('success', "Gmail sync complete. Fetched {$result['fetched']} new emails, skipped {$result['skipped']} duplicates.");
        } catch (\Exception $e) {
            session()->flash('error', 'Gmail sync failed: '.$e->getMessage());
        }
    }

    public function markRead(int $emailId, EmailInboxService $service): void
    {
        $service->markAsRead($emailId);
    }

    public function render(EmailInboxService $service)
    {
        $filters = [
            'search' => $this->search,
            'category' => $this->filterCategory,
            'read' => $this->filterRead,
        ];

        return view('livewire.admin.email.inbox.index', [
            'emails' => $service->getEmails($filters, 20),
            'stats' => $service->getRecentStats(),
        ]);
    }
}
