<?php

namespace App\Services;

use App\Models\ApiKey;
use App\Models\Experience\Experience;
use App\Models\JobSearch\CoverLetter;
use App\Models\JobSearch\JobListing;
use App\Models\Profile;
use App\Models\Skill;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;

class AiCoverLetterService
{
    public function generate(User $user, JobListing $job, string $provider): CoverLetter
    {
        $apiKey = ApiKey::query()
            ->forUser($user->id)
            ->forProvider($provider === 'claude' ? ApiKey::PROVIDER_CLAUDE : ApiKey::PROVIDER_OPENAI)
            ->connected()
            ->first();

        if (! $apiKey) {
            throw new \RuntimeException('No connected API key found for '.$provider.'.');
        }

        $prompt = $this->buildPrompt($user, $job);

        $result = $provider === 'claude'
            ? $this->callClaude($prompt, $apiKey)
            : $this->callOpenAI($prompt, $apiKey);

        return CoverLetter::create([
            'user_id' => $user->id,
            'job_listing_id' => $job->id,
            'job_title' => $job->title,
            'company_name' => $job->company_name,
            'job_description_snippet' => $job->description ? mb_substr($job->description, 0, 2000) : null,
            'content' => $result['content'],
            'ai_provider' => $provider,
            'ai_model' => $result['model'],
            'prompt_tokens' => $result['prompt_tokens'],
            'completion_tokens' => $result['completion_tokens'],
            'is_edited' => false,
        ]);
    }

    public function buildPrompt(User $user, JobListing $job): string
    {
        $profile = Profile::where('user_id', $user->id)->first();
        $skills = Skill::query()->active()->get();
        $experiences = Experience::query()->active()->ordered()->work()->with('responsibilities')->get();

        $profileSection = '';
        if ($profile) {
            $profileSection = "Name: {$user->name}\n";
            $profileSection .= $profile->tagline ? "Tagline: {$profile->tagline}\n" : '';
            $profileSection .= $profile->bio ? "Bio: {$profile->bio}\n" : '';
            $profileSection .= $profile->location ? "Location: {$profile->location}\n" : '';
        } else {
            $profileSection = "Name: {$user->name}\n";
        }

        $skillsSection = $skills->map(function ($skill) {
            return "- {$skill->title} (Proficiency: {$skill->proficiency}%)";
        })->implode("\n");

        $experienceSection = $experiences->map(function ($exp) {
            $period = $exp->start_date->format('M Y').' - '.($exp->is_current ? 'Present' : ($exp->end_date ? $exp->end_date->format('M Y') : 'N/A'));
            $responsibilities = $exp->responsibilities->pluck('description')->map(fn ($r) => "    - {$r}")->implode("\n");

            return "- {$exp->role} at {$exp->company} ({$period})\n{$responsibilities}";
        })->implode("\n");

        $jobSection = "Title: {$job->title}\n";
        $jobSection .= $job->company_name ? "Company: {$job->company_name}\n" : '';
        $jobSection .= $job->location ? "Location: {$job->location}\n" : '';
        $jobSection .= $job->description ? "Description: {$job->description}\n" : '';

        if ($job->tech_stack && is_array($job->tech_stack) && count($job->tech_stack) > 0) {
            $jobSection .= 'Tech Stack: '.implode(', ', $job->tech_stack)."\n";
        }

        if ($job->salary_text) {
            $jobSection .= "Salary: {$job->salary_text}\n";
        } elseif ($job->salary_min || $job->salary_max) {
            $currency = $job->salary_currency ?? 'USD';
            $min = $job->salary_min ? number_format($job->salary_min) : '?';
            $max = $job->salary_max ? number_format($job->salary_max) : '?';
            $jobSection .= "Salary: {$currency} {$min} - {$max}\n";
        }

        return <<<PROMPT
You are a professional cover letter writer. Write a personalized cover letter for the following candidate applying to the specified job.

=== CANDIDATE PROFILE ===
{$profileSection}
=== SKILLS ===
{$skillsSection}

=== WORK EXPERIENCE ===
{$experienceSection}

=== JOB LISTING ===
{$jobSection}
=== INSTRUCTIONS ===
Write a professional, personalized cover letter. Keep it concise (3-4 paragraphs). Focus on matching the candidate's skills and experience to the job requirements. Do not include any placeholders — use real data from the profile and job listing. Start with "Dear Hiring Manager," and end with a professional sign-off using the candidate's name. Output ONLY the cover letter text — no commentary, no markdown formatting.
PROMPT;
    }

    public function callClaude(string $prompt, ApiKey $apiKey): array
    {
        $response = Http::timeout(60)
            ->withHeaders([
                'x-api-key' => $apiKey->key_value,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => 'claude-sonnet-4-20250514',
                'max_tokens' => 2048,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Failed to reach the AI service. Please try again.');
        }

        $body = $response->json();

        return [
            'content' => $body['content'][0]['text'] ?? '',
            'model' => $body['model'] ?? 'claude-sonnet-4-20250514',
            'prompt_tokens' => $body['usage']['input_tokens'] ?? null,
            'completion_tokens' => $body['usage']['output_tokens'] ?? null,
        ];
    }

    public function callOpenAI(string $prompt, ApiKey $apiKey): array
    {
        $response = Http::timeout(60)
            ->withHeaders([
                'Authorization' => 'Bearer '.$apiKey->key_value,
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'max_tokens' => 2048,
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Failed to reach the AI service. Please try again.');
        }

        $body = $response->json();

        return [
            'content' => $body['choices'][0]['message']['content'] ?? '',
            'model' => $body['model'] ?? 'gpt-4o',
            'prompt_tokens' => $body['usage']['prompt_tokens'] ?? null,
            'completion_tokens' => $body['usage']['completion_tokens'] ?? null,
        ];
    }

    public function getAvailableProviders(User $user): array
    {
        $providers = [];

        $claudeKey = ApiKey::query()
            ->forUser($user->id)
            ->forProvider(ApiKey::PROVIDER_CLAUDE)
            ->connected()
            ->first();

        if ($claudeKey) {
            $providers[] = 'claude';
        }

        $openaiKey = ApiKey::query()
            ->forUser($user->id)
            ->forProvider(ApiKey::PROVIDER_OPENAI)
            ->connected()
            ->first();

        if ($openaiKey) {
            $providers[] = 'openai';
        }

        return $providers;
    }

    public function update(CoverLetter $coverLetter, string $content): CoverLetter
    {
        $coverLetter->update([
            'content' => $content,
            'is_edited' => true,
        ]);

        return $coverLetter->fresh();
    }

    public function delete(CoverLetter $coverLetter): void
    {
        $coverLetter->delete();
    }

    public function getLettersForUser(User $user, ?string $search, int $perPage): LengthAwarePaginator
    {
        return CoverLetter::query()
            ->forUser($user->id)
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('job_title', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%");
                });
            })
            ->latest('created_at')
            ->paginate($perPage);
    }

    public function generatePdf(CoverLetter $coverLetter): \Barryvdh\DomPDF\PDF
    {
        $profile = Profile::where('user_id', $coverLetter->user_id)->first();
        $user = $coverLetter->user;

        return Pdf::loadView('cover-letter.templates.default', [
            'coverLetter' => $coverLetter,
            'profile' => $profile,
            'user' => $user,
        ])
            ->setPaper('a4')
            ->setOption('isRemoteEnabled', true);
    }
}
