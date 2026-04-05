<?php

namespace App\Livewire\Admin\ProjectManagement\DesignBoard;

use App\Models\ProjectManagement\Diagram;
use App\Models\ProjectManagement\ProjectBoard;
use App\Models\ProjectManagement\ProjectRequirement;
use App\Services\DesignBoardService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class DesignBoardIndex extends Component
{
    // --- Board Selection ---
    #[Url]
    public ?int $selectedBoardId = null;

    // --- Tab Navigation ---
    public string $activeTab = 'diagrams';

    // --- Diagram CRUD ---
    public bool $showDiagramModal = false;

    public ?int $editingDiagramId = null;

    public string $diagramTitle = '';

    public string $diagramType = 'flowchart';

    public string $diagramDescription = '';

    public string $diagramMermaidSyntax = '';

    // --- Diagram Editor ---
    public ?int $activeDiagramId = null;

    public string $editorMermaidSyntax = '';

    public string $editorDescription = '';

    // --- Requirement CRUD ---
    public bool $showRequirementModal = false;

    public ?int $editingRequirementId = null;

    public string $requirementType = 'functional';

    public string $requirementTitle = '';

    public string $requirementDescription = '';

    // --- AI States ---
    public bool $isGeneratingDiagram = false;

    public bool $isGeneratingRequirements = false;

    public bool $isGeneratingTasks = false;

    public ?int $lastAiTokenCount = null;

    public ?string $lastAiProvider = null;

    // --- Task Generation Preview ---
    public bool $showTaskPreview = false;

    public array $generatedTasks = [];

    public array $selectedTaskIndices = [];

    // --- Requirements AI ---
    public string $projectDescriptionForAi = '';

    public function mount(): void
    {
        if (! $this->selectedBoardId) {
            $board = ProjectBoard::query()
                ->forUser(auth()->id())
                ->ordered()
                ->first();

            if ($board) {
                $this->selectedBoardId = $board->id;
            }
        }
    }

    public function selectBoard(string $boardId): void
    {
        $this->selectedBoardId = (int) $boardId;
        $this->closeEditor();
        $this->resetDiagramForm();
        $this->resetRequirementForm();
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->closeEditor();
    }

    // --- Diagram CRUD ---

    public function openDiagramModal(?int $diagramId = null): void
    {
        $this->resetDiagramForm();

        if ($diagramId) {
            $diagram = Diagram::findOrFail($diagramId);
            $this->editingDiagramId = $diagram->id;
            $this->diagramTitle = $diagram->title;
            $this->diagramType = $diagram->type;
            $this->diagramDescription = $diagram->description ?? '';
            $this->diagramMermaidSyntax = $diagram->mermaid_syntax ?? '';
        }

        $this->showDiagramModal = true;
    }

    public function saveDiagram(DesignBoardService $service): void
    {
        $this->validate([
            'diagramTitle' => 'required|string|max:255',
            'diagramType' => 'required|in:'.implode(',', Diagram::TYPES),
            'diagramDescription' => 'nullable|string|max:5000',
            'diagramMermaidSyntax' => 'nullable|string',
        ]);

        if ($this->editingDiagramId) {
            $diagram = Diagram::findOrFail($this->editingDiagramId);
            $service->updateDiagram($diagram, [
                'title' => $this->diagramTitle,
                'type' => $this->diagramType,
                'description' => $this->diagramDescription ?: null,
                'mermaid_syntax' => $this->diagramMermaidSyntax ?: null,
            ]);
            session()->flash('success', 'Diagram updated successfully.');
        } else {
            $service->createDiagram([
                'board_id' => $this->selectedBoardId,
                'user_id' => auth()->id(),
                'title' => $this->diagramTitle,
                'type' => $this->diagramType,
                'description' => $this->diagramDescription ?: null,
                'mermaid_syntax' => $this->diagramMermaidSyntax ?: null,
            ]);
            session()->flash('success', 'Diagram created successfully.');
        }

        $this->showDiagramModal = false;
        $this->resetDiagramForm();
    }

    public function deleteDiagram(DesignBoardService $service, int $diagramId): void
    {
        $diagram = Diagram::findOrFail($diagramId);
        $service->deleteDiagram($diagram);

        if ($this->activeDiagramId === $diagramId) {
            $this->closeEditor();
        }

        session()->flash('success', 'Diagram deleted successfully.');
    }

    // --- Diagram Editor ---

    public function openEditor(int $diagramId): void
    {
        $diagram = Diagram::findOrFail($diagramId);
        $this->activeDiagramId = $diagram->id;
        $this->editorMermaidSyntax = $diagram->mermaid_syntax ?? '';
        $this->editorDescription = $diagram->description ?? '';
    }

    public function closeEditor(): void
    {
        $this->activeDiagramId = null;
        $this->editorMermaidSyntax = '';
        $this->editorDescription = '';
    }

    public function saveEditorContent(DesignBoardService $service): void
    {
        if (! $this->activeDiagramId) {
            return;
        }

        $diagram = Diagram::findOrFail($this->activeDiagramId);
        $service->updateDiagram($diagram, [
            'mermaid_syntax' => $this->editorMermaidSyntax ?: null,
            'description' => $this->editorDescription ?: null,
        ]);

        session()->flash('success', 'Diagram content saved.');
    }

    // --- AI Diagram Generation ---

    public function generateDiagram(DesignBoardService $service): void
    {
        if (! $this->activeDiagramId || ! $this->editorDescription) {
            session()->flash('error', 'Please enter a description to generate a diagram.');

            return;
        }

        $this->isGeneratingDiagram = true;

        try {
            $diagram = Diagram::findOrFail($this->activeDiagramId);
            $result = $service->generateDiagramFromText(
                auth()->id(),
                $this->editorDescription,
                $diagram->type
            );

            $this->editorMermaidSyntax = $result['content'];
            $this->lastAiTokenCount = $result['tokens'];
            $this->lastAiProvider = $result['provider'];

            session()->flash('success', 'Diagram generated via '.ucfirst($result['provider']).'.');
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        } finally {
            $this->isGeneratingDiagram = false;
        }
    }

    public function getEstimatedTokens(): int
    {
        $service = app(DesignBoardService::class);

        return $service->estimateTokens($this->editorDescription);
    }

    // --- Requirement CRUD ---

    public function openRequirementModal(?int $requirementId = null): void
    {
        $this->resetRequirementForm();

        if ($requirementId) {
            $req = ProjectRequirement::findOrFail($requirementId);
            $this->editingRequirementId = $req->id;
            $this->requirementType = $req->type;
            $this->requirementTitle = $req->title;
            $this->requirementDescription = $req->description ?? '';
        }

        $this->showRequirementModal = true;
    }

    public function saveRequirement(DesignBoardService $service): void
    {
        $this->validate([
            'requirementType' => 'required|in:'.implode(',', ProjectRequirement::TYPES),
            'requirementTitle' => 'required|string|max:255',
            'requirementDescription' => 'nullable|string|max:5000',
        ]);

        if ($this->editingRequirementId) {
            $req = ProjectRequirement::findOrFail($this->editingRequirementId);
            $service->updateRequirement($req, [
                'type' => $this->requirementType,
                'title' => $this->requirementTitle,
                'description' => $this->requirementDescription ?: null,
            ]);
            session()->flash('success', 'Requirement updated successfully.');
        } else {
            $service->createRequirement([
                'board_id' => $this->selectedBoardId,
                'user_id' => auth()->id(),
                'type' => $this->requirementType,
                'title' => $this->requirementTitle,
                'description' => $this->requirementDescription ?: null,
            ]);
            session()->flash('success', 'Requirement created successfully.');
        }

        $this->showRequirementModal = false;
        $this->resetRequirementForm();
    }

    public function deleteRequirement(DesignBoardService $service, int $requirementId): void
    {
        $req = ProjectRequirement::findOrFail($requirementId);
        $service->deleteRequirement($req);

        session()->flash('success', 'Requirement deleted successfully.');
    }

    // --- AI Requirements Generation ---

    public function generateRequirements(DesignBoardService $service): void
    {
        if (! $this->projectDescriptionForAi) {
            session()->flash('error', 'Please enter a project description.');

            return;
        }

        $this->isGeneratingRequirements = true;

        try {
            $result = $service->generateRequirements(auth()->id(), $this->projectDescriptionForAi);

            if (empty($result['requirements'])) {
                session()->flash('error', 'AI returned empty results. Please try again or add requirements manually.');

                return;
            }

            foreach ($result['requirements'] as $req) {
                $service->createRequirement([
                    'board_id' => $this->selectedBoardId,
                    'user_id' => auth()->id(),
                    'type' => $req['type'] ?? 'functional',
                    'title' => $req['title'] ?? 'Untitled',
                    'description' => $req['description'] ?? null,
                ]);
            }

            $this->lastAiTokenCount = $result['tokens'];
            $this->lastAiProvider = $result['provider'];

            session()->flash('success', count($result['requirements']).' requirements generated via '.ucfirst($result['provider']).'.');
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        } finally {
            $this->isGeneratingRequirements = false;
        }
    }

    // --- AI Task Generation ---

    public function generateTasks(DesignBoardService $service): void
    {
        if (! $this->selectedBoardId) {
            return;
        }

        $this->isGeneratingTasks = true;

        try {
            $result = $service->generateTaskSuggestions(auth()->id(), $this->selectedBoardId);

            if (empty($result['tasks'])) {
                session()->flash('error', 'AI returned no tasks. Add more diagrams or requirements and try again.');

                return;
            }

            $this->generatedTasks = $result['tasks'];
            $this->selectedTaskIndices = array_keys($result['tasks']);
            $this->lastAiTokenCount = $result['tokens'];
            $this->lastAiProvider = $result['provider'];
            $this->showTaskPreview = true;
        } catch (\Throwable $e) {
            session()->flash('error', $e->getMessage());
        } finally {
            $this->isGeneratingTasks = false;
        }
    }

    public function toggleTaskSelection(int $index): void
    {
        if (in_array($index, $this->selectedTaskIndices)) {
            $this->selectedTaskIndices = array_values(array_diff($this->selectedTaskIndices, [$index]));
        } else {
            $this->selectedTaskIndices[] = $index;
        }
    }

    public function selectAllTasks(): void
    {
        $this->selectedTaskIndices = array_keys($this->generatedTasks);
    }

    public function deselectAllTasks(): void
    {
        $this->selectedTaskIndices = [];
    }

    public function approveSelectedTasks(DesignBoardService $service): void
    {
        if (empty($this->selectedTaskIndices)) {
            session()->flash('error', 'No tasks selected.');

            return;
        }

        $approvedTasks = [];
        foreach ($this->selectedTaskIndices as $index) {
            if (isset($this->generatedTasks[$index])) {
                $approvedTasks[] = $this->generatedTasks[$index];
            }
        }

        $created = $service->createApprovedTasks($approvedTasks, $this->selectedBoardId, auth()->id());

        $this->showTaskPreview = false;
        $this->generatedTasks = [];
        $this->selectedTaskIndices = [];

        session()->flash('success', $created.' tasks created in the project board.');
    }

    public function cancelTaskPreview(): void
    {
        $this->showTaskPreview = false;
        $this->generatedTasks = [];
        $this->selectedTaskIndices = [];
    }

    public function render(DesignBoardService $service)
    {
        $userId = auth()->id();
        $boards = ProjectBoard::query()->forUser($userId)->ordered()->get();

        $diagrams = collect();
        $requirements = collect();

        if ($this->selectedBoardId) {
            $diagrams = $service->getDiagrams($this->selectedBoardId);
            $requirements = $service->getRequirements($this->selectedBoardId);
        }

        $hasAiKey = $service->getAiApiKey($userId) !== null;

        return view('livewire.admin.project-management.design-board.index', [
            'boards' => $boards,
            'diagrams' => $diagrams,
            'requirements' => $requirements,
            'hasAiKey' => $hasAiKey,
            'diagramTypes' => Diagram::TYPES,
        ]);
    }

    private function resetDiagramForm(): void
    {
        $this->editingDiagramId = null;
        $this->diagramTitle = '';
        $this->diagramType = 'flowchart';
        $this->diagramDescription = '';
        $this->diagramMermaidSyntax = '';
    }

    private function resetRequirementForm(): void
    {
        $this->editingRequirementId = null;
        $this->requirementType = 'functional';
        $this->requirementTitle = '';
        $this->requirementDescription = '';
    }
}
