<?php

namespace App\Livewire\Admin\Portfolio\ResumeBuilder;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class ResumeBuilderIndex extends Component
{
    private const ITEM_LIMITS = [
        'jobs' => 3,
        'projects' => 3,
        'skill_groups' => 5,
        'strengths' => 6,
        'achievements' => 4,
        'educations' => 2,
        'job_bullets' => 3,
        'project_bullets' => 3,
        'skill_tags' => 8,
    ];

    private const FIELD_LIMITS = [
        'name' => [5, 40],
        'tagline' => [9, 70],
        'phone' => [null, 20],
        'email' => [null, 50],
        'location' => [4, 30],
        'github' => [null, 50],
        'profile' => [50, 350],
        'job.company' => [5, 40],
        'job.role' => [7, 50],
        'job.start' => [null, 20],
        'job.end' => [null, 20],
        'job.bullet' => [20, 130],
        'project.title' => [7, 60],
        'project.subtitle' => [9, 70],
        'project.bullet' => [20, 130],
        'project.tech' => [15, 100],
        'skill.category' => [4, 30],
        'skill.tag' => [3, 25],
        'strength' => [4, 30],
        'achievement' => [16, 110],
        'education.degree' => [9, 70],
        'education.institution' => [9, 70],
        'education.start' => [1, 9],
        'education.end' => [1, 9],
    ];

    public ?string $openModal = null;

    // ----- Formatting (Phase 2C): applied to entire resume -----
    public string $fontFamily = 'helvetica';

    public string $fontSize = '9pt';

    public bool $bold = false;

    public string $textColor = 'black';

    public string $textAlign = 'left';

    public string $lineSpacing = 'normal';

    public string $sectionSpacing = 'normal';
    // -----------------------------------------------------------

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
        if ($this->isRowKeyAtCap($key)) {
            return;
        }
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
        if (count($this->form['jobs'][$jobIndex]['bullets'] ?? []) >= self::ITEM_LIMITS['job_bullets']) {
            return;
        }
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
        if (count($this->form['projects'][$projectIndex]['bullets'] ?? []) >= self::ITEM_LIMITS['project_bullets']) {
            return;
        }
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
        if (count($this->form['groups'][$groupIndex]['tags'] ?? []) >= self::ITEM_LIMITS['skill_tags']) {
            return;
        }
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
        if (! $this->isFormValid()) {
            return;
        }

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

    public function isFormValid(): bool
    {
        if ($this->openModal === null) {
            return true;
        }

        return match ($this->openModal) {
            'header' => $this->checkFields([
                'name' => $this->form['name'] ?? '',
                'tagline' => $this->form['tagline'] ?? '',
                'phone' => $this->form['phone'] ?? '',
                'email' => $this->form['email'] ?? '',
                'location' => $this->form['location'] ?? '',
                'github' => $this->form['github'] ?? '',
            ]),
            'profile' => $this->checkFields([
                'profile' => $this->form['summary'] ?? '',
            ]),
            'work' => $this->checkJobs(),
            'projects' => $this->checkProjects(),
            'skills' => $this->checkSkills(),
            'strengths' => $this->checkSimpleList('strength', $this->form['items'] ?? []),
            'achievements' => $this->checkSimpleList('achievement', $this->form['items'] ?? []),
            'education' => $this->checkEducation(),
            default => true,
        };
    }

    private function checkFields(array $fields): bool
    {
        foreach ($fields as $limitKey => $value) {
            if ($this->isValueOverLimit((string) $value, self::FIELD_LIMITS[$limitKey] ?? [null, null])) {
                return false;
            }
        }

        return true;
    }

    private function checkJobs(): bool
    {
        foreach ($this->form['jobs'] ?? [] as $job) {
            $ok = $this->checkFields([
                'job.company' => $job['company'] ?? '',
                'job.role' => $job['role'] ?? '',
                'job.start' => $job['start'] ?? '',
                'job.end' => $job['end'] ?? '',
            ]);
            if (! $ok) {
                return false;
            }
            foreach ($job['bullets'] ?? [] as $bullet) {
                if ($this->isValueOverLimit((string) $bullet, self::FIELD_LIMITS['job.bullet'])) {
                    return false;
                }
            }
        }

        return true;
    }

    private function checkProjects(): bool
    {
        foreach ($this->form['projects'] ?? [] as $project) {
            $ok = $this->checkFields([
                'project.title' => $project['title'] ?? '',
                'project.subtitle' => $project['subtitle'] ?? '',
                'project.tech' => $project['tech'] ?? '',
            ]);
            if (! $ok) {
                return false;
            }
            foreach ($project['bullets'] ?? [] as $bullet) {
                if ($this->isValueOverLimit((string) $bullet, self::FIELD_LIMITS['project.bullet'])) {
                    return false;
                }
            }
        }

        return true;
    }

    private function checkSkills(): bool
    {
        foreach ($this->form['groups'] ?? [] as $group) {
            if ($this->isValueOverLimit((string) ($group['category'] ?? ''), self::FIELD_LIMITS['skill.category'])) {
                return false;
            }
            foreach ($group['tags'] ?? [] as $tag) {
                if ($this->isValueOverLimit((string) $tag, self::FIELD_LIMITS['skill.tag'])) {
                    return false;
                }
            }
        }

        return true;
    }

    private function checkSimpleList(string $limitKey, array $items): bool
    {
        foreach ($items as $item) {
            if ($this->isValueOverLimit((string) $item, self::FIELD_LIMITS[$limitKey])) {
                return false;
            }
        }

        return true;
    }

    private function checkEducation(): bool
    {
        foreach ($this->form['entries'] ?? [] as $entry) {
            $ok = $this->checkFields([
                'education.degree' => $entry['degree'] ?? '',
                'education.institution' => $entry['institution'] ?? '',
                'education.start' => $entry['start'] ?? '',
                'education.end' => $entry['end'] ?? '',
            ]);
            if (! $ok) {
                return false;
            }
        }

        return true;
    }

    private function isValueOverLimit(string $value, array $limit): bool
    {
        [$maxWords, $maxChars] = $limit;
        if ($maxChars !== null && mb_strlen($value) > $maxChars) {
            return true;
        }
        if ($maxWords !== null && $this->wordCount($value) > $maxWords) {
            return true;
        }

        return false;
    }

    private function wordCount(string $value): int
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return 0;
        }

        return count(preg_split('/\s+/', $trimmed));
    }

    private function isRowKeyAtCap(string $key): bool
    {
        $cap = match (true) {
            $this->openModal === 'work' && $key === 'jobs' => self::ITEM_LIMITS['jobs'],
            $this->openModal === 'projects' && $key === 'projects' => self::ITEM_LIMITS['projects'],
            $this->openModal === 'skills' && $key === 'groups' => self::ITEM_LIMITS['skill_groups'],
            $this->openModal === 'strengths' && $key === 'items' => self::ITEM_LIMITS['strengths'],
            $this->openModal === 'achievements' && $key === 'items' => self::ITEM_LIMITS['achievements'],
            $this->openModal === 'education' && $key === 'entries' => self::ITEM_LIMITS['educations'],
            default => null,
        };

        return $cap !== null && count($this->form[$key] ?? []) >= $cap;
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

    public function resetFormatting(): void
    {
        $this->fontFamily = 'helvetica';
        $this->fontSize = '9pt';
        $this->bold = false;
        $this->textColor = 'black';
        $this->textAlign = 'left';
        $this->lineSpacing = 'normal';
        $this->sectionSpacing = 'normal';
    }

    public function loadSampleData(): void
    {
        $data = require __DIR__.'/sample-data.php';

        $this->header = $data['header'] ?? [];
        $this->profile = (string) ($data['profile'] ?? '');
        $this->experiences = $data['experiences'] ?? [];
        $this->projects = $data['projects'] ?? [];
        $this->skillGroups = $data['skill_groups'] ?? [];
        $this->strengths = $data['strengths'] ?? [];
        $this->achievements = $data['achievements'] ?? [];
        $this->educations = $data['educations'] ?? [];
    }

    public function downloadPdf()
    {
        $pdf = Pdf::loadView('resume.templates.builder', [
            'header' => $this->header,
            'profile' => $this->profile,
            'experiences' => $this->experiences,
            'projects' => $this->projects,
            'skillGroups' => $this->skillGroups,
            'strengths' => $this->strengths,
            'achievements' => $this->achievements,
            'educations' => $this->educations,
            'fontFamily' => $this->fontFamily,
            'fontSize' => $this->fontSize,
            'bold' => $this->bold,
            'textColor' => $this->textColor,
            'textAlign' => $this->textAlign,
            'lineSpacing' => $this->lineSpacing,
            'sectionSpacing' => $this->sectionSpacing,
        ])->setPaper('A4', 'portrait');

        $name = trim((string) ($this->header['name'] ?? ''));
        $filename = $name !== '' ? Str::slug($name).'.pdf' : 'resume.pdf';

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            $filename
        );
    }

    public function render()
    {
        return view('livewire.admin.portfolio.resume-builder.index', [
            'itemLimits' => self::ITEM_LIMITS,
            'fieldLimits' => self::FIELD_LIMITS,
        ]);
    }

    /**
     * Limited-choice maps exposed to the toolbar view for rendering dropdowns / swatches.
     */
    public function formattingOptions(): array
    {
        return [
            'fontFamilies' => [
                'helvetica' => 'Helvetica',
                'dejavu' => 'DejaVu Sans',
                'times' => 'Times',
                'courier' => 'Courier',
            ],
            'fontSizes' => ['9pt', '10pt', '11pt', '12pt'],
            'textColors' => [
                'black' => '#1f2937',
                'gray' => '#4b5563',
                'blue' => '#1d4ed8',
                'red' => '#991b1b',
                'green' => '#15803d',
            ],
            'alignments' => ['left', 'center', 'right', 'justify'],
            'lineSpacings' => ['tight' => 'Tight', 'normal' => 'Normal', 'loose' => 'Loose'],
            'sectionSpacings' => ['compact' => 'Compact', 'normal' => 'Normal', 'spacious' => 'Spacious'],
        ];
    }
}
