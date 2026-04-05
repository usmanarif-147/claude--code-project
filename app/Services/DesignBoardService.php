<?php

namespace App\Services;

use App\Models\ApiKey;
use App\Models\ProjectManagement\Diagram;
use App\Models\ProjectManagement\ProjectBoard;
use App\Models\ProjectManagement\ProjectBoardColumn;
use App\Models\ProjectManagement\ProjectRequirement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DesignBoardService
{
    // ===== DIAGRAM CRUD =====

    /**
     * Get all diagrams for a board, ordered by sort_order.
     */
    public function getDiagrams(int $boardId): Collection
    {
        return Diagram::query()
            ->forBoard($boardId)
            ->ordered()
            ->get();
    }

    /**
     * Create a new diagram. Sets sort_order to max + 1 for the board.
     */
    public function createDiagram(array $data): Diagram
    {
        $maxSortOrder = Diagram::where('board_id', $data['board_id'])->max('sort_order') ?? -1;
        $data['sort_order'] = $maxSortOrder + 1;

        return Diagram::create($data);
    }

    /**
     * Update diagram title, type, mermaid_syntax, or description.
     */
    public function updateDiagram(Diagram $diagram, array $data): Diagram
    {
        $diagram->update($data);

        return $diagram;
    }

    /**
     * Delete a diagram.
     */
    public function deleteDiagram(Diagram $diagram): void
    {
        $diagram->delete();
    }

    // ===== REQUIREMENTS CRUD =====

    /**
     * Get all requirements for a board, grouped by type, ordered by sort_order.
     */
    public function getRequirements(int $boardId): Collection
    {
        return ProjectRequirement::query()
            ->forBoard($boardId)
            ->ordered()
            ->get();
    }

    /**
     * Create a new requirement.
     */
    public function createRequirement(array $data): ProjectRequirement
    {
        $maxSortOrder = ProjectRequirement::where('board_id', $data['board_id'])->max('sort_order') ?? -1;
        $data['sort_order'] = $maxSortOrder + 1;

        return ProjectRequirement::create($data);
    }

    /**
     * Update requirement title, description, or type.
     */
    public function updateRequirement(ProjectRequirement $req, array $data): ProjectRequirement
    {
        $req->update($data);

        return $req;
    }

    /**
     * Delete a requirement.
     */
    public function deleteRequirement(ProjectRequirement $req): void
    {
        $req->delete();
    }

    // ===== AI DIAGRAM GENERATION =====

    /**
     * Generate Mermaid syntax from a text description using AI.
     *
     * @return array{content: string, tokens: ?int, provider: string}
     *
     * @throws \RuntimeException when both providers fail
     */
    public function generateDiagramFromText(int $userId, string $description, string $diagramType): array
    {
        $mermaidTypeMap = [
            'system-design' => 'flowchart TD',
            'erd' => 'erDiagram',
            'class-diagram' => 'classDiagram',
            'uml' => 'sequenceDiagram',
            'workflow' => 'flowchart LR',
            'flowchart' => 'flowchart TD',
        ];

        $mermaidDiagramType = $mermaidTypeMap[$diagramType] ?? 'flowchart TD';

        $prompt = <<<PROMPT
You are a software architect. Generate a Mermaid.js diagram based on the following description.

Diagram type: {$diagramType}
Description: {$description}

Rules:
- Output ONLY valid Mermaid syntax — no markdown code fences, no explanations, no extra text
- The diagram must be of type: {$mermaidDiagramType}
- Keep the diagram clear and readable
- Use meaningful node labels
- For ERD diagrams, use erDiagram syntax
- For class diagrams, use classDiagram syntax
- For flowcharts, use flowchart TD syntax
- For sequence/UML diagrams, use sequenceDiagram syntax
- For system design, use flowchart TD with subgraphs for components
PROMPT;

        return $this->callAiWithFallback($prompt);
    }

    // ===== AI REQUIREMENTS GENERATION =====

    /**
     * Generate functional and non-functional requirements from a project description.
     *
     * @return array{requirements: array, tokens: ?int, provider: string}
     */
    public function generateRequirements(int $userId, string $projectDescription): array
    {
        $prompt = <<<PROMPT
You are a software requirements analyst. Based on the following project description, generate a structured list of functional and non-functional requirements.

Project Description: {$projectDescription}

Rules:
- Output valid JSON only — no markdown, no explanations
- Format: [{"type": "functional", "title": "Short title", "description": "Detailed description"}, ...]
- Include 5-10 functional requirements and 3-5 non-functional requirements
- Functional requirements describe WHAT the system should do
- Non-functional requirements describe HOW the system should perform (security, performance, scalability, etc.)
- Keep titles concise (under 80 characters)
- Keep descriptions to 1-2 sentences
PROMPT;

        $result = $this->callAiWithFallback($prompt);

        $requirements = $this->parseJsonResponse($result['content']);

        return [
            'requirements' => $requirements,
            'tokens' => $result['tokens'],
            'provider' => $result['provider'],
        ];
    }

    // ===== AI TASK GENERATION =====

    /**
     * Generate task suggestions from all diagrams + requirements for a board.
     *
     * @return array{tasks: array, tokens: ?int, provider: string}
     */
    public function generateTaskSuggestions(int $userId, int $boardId): array
    {
        $diagrams = Diagram::query()->forBoard($boardId)->ordered()->get();
        $requirements = ProjectRequirement::query()->forBoard($boardId)->ordered()->get();

        $diagramsSection = "=== DIAGRAMS ===\n";
        foreach ($diagrams as $diagram) {
            $syntax = mb_substr($diagram->mermaid_syntax ?? '', 0, 3000);
            $diagramsSection .= "--- {$diagram->title} ({$diagram->type}) ---\n";
            $diagramsSection .= $syntax."\n\n";
        }

        $requirementsSection = "=== REQUIREMENTS ===\n";
        foreach ($requirements as $req) {
            $requirementsSection .= "- [{$req->type}] {$req->title}: {$req->description}\n";
        }

        $prompt = <<<PROMPT
You are a project manager. Based on the following system diagrams and requirements, generate actionable development tasks for a Kanban board.

{$diagramsSection}

{$requirementsSection}

Rules:
- Output valid JSON only — no markdown, no explanations
- Format: [{"title": "Task title", "description": "What needs to be done", "priority": "low|medium|high|urgent"}, ...]
- Break down the work into small, actionable tasks (2-4 hours of work each)
- Assign appropriate priorities based on dependencies and importance
- Include tasks for all major components shown in the diagrams
- Include tasks derived from requirements
- Order tasks logically (foundations first, features second, polish last)
- Generate between 10-30 tasks depending on project complexity
PROMPT;

        $result = $this->callAiWithFallback($prompt);

        $tasks = $this->parseJsonResponse($result['content']);

        return [
            'tasks' => $tasks,
            'tokens' => $result['tokens'],
            'provider' => $result['provider'],
        ];
    }

    /**
     * Create approved tasks in the Kanban board using ProjectTaskService.
     */
    public function createApprovedTasks(array $approvedTasks, int $boardId, int $userId): int
    {
        $taskService = app(ProjectTaskService::class);

        // Find the first column (Todo or first available) for the board
        $column = ProjectBoardColumn::where('board_id', $boardId)
            ->orderBy('sort_order')
            ->skip(1) // Skip "New", use "Todo" as default
            ->first();

        if (! $column) {
            $column = ProjectBoardColumn::where('board_id', $boardId)
                ->orderBy('sort_order')
                ->first();
        }

        if (! $column) {
            return 0;
        }

        $created = 0;
        foreach ($approvedTasks as $task) {
            $taskService->create([
                'board_id' => $boardId,
                'column_id' => $column->id,
                'user_id' => $userId,
                'title' => $task['title'] ?? 'Untitled Task',
                'description' => $task['description'] ?? null,
                'priority' => $task['priority'] ?? 'medium',
            ]);
            $created++;
        }

        return $created;
    }

    // ===== PDF EXPORT =====

    /**
     * Export a single diagram as PDF.
     */
    public function exportDiagramPdf(Diagram $diagram): \Symfony\Component\HttpFoundation\Response
    {
        $data = [
            'diagrams' => collect([$diagram]),
            'title' => $diagram->title,
            'generatedAt' => now()->format('M j, Y g:i A'),
        ];

        $filename = str_replace(' ', '_', $diagram->title).'_Diagram.pdf';

        return Pdf::loadView('project-management.pdf.diagram', $data)
            ->setPaper('a4')
            ->download($filename);
    }

    /**
     * Export all diagrams for a board as a single PDF.
     */
    public function exportAllDiagramsPdf(int $boardId, int $userId): \Symfony\Component\HttpFoundation\Response
    {
        $board = ProjectBoard::query()
            ->forUser($userId)
            ->findOrFail($boardId);

        $diagrams = Diagram::query()
            ->forBoard($boardId)
            ->ordered()
            ->get();

        $data = [
            'diagrams' => $diagrams,
            'title' => $board->name.' — All Diagrams',
            'generatedAt' => now()->format('M j, Y g:i A'),
        ];

        $filename = str_replace(' ', '_', $board->name).'_All_Diagrams.pdf';

        return Pdf::loadView('project-management.pdf.diagram', $data)
            ->setPaper('a4')
            ->download($filename);
    }

    // ===== AI HELPERS =====

    /**
     * Get AI API key — Gemini primary, Groq fallback.
     *
     * @return array{provider: string, key: string}|null
     */
    public function getAiApiKey(int $userId): ?array
    {
        $gemini = ApiKey::query()
            ->forProvider(ApiKey::PROVIDER_GEMINI)
            ->connected()
            ->first();

        if ($gemini) {
            return ['provider' => 'gemini', 'key' => $gemini->key_value];
        }

        $groq = ApiKey::query()
            ->forProvider(ApiKey::PROVIDER_GROQ)
            ->connected()
            ->first();

        if ($groq) {
            return ['provider' => 'groq', 'key' => $groq->key_value];
        }

        return null;
    }

    /**
     * Estimate token count for a string (rough: chars / 4).
     */
    public function estimateTokens(string $text): int
    {
        return (int) ceil(strlen($text) / 4);
    }

    /**
     * Call AI with Gemini primary, Groq fallback.
     *
     * @return array{content: string, tokens: ?int, provider: string}
     *
     * @throws \RuntimeException
     */
    private function callAiWithFallback(string $prompt): array
    {
        $geminiKey = ApiKey::query()
            ->forProvider(ApiKey::PROVIDER_GEMINI)
            ->connected()
            ->first();

        if ($geminiKey) {
            try {
                return $this->callGemini($geminiKey->key_value, $prompt);
            } catch (\Throwable $e) {
                Log::warning('Gemini failed, falling back to Groq: '.$e->getMessage());
            }
        }

        $groqKey = ApiKey::query()
            ->forProvider(ApiKey::PROVIDER_GROQ)
            ->connected()
            ->first();

        if ($groqKey) {
            try {
                return $this->callGroq($groqKey->key_value, $prompt);
            } catch (\Throwable $e) {
                Log::error('Both AI providers failed: '.$e->getMessage());
                throw new \RuntimeException('AI generation failed. Please try again or write manually.');
            }
        }

        throw new \RuntimeException('No AI API keys configured. Please add a Gemini or Groq key in Settings.');
    }

    /**
     * Call Gemini API with a prompt.
     *
     * @return array{content: string, tokens: ?int, provider: string}
     */
    private function callGemini(string $apiKey, string $prompt): array
    {
        $response = Http::timeout(30)
            ->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key='.$apiKey, [
                'contents' => [
                    [
                        'role' => 'user',
                        'parts' => [['text' => $prompt]],
                    ],
                ],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Gemini API request failed: '.$response->status());
        }

        $body = $response->json();
        $text = $body['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $tokens = $body['usageMetadata']['totalTokenCount'] ?? null;

        return [
            'content' => $text,
            'tokens' => $tokens,
            'provider' => 'gemini',
        ];
    }

    /**
     * Call Groq API with a prompt.
     *
     * @return array{content: string, tokens: ?int, provider: string}
     */
    private function callGroq(string $apiKey, string $prompt): array
    {
        $response = Http::timeout(30)
            ->withToken($apiKey)
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => 'llama-3.1-70b-versatile',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 2048,
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Groq API request failed: '.$response->status());
        }

        $body = $response->json();
        $text = $body['choices'][0]['message']['content'] ?? '';
        $tokens = $body['usage']['total_tokens'] ?? null;

        return [
            'content' => $text,
            'tokens' => $tokens,
            'provider' => 'groq',
        ];
    }

    /**
     * Parse JSON response from AI, handling markdown wrapping.
     */
    private function parseJsonResponse(string $content): array
    {
        // Try direct JSON decode
        $decoded = json_decode($content, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        // Try extracting JSON array from between [ and ]
        $start = strpos($content, '[');
        $end = strrpos($content, ']');
        if ($start !== false && $end !== false && $end > $start) {
            $jsonStr = substr($content, $start, $end - $start + 1);
            $decoded = json_decode($jsonStr, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        Log::warning('Failed to parse AI JSON response', ['content' => $content]);

        return [];
    }
}
