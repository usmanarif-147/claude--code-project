<?php

namespace App\Livewire\Admin\Portfolio\ResumeBuilder;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class ResumeBuilderIndex extends Component
{
    public ?string $openModal = null;

    public array $header = [];

    public string $profile = '';

    public array $experiences = [];

    public array $projects = [];

    public array $skillGroups = [];

    public array $strengths = [];

    public array $achievements = [];

    public array $educations = [];

    public array $form = [];

    public function openSection(string $section): void
    {
        $this->openModal = $section;
        $this->form = $this->initialFormFor($section);
    }

    public function closeSection(): void
    {
        $this->openModal = null;
        $this->form = [];
    }

    public function addRow(string $key): void
    {
        $this->form[$key][] = $this->blankRowFor($this->openModal, $key);
    }

    public function removeRow(string $key, int $index): void
    {
        if (isset($this->form[$key][$index])) {
            array_splice($this->form[$key], $index, 1);
        }
    }

    public function addBulletToJob(int $jobIndex): void
    {
        $this->form['jobs'][$jobIndex]['bullets'][] = '';
    }

    public function removeBulletFromJob(int $jobIndex, int $bulletIndex): void
    {
        if (isset($this->form['jobs'][$jobIndex]['bullets'][$bulletIndex])) {
            array_splice($this->form['jobs'][$jobIndex]['bullets'], $bulletIndex, 1);
        }
    }

    public function addBulletToProject(int $projectIndex): void
    {
        $this->form['projects'][$projectIndex]['bullets'][] = '';
    }

    public function removeBulletFromProject(int $projectIndex, int $bulletIndex): void
    {
        if (isset($this->form['projects'][$projectIndex]['bullets'][$bulletIndex])) {
            array_splice($this->form['projects'][$projectIndex]['bullets'], $bulletIndex, 1);
        }
    }

    public function addTagToGroup(int $groupIndex): void
    {
        $this->form['groups'][$groupIndex]['tags'][] = '';
    }

    public function removeTagFromGroup(int $groupIndex, int $tagIndex): void
    {
        if (isset($this->form['groups'][$groupIndex]['tags'][$tagIndex])) {
            array_splice($this->form['groups'][$groupIndex]['tags'], $tagIndex, 1);
        }
    }

    public function save(): void
    {
        match ($this->openModal) {
            'header' => $this->header = $this->form,
            'profile' => $this->profile = (string) ($this->form['summary'] ?? ''),
            'work' => $this->experiences = array_values($this->form['jobs'] ?? []),
            'projects' => $this->projects = array_values($this->form['projects'] ?? []),
            'skills' => $this->skillGroups = array_values($this->form['groups'] ?? []),
            'strengths' => $this->strengths = array_values(array_filter($this->form['items'] ?? [], fn ($v) => trim((string) $v) !== '')),
            'achievements' => $this->achievements = array_values(array_filter($this->form['items'] ?? [], fn ($v) => trim((string) $v) !== '')),
            'education' => $this->educations = array_values($this->form['entries'] ?? []),
            default => null,
        };

        $this->closeSection();
    }

    private function initialFormFor(string $section): array
    {
        return match ($section) {
            'header' => $this->header !== [] ? $this->header : [
                'name' => '',
                'tagline' => '',
                'phone' => '',
                'email' => '',
                'location' => '',
                'github' => '',
            ],
            'profile' => ['summary' => $this->profile],
            'work' => ['jobs' => $this->experiences !== [] ? $this->experiences : [$this->blankJob()]],
            'projects' => ['projects' => $this->projects !== [] ? $this->projects : [$this->blankProject()]],
            'skills' => ['groups' => $this->skillGroups !== [] ? $this->skillGroups : [$this->blankSkillGroup()]],
            'strengths' => ['items' => $this->strengths !== [] ? $this->strengths : ['']],
            'achievements' => ['items' => $this->achievements !== [] ? $this->achievements : ['']],
            'education' => ['entries' => $this->educations !== [] ? $this->educations : [$this->blankEducation()]],
            default => [],
        };
    }

    private function blankRowFor(?string $section, string $key): array|string
    {
        return match (true) {
            $section === 'work' && $key === 'jobs' => $this->blankJob(),
            $section === 'projects' && $key === 'projects' => $this->blankProject(),
            $section === 'skills' && $key === 'groups' => $this->blankSkillGroup(),
            $section === 'education' && $key === 'entries' => $this->blankEducation(),
            default => '',
        };
    }

    private function blankJob(): array
    {
        return [
            'company' => '',
            'role' => '',
            'start' => '',
            'end' => '',
            'is_current' => false,
            'bullets' => [''],
        ];
    }

    private function blankProject(): array
    {
        return [
            'title' => '',
            'subtitle' => '',
            'bullets' => [''],
            'tech' => '',
        ];
    }

    private function blankSkillGroup(): array
    {
        return [
            'category' => '',
            'tags' => [''],
        ];
    }

    private function blankEducation(): array
    {
        return [
            'degree' => '',
            'institution' => '',
            'start' => '',
            'end' => '',
        ];
    }

    public function render()
    {
        return view('livewire.admin.portfolio.resume-builder.index');
    }
}
