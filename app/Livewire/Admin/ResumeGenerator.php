<?php

namespace App\Livewire\Admin;

use App\Services\ResumeService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
class ResumeGenerator extends Component
{
    use WithFileUploads;

    // Carousel
    public int $currentTemplateIndex = 0;

    public string $previewHtml = '';

    // Modal state
    public string $activeModal = '';

    // Personal Info
    public string $editName = '';

    public string $editEmail = '';

    public string $editTagline = '';

    public string $editPhone = '';

    public string $editLocation = '';

    public string $editLinkedin = '';

    public string $editGithub = '';

    // About
    public string $editBio = '';

    // Experience
    public ?int $editExperienceId = null;

    public string $editExpRole = '';

    public string $editExpCompany = '';

    public string $editExpStartDate = '';

    public string $editExpEndDate = '';

    public bool $editExpIsCurrent = false;

    public string $editExpDescription = '';

    public array $editExpResponsibilities = [];

    // Education
    public ?int $editEducationId = null;

    public string $editEduDegree = '';

    public string $editEduField = '';

    public string $editEduCompany = '';

    public string $editEduStartDate = '';

    public string $editEduEndDate = '';

    public bool $editEduIsCurrent = false;

    // Skills
    public array $editSkills = [];

    public string $newSkillTitle = '';

    public string $newSkillCategory = '';

    // Technologies
    public array $editTechnologies = [];

    public string $newTechName = '';

    public string $newTechCategory = '';

    // Projects
    public ?int $editProjectId = null;

    public string $editProjTitle = '';

    public string $editProjShortDescription = '';

    public string $editProjDescription = '';

    public array $editProjTechStack = [];

    public string $newTechStackItem = '';

    // AI uploads
    public $templateScreenshot;

    public $resumeFile;

    public bool $isProcessing = false;

    public string $processingMessage = '';

    // Parsed resume data (preview before confirm)
    public array $parsedResumeData = [];

    public function mount(ResumeService $service): void
    {
        $keys = $service->getAllTemplateKeys();
        $this->currentTemplateIndex = 0;
        $template = $keys[$this->currentTemplateIndex] ?? 'modern';
        $this->previewHtml = $service->generateHtml($template);
    }

    // ─── Carousel ────────────────────────────────────────────────

    public function nextTemplate(ResumeService $service): void
    {
        $keys = $service->getAllTemplateKeys();
        $this->currentTemplateIndex = ($this->currentTemplateIndex + 1) % count($keys);
        $this->refreshPreview($service);
    }

    public function prevTemplate(ResumeService $service): void
    {
        $keys = $service->getAllTemplateKeys();
        $this->currentTemplateIndex = ($this->currentTemplateIndex - 1 + count($keys)) % count($keys);
        $this->refreshPreview($service);
    }

    public function selectTemplate(int $index, ResumeService $service): void
    {
        $keys = $service->getAllTemplateKeys();
        if ($index >= 0 && $index < count($keys)) {
            $this->currentTemplateIndex = $index;
            $this->refreshPreview($service);
        }
    }

    public function refreshPreview(ResumeService $service): void
    {
        $keys = $service->getAllTemplateKeys();
        $template = $keys[$this->currentTemplateIndex] ?? 'modern';

        try {
            $this->previewHtml = $service->generateHtml($template);
        } catch (\Throwable $e) {
            $this->previewHtml = '<html><body style="padding:40px;font-family:sans-serif;color:#666"><h2>Template Error</h2><p>'.e($e->getMessage()).'</p></body></html>';
        }
    }

    // ─── Modal Management ────────────────────────────────────────

    public function openModal(string $section, ResumeService $service): void
    {
        $this->activeModal = $section;
        $data = $service->getResumeData();

        match ($section) {
            'personal' => $this->loadPersonalInfo($data),
            'about' => $this->editBio = $data['profile']->bio ?? '',
            'experience' => $this->resetExperienceForm(),
            'education' => $this->resetEducationForm(),
            'skills' => $this->loadSkills($data),
            'technologies' => $this->loadTechnologies($data),
            'projects' => $this->resetProjectForm(),
            default => null,
        };
    }

    public function closeModal(): void
    {
        $this->activeModal = '';
        $this->resetValidation();
    }

    // ─── Personal Info ───────────────────────────────────────────

    private function loadPersonalInfo(array $data): void
    {
        $this->editName = $data['user']->name ?? '';
        $this->editEmail = $data['user']->email ?? '';
        $this->editTagline = $data['profile']->tagline ?? '';
        $this->editPhone = $data['profile']->phone ?? '';
        $this->editLocation = $data['profile']->location ?? '';
        $this->editLinkedin = $data['profile']->linkedin_url ?? '';
        $this->editGithub = $data['profile']->github_url ?? '';
    }

    public function savePersonalInfo(ResumeService $service): void
    {
        $this->validate([
            'editName' => 'required|string|max:255',
            'editEmail' => 'required|email|max:255',
            'editTagline' => 'nullable|string|max:255',
            'editPhone' => 'nullable|string|max:50',
            'editLocation' => 'nullable|string|max:255',
            'editLinkedin' => 'nullable|url|max:500',
            'editGithub' => 'nullable|url|max:500',
        ]);

        $service->updatePersonalInfo([
            'name' => $this->editName,
            'email' => $this->editEmail,
            'tagline' => $this->editTagline,
            'phone' => $this->editPhone,
            'location' => $this->editLocation,
            'linkedin_url' => $this->editLinkedin,
            'github_url' => $this->editGithub,
        ]);

        session()->flash('success', 'Personal info updated.');
        $this->refreshPreview($service);
        $this->closeModal();
    }

    // ─── About ───────────────────────────────────────────────────

    public function saveAbout(ResumeService $service): void
    {
        $this->validate([
            'editBio' => 'nullable|string|max:5000',
        ]);

        $service->updateAbout($this->editBio);

        session()->flash('success', 'About section updated.');
        $this->refreshPreview($service);
        $this->closeModal();
    }

    // ─── Experience ──────────────────────────────────────────────

    private function resetExperienceForm(): void
    {
        $this->editExperienceId = null;
        $this->editExpRole = '';
        $this->editExpCompany = '';
        $this->editExpStartDate = '';
        $this->editExpEndDate = '';
        $this->editExpIsCurrent = false;
        $this->editExpDescription = '';
        $this->editExpResponsibilities = [''];
    }

    public function editExperienceItem(int $id): void
    {
        $exp = \App\Models\Experience\Experience::with('responsibilities')->findOrFail($id);
        $this->editExperienceId = $exp->id;
        $this->editExpRole = $exp->role ?? '';
        $this->editExpCompany = $exp->company ?? '';
        $this->editExpStartDate = $exp->start_date?->format('Y-m-d') ?? '';
        $this->editExpEndDate = $exp->end_date?->format('Y-m-d') ?? '';
        $this->editExpIsCurrent = (bool) $exp->is_current;
        $this->editExpDescription = $exp->description ?? '';
        $this->editExpResponsibilities = $exp->responsibilities->pluck('description')->toArray();
        if (empty($this->editExpResponsibilities)) {
            $this->editExpResponsibilities = [''];
        }
    }

    public function newExperienceItem(): void
    {
        $this->resetExperienceForm();
    }

    public function addResponsibility(): void
    {
        $this->editExpResponsibilities[] = '';
    }

    public function removeResponsibility(int $index): void
    {
        unset($this->editExpResponsibilities[$index]);
        $this->editExpResponsibilities = array_values($this->editExpResponsibilities);
        if (empty($this->editExpResponsibilities)) {
            $this->editExpResponsibilities = [''];
        }
    }

    public function saveExperience(ResumeService $service): void
    {
        $this->validate([
            'editExpRole' => 'required|string|max:255',
            'editExpCompany' => 'required|string|max:255',
            'editExpStartDate' => 'required|date',
            'editExpEndDate' => 'nullable|date',
            'editExpIsCurrent' => 'boolean',
            'editExpDescription' => 'nullable|string|max:5000',
        ]);

        $service->saveExperienceItem([
            'role' => $this->editExpRole,
            'company' => $this->editExpCompany,
            'start_date' => $this->editExpStartDate,
            'end_date' => $this->editExpEndDate,
            'is_current' => $this->editExpIsCurrent,
            'description' => $this->editExpDescription,
            'responsibilities' => array_filter($this->editExpResponsibilities),
        ], $this->editExperienceId);

        session()->flash('success', $this->editExperienceId ? 'Experience updated.' : 'Experience added.');
        $this->resetExperienceForm();
        $this->refreshPreview($service);
    }

    public function deleteExperience(int $id, ResumeService $service): void
    {
        \App\Models\Experience\Experience::findOrFail($id)->delete();
        session()->flash('success', 'Experience deleted.');
        $this->refreshPreview($service);
    }

    // ─── Education ───────────────────────────────────────────────

    private function resetEducationForm(): void
    {
        $this->editEducationId = null;
        $this->editEduDegree = '';
        $this->editEduField = '';
        $this->editEduCompany = '';
        $this->editEduStartDate = '';
        $this->editEduEndDate = '';
        $this->editEduIsCurrent = false;
    }

    public function editEducationItem(int $id): void
    {
        $edu = \App\Models\Experience\Experience::findOrFail($id);
        $this->editEducationId = $edu->id;
        $this->editEduDegree = $edu->degree ?? $edu->role ?? '';
        $this->editEduField = $edu->field_of_study ?? '';
        $this->editEduCompany = $edu->company ?? '';
        $this->editEduStartDate = $edu->start_date?->format('Y-m-d') ?? '';
        $this->editEduEndDate = $edu->end_date?->format('Y-m-d') ?? '';
        $this->editEduIsCurrent = (bool) $edu->is_current;
    }

    public function newEducationItem(): void
    {
        $this->resetEducationForm();
    }

    public function saveEducation(ResumeService $service): void
    {
        $this->validate([
            'editEduDegree' => 'required|string|max:255',
            'editEduField' => 'nullable|string|max:255',
            'editEduCompany' => 'required|string|max:255',
            'editEduStartDate' => 'required|date',
            'editEduEndDate' => 'nullable|date',
            'editEduIsCurrent' => 'boolean',
        ]);

        $service->saveEducationItem([
            'degree' => $this->editEduDegree,
            'field_of_study' => $this->editEduField,
            'company' => $this->editEduCompany,
            'start_date' => $this->editEduStartDate,
            'end_date' => $this->editEduEndDate,
            'is_current' => $this->editEduIsCurrent,
        ], $this->editEducationId);

        session()->flash('success', $this->editEducationId ? 'Education updated.' : 'Education added.');
        $this->resetEducationForm();
        $this->refreshPreview($service);
    }

    public function deleteEducation(int $id, ResumeService $service): void
    {
        \App\Models\Experience\Experience::findOrFail($id)->delete();
        session()->flash('success', 'Education entry deleted.');
        $this->refreshPreview($service);
    }

    // ─── Skills ──────────────────────────────────────────────────

    private function loadSkills(array $data): void
    {
        $this->editSkills = $data['skills']->map(fn ($s) => [
            'id' => $s->id,
            'title' => $s->title,
            'category' => $s->category ?? '',
            'proficiency' => $s->proficiency ?? 80,
        ])->toArray();
        $this->newSkillTitle = '';
        $this->newSkillCategory = '';
    }

    public function addSkill(ResumeService $service): void
    {
        $this->validate([
            'newSkillTitle' => 'required|string|max:255',
            'newSkillCategory' => 'nullable|string|max:100',
        ]);

        $this->editSkills[] = [
            'title' => $this->newSkillTitle,
            'category' => $this->newSkillCategory,
            'proficiency' => 80,
        ];
        $this->newSkillTitle = '';
        $this->newSkillCategory = '';
    }

    public function removeSkill(int $index): void
    {
        if (isset($this->editSkills[$index]['id'])) {
            \App\Models\Skill::where('id', $this->editSkills[$index]['id'])->delete();
        }
        unset($this->editSkills[$index]);
        $this->editSkills = array_values($this->editSkills);
    }

    public function saveSkills(ResumeService $service): void
    {
        $service->syncSkills($this->editSkills);
        session()->flash('success', 'Skills updated.');
        $this->refreshPreview($service);
        $this->closeModal();
    }

    // ─── Technologies ────────────────────────────────────────────

    private function loadTechnologies(array $data): void
    {
        $allTechs = [];
        foreach ($data['technologies'] as $category => $techs) {
            foreach ($techs as $tech) {
                $allTechs[] = [
                    'id' => $tech->id,
                    'name' => $tech->name,
                    'category' => $tech->category ?? $category,
                ];
            }
        }
        $this->editTechnologies = $allTechs;
        $this->newTechName = '';
        $this->newTechCategory = '';
    }

    public function addTechnology(ResumeService $service): void
    {
        $this->validate([
            'newTechName' => 'required|string|max:255',
            'newTechCategory' => 'required|string|max:100',
        ]);

        $this->editTechnologies[] = [
            'name' => $this->newTechName,
            'category' => $this->newTechCategory,
        ];
        $this->newTechName = '';
        $this->newTechCategory = '';
    }

    public function removeTechnology(int $index): void
    {
        if (isset($this->editTechnologies[$index]['id'])) {
            \App\Models\Technology::where('id', $this->editTechnologies[$index]['id'])->delete();
        }
        unset($this->editTechnologies[$index]);
        $this->editTechnologies = array_values($this->editTechnologies);
    }

    public function saveTechnologies(ResumeService $service): void
    {
        $service->syncTechnologies($this->editTechnologies);
        session()->flash('success', 'Technologies updated.');
        $this->refreshPreview($service);
        $this->closeModal();
    }

    // ─── Projects ────────────────────────────────────────────────

    private function resetProjectForm(): void
    {
        $this->editProjectId = null;
        $this->editProjTitle = '';
        $this->editProjShortDescription = '';
        $this->editProjDescription = '';
        $this->editProjTechStack = [];
        $this->newTechStackItem = '';
    }

    public function editProjectItem(int $id): void
    {
        $project = \App\Models\Project\Project::findOrFail($id);
        $this->editProjectId = $project->id;
        $this->editProjTitle = $project->title ?? '';
        $this->editProjShortDescription = $project->short_description ?? '';
        $this->editProjDescription = $project->description ?? '';
        $this->editProjTechStack = $project->tech_stack ?? [];
        $this->newTechStackItem = '';
    }

    public function newProjectItem(): void
    {
        $this->resetProjectForm();
    }

    public function addTechStackItem(): void
    {
        if (! empty($this->newTechStackItem)) {
            $this->editProjTechStack[] = $this->newTechStackItem;
            $this->newTechStackItem = '';
        }
    }

    public function removeTechStackItem(int $index): void
    {
        unset($this->editProjTechStack[$index]);
        $this->editProjTechStack = array_values($this->editProjTechStack);
    }

    public function saveProject(ResumeService $service): void
    {
        $this->validate([
            'editProjTitle' => 'required|string|max:255',
            'editProjShortDescription' => 'nullable|string|max:1000',
            'editProjDescription' => 'nullable|string|max:5000',
        ]);

        $service->saveProjectItem([
            'title' => $this->editProjTitle,
            'short_description' => $this->editProjShortDescription,
            'description' => $this->editProjDescription,
            'tech_stack' => $this->editProjTechStack,
        ], $this->editProjectId);

        session()->flash('success', $this->editProjectId ? 'Project updated.' : 'Project added.');
        $this->resetProjectForm();
        $this->refreshPreview($service);
    }

    public function deleteProject(int $id, ResumeService $service): void
    {
        \App\Models\Project\Project::findOrFail($id)->delete();
        session()->flash('success', 'Project deleted.');
        $this->refreshPreview($service);
    }

    // ─── AI: Upload Template Screenshot ──────────────────────────

    public function uploadTemplateScreenshot(ResumeService $service): void
    {
        $this->validate([
            'templateScreenshot' => 'required|image|mimes:png,jpg,jpeg,webp|max:5120',
        ]);

        $this->isProcessing = true;
        $this->processingMessage = 'AI is analyzing the screenshot and generating a template...';

        try {
            $imageData = base64_encode(file_get_contents($this->templateScreenshot->getRealPath()));
            $mimeType = $this->templateScreenshot->getMimeType();

            $bladeHtml = $service->generateTemplateFromScreenshot($imageData, $mimeType);
            $templateKey = $service->saveAiTemplate($bladeHtml);

            $keys = $service->getAllTemplateKeys();
            $this->currentTemplateIndex = array_search($templateKey, $keys) ?: 0;

            $this->refreshPreview($service);
            session()->flash('success', 'AI template generated and added to carousel.');
            $this->closeModal();
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        } finally {
            $this->isProcessing = false;
            $this->processingMessage = '';
            $this->templateScreenshot = null;
        }
    }

    // ─── AI: Upload Resume Details ───────────────────────────────

    public function uploadResumeDetails(ResumeService $service): void
    {
        $this->validate([
            'resumeFile' => 'required|file|mimes:txt,json,pdf|max:5120',
        ]);

        $this->isProcessing = true;
        $this->processingMessage = 'AI is parsing your resume details...';

        try {
            $fileType = $this->resumeFile->getClientOriginalExtension();

            if ($fileType === 'pdf') {
                $content = $service->extractPdfText($this->resumeFile->getRealPath());
            } else {
                $content = file_get_contents($this->resumeFile->getRealPath());
            }

            $parsed = $service->parseResumeDetails($content, $fileType);
            $this->parsedResumeData = $parsed;
            $this->activeModal = 'preview-details';
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        } finally {
            $this->isProcessing = false;
            $this->processingMessage = '';
            $this->resumeFile = null;
        }
    }

    public function confirmImportDetails(ResumeService $service): void
    {
        try {
            // Filter out nulls from removed items
            $data = $this->parsedResumeData;
            foreach (['skills', 'technologies', 'experiences', 'education', 'projects'] as $key) {
                if (isset($data[$key])) {
                    $data[$key] = array_values(array_filter($data[$key]));
                }
            }

            $summary = $service->importParsedData($data);
            $this->parsedResumeData = [];
            $this->refreshPreview($service);
            session()->flash('success', 'Resume details imported: '.implode(', ', $summary));
            $this->closeModal();
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function discardParsedData(): void
    {
        $this->parsedResumeData = [];
        $this->activeModal = 'upload-details';
    }

    // ─── Delete Custom Template ──────────────────────────────────

    public function deleteCustomTemplate(string $key, ResumeService $service): void
    {
        try {
            $service->deleteCustomTemplate($key);
            $this->currentTemplateIndex = 0;
            $this->refreshPreview($service);
            session()->flash('success', 'Custom template deleted.');
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    // ─── Render ──────────────────────────────────────────────────

    public function render(ResumeService $service)
    {
        $data = $service->getResumeData();
        $templateKeys = $service->getAllTemplateKeys();

        return view('livewire.admin.resume-generator', [
            'templateKeys' => $templateKeys,
            'templateCount' => count($templateKeys),
            'currentTemplateName' => $templateKeys[$this->currentTemplateIndex] ?? 'modern',
            'isCustomTemplate' => ! $service->isBuiltInTemplate($templateKeys[$this->currentTemplateIndex] ?? 'modern'),
            'skillCount' => $data['skills']->count(),
            'technologyCount' => count($data['technologies'], COUNT_RECURSIVE) - count($data['technologies']),
            'experienceCount' => $data['workExperience']->count(),
            'educationCount' => $data['education']->count(),
            'projectCount' => $data['projects']->count(),
            'experiences' => $data['workExperience'],
            'educationList' => $data['education'],
            'projects' => $data['projects'],
        ]);
    }
}
