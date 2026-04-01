<?php

namespace App\Services;

use App\Models\ApiKey;
use App\Models\JobSearch\JobListing;
use App\Models\JobSearch\JobMatchScore;
use App\Models\JobSearchFilter;
use App\Models\Profile;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiJobMatchService
{
    public function getConfiguredProvider(int $userId): ?string
    {
        $claudeKey = ApiKey::query()
            ->forUser($userId)
            ->forProvider(ApiKey::PROVIDER_CLAUDE)
            ->connected()
            ->first();

        if ($claudeKey) {
            return 'claude';
        }

        $openaiKey = ApiKey::query()
            ->forUser($userId)
            ->forProvider(ApiKey::PROVIDER_OPENAI)
            ->connected()
            ->first();

        if ($openaiKey) {
            return 'openai';
        }

        return null;
    }

    public function scoreJob(JobListing $job, User $user): JobMatchScore
    {
        $provider = $this->getConfiguredProvider($user->id);

        if (! $provider) {
            throw new \RuntimeException('No AI provider configured. Add a Claude or OpenAI API key in Settings.');
        }

        $userContext = $this->buildUserContext($user);
        $jobContext = $this->buildJobContext($job);
        $prompt = $this->buildPrompt($userContext, $jobContext);

        $apiKey = ApiKey::query()
            ->forUser($user->id)
            ->forProvider($provider === 'claude' ? ApiKey::PROVIDER_CLAUDE : ApiKey::PROVIDER_OPENAI)
            ->connected()
            ->first();

        $model = null;
        $responseData = $provider === 'claude'
            ? $this->callClaude($prompt, $apiKey, $model)
            : $this->callOpenai($prompt, $apiKey, $model);

        $parsed = $this->parseAiResponse($responseData);

        return JobMatchScore::updateOrCreate(
            [
                'user_id' => $user->id,
                'job_listing_id' => $job->id,
            ],
            [
                'score' => max(0, min(100, (int) $parsed['score'])),
                'explanation' => $parsed['explanation'] ?? null,
                'matched_skills' => $parsed['matched_skills'] ?? [],
                'missing_skills' => $parsed['missing_skills'] ?? [],
                'bonus_factors' => $parsed['bonus_factors'] ?? [],
                'ai_provider' => $provider,
                'ai_model' => $model,
                'scored_at' => now(),
            ]
        );
    }

    public function scoreUnscored(User $user): array
    {
        $summary = ['scored' => 0, 'failed' => 0, 'skipped' => 0];

        $jobs = JobListing::query()
            ->forUser($user->id)
            ->visible()
            ->whereNotIn('id', function ($q) use ($user) {
                $q->select('job_listing_id')
                    ->from('job_match_scores')
                    ->where('user_id', $user->id);
            })
            ->get();

        foreach ($jobs as $job) {
            try {
                $this->scoreJob($job, $user);
                $summary['scored']++;
            } catch (\Throwable $e) {
                Log::warning('AI job scoring failed', ['job_id' => $job->id, 'error' => $e->getMessage()]);
                $summary['failed']++;
            }

            usleep(500000); // 500ms delay between API calls
        }

        return $summary;
    }

    public function rescoreAll(User $user): array
    {
        JobMatchScore::query()->forUser($user->id)->delete();

        $summary = ['scored' => 0, 'failed' => 0, 'skipped' => 0];

        $jobs = JobListing::query()
            ->forUser($user->id)
            ->visible()
            ->get();

        foreach ($jobs as $job) {
            try {
                $this->scoreJob($job, $user);
                $summary['scored']++;
            } catch (\Throwable $e) {
                Log::warning('AI job re-scoring failed', ['job_id' => $job->id, 'error' => $e->getMessage()]);
                $summary['failed']++;
            }

            usleep(500000);
        }

        return $summary;
    }

    public function rescoreJob(JobListing $job, User $user): JobMatchScore
    {
        JobMatchScore::query()
            ->where('user_id', $user->id)
            ->where('job_listing_id', $job->id)
            ->delete();

        return $this->scoreJob($job, $user);
    }

    public function buildUserContext(User $user): array
    {
        $skills = Skill::query()->active()->ordered()->get();
        $profile = Profile::query()->where('user_id', $user->id)->first();
        $filters = JobSearchFilter::query()->where('user_id', $user->id)->first();

        return [
            'skills' => $skills->pluck('title')->toArray(),
            'skill_categories' => $skills->groupBy('category')->map(fn ($group) => $group->pluck('title')->toArray())->toArray(),
            'preferred_tech' => $filters?->preferred_tech ?? [],
            'preferred_titles' => $filters?->preferred_titles ?? [],
            'preferred_location_type' => $filters?->location_type,
            'bio' => $profile?->bio ?? '',
            'tagline' => $profile?->tagline ?? '',
        ];
    }

    public function buildJobContext(JobListing $job): array
    {
        return [
            'title' => $job->title,
            'company' => $job->company_name,
            'description' => $job->description ?? '',
            'tech_stack' => $job->tech_stack ?? [],
            'location' => $job->location,
            'location_type' => $job->location_type,
            'salary_min' => $job->salary_min,
            'salary_max' => $job->salary_max,
            'salary_currency' => $job->salary_currency,
            'salary_text' => $job->salary_text,
        ];
    }

    public function buildPrompt(array $userContext, array $jobContext): string
    {
        $skills = implode(', ', $userContext['skills']);
        $preferredTech = implode(', ', $userContext['preferred_tech']);
        $preferredTitles = implode(', ', $userContext['preferred_titles']);
        $techStack = implode(', ', $jobContext['tech_stack']);

        $salaryInfo = '';
        if ($jobContext['salary_min'] || $jobContext['salary_max']) {
            $salaryInfo = sprintf(
                'Salary: %s %s - %s',
                $jobContext['salary_currency'] ?? 'USD',
                $jobContext['salary_min'] ? number_format($jobContext['salary_min']) : 'N/A',
                $jobContext['salary_max'] ? number_format($jobContext['salary_max']) : 'N/A'
            );
        } elseif ($jobContext['salary_text']) {
            $salaryInfo = 'Salary: '.$jobContext['salary_text'];
        }

        return <<<PROMPT
You are a job matching expert. Analyze how well this job listing matches the candidate's profile and return a match score.

CANDIDATE PROFILE:
- Skills: {$skills}
- Preferred Technologies: {$preferredTech}
- Preferred Job Titles: {$preferredTitles}
- Location Preference: {$userContext['preferred_location_type']}
- Bio: {$userContext['bio']}

JOB LISTING:
- Title: {$jobContext['title']}
- Company: {$jobContext['company']}
- Description: {$jobContext['description']}
- Tech Stack: {$techStack}
- Location: {$jobContext['location']}
- Location Type: {$jobContext['location_type']}
- {$salaryInfo}

Respond with ONLY a valid JSON object (no markdown, no code fences) in this exact format:
{
  "score": <integer 0-100>,
  "explanation": "<2-3 sentence explanation of why this job matches or doesn't match>",
  "matched_skills": ["<skill1>", "<skill2>"],
  "missing_skills": ["<skill1>", "<skill2>"],
  "bonus_factors": ["<factor1>", "<factor2>"]
}

Scoring guidelines:
- 90-100: Nearly perfect match — most skills align, title matches, strong fit
- 70-89: Good match — many skills overlap, reasonable fit
- 50-69: Moderate match — some overlap but significant gaps
- 30-49: Weak match — few skills match, title mismatch
- 0-29: Poor match — almost no alignment

Rules:
- matched_skills: skills the candidate has that the job requires or prefers
- missing_skills: skills the job requires that the candidate lacks
- bonus_factors: extra positive signals like matching location preference, salary range, seniority fit
- Keep explanation concise (2-3 sentences max)
- Score must be an integer between 0 and 100
PROMPT;
    }

    public function callClaude(string $prompt, ApiKey $apiKey, ?string &$model = null): array
    {
        $model = 'claude-sonnet-4-20250514';

        $response = Http::timeout(60)
            ->withHeaders([
                'x-api-key' => $apiKey->key_value,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => $model,
                'max_tokens' => 1024,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
            ]);

        if ($response->status() === 429) {
            $retryAfter = (int) ($response->header('retry-after') ?: 5);
            sleep($retryAfter);

            $response = Http::timeout(60)
                ->withHeaders([
                    'x-api-key' => $apiKey->key_value,
                    'anthropic-version' => '2023-06-01',
                    'content-type' => 'application/json',
                ])
                ->post('https://api.anthropic.com/v1/messages', [
                    'model' => $model,
                    'max_tokens' => 1024,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                ]);
        }

        if ($response->failed()) {
            throw new \RuntimeException('Failed to reach the AI service. Please try again.');
        }

        $body = $response->json();
        $text = $body['content'][0]['text'] ?? '';

        return $this->decodeJsonResponse($text);
    }

    public function callOpenai(string $prompt, ApiKey $apiKey, ?string &$model = null): array
    {
        $model = 'gpt-4o-mini';

        $response = Http::timeout(60)
            ->withHeaders([
                'Authorization' => 'Bearer '.$apiKey->key_value,
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'max_tokens' => 1024,
            ]);

        if ($response->status() === 429) {
            $retryAfter = (int) ($response->header('retry-after') ?: 5);
            sleep($retryAfter);

            $response = Http::timeout(60)
                ->withHeaders([
                    'Authorization' => 'Bearer '.$apiKey->key_value,
                    'Content-Type' => 'application/json',
                ])
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'max_tokens' => 1024,
                ]);
        }

        if ($response->failed()) {
            throw new \RuntimeException('Failed to reach the AI service. Please try again.');
        }

        $body = $response->json();
        $text = $body['choices'][0]['message']['content'] ?? '';

        return $this->decodeJsonResponse($text);
    }

    public function parseAiResponse(array $response): array
    {
        return [
            'score' => isset($response['score']) ? max(0, min(100, (int) $response['score'])) : 0,
            'explanation' => $response['explanation'] ?? null,
            'matched_skills' => is_array($response['matched_skills'] ?? null) ? $response['matched_skills'] : [],
            'missing_skills' => is_array($response['missing_skills'] ?? null) ? $response['missing_skills'] : [],
            'bonus_factors' => is_array($response['bonus_factors'] ?? null) ? $response['bonus_factors'] : [],
        ];
    }

    public function getScoredFeed(User $user, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = JobListing::query()
            ->forUser($user->id)
            ->visible()
            ->leftJoin('job_match_scores', function ($join) use ($user) {
                $join->on('job_listings.id', '=', 'job_match_scores.job_listing_id')
                    ->where('job_match_scores.user_id', '=', $user->id);
            })
            ->select('job_listings.*')
            ->addSelect('job_match_scores.score as match_score')
            ->addSelect('job_match_scores.explanation as match_explanation')
            ->addSelect('job_match_scores.matched_skills as match_matched_skills')
            ->addSelect('job_match_scores.missing_skills as match_missing_skills')
            ->addSelect('job_match_scores.bonus_factors as match_bonus_factors')
            ->addSelect('job_match_scores.ai_provider as match_ai_provider')
            ->addSelect('job_match_scores.scored_at as match_scored_at');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('job_listings.title', 'like', "%{$search}%")
                    ->orWhere('job_listings.company_name', 'like', "%{$search}%")
                    ->orWhere('job_listings.description', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['platform'])) {
            $query->where('job_listings.source_platform', $filters['platform']);
        }

        if (! empty($filters['locationType'])) {
            $query->where('job_listings.location_type', $filters['locationType']);
        }

        if (isset($filters['minScore']) && $filters['minScore'] !== null && $filters['minScore'] !== '') {
            $query->where('job_match_scores.score', '>=', (int) $filters['minScore']);
        }

        $sortBy = $filters['sortBy'] ?? 'score';
        match ($sortBy) {
            'posted_at' => $query->orderByDesc('job_listings.posted_at'),
            'company_name' => $query->orderBy('job_listings.company_name'),
            default => $query->orderByRaw('job_match_scores.score IS NULL ASC, job_match_scores.score DESC'),
        };

        return $query->paginate($perPage);
    }

    public function getScoreStats(User $user): array
    {
        $scored = JobMatchScore::query()->forUser($user->id);
        $totalJobs = JobListing::query()->forUser($user->id)->visible()->count();
        $totalScored = (clone $scored)->count();

        return [
            'average_score' => $totalScored > 0 ? (int) round((clone $scored)->avg('score')) : 0,
            'high_match_count' => (clone $scored)->highMatches()->count(),
            'total_scored' => $totalScored,
            'total_unscored' => $totalJobs - $totalScored,
            'last_scored_at' => (clone $scored)->latest('scored_at')->value('scored_at'),
        ];
    }

    private function decodeJsonResponse(string $text): array
    {
        $text = preg_replace('/^```(?:json)?\s*/m', '', $text);
        $text = preg_replace('/```\s*$/m', '', $text);
        $text = trim($text);

        $parsed = json_decode($text, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($parsed)) {
            throw new \RuntimeException('AI returned an unexpected response format.');
        }

        return $parsed;
    }
}
