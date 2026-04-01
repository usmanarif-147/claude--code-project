<?php

namespace App\Livewire\Admin\Email\Templates;

use App\Services\EmailTemplateService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class EmailTemplateIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $filterCategory = '';

    public int $perPage = 10;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCategory(): void
    {
        $this->resetPage();
    }

    public function delete(EmailTemplateService $service, int $id): void
    {
        $service->delete($id);
        session()->flash('success', 'Email template deleted successfully.');
    }

    public function toggleFavorite(EmailTemplateService $service, int $id): void
    {
        $service->toggleFavorite($id);
    }

    public function copyToClipboard(EmailTemplateService $service, int $id): void
    {
        $template = $service->getById($id);
        $service->markUsed($id);

        $content = $template->subject
            ? "Subject: {$template->subject}\n\n{$template->body}"
            : $template->body;

        $this->dispatch('copy-to-clipboard', content: $content, id: $id);
    }

    public function render()
    {
        $service = app(EmailTemplateService::class);

        return view('livewire.admin.email.templates.index', [
            'templates' => $service->getAll($this->search, $this->filterCategory, $this->perPage),
            'categories' => $service->getCategories(),
        ]);
    }
}
