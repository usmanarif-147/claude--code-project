<?php

namespace App\Services;

use App\Models\ApiKey;
use App\Models\Experience\Experience;
use App\Models\Profile;
use App\Models\Project\Project;
use App\Models\ResumeDownload;
use App\Models\Skill;
use App\Models\Technology;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ResumeService
{
    public function getResumeData(?User $user = null): array
    {
        $user = $user ?? Auth::user();
        $profile = Profile::where('user_id', $user->id)->first();

        return [
            'user' => $user,
            'profile' => $profile,
            'skills' => Skill::query()->active()->ordered()->get(),
            'technologies' => Technology::groupedByCategory(),
            'workExperience' => Experience::query()->active()->ordered()->work()->with('responsibilities')->get(),
            'education' => Experience::query()->active()->ordered()->education()->get(),
            'projects' => Project::query()->active()->ordered()->get(),
        ];
    }

    public function getAllTemplateKeys(): array
    {
        $keys = [];

        // Built-in templates
        $builtInPath = resource_path('views/resume/templates');
        if (File::isDirectory($builtInPath)) {
            foreach (File::glob($builtInPath.'/*.blade.php') as $file) {
                $keys[] = pathinfo($file, PATHINFO_FILENAME);
            }
        }

        // Remove .blade from filenames like "modern.blade"
        $keys = array_map(function ($key) {
            return str_replace('.blade', '', $key);
        }, $keys);

        // AI-generated templates
        $customPath = storage_path('app/resume-templates');
        if (File::isDirectory($customPath)) {
            foreach (File::glob($customPath.'/*.blade.php') as $file) {
                $name = pathinfo($file, PATHINFO_FILENAME);
                $keys[] = str_replace('.blade', '', $name);
            }
        }

        return array_values(array_unique($keys));
    }

    public function getAvailableTemplates(): array
    {
        $templates = [];
        foreach ($this->getAllTemplateKeys() as $key) {
            $templates[$key] = $this->isBuiltInTemplate($key)
                ? ucfirst($key).' — Built-in template'
                : ucfirst(str_replace('ai-', 'AI ', $key)).' — AI-generated';
        }

        return $templates;
    }

    public function isValidTemplate(string $template): bool
    {
        return in_array($template, $this->getAllTemplateKeys());
    }

    public function isBuiltInTemplate(string $template): bool
    {
        return File::exists(resource_path("views/resume/templates/{$template}.blade.php"));
    }

    public function generateHtml(string $template = 'modern', ?User $user = null): string
    {
        if (! $this->isValidTemplate($template)) {
            $template = 'modern';
        }

        $data = $this->getResumeData($user);

        if ($this->isBuiltInTemplate($template)) {
            return view("resume.templates.{$template}", $data)->render();
        }

        // AI-generated template from storage
        $templatePath = storage_path("app/resume-templates/{$template}.blade.php");
        if (File::exists($templatePath)) {
            $bladeContent = File::get($templatePath);

            return Blade::render($bladeContent, $data);
        }

        return view('resume.templates.modern', $data)->render();
    }

    public function generatePdf(string $template = 'modern', ?User $user = null): \Barryvdh\DomPDF\PDF
    {
        if (! $this->isValidTemplate($template)) {
            $template = 'modern';
        }

        $data = $this->getResumeData($user);

        if ($this->isBuiltInTemplate($template)) {
            return Pdf::loadView("resume.templates.{$template}", $data)
                ->setPaper('a4')
                ->setOption('isRemoteEnabled', true);
        }

        // AI-generated template
        $html = $this->generateHtml($template, $user);

        return Pdf::loadHTML($html)
            ->setPaper('a4')
            ->setOption('isRemoteEnabled', true);
    }

    public function download(string $template, ?\Illuminate\Http\Request $request = null, ?User $user = null): Response
    {
        $pdf = $this->generatePdf($template, $user);

        if ($request) {
            ResumeDownload::create([
                'ip_address' => $request->ip(),
                'referrer' => $request->headers->get('referer') ? substr($request->headers->get('referer'), 0, 500) : null,
                'template_used' => $template,
                'downloaded_at' => now(),
            ]);
        }

        $user = $user ?? Auth::user();
        $filename = str_replace(' ', '_', $user->name).'_Resume.pdf';

        return $pdf->download($filename);
    }

    // ─── CRUD Methods for Edit Modals ────────────────────────────

    public function updatePersonalInfo(array $data): void
    {
        $user = Auth::user();
        $user->update(['name' => $data['name'], 'email' => $data['email']]);

        Profile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'tagline' => $data['tagline'] ?? null,
                'phone' => $data['phone'] ?? null,
                'location' => $data['location'] ?? null,
                'linkedin_url' => $data['linkedin_url'] ?? null,
                'github_url' => $data['github_url'] ?? null,
            ]
        );
    }

    public function updateAbout(string $bio): void
    {
        $user = Auth::user();
        Profile::updateOrCreate(
            ['user_id' => $user->id],
            ['bio' => $bio]
        );
    }

    public function saveExperienceItem(array $data, ?int $id = null): Experience
    {
        $attrs = [
            'type' => 'work',
            'role' => $data['role'],
            'company' => $data['company'],
            'start_date' => $data['start_date'],
            'end_date' => $data['is_current'] ? null : ($data['end_date'] ?? null),
            'is_current' => $data['is_current'] ?? false,
            'description' => $data['description'] ?? null,
            'is_active' => true,
        ];

        if ($id) {
            $experience = Experience::findOrFail($id);
            $experience->update($attrs);
        } else {
            $attrs['sort_order'] = Experience::max('sort_order') + 1;
            $experience = Experience::create($attrs);
        }

        // Sync responsibilities
        if (isset($data['responsibilities'])) {
            $experience->responsibilities()->delete();
            foreach (array_filter($data['responsibilities']) as $index => $desc) {
                $experience->responsibilities()->create([
                    'description' => $desc,
                    'sort_order' => $index,
                ]);
            }
        }

        return $experience;
    }

    public function saveEducationItem(array $data, ?int $id = null): Experience
    {
        $attrs = [
            'type' => 'education',
            'role' => $data['degree'] ?? '',
            'company' => $data['company'],
            'start_date' => $data['start_date'],
            'end_date' => $data['is_current'] ? null : ($data['end_date'] ?? null),
            'is_current' => $data['is_current'] ?? false,
            'degree' => $data['degree'] ?? null,
            'field_of_study' => $data['field_of_study'] ?? null,
            'is_active' => true,
        ];

        if ($id) {
            $experience = Experience::findOrFail($id);
            $experience->update($attrs);
        } else {
            $attrs['sort_order'] = Experience::max('sort_order') + 1;
            $experience = Experience::create($attrs);
        }

        return $experience;
    }

    public function syncSkills(array $skills): void
    {
        foreach ($skills as $skillData) {
            if (isset($skillData['id'])) {
                Skill::where('id', $skillData['id'])->update([
                    'title' => $skillData['title'],
                    'category' => $skillData['category'] ?? null,
                    'proficiency' => $skillData['proficiency'] ?? 80,
                ]);
            } else {
                Skill::create([
                    'title' => $skillData['title'],
                    'category' => $skillData['category'] ?? null,
                    'proficiency' => $skillData['proficiency'] ?? 80,
                    'is_active' => true,
                    'sort_order' => Skill::max('sort_order') + 1,
                ]);
            }
        }
    }

    public function syncTechnologies(array $technologies): void
    {
        foreach ($technologies as $techData) {
            if (isset($techData['id'])) {
                Technology::where('id', $techData['id'])->update([
                    'name' => $techData['name'],
                    'category' => $techData['category'],
                ]);
            } else {
                Technology::create([
                    'name' => $techData['name'],
                    'category' => $techData['category'],
                    'is_active' => true,
                    'sort_order' => Technology::max('sort_order') + 1,
                ]);
            }
        }
    }

    public function saveProjectItem(array $data, ?int $id = null): Project
    {
        $attrs = [
            'title' => $data['title'],
            'slug' => Str::slug($data['title']),
            'short_description' => $data['short_description'] ?? null,
            'description' => $data['description'] ?? null,
            'tech_stack' => $data['tech_stack'] ?? [],
            'is_active' => true,
        ];

        if ($id) {
            $project = Project::findOrFail($id);
            $project->update($attrs);
        } else {
            $attrs['sort_order'] = Project::max('sort_order') + 1;
            $project = Project::create($attrs);
        }

        return $project;
    }

    // ─── AI Methods ──────────────────────────────────────────────

    public function generateTemplateFromScreenshot(string $imageBase64, string $mimeType): string
    {
        $apiKey = $this->getGeminiApiKey();

        $response = Http::timeout(120)
            ->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key='.$apiKey->key_value, [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'inlineData' => [
                                    'mimeType' => $mimeType,
                                    'data' => $imageBase64,
                                ],
                            ],
                            [
                                'text' => $this->getTemplateGenerationPrompt(),
                            ],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 8000,
                    'temperature' => 0.3,
                ],
            ]);

        if ($response->failed()) {
            $error = $response->json('error.message') ?? 'Unknown error';
            throw new \RuntimeException('Failed to generate template: '.$error);
        }

        $body = $response->json();
        $bladeHtml = $body['candidates'][0]['content']['parts'][0]['text'] ?? '';

        // Extract HTML from code block if wrapped
        if (preg_match('/```(?:html|blade)?\s*\n(.*?)\n```/s', $bladeHtml, $matches)) {
            $bladeHtml = $matches[1];
        }

        return $bladeHtml;
    }

    public function saveAiTemplate(string $bladeHtml): string
    {
        $sanitized = $this->sanitizeBladeTemplate($bladeHtml);
        $key = 'ai-'.time();
        $path = storage_path("app/resume-templates/{$key}.blade.php");

        File::put($path, $sanitized);

        return $key;
    }

    public function deleteCustomTemplate(string $key): void
    {
        if ($this->isBuiltInTemplate($key)) {
            throw new \RuntimeException('Cannot delete built-in templates.');
        }

        $path = storage_path("app/resume-templates/{$key}.blade.php");
        if (File::exists($path)) {
            File::delete($path);
        }
    }

    public function parseResumeDetails(string $content, string $fileType): array
    {
        $prompt = $this->getDetailsParsingPrompt($content, $fileType);

        // Try Gemini first, fall back to Groq
        $text = $this->callGeminiText($prompt) ?? $this->callGroqText($prompt);

        if (! $text) {
            throw new \RuntimeException('No AI provider available. Please add a Gemini or Groq API key in Settings > API Keys.');
        }

        // Extract JSON from code block if wrapped
        if (preg_match('/```(?:json)?\s*\n(.*?)\n```/s', $text, $matches)) {
            $text = $matches[1];
        }

        $parsed = json_decode($text, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('AI returned invalid JSON. Please try again.');
        }

        return $parsed;
    }

    public function importParsedData(array $data): array
    {
        $summary = [];

        if (! empty($data['profile'])) {
            $user = Auth::user();
            if (isset($data['profile']['name'])) {
                $user->update(['name' => $data['profile']['name']]);
            }
            Profile::updateOrCreate(
                ['user_id' => $user->id],
                array_filter([
                    'tagline' => $data['profile']['tagline'] ?? null,
                    'bio' => $data['profile']['bio'] ?? null,
                    'phone' => $data['profile']['phone'] ?? null,
                    'location' => $data['profile']['location'] ?? null,
                    'linkedin_url' => $data['profile']['linkedin_url'] ?? null,
                    'github_url' => $data['profile']['github_url'] ?? null,
                ])
            );
            $summary[] = 'Profile updated';
        }

        if (! empty($data['skills'])) {
            $count = 0;
            foreach ($data['skills'] as $skill) {
                $existing = Skill::where('title', $skill['title'])->first();
                if (! $existing) {
                    Skill::create([
                        'title' => $skill['title'],
                        'category' => $skill['category'] ?? null,
                        'proficiency' => $skill['proficiency'] ?? 80,
                        'is_active' => true,
                        'sort_order' => Skill::max('sort_order') + 1,
                    ]);
                    $count++;
                }
            }
            if ($count > 0) {
                $summary[] = "{$count} skills added";
            }
        }

        if (! empty($data['technologies'])) {
            $count = 0;
            foreach ($data['technologies'] as $tech) {
                $existing = Technology::where('name', $tech['name'])
                    ->where('category', $tech['category'] ?? '')
                    ->first();
                if (! $existing) {
                    Technology::create([
                        'name' => $tech['name'],
                        'category' => $tech['category'] ?? 'Other',
                        'is_active' => true,
                        'sort_order' => Technology::max('sort_order') + 1,
                    ]);
                    $count++;
                }
            }
            if ($count > 0) {
                $summary[] = "{$count} technologies added";
            }
        }

        if (! empty($data['experiences'])) {
            $count = 0;
            foreach ($data['experiences'] as $exp) {
                $this->saveExperienceItem([
                    'role' => $exp['role'] ?? $exp['title'] ?? '',
                    'company' => $exp['company'] ?? '',
                    'start_date' => $exp['start_date'] ?? now()->format('Y-m-d'),
                    'end_date' => $exp['end_date'] ?? null,
                    'is_current' => $exp['is_current'] ?? false,
                    'description' => $exp['description'] ?? null,
                    'responsibilities' => $exp['responsibilities'] ?? [],
                ]);
                $count++;
            }
            $summary[] = "{$count} experiences added";
        }

        if (! empty($data['education'])) {
            $count = 0;
            foreach ($data['education'] as $edu) {
                $this->saveEducationItem([
                    'degree' => $edu['degree'] ?? $edu['title'] ?? '',
                    'field_of_study' => $edu['field_of_study'] ?? $edu['field'] ?? null,
                    'company' => $edu['company'] ?? $edu['institution'] ?? '',
                    'start_date' => $edu['start_date'] ?? now()->format('Y-m-d'),
                    'end_date' => $edu['end_date'] ?? null,
                    'is_current' => $edu['is_current'] ?? false,
                ]);
                $count++;
            }
            $summary[] = "{$count} education entries added";
        }

        if (! empty($data['projects'])) {
            $count = 0;
            foreach ($data['projects'] as $proj) {
                $this->saveProjectItem([
                    'title' => $proj['title'] ?? '',
                    'short_description' => $proj['short_description'] ?? $proj['description'] ?? null,
                    'description' => $proj['description'] ?? null,
                    'tech_stack' => $proj['tech_stack'] ?? [],
                ]);
                $count++;
            }
            $summary[] = "{$count} projects added";
        }

        return $summary;
    }

    // ─── Private Helpers ─────────────────────────────────────────

    private function getGeminiApiKey(): ApiKey
    {
        $user = Auth::user();
        $apiKey = ApiKey::query()
            ->forUser($user->id)
            ->forProvider(ApiKey::PROVIDER_GEMINI)
            ->connected()
            ->first();

        if (! $apiKey) {
            throw new \RuntimeException('No connected Gemini API key found. Please add one in Settings > API Keys.');
        }

        return $apiKey;
    }

    private function getGroqApiKey(): ?ApiKey
    {
        $user = Auth::user();

        return ApiKey::query()
            ->forUser($user->id)
            ->forProvider(ApiKey::PROVIDER_GROQ)
            ->connected()
            ->first();
    }

    private function callGeminiText(string $prompt): ?string
    {
        try {
            $apiKey = $this->getGeminiApiKey();
        } catch (\RuntimeException) {
            return null;
        }

        $response = Http::timeout(90)
            ->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key='.$apiKey->key_value, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
                'generationConfig' => [
                    'maxOutputTokens' => 4096,
                    'temperature' => 0.2,
                ],
            ]);

        if ($response->failed()) {
            return null;
        }

        return $response->json('candidates.0.content.parts.0.text');
    }

    private function callGroqText(string $prompt): ?string
    {
        $apiKey = $this->getGroqApiKey();
        if (! $apiKey) {
            return null;
        }

        $response = Http::timeout(90)
            ->withHeaders([
                'Authorization' => 'Bearer '.$apiKey->key_value,
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => 'llama-3.3-70b-versatile',
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => 4096,
                'temperature' => 0.2,
            ]);

        if ($response->failed()) {
            return null;
        }

        return $response->json('choices.0.message.content');
    }

    private function sanitizeBladeTemplate(string $html): string
    {
        // Remove dangerous Blade/PHP directives
        $html = preg_replace('/@php.*?@endphp/s', '', $html);
        $html = preg_replace('/<\?php.*?\?>/s', '', $html);
        $html = preg_replace('/\{!!.*?!!\}/s', '', $html);
        $html = preg_replace('/@include\s*\(.*?\)/', '', $html);
        $html = preg_replace('/@extends\s*\(.*?\)/', '', $html);
        $html = preg_replace('/@require\s*\(.*?\)/', '', $html);
        $html = preg_replace('/@inject\s*\(.*?\)/', '', $html);

        return $html;
    }

    private function getTemplateGenerationPrompt(): string
    {
        return <<<'PROMPT'
Analyze this resume screenshot and generate a complete HTML resume template that closely matches its visual design, layout, and styling.

Generate a SINGLE self-contained HTML file with inline CSS that can be used as a resume PDF template. The template must use these Blade variables for data:

Variables available:
- $user->name, $user->email
- $profile->tagline, $profile->bio, $profile->phone, $profile->location, $profile->linkedin_url, $profile->github_url
- $skills (collection, each has: title, category, proficiency 0-100)
- $technologies (collection grouped by category, each has: name, category)
- $workExperience (collection, each has: role, company, start_date, end_date, is_current, description, responsibilities relation with description field)
- $education (collection, each has: degree, field_of_study, company, start_date, end_date, is_current)
- $projects (collection, each has: title, short_description, tech_stack array)

Requirements:
1. Complete HTML document with <html>, <head>, <body>
2. ALL styles must be inline CSS in a <style> tag — no external stylesheets
3. Use {{ $variable }} syntax for outputting data (Blade escaped output)
4. Use @foreach/@endforeach for loops, @if/@endif for conditionals
5. Page size: A4 (210mm x 297mm)
6. Match the screenshot's layout, colors, fonts, and spacing as closely as possible
7. Output ONLY the HTML code — no explanation, no markdown

Do NOT use @php, @include, @extends, or {!! !!} directives.
PROMPT;
    }

    private function getDetailsParsingPrompt(string $content, string $fileType): string
    {
        return <<<PROMPT
Parse the following resume/CV data from a {$fileType} file and extract structured information. Return ONLY valid JSON with no explanation.

The JSON must have these keys (include only keys that have data — omit empty ones):
{
  "profile": {
    "name": "string",
    "tagline": "string",
    "bio": "string",
    "phone": "string",
    "location": "string",
    "linkedin_url": "string",
    "github_url": "string"
  },
  "skills": [
    {"title": "string", "category": "string or null", "proficiency": 80}
  ],
  "technologies": [
    {"name": "string", "category": "Frontend|Backend|Database|DevOps|Other"}
  ],
  "experiences": [
    {
      "role": "string",
      "company": "string",
      "start_date": "YYYY-MM-DD",
      "end_date": "YYYY-MM-DD or null",
      "is_current": false,
      "description": "string or null",
      "responsibilities": ["string"]
    }
  ],
  "education": [
    {
      "degree": "string",
      "field_of_study": "string or null",
      "institution": "string",
      "start_date": "YYYY-MM-DD",
      "end_date": "YYYY-MM-DD or null",
      "is_current": false
    }
  ],
  "projects": [
    {
      "title": "string",
      "description": "string",
      "tech_stack": ["string"]
    }
  ]
}

For dates, use best estimates if only year is given (use January 1st). For proficiency, estimate 0-100 based on context.

=== FILE CONTENT ===
{$content}
PROMPT;
    }
}
