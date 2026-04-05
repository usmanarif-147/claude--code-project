# Phase 3: Design Board — Improvement Spec

> Target file: `/home/usman/personal/laravel-projects/portfolio/docs/tasks-module/updates/phase3-design-board-spec.md`

Below is the complete spec content to be written to that file.

---

# Design Board — Improvement Spec (Phase 3)

## 1. UPDATE OVERVIEW

Add a new "Design Board" feature inside the Project Management module. The Design Board lets users create system design diagrams (ERD, class, UML, workflow, flowchart, system-design) using Mermaid.js text syntax with a live preview panel. It also supports AI-powered generation of diagrams from text descriptions, AI-powered generation of functional and non-functional requirements per project, and AI-powered task generation from diagrams and requirements that feeds directly into the existing Kanban board. Diagrams can be exported as PDF.

**Key principles:**
- Mermaid.js is the only diagramming engine — text editor + live preview, no canvas editor
- Diagram data is stored as plain Mermaid syntax text in the database — no images, no JSON
- AI is optional — every feature works manually without AI
- Uses Gemini (primary) and Groq (fallback) — both already integrated via ApiKey model and ApiKeyService
- Follows the same Gemini/Groq fallback pattern established in AiChatbotService

**Scope:** New feature only — no modifications to existing ProjectBoard, ProjectTask, Calendar, or WeeklyReview logic. Phase 2 renames (Tasks -> ProjectManagement) are assumed complete before this phase begins.

---

## 2. DATABASE SCHEMA

### 2.1 `diagrams` table

| Column | Type | Constraints |
|---|---|---|
| id | bigint unsigned | PK, auto-increment |
| board_id | bigint unsigned | FK -> project_boards.id, CASCADE on delete |
| user_id | bigint unsigned | FK -> users.id, CASCADE on delete |
| title | varchar(255) | NOT NULL |
| type | varchar(50) | NOT NULL, enum values: system-design, erd, class-diagram, uml, workflow, flowchart |
| mermaid_syntax | text | NULLABLE (empty when first created, filled by user or AI) |
| description | text | NULLABLE (text description used as AI input) |
| sort_order | unsigned integer | DEFAULT 0 |
| created_at | timestamp | NULLABLE |
| updated_at | timestamp | NULLABLE |

Indexes: `diagrams_board_id_foreign` on board_id, `diagrams_user_id_foreign` on user_id

**Notes:**
- `board_id` links to `project_boards` — each board (project) can have multiple diagrams
- `type` is stored as a string column validated at the application layer (matching existing pattern — ProjectTask stores priority as a string validated in rules)
- `description` stores the text description the user provides for AI generation — kept separate from Mermaid syntax so the user can regenerate without losing the original prompt
- `mermaid_syntax` stores the raw Mermaid text that renders the diagram

### 2.2 `project_requirements` table

| Column | Type | Constraints |
|---|---|---|
| id | bigint unsigned | PK, auto-increment |
| board_id | bigint unsigned | FK -> project_boards.id, CASCADE on delete |
| user_id | bigint unsigned | FK -> users.id, CASCADE on delete |
| type | varchar(20) | NOT NULL, enum values: functional, non-functional |
| title | varchar(255) | NOT NULL |
| description | text | NULLABLE |
| sort_order | unsigned integer | DEFAULT 0 |
| created_at | timestamp | NULLABLE |
| updated_at | timestamp | NULLABLE |

Indexes: `project_requirements_board_id_foreign` on board_id, `project_requirements_user_id_foreign` on user_id

---

## 3. NEW FILES TO CREATE

### 3.1 Migrations

```
database/migrations/YYYY_MM_DD_000001_create_diagrams_table.php
database/migrations/YYYY_MM_DD_000002_create_project_requirements_table.php
```

### 3.2 Models

```
app/Models/ProjectManagement/Diagram.php
app/Models/ProjectManagement/ProjectRequirement.php
```

**Diagram.php** relationships:
- `board()` — BelongsTo ProjectBoard (via `board_id`)
- `user()` — BelongsTo User

Scopes: `scopeForUser`, `scopeForBoard`, `scopeOrdered`, `scopeOfType`

Fillable: `board_id`, `user_id`, `title`, `type`, `mermaid_syntax`, `description`, `sort_order`

Casts: `sort_order` => integer

Constants: `TYPES = ['system-design', 'erd', 'class-diagram', 'uml', 'workflow', 'flowchart']`

**ProjectRequirement.php** relationships:
- `board()` — BelongsTo ProjectBoard (via `board_id`)
- `user()` — BelongsTo User

Scopes: `scopeForUser`, `scopeForBoard`, `scopeOrdered`, `scopeOfType`

Fillable: `board_id`, `user_id`, `type`, `title`, `description`, `sort_order`

Casts: `sort_order` => integer

Constants: `TYPES = ['functional', 'non-functional']`

### 3.3 Service

```
app/Services/DesignBoardService.php
```

### 3.4 Livewire Component

```
app/Livewire/Admin/ProjectManagement/DesignBoard/DesignBoardIndex.php
```

### 3.5 Blade View

```
resources/views/livewire/admin/project-management/design-board/index.blade.php
```

### 3.6 PDF Template

```
resources/views/project-management/pdf/diagram.blade.php
```

### 3.7 Route File

```
routes/admin/project-management/design-board.php
```

### 3.8 Export Controller (or extend existing)

```
app/Http/Controllers/DiagramExportController.php
```

### 3.9 Add relationship to existing ProjectBoard model

Add to `app/Models/ProjectManagement/ProjectBoard.php` (post Phase 2 rename):
- `diagrams()` — HasMany Diagram
- `requirements()` — HasMany ProjectRequirement

---

## 4. SERVICE METHODS — DesignBoardService

All business logic lives here. The Livewire component calls these methods — it never contains business logic directly.

```php
class DesignBoardService
{
    // ===== DIAGRAM CRUD =====

    /**
     * Get all diagrams for a board, ordered by sort_order.
     */
    public function getDiagrams(int $boardId): Collection;

    /**
     * Create a new diagram.
     * Sets sort_order to max + 1 for the board.
     */
    public function createDiagram(array $data): Diagram;

    /**
     * Update diagram title, type, mermaid_syntax, or description.
     */
    public function updateDiagram(Diagram $diagram, array $data): Diagram;

    /**
     * Delete a diagram.
     */
    public function deleteDiagram(Diagram $diagram): void;

    // ===== REQUIREMENTS CRUD =====

    /**
     * Get all requirements for a board, grouped by type, ordered by sort_order.
     */
    public function getRequirements(int $boardId): Collection;

    /**
     * Create a new requirement.
     */
    public function createRequirement(array $data): ProjectRequirement;

    /**
     * Update requirement title, description, or type.
     */
    public function updateRequirement(ProjectRequirement $req, array $data): ProjectRequirement;

    /**
     * Delete a requirement.
     */
    public function deleteRequirement(ProjectRequirement $req): void;

    // ===== AI DIAGRAM GENERATION =====

    /**
     * Generate Mermaid syntax from a text description using AI.
     * Uses Gemini (primary), Groq (fallback).
     *
     * @param string $description  Text description of what to diagram
     * @param string $diagramType  One of Diagram::TYPES
     * @return array{content: string, tokens: ?int, provider: string}
     * @throws \RuntimeException when both providers fail
     */
    public function generateDiagramFromText(int $userId, string $description, string $diagramType): array;

    // ===== AI REQUIREMENTS GENERATION =====

    /**
     * Generate functional and non-functional requirements from a project description.
     *
     * @param string $projectDescription  Text describing the project
     * @return array{requirements: array, tokens: ?int, provider: string}
     *   requirements = [ ['type' => 'functional|non-functional', 'title' => '...', 'description' => '...'], ... ]
     */
    public function generateRequirements(int $userId, string $projectDescription): array;

    // ===== AI TASK GENERATION =====

    /**
     * Generate task suggestions from all diagrams + requirements for a board.
     * Returns array of suggested tasks — does NOT create them yet (preview step).
     *
     * @return array{tasks: array, tokens: ?int, provider: string}
     *   tasks = [ ['title' => '...', 'description' => '...', 'priority' => 'medium', 'column' => 'Todo'], ... ]
     */
    public function generateTaskSuggestions(int $userId, int $boardId): array;

    /**
     * Create approved tasks in the Kanban board.
     * Uses ProjectTaskService->create() for each task.
     *
     * @param array $approvedTasks  Array of task data from generateTaskSuggestions (user-approved subset)
     * @param int $boardId
     * @param int $userId
     * @return int  Number of tasks created
     */
    public function createApprovedTasks(array $approvedTasks, int $boardId, int $userId): int;

    // ===== PDF EXPORT =====

    /**
     * Export a single diagram as PDF.
     * The PDF contains the diagram title, type, description, and the Mermaid syntax
     * rendered as a code block (Mermaid rendering happens client-side, so PDF shows syntax + metadata).
     */
    public function exportDiagramPdf(Diagram $diagram): \Symfony\Component\HttpFoundation\Response;

    /**
     * Export all diagrams for a board as a single PDF.
     */
    public function exportAllDiagramsPdf(int $boardId, int $userId): \Symfony\Component\HttpFoundation\Response;

    // ===== AI HELPERS (private) =====

    /**
     * Get Gemini or Groq API key for a user (Gemini primary, Groq fallback).
     * Follows the same pattern as AiChatbotService.
     */
    private function getAiApiKey(int $userId): ?array;

    /**
     * Call Gemini API with a prompt. Returns ['content' => string, 'tokens' => ?int, 'provider' => 'gemini'].
     */
    private function callGemini(string $apiKey, string $prompt): array;

    /**
     * Call Groq API with a prompt. Returns ['content' => string, 'tokens' => ?int, 'provider' => 'groq'].
     */
    private function callGroq(string $apiKey, string $prompt): array;

    /**
     * Estimate token count for a string (rough: chars / 4).
     */
    public function estimateTokens(string $text): int;
}
```

### AI API Call Pattern (from AiChatbotService)

The Gemini and Groq call methods follow the exact pattern from `AiChatbotService`:

**Gemini:**
- Endpoint: `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={apiKey}`
- Body: `{ "contents": [{ "role": "user", "parts": [{ "text": prompt }] }] }`
- Response: `$body['candidates'][0]['content']['parts'][0]['text']`
- Tokens: `$body['usageMetadata']['totalTokenCount']`

**Groq:**
- Endpoint: `https://api.groq.com/openai/v1/chat/completions`
- Headers: `Authorization: Bearer {apiKey}`
- Body: `{ "model": "llama-3.1-70b-versatile", "messages": [{ "role": "user", "content": prompt }], "max_tokens": 2048 }`
- Response: `$body['choices'][0]['message']['content']`
- Tokens: `$body['usage']['total_tokens']`

**Fallback pattern:**
```php
try {
    return $this->callGemini($geminiKey, $prompt);
} catch (\Throwable $e) {
    Log::warning('Gemini failed, falling back to Groq: ' . $e->getMessage());
}
try {
    return $this->callGroq($groqKey, $prompt);
} catch (\Throwable $e) {
    Log::error('Both AI providers failed: ' . $e->getMessage());
    throw new \RuntimeException('AI generation failed. Please try again or write manually.');
}
```

---

## 5. LIVEWIRE COMPONENT DESIGN — DesignBoardIndex

### Properties

```php
#[Layout('components.layouts.admin')]
class DesignBoardIndex extends Component
{
    // --- Board Selection (shared concept with ProjectBoardIndex) ---
    #[Url]
    public ?int $selectedBoardId = null;

    // --- Tab Navigation ---
    public string $activeTab = 'diagrams';  // 'diagrams' | 'requirements'

    // --- Diagram CRUD ---
    public bool $showDiagramModal = false;
    public ?int $editingDiagramId = null;
    public string $diagramTitle = '';
    public string $diagramType = 'flowchart';
    public string $diagramDescription = '';
    public string $diagramMermaidSyntax = '';

    // --- Diagram Editor (inline, shown when a diagram is selected for editing) ---
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
    public array $generatedTasks = [];  // Array of suggested tasks from AI
    public array $selectedTaskIndices = [];  // Which tasks the user has approved

    // --- Requirements AI ---
    public string $projectDescriptionForAi = '';  // Text input for AI requirements generation
}
```

### Methods

```php
// --- Lifecycle ---
public function mount(): void;  // Load first board if none selected (same pattern as ProjectBoardIndex)

// --- Board Selection ---
public function selectBoard(string $boardId): void;

// --- Tab Navigation ---
public function switchTab(string $tab): void;

// --- Diagram CRUD ---
public function openDiagramModal(?int $diagramId = null): void;
public function saveDiagram(DesignBoardService $service): void;  // Create or update
public function deleteDiagram(DesignBoardService $service, int $diagramId): void;

// --- Diagram Editor ---
public function openEditor(int $diagramId): void;  // Load diagram into editor panel
public function closeEditor(): void;
public function saveEditorContent(DesignBoardService $service): void;  // Save mermaid_syntax from editor

// --- AI Diagram Generation ---
public function generateDiagram(DesignBoardService $service): void;  // Generate Mermaid from description
public function getEstimatedTokens(): int;  // Token estimate for current description

// --- Requirement CRUD ---
public function openRequirementModal(?int $requirementId = null): void;
public function saveRequirement(DesignBoardService $service): void;
public function deleteRequirement(DesignBoardService $service, int $requirementId): void;

// --- AI Requirements Generation ---
public function generateRequirements(DesignBoardService $service): void;

// --- AI Task Generation ---
public function generateTasks(DesignBoardService $service): void;  // Generates preview
public function toggleTaskSelection(int $index): void;  // Toggle task in preview
public function selectAllTasks(): void;
public function deselectAllTasks(): void;
public function approveSelectedTasks(DesignBoardService $service): void;  // Creates tasks in Kanban
public function cancelTaskPreview(): void;

// --- PDF Export ---
public function exportDiagramPdf(int $diagramId): mixed;  // Download single diagram
public function exportAllDiagramsPdf(): mixed;  // Download all diagrams for board

// --- Render ---
public function render(DesignBoardService $service);
```

### Render Method Data

```php
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
```

---

## 6. VIEW DESIGN

### Layout Structure

```
┌─────────────────────────────────────────────────────────┐
│  Breadcrumb: Dashboard > Project Management > Design Board │
├─────────────────────────────────────────────────────────┤
│  Page Title: DESIGN BOARD          [Export All PDF btn]  │
│  Subtitle: Create diagrams and requirements              │
├─────────────────────────────────────────────────────────┤
│  Board Selector Dropdown (same boards as Project Board)  │
├─────────────────────────────────────────────────────────┤
│  Tab Bar: [Diagrams] [Requirements]  [Generate Tasks btn]│
├─────────────────────────────────────────────────────────┤
│                                                          │
│  === DIAGRAMS TAB ===                                    │
│  ┌──────────────────────────────────────────────────┐   │
│  │ Diagram Cards Grid (2 columns)                    │   │
│  │ ┌─────────────────┐  ┌─────────────────┐         │   │
│  │ │ ERD Diagram      │  │ Class Diagram   │         │   │
│  │ │ Type badge       │  │ Type badge      │         │   │
│  │ │ [Edit] [PDF] [X] │  │ [Edit] [PDF] [X]│         │   │
│  │ └─────────────────┘  └─────────────────┘         │   │
│  │              [+ Add Diagram]                      │   │
│  └──────────────────────────────────────────────────┘   │
│                                                          │
│  === EDITOR PANEL (shown when a diagram is selected) === │
│  ┌──────────────────────────────────────────────────┐   │
│  │ Split View (50/50):                               │   │
│  │ ┌────────────────┐  ┌────────────────┐            │   │
│  │ │ Text Editor     │  │ Mermaid Preview │            │   │
│  │ │ (textarea)      │  │ (live render)   │            │   │
│  │ │                 │  │                 │            │   │
│  │ │                 │  │                 │            │   │
│  │ └────────────────┘  └────────────────┘            │   │
│  │ Description input    [AI Generate] [Save] [Close]  │   │
│  │ Token count: ~1,250 tokens                         │   │
│  └──────────────────────────────────────────────────┘   │
│                                                          │
│  === REQUIREMENTS TAB ===                                │
│  ┌──────────────────────────────────────────────────┐   │
│  │ Functional Requirements                           │   │
│  │ ┌────────────────────────────────────────────┐    │   │
│  │ │ FR-1: User can register      [Edit] [X]    │    │   │
│  │ │ FR-2: User can login          [Edit] [X]    │    │   │
│  │ └────────────────────────────────────────────┘    │   │
│  │ Non-Functional Requirements                       │   │
│  │ ┌────────────────────────────────────────────┐    │   │
│  │ │ NFR-1: Response time < 200ms  [Edit] [X]   │    │   │
│  │ └────────────────────────────────────────────┘    │   │
│  │ [+ Add Requirement]                               │   │
│  │ ─── AI Section ───                                │   │
│  │ Project description textarea                      │   │
│  │ Token count: ~800 tokens                          │   │
│  │ [Generate Requirements with AI]                   │   │
│  └──────────────────────────────────────────────────┘   │
│                                                          │
│  === TASK GENERATION PREVIEW (modal/panel) ===           │
│  ┌──────────────────────────────────────────────────┐   │
│  │ Generated Tasks (12 tasks)   [Select All]         │   │
│  │ ☑ Task 1: Create user model    Priority: High     │   │
│  │ ☑ Task 2: Design login page    Priority: Medium   │   │
│  │ ☐ Task 3: Setup CI/CD          Priority: Low      │   │
│  │        [Approve Selected (10)] [Cancel]            │   │
│  └──────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────┘
```

### Component Patterns Used

- **Board selector:** Dropdown `<select>` with `wire:change="selectBoard($event.target.value)"` — same pattern as ProjectBoardIndex
- **Tab navigation:** Two tab buttons with `wire:click="switchTab('diagrams')"` — active tab uses `bg-primary/10 text-primary-light` styling
- **Diagram cards:** `bg-dark-800 border border-dark-700 rounded-xl p-5` — standard card pattern
- **Editor panel:** Two-column grid inside a card. Left: `<textarea>` for Mermaid syntax. Right: `<div>` where Mermaid.js renders the preview
- **Mermaid.js rendering:** Use Alpine.js `x-effect` to watch the textarea value and re-render the Mermaid diagram on the right panel. Load Mermaid.js via CDN (`<script src="https://cdn.jsdelivr.net/npm/mermaid/dist/mermaid.min.js"></script>`)
- **AI buttons:** Primary button with `wire:click` and `wire:loading` for spinner state. Disabled when `!$hasAiKey`
- **Token count:** Small `text-xs text-gray-500` label below description fields showing `~{{ estimatedTokens }} tokens`
- **Task preview modal:** Standard modal overlay with checkbox list, select all/none buttons, and approve/cancel
- **Requirements list:** Grouped into two sections (functional/non-functional) with inline edit/delete buttons

### Mermaid.js Integration (Alpine.js)

```blade
{{-- In the editor panel --}}
<div x-data="{
    syntax: @entangle('editorMermaidSyntax'),
    async renderDiagram() {
        const el = this.$refs.preview;
        if (!this.syntax || !this.syntax.trim()) {
            el.innerHTML = '<p class=\'text-gray-500 text-sm\'>Enter Mermaid syntax to see preview</p>';
            return;
        }
        try {
            el.innerHTML = '';
            const { svg } = await mermaid.render('diagram-preview', this.syntax);
            el.innerHTML = svg;
        } catch (e) {
            el.innerHTML = '<p class=\'text-red-400 text-sm\'>Invalid Mermaid syntax</p>';
        }
    }
}"
x-effect="renderDiagram()"
x-init="mermaid.initialize({ startOnLoad: false, theme: 'dark' })">
    <textarea x-model="syntax" wire:model.live.debounce.500ms="editorMermaidSyntax" ...></textarea>
    <div x-ref="preview" class="bg-dark-700 rounded-lg p-4 min-h-[300px] overflow-auto"></div>
</div>
```

---

## 7. AI INTERACTION DESIGN

### 7.1 Provider Selection

Same as AiChatbotService — Gemini primary, Groq fallback:
1. Check for connected Gemini API key via `ApiKey::forProvider(ApiKey::PROVIDER_GEMINI)->connected()->first()`
2. If Gemini unavailable or fails, check for Groq key
3. If both fail, throw RuntimeException caught by Livewire component which flashes an error

### 7.2 Prompt Templates

**Diagram Generation Prompt:**
```
You are a software architect. Generate a Mermaid.js diagram based on the following description.

Diagram type: {diagramType}
Description: {description}

Rules:
- Output ONLY valid Mermaid syntax — no markdown code fences, no explanations, no extra text
- The diagram must be of type: {mermaidDiagramType}
- Keep the diagram clear and readable
- Use meaningful node labels
- For ERD diagrams, use erDiagram syntax
- For class diagrams, use classDiagram syntax
- For flowcharts, use flowchart TD syntax
- For sequence/UML diagrams, use sequenceDiagram syntax
- For system design, use flowchart TD with subgraphs for components
```

Type mapping for Mermaid syntax:
- `system-design` -> `flowchart TD` (with subgraphs)
- `erd` -> `erDiagram`
- `class-diagram` -> `classDiagram`
- `uml` -> `sequenceDiagram`
- `workflow` -> `flowchart LR`
- `flowchart` -> `flowchart TD`

**Requirements Generation Prompt:**
```
You are a software requirements analyst. Based on the following project description, generate a structured list of functional and non-functional requirements.

Project Description: {projectDescription}

Rules:
- Output valid JSON only — no markdown, no explanations
- Format: [{"type": "functional", "title": "Short title", "description": "Detailed description"}, ...]
- Include 5-10 functional requirements and 3-5 non-functional requirements
- Functional requirements describe WHAT the system should do
- Non-functional requirements describe HOW the system should perform (security, performance, scalability, etc.)
- Keep titles concise (under 80 characters)
- Keep descriptions to 1-2 sentences
```

**Task Generation Prompt:**
```
You are a project manager. Based on the following system diagrams and requirements, generate actionable development tasks for a Kanban board.

{diagramsSection}

{requirementsSection}

Rules:
- Output valid JSON only — no markdown, no explanations
- Format: [{"title": "Task title", "description": "What needs to be done", "priority": "low|medium|high|urgent"}, ...]
- Break down the work into small, actionable tasks (2-4 hours of work each)
- Assign appropriate priorities based on dependencies and importance
- Include tasks for all major components shown in the diagrams
- Include tasks derived from requirements
- Order tasks logically (foundations first, features second, polish last)
- Generate between 10-30 tasks depending on project complexity
```

### 7.3 Token Count Estimation

Before each AI call, display an estimated token count:
- Rough estimate: `ceil(strlen($text) / 4)` — standard approximation for English text
- Show in the UI as: `~{count} tokens` in `text-xs text-gray-500`
- For task generation, calculate the combined size of all diagrams + requirements for the board
- Warn if estimated tokens exceed 10,000: show amber warning "Large prompt — AI may truncate or fail on free tier"

### 7.4 Error Handling

- Both providers fail: Flash error message "AI generation is unavailable. You can write content manually."
- No API key configured: Disable AI buttons, show tooltip "Configure Gemini or Groq API key in Settings"
- Rate limited: Flash warning "AI rate limit reached. Please wait a moment and try again."
- Invalid JSON response from requirements/task generation: Flash error, log the raw response for debugging
- All errors are caught in the Livewire component with try/catch, never crash the page

### 7.5 Response Parsing

For requirements and task generation, AI returns JSON. Parsing strategy:
1. Attempt `json_decode($content, true)`
2. If that fails, try extracting JSON from between `[` and `]` in case the AI wrapped it in markdown
3. If that fails, log warning and return empty array with flash error

---

## 8. SIDEBAR NAVIGATION

After Phase 2 renames, the sidebar "Project Management" group will contain:
- Project Board
- **Design Board** (NEW — inserted here, between Project Board and Calendar)
- Calendar
- Weekly Review

The Design Board link follows the exact same pattern as existing sidebar items:

```blade
<a href="{{ route('admin.project-management.design-board.index') }}"
   wire:navigate
   class="flex items-center gap-3 pl-10 pr-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.project-management.design-board.*') ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white hover:bg-dark-700' }} transition-colors">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
    </svg>
    Design Board
</a>
```

Icon: A layout/design icon (template/grid) to visually distinguish from the Kanban board icon.

---

## 9. IMPLEMENTATION ORDER

### Step 1: Database Migrations
1. Create `diagrams` table migration
2. Create `project_requirements` table migration
3. Run migrations: `docker compose exec app php artisan migrate`

### Step 2: Models
4. Create `app/Models/ProjectManagement/Diagram.php` with relationships, scopes, fillable, casts, and TYPES constant
5. Create `app/Models/ProjectManagement/ProjectRequirement.php` with relationships, scopes, fillable, casts, and TYPES constant
6. Add `diagrams()` and `requirements()` HasMany relationships to `ProjectBoard.php`

### Step 3: Service
7. Create `app/Services/DesignBoardService.php` with all CRUD methods first (no AI yet)
8. Add AI helper methods (`getAiApiKey`, `callGemini`, `callGroq`, `estimateTokens`)
9. Add `generateDiagramFromText` method with prompt template
10. Add `generateRequirements` method with prompt template and JSON parsing
11. Add `generateTaskSuggestions` method with prompt template and JSON parsing
12. Add `createApprovedTasks` method (uses `ProjectTaskService::create()`)
13. Add `exportDiagramPdf` and `exportAllDiagramsPdf` methods (reuse DomPDF pattern from `ProjectBoardExportService`)

### Step 4: Route
14. Create `routes/admin/project-management/design-board.php` with route to `DesignBoardIndex`
15. Create `app/Http/Controllers/DiagramExportController.php` for PDF download route (non-Livewire download)

### Step 5: Livewire Component
16. Create `app/Livewire/Admin/ProjectManagement/DesignBoard/DesignBoardIndex.php`
17. Implement mount, board selection, tab switching
18. Implement diagram CRUD methods
19. Implement requirement CRUD methods
20. Implement AI generation methods with loading states
21. Implement task generation preview and approval flow

### Step 6: View
22. Create `resources/views/livewire/admin/project-management/design-board/index.blade.php`
23. Build layout: breadcrumb, page header, board selector, tab navigation
24. Build diagrams tab: card grid, add button, editor panel with Mermaid.js
25. Build requirements tab: grouped list, add/edit forms, AI generation section
26. Build task preview modal
27. Add Mermaid.js CDN script and Alpine.js rendering logic

### Step 7: PDF Template
28. Create `resources/views/project-management/pdf/diagram.blade.php` for diagram PDF export

### Step 8: Sidebar
29. Add "Design Board" link to sidebar in `resources/views/components/layouts/admin.blade.php` under Project Management group, between Project Board and Calendar

### Step 9: Testing & Polish
30. Manual test: create diagram manually, verify Mermaid preview renders
31. Manual test: AI diagram generation with Gemini
32. Manual test: AI requirements generation
33. Manual test: AI task generation -> preview -> approve -> verify tasks in Kanban
34. Manual test: PDF export for single diagram and all diagrams
35. Run `docker compose exec app ./vendor/bin/pint` to format code

---

## 10. EDGE CASES & RISKS

### Large Mermaid Syntax Rendering
- **Risk:** Very complex diagrams (100+ nodes) may slow down client-side rendering
- **Mitigation:** Debounce the live preview (500ms delay via `wire:model.live.debounce.500ms`). If rendering takes > 3 seconds, show a warning. The `mermaid.render()` call is wrapped in try/catch so syntax errors show a friendly message instead of crashing

### AI Token Limits on Free Tier
- **Risk:** Gemini free tier: ~15 RPM, ~1M tokens/day. Groq free tier: ~30 RPM, ~6K tokens/min. Task generation prompts with many diagrams could be large
- **Mitigation:** Show token estimate before AI calls. Warn if prompt exceeds 10,000 tokens. Keep prompts focused — only send diagram syntax and requirement titles/descriptions, not metadata. Truncate individual diagram syntax to 3,000 chars if needed

### Mermaid.js Library Loading
- **Risk:** CDN outage could break the preview
- **Mitigation:** Load Mermaid.js from jsDelivr CDN (`https://cdn.jsdelivr.net/npm/mermaid/dist/mermaid.min.js`). This is sufficient for a personal portfolio tool. If CDN fails, the text editor still works — only the preview panel is affected. Could later switch to npm + Vite bundle if needed

### AI Returning Invalid Mermaid Syntax
- **Risk:** AI-generated Mermaid syntax might not render correctly
- **Mitigation:** The generated syntax is placed in the editor where the user can see and fix it. Mermaid.js shows parse errors in the preview panel. The user always has manual control

### AI Returning Invalid JSON (Requirements/Tasks)
- **Risk:** Requirements and task generation ask AI for JSON, which it may wrap in markdown or produce malformed
- **Mitigation:** Strip markdown code fences before parsing. Try extracting JSON array between first `[` and last `]`. If parsing fails, flash an error and log the raw response. User can retry or add requirements/tasks manually

### PDF Export Limitations
- **Risk:** DomPDF cannot render Mermaid.js diagrams (they require JavaScript). PDF will show the Mermaid syntax as a code block, not a rendered image
- **Mitigation:** The PDF includes: diagram title, type, description, and the Mermaid syntax formatted as a code block. For visual export, users can screenshot the preview or copy the Mermaid syntax into external tools. This is an acceptable tradeoff for Phase 3 — rendered diagram images in PDF could be added later using a headless browser (Puppeteer/Browsershot)

### No Board Selected / Empty State
- **Risk:** User navigates to Design Board with no project boards created
- **Mitigation:** Show an empty state message: "No project boards found. Create a board in the Project Board page first." with a link to the Project Board page

### Concurrent AI Calls
- **Risk:** User rapidly clicking AI generate buttons
- **Mitigation:** Loading state flags (`isGeneratingDiagram`, `isGeneratingRequirements`, `isGeneratingTasks`) disable the buttons during generation. Livewire's built-in request queueing prevents double-sends

---

## SUMMARY OF ALL NEW FILES

| # | File Path | Type |
|---|---|---|
| 1 | `database/migrations/YYYY_MM_DD_000001_create_diagrams_table.php` | Migration |
| 2 | `database/migrations/YYYY_MM_DD_000002_create_project_requirements_table.php` | Migration |
| 3 | `app/Models/ProjectManagement/Diagram.php` | Model |
| 4 | `app/Models/ProjectManagement/ProjectRequirement.php` | Model |
| 5 | `app/Services/DesignBoardService.php` | Service |
| 6 | `app/Livewire/Admin/ProjectManagement/DesignBoard/DesignBoardIndex.php` | Livewire Component |
| 7 | `resources/views/livewire/admin/project-management/design-board/index.blade.php` | Blade View |
| 8 | `resources/views/project-management/pdf/diagram.blade.php` | PDF Template |
| 9 | `routes/admin/project-management/design-board.php` | Route |
| 10 | `app/Http/Controllers/DiagramExportController.php` | Controller |

## EXISTING FILES TO MODIFY

| # | File Path | Change |
|---|---|---|
| 1 | `app/Models/ProjectManagement/ProjectBoard.php` | Add `diagrams()` and `requirements()` HasMany relationships |
| 2 | `resources/views/components/layouts/admin.blade.php` | Add Design Board sidebar link under Project Management |
