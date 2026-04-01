<?php

namespace App\Livewire\Admin\Email\SmartReply;

use App\Models\Email\Email;
use App\Models\Email\SmartReplyDraft;
use App\Models\EmailTemplate;
use App\Services\SmartReplyDraftService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class SmartReplyForm extends Component
{
    public ?int $draftId = null;

    public ?int $emailId = null;

    public ?int $templateId = null;

    public string $tone = 'formal';

    public string $promptContext = '';

    public string $generatedBody = '';

    public string $editedBody = '';

    public string $status = 'draft';

    public string $emailSubject = '';

    public string $emailSnippet = '';

    public string $emailFrom = '';

    public array $templates = [];

    public bool $isGenerating = false;

    public function mount(?int $smartReplyDraft = null, ?int $email = null): void
    {
        if ($smartReplyDraft) {
            $draft = SmartReplyDraft::with(['email', 'template'])->findOrFail($smartReplyDraft);
            $this->draftId = $draft->id;
            $this->emailId = $draft->email_id;
            $this->templateId = $draft->template_id;
            $this->tone = $draft->tone;
            $this->promptContext = $draft->prompt_context ?? '';
            $this->generatedBody = $draft->generated_body;
            $this->editedBody = $draft->edited_body ?? '';
            $this->status = $draft->status;

            if ($draft->email) {
                $this->emailSubject = $draft->email->subject ?? '(No Subject)';
                $this->emailSnippet = $draft->email->body_preview ?? $draft->email->snippet ?? '';
                $this->emailFrom = $draft->email->from_name
                    ? "{$draft->email->from_name} <{$draft->email->from_email}>"
                    : $draft->email->from_email;
            }
        } elseif ($email) {
            $emailRecord = Email::findOrFail($email);
            $this->emailId = $emailRecord->id;
            $this->emailSubject = $emailRecord->subject ?? '(No Subject)';
            $this->emailSnippet = $emailRecord->body_preview ?? $emailRecord->snippet ?? '';
            $this->emailFrom = $emailRecord->from_name
                ? "{$emailRecord->from_name} <{$emailRecord->from_email}>"
                : $emailRecord->from_email;
        }

        $this->loadTemplates();
    }

    public function loadTemplates(): void
    {
        $this->templates = EmailTemplate::query()
            ->orderBy('name')
            ->get(['id', 'name', 'category'])
            ->toArray();
    }

    public function generate(SmartReplyDraftService $service): void
    {
        $this->validate([
            'emailId' => 'required|exists:emails,id',
            'tone' => 'required|in:formal,friendly,brief',
            'templateId' => 'nullable|exists:email_templates,id',
            'promptContext' => 'nullable|string|max:1000',
        ]);

        $this->isGenerating = true;

        try {
            $draft = $service->generate(
                $this->emailId,
                $this->tone,
                $this->templateId,
                $this->promptContext ?: null
            );

            $this->draftId = $draft->id;
            $this->generatedBody = $draft->generated_body;
            $this->editedBody = '';
            $this->status = $draft->status;

            session()->flash('success', 'Reply draft generated.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        } finally {
            $this->isGenerating = false;
        }
    }

    public function saveEdit(SmartReplyDraftService $service): void
    {
        $this->validate([
            'editedBody' => 'required|string|max:10000',
        ]);

        $service->updateEditedBody($this->draftId, $this->editedBody);
        session()->flash('success', 'Draft updated.');
    }

    public function copyToClipboard(SmartReplyDraftService $service): void
    {
        if (! $this->draftId) {
            return;
        }

        $draft = $service->markCopied($this->draftId);
        $this->status = $draft->status;
        $finalBody = ! empty($this->editedBody) ? $this->editedBody : $this->generatedBody;
        $this->dispatch('copy-to-clipboard', text: $finalBody);
        session()->flash('success', 'Copied to clipboard.');
    }

    public function markSent(SmartReplyDraftService $service): void
    {
        if (! $this->draftId) {
            return;
        }

        $draft = $service->markSent($this->draftId);
        $this->status = $draft->status;
        session()->flash('success', 'Marked as sent.');
    }

    public function deleteDraft(SmartReplyDraftService $service): void
    {
        if (! $this->draftId) {
            return;
        }

        $service->delete($this->draftId);
        session()->flash('success', 'Draft deleted successfully.');
        $this->redirect(route('admin.email.smart-reply.index'), navigate: true);
    }

    public function updatedTone(): void
    {
        if (! $this->draftId) {
            $this->generatedBody = '';
            $this->editedBody = '';
        }
    }

    public function render()
    {
        return view('livewire.admin.email.smart-reply.form');
    }
}
