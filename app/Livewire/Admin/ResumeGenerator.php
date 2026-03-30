<?php

namespace App\Livewire\Admin;

use App\Services\ResumeService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class ResumeGenerator extends Component
{
    public string $selectedTemplate = 'modern';

    public string $previewHtml = '';

    public function mount(ResumeService $service): void
    {
        $this->previewHtml = $service->generateHtml($this->selectedTemplate);
    }

    public function updatedSelectedTemplate(ResumeService $service): void
    {
        $this->previewHtml = $service->generateHtml($this->selectedTemplate);
    }

    public function render(ResumeService $service)
    {
        $data = $service->getResumeData();

        return view('livewire.admin.resume-generator', [
            'templates' => $service->getAvailableTemplates(),
            'skillCount' => $data['skills']->count(),
            'experienceCount' => $data['workExperience']->count(),
            'projectCount' => $data['projects']->count(),
        ]);
    }
}
