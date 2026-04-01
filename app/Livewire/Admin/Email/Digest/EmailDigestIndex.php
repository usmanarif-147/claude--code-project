<?php

namespace App\Livewire\Admin\Email\Digest;

use App\Models\Email\EmailDigest;
use App\Services\EmailDigestService;
use App\Services\EmailInboxService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class EmailDigestIndex extends Component
{
    use WithPagination;

    public string $selectedDate = '';

    public function mount(): void
    {
        $this->selectedDate = today()->toDateString();
    }

    public function generateDigest(EmailDigestService $service): void
    {
        $this->validate([
            'selectedDate' => 'required|date|before_or_equal:today',
        ]);

        try {
            $digest = $service->generateDigest($this->selectedDate);

            if ($digest->status === 'completed') {
                session()->flash('success', 'Digest generated successfully for '.$digest->digest_date->format('M j, Y').'.');
            } else {
                session()->flash('error', 'Digest generation failed: '.($digest->error_message ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to generate digest: '.$e->getMessage());
        }
    }

    public function viewDigest(int $digestId): void
    {
        $digest = EmailDigest::findOrFail($digestId);
        $this->selectedDate = $digest->digest_date->toDateString();
    }

    public function render(EmailDigestService $digestService, EmailInboxService $inboxService)
    {
        $currentDigest = EmailDigest::forDate($this->selectedDate)->first();

        return view('livewire.admin.email.digest.index', [
            'currentDigest' => $currentDigest,
            'digestHistory' => $digestService->getDigestHistory(10),
            'unreadCount' => $inboxService->getUnreadCount(),
            'lastSync' => \App\Models\Email\EmailSyncLog::where('status', 'success')->latest('synced_at')->first(),
        ]);
    }
}
