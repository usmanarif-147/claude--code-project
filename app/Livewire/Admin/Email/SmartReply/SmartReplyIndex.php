<?php

namespace App\Livewire\Admin\Email\SmartReply;

use App\Services\SmartReplyDraftService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class SmartReplyIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $filterStatus = '';

    #[Url]
    public string $filterTone = '';

    public int $perPage = 10;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterTone(): void
    {
        $this->resetPage();
    }

    public function deleteDraft(int $id, SmartReplyDraftService $service): void
    {
        $service->delete($id);
        session()->flash('success', 'Draft deleted successfully.');
    }

    public function markCopied(int $id, SmartReplyDraftService $service): void
    {
        $draft = $service->markCopied($id);
        $this->dispatch('copy-to-clipboard', text: $draft->final_body);
        session()->flash('success', 'Draft copied to clipboard.');
    }

    public function markSent(int $id, SmartReplyDraftService $service): void
    {
        $service->markSent($id);
        session()->flash('success', 'Draft marked as sent.');
    }

    public function render(SmartReplyDraftService $service)
    {
        return view('livewire.admin.email.smart-reply.index', [
            'drafts' => $service->getAll($this->search, $this->filterStatus, $this->filterTone, $this->perPage),
        ]);
    }
}
