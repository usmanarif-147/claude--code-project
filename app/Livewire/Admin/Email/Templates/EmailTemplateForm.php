<?php

namespace App\Livewire\Admin\Email\Templates;

use App\Models\EmailTemplate;
use App\Services\EmailTemplateService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class EmailTemplateForm extends Component
{
    public ?int $emailTemplateId = null;

    public string $name = '';

    public string $category = 'custom';

    public string $subject = '';

    public string $body = '';

    public bool $is_favorite = false;

    public int $sort_order = 0;

    public function mount(?EmailTemplate $emailTemplate = null): void
    {
        if ($emailTemplate && $emailTemplate->exists) {
            $this->emailTemplateId = $emailTemplate->id;
            $this->name = $emailTemplate->name;
            $this->category = $emailTemplate->category;
            $this->subject = $emailTemplate->subject ?? '';
            $this->body = $emailTemplate->body;
            $this->is_favorite = $emailTemplate->is_favorite;
            $this->sort_order = $emailTemplate->sort_order ?? 0;
        }
    }

    public function save(EmailTemplateService $service): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|in:interview_follow_up,freelance_proposal,thank_you,cold_outreach,custom',
            'subject' => 'nullable|string|max:500',
            'body' => 'required|string|max:10000',
            'is_favorite' => 'boolean',
            'sort_order' => 'integer|min:0|max:999',
        ]);

        if ($this->emailTemplateId) {
            $service->update($this->emailTemplateId, $validated);
            $message = 'Email template updated successfully.';
        } else {
            $service->create($validated);
            $message = 'Email template created successfully.';
        }

        session()->flash('success', $message);
        $this->redirect(route('admin.email.templates.index'), navigate: true);
    }

    public function render()
    {
        $service = app(EmailTemplateService::class);

        return view('livewire.admin.email.templates.form', [
            'categories' => $service->getCategories(),
        ]);
    }
}
