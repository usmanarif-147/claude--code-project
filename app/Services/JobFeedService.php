<?php

namespace App\Services;

use App\Models\ApiKey;
use App\Models\JobSearch\JobFetchLog;
use App\Models\JobSearch\JobListing;
use App\Models\JobSearchFilter;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class JobFeedService
{
    /**
     * Orchestrates fetching from all enabled platforms.
     */
    public function fetchAllPlatforms(User $user): array
    {
        $filter = JobSearchFilter::where('user_id', $user->id)->first();

        if (! $filter) {
            return [
                'total_fetched' => 0,
                'platforms_fetched' => 0,
                'errors' => ['No job search filters configured. Please configure your filters in Settings.'],
            ];
        }

        $enabledPlatforms = $filter->enabled_platforms ?? [];

        if (empty($enabledPlatforms)) {
            return [
                'total_fetched' => 0,
                'platforms_fetched' => 0,
                'errors' => ['No platforms enabled. Please enable platforms in Settings.'],
            ];
        }

        $totalFetched = 0;
        $platformsFetched = 0;
        $errors = [];

        foreach ($enabledPlatforms as $platform) {
            $startedAt = now();

            try {
                $count = match ($platform) {
                    JobSearchFilter::JSEARCH => $this->fetchFromJSearch($user, $filter),
                    JobSearchFilter::REMOTEOK => $this->fetchFromRemoteOK($user, $filter),
                    JobSearchFilter::REMOTIVE => $this->fetchFromRemotive($user, $filter),
                    JobSearchFilter::ADZUNA => $this->fetchFromAdzuna($user, $filter),
                    JobSearchFilter::ROZEE => $this->fetchFromSerpApi($user, $filter, 'rozee'),
                    JobSearchFilter::MUSTAKBIL => $this->fetchFromSerpApi($user, $filter, 'mustakbil'),
                    default => 0,
                };

                $totalFetched += $count;
                $platformsFetched++;

                $this->logFetch($user, $platform, JobFetchLog::STATUS_SUCCESS, $count, 0, null, $startedAt);
            } catch (\Exception $e) {
                $errors[] = "{$platform}: {$e->getMessage()}";
                Log::warning("Job fetch failed for platform {$platform}", ['error' => $e->getMessage()]);

                $this->logFetch($user, $platform, JobFetchLog::STATUS_FAILED, 0, 0, $e->getMessage(), $startedAt);
            }
        }

        return [
            'total_fetched' => $totalFetched,
            'platforms_fetched' => $platformsFetched,
            'errors' => $errors,
        ];
    }

    /**
     * Fetch from JSearch API (RapidAPI) — covers Indeed, Glassdoor, LinkedIn.
     */
    public function fetchFromJSearch(User $user, JobSearchFilter $filter): int
    {
        $apiKey = ApiKey::forUser($user->id)->forProvider(ApiKey::PROVIDER_JSEARCH)->connected()->first();

        if (! $apiKey) {
            throw new \RuntimeException('JSearch API key not configured or not connected.');
        }

        $query = $this->buildSearchQuery($filter);

        $response = Http::withHeaders([
            'X-RapidAPI-Key' => $apiKey->key_value,
            'X-RapidAPI-Host' => 'jsearch.p.rapidapi.com',
        ])->get('https://jsearch.p.rapidapi.com/search', [
            'query' => $query,
            'page' => 1,
            'num_pages' => 1,
            'date_posted' => 'week',
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('JSearch API request failed: '.$response->status());
        }

        $data = $response->json('data', []);
        $count = 0;

        foreach ($data as $job) {
            $inserted = $this->upsertJob($user, [
                'external_id' => $job['job_id'] ?? Str::random(20),
                'source_platform' => JobListing::PLATFORM_JSEARCH,
                'title' => Str::limit($job['job_title'] ?? 'Untitled', 497),
                'company_name' => $job['employer_name'] ?? null,
                'company_logo_url' => $job['employer_logo'] ?? null,
                'description' => Str::limit($job['job_description'] ?? '', 2000),
                'location' => $job['job_city'] ? ($job['job_city'].', '.($job['job_country'] ?? '')) : ($job['job_country'] ?? null),
                'location_type' => $job['job_is_remote'] ? 'remote' : 'onsite',
                'country' => $job['job_country'] ?? null,
                'salary_min' => $job['job_min_salary'] ?? null,
                'salary_max' => $job['job_max_salary'] ?? null,
                'salary_currency' => $job['job_salary_currency'] ?? 'USD',
                'salary_text' => $job['job_salary_period'] ? ($job['job_min_salary'].' - '.$job['job_max_salary'].' '.$job['job_salary_currency'].'/'.$job['job_salary_period']) : null,
                'tech_stack' => $this->extractTechStack($job['job_description'] ?? ''),
                'job_url' => $job['job_apply_link'] ?? $job['job_google_link'] ?? '',
                'posted_at' => isset($job['job_posted_at_datetime_utc']) ? Carbon::parse($job['job_posted_at_datetime_utc']) : null,
            ]);

            if ($inserted) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Fetch from RemoteOK free API.
     */
    public function fetchFromRemoteOK(User $user, JobSearchFilter $filter): int
    {
        $response = Http::withHeaders([
            'User-Agent' => 'PortfolioJobFeed/1.0',
        ])->get('https://remoteok.com/api');

        if (! $response->successful()) {
            throw new \RuntimeException('RemoteOK API request failed: '.$response->status());
        }

        $data = $response->json();
        // First element is metadata, skip it
        $jobs = array_slice($data, 1);
        $count = 0;

        foreach (array_slice($jobs, 0, 50) as $job) {
            if (! $this->matchesFilter($job['position'] ?? '', $filter)) {
                continue;
            }

            $inserted = $this->upsertJob($user, [
                'external_id' => (string) ($job['id'] ?? Str::random(20)),
                'source_platform' => JobListing::PLATFORM_REMOTEOK,
                'title' => Str::limit($job['position'] ?? 'Untitled', 497),
                'company_name' => $job['company'] ?? null,
                'company_logo_url' => $job['company_logo'] ?? null,
                'description' => Str::limit($job['description'] ?? '', 2000),
                'location' => $job['location'] ?? 'Remote',
                'location_type' => 'remote',
                'country' => null,
                'salary_min' => $this->parseSalaryValue($job['salary_min'] ?? null),
                'salary_max' => $this->parseSalaryValue($job['salary_max'] ?? null),
                'salary_currency' => 'USD',
                'salary_text' => $job['salary'] ?? null,
                'tech_stack' => $job['tags'] ?? [],
                'job_url' => $job['url'] ?? ('https://remoteok.com/remote-jobs/'.$job['slug']),
                'posted_at' => isset($job['date']) ? Carbon::parse($job['date']) : null,
            ]);

            if ($inserted) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Fetch from Remotive free API.
     */
    public function fetchFromRemotive(User $user, JobSearchFilter $filter): int
    {
        $query = $this->buildSearchQuery($filter);

        $response = Http::get('https://remotive.com/api/remote-jobs', [
            'search' => $query,
            'limit' => 50,
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Remotive API request failed: '.$response->status());
        }

        $jobs = $response->json('jobs', []);
        $count = 0;

        foreach ($jobs as $job) {
            $inserted = $this->upsertJob($user, [
                'external_id' => (string) ($job['id'] ?? Str::random(20)),
                'source_platform' => JobListing::PLATFORM_REMOTIVE,
                'title' => Str::limit($job['title'] ?? 'Untitled', 497),
                'company_name' => $job['company_name'] ?? null,
                'company_logo_url' => $job['company_logo_url'] ?? $job['company_logo'] ?? null,
                'description' => Str::limit(strip_tags($job['description'] ?? ''), 2000),
                'location' => $job['candidate_required_location'] ?? 'Remote',
                'location_type' => 'remote',
                'country' => null,
                'salary_min' => null,
                'salary_max' => null,
                'salary_currency' => 'USD',
                'salary_text' => $job['salary'] ?? null,
                'tech_stack' => $job['tags'] ?? [],
                'job_url' => $job['url'] ?? '',
                'posted_at' => isset($job['publication_date']) ? Carbon::parse($job['publication_date']) : null,
            ]);

            if ($inserted) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Fetch from Adzuna API.
     */
    public function fetchFromAdzuna(User $user, JobSearchFilter $filter): int
    {
        $apiKey = ApiKey::forUser($user->id)->forProvider(ApiKey::PROVIDER_ADZUNA)->connected()->first();

        if (! $apiKey) {
            throw new \RuntimeException('Adzuna API key not configured or not connected.');
        }

        $extraData = $apiKey->extra_data ?? [];
        $appId = $extraData['app_id'] ?? null;
        $appKey = $apiKey->key_value;

        if (! $appId) {
            throw new \RuntimeException('Adzuna app_id not configured in extra_data.');
        }

        $query = $this->buildSearchQuery($filter);
        $country = 'gb'; // Default to GB; could be made configurable

        $response = Http::get("https://api.adzuna.com/v1/api/jobs/{$country}/search/1", [
            'app_id' => $appId,
            'app_key' => $appKey,
            'what' => $query,
            'results_per_page' => 50,
            'max_days_old' => 7,
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('Adzuna API request failed: '.$response->status());
        }

        $jobs = $response->json('results', []);
        $count = 0;

        foreach ($jobs as $job) {
            $inserted = $this->upsertJob($user, [
                'external_id' => (string) ($job['id'] ?? Str::random(20)),
                'source_platform' => JobListing::PLATFORM_ADZUNA,
                'title' => Str::limit($job['title'] ?? 'Untitled', 497),
                'company_name' => $job['company']['display_name'] ?? null,
                'company_logo_url' => null,
                'description' => Str::limit($job['description'] ?? '', 2000),
                'location' => $job['location']['display_name'] ?? null,
                'location_type' => $this->detectLocationType($job['title'] ?? '', $job['description'] ?? ''),
                'country' => $job['location']['area'][0] ?? null,
                'salary_min' => isset($job['salary_min']) ? (int) $job['salary_min'] : null,
                'salary_max' => isset($job['salary_max']) ? (int) $job['salary_max'] : null,
                'salary_currency' => 'GBP',
                'salary_text' => isset($job['salary_min']) ? ($job['salary_min'].' - '.($job['salary_max'] ?? '').' GBP') : null,
                'tech_stack' => $this->extractTechStack($job['description'] ?? ''),
                'job_url' => $job['redirect_url'] ?? '',
                'posted_at' => isset($job['created']) ? Carbon::parse($job['created']) : null,
            ]);

            if ($inserted) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Fetch Pakistani job sites (Rozee.pk, Mustakbil) via SerpAPI Google search.
     */
    public function fetchFromSerpApi(User $user, JobSearchFilter $filter, string $site): int
    {
        $apiKey = ApiKey::forUser($user->id)->forProvider(ApiKey::PROVIDER_SERPAPI)->connected()->first();

        if (! $apiKey) {
            throw new \RuntimeException('SerpAPI key not configured or not connected.');
        }

        $query = $this->buildSearchQuery($filter);
        $siteDomain = $site === 'rozee' ? 'rozee.pk' : 'mustakbil.com';
        $platform = $site === 'rozee' ? JobListing::PLATFORM_ROZEE : JobListing::PLATFORM_MUSTAKBIL;

        $response = Http::get('https://serpapi.com/search', [
            'engine' => 'google_jobs',
            'q' => $query." site:{$siteDomain}",
            'api_key' => $apiKey->key_value,
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException("SerpAPI request failed for {$site}: ".$response->status());
        }

        $jobs = $response->json('jobs_results', []);
        $count = 0;

        foreach ($jobs as $job) {
            $jobUrl = '';
            if (isset($job['related_links'][0]['link'])) {
                $jobUrl = $job['related_links'][0]['link'];
            } elseif (isset($job['link'])) {
                $jobUrl = $job['link'];
            }

            $inserted = $this->upsertJob($user, [
                'external_id' => md5(($job['title'] ?? '').($job['company_name'] ?? '').$platform),
                'source_platform' => $platform,
                'title' => Str::limit($job['title'] ?? 'Untitled', 497),
                'company_name' => $job['company_name'] ?? null,
                'company_logo_url' => $job['thumbnail'] ?? null,
                'description' => Str::limit($job['description'] ?? '', 2000),
                'location' => $job['location'] ?? 'Pakistan',
                'location_type' => $this->detectLocationType($job['title'] ?? '', $job['description'] ?? ''),
                'country' => 'Pakistan',
                'salary_min' => null,
                'salary_max' => null,
                'salary_currency' => 'PKR',
                'salary_text' => null,
                'tech_stack' => $this->extractTechStack($job['description'] ?? ''),
                'job_url' => $jobUrl,
                'posted_at' => isset($job['detected_extensions']['posted_at']) ? $this->parseRelativeDate($job['detected_extensions']['posted_at']) : null,
            ]);

            if ($inserted) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * AI-powered deduplication using title + company similarity.
     */
    public function deduplicateJobs(User $user): int
    {
        $jobs = JobListing::forUser($user->id)
            ->whereNull('duplicate_group_id')
            ->orderBy('fetched_at')
            ->get();

        $duplicatesFound = 0;
        $processed = [];

        foreach ($jobs as $job) {
            $normalizedTitle = $this->normalizeString($job->title);
            $normalizedCompany = $this->normalizeString($job->company_name ?? '');

            $matchFound = false;

            foreach ($processed as $groupId => $group) {
                $similarity = 0;
                similar_text($normalizedTitle, $group['title'], $similarity);

                $companySimilarity = 0;
                if ($normalizedCompany && $group['company']) {
                    similar_text($normalizedCompany, $group['company'], $companySimilarity);
                }

                if ($similarity > 85 && ($companySimilarity > 85 || (! $normalizedCompany && ! $group['company']))) {
                    $job->update([
                        'duplicate_group_id' => $groupId,
                        'is_duplicate_primary' => false,
                    ]);
                    $duplicatesFound++;
                    $matchFound = true;

                    break;
                }
            }

            if (! $matchFound) {
                $groupId = Str::uuid()->toString();
                $job->update([
                    'duplicate_group_id' => $groupId,
                    'is_duplicate_primary' => true,
                ]);
                $processed[$groupId] = [
                    'title' => $normalizedTitle,
                    'company' => $normalizedCompany,
                ];
            }
        }

        return $duplicatesFound;
    }

    /**
     * Get filtered, paginated job feed.
     */
    public function getFilteredFeed(User $user, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = JobListing::forUser($user->id)->visible();

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['platform'])) {
            $query->byPlatform($filters['platform']);
        }

        if (! empty($filters['location_type'])) {
            $query->byLocationType($filters['location_type']);
        }

        if (! empty($filters['country'])) {
            if ($filters['country'] === 'Pakistan') {
                $query->where('country', 'Pakistan');
            } else {
                $query->where(function ($q) {
                    $q->where('country', '!=', 'Pakistan')->orWhereNull('country');
                });
            }
        }

        if (! empty($filters['status'])) {
            if ($filters['status'] === 'unseen') {
                $query->whereNull('user_status');
            } else {
                $query->byStatus($filters['status']);
            }
        }

        if (! empty($filters['salary_min'])) {
            $query->where('salary_min', '>=', (int) $filters['salary_min']);
        }

        if (! empty($filters['salary_max'])) {
            $query->where('salary_max', '<=', (int) $filters['salary_max']);
        }

        return $query
            ->orderByDesc('posted_at')
            ->orderByDesc('fetched_at')
            ->paginate($perPage);
    }

    /**
     * Update job user status (interested, not_relevant, or null to clear).
     */
    public function updateJobStatus(JobListing $job, ?string $status): void
    {
        $job->update(['user_status' => $status]);
    }

    /**
     * Hide a job from the feed.
     */
    public function hideJob(JobListing $job): void
    {
        $job->update(['is_hidden' => true]);
    }

    /**
     * Get stat card data for the job feed.
     */
    public function getStats(User $user): array
    {
        $baseQuery = JobListing::forUser($user->id)->visible();

        $totalJobs = (clone $baseQuery)->count();
        $newToday = (clone $baseQuery)->whereDate('fetched_at', today())->count();
        $interestedCount = (clone $baseQuery)->byStatus(JobListing::STATUS_INTERESTED)->count();

        $platformsActive = JobFetchLog::where('user_id', $user->id)
            ->where('status', JobFetchLog::STATUS_SUCCESS)
            ->distinct('platform')
            ->count('platform');

        $lastFetch = $this->getLastFetchLog($user);

        return [
            'total_jobs' => $totalJobs,
            'new_today' => $newToday,
            'interested_count' => $interestedCount,
            'platforms_active' => $platformsActive,
            'last_fetch_at' => $lastFetch?->started_at,
        ];
    }

    /**
     * Get the most recent fetch log for this user.
     */
    public function getLastFetchLog(User $user): ?JobFetchLog
    {
        return JobFetchLog::where('user_id', $user->id)
            ->orderByDesc('started_at')
            ->first();
    }

    /**
     * Create a fetch log entry.
     */
    public function logFetch(User $user, string $platform, string $status, int $jobsFetched, int $duplicatesFound, ?string $error, Carbon $startedAt): JobFetchLog
    {
        return JobFetchLog::create([
            'user_id' => $user->id,
            'platform' => $platform,
            'status' => $status,
            'jobs_fetched' => $jobsFetched,
            'duplicates_found' => $duplicatesFound,
            'error_message' => $error,
            'started_at' => $startedAt,
            'completed_at' => now(),
        ]);
    }

    /**
     * Upsert a job listing — returns true if inserted, false if already exists.
     */
    private function upsertJob(User $user, array $data): bool
    {
        $existing = JobListing::where('user_id', $user->id)
            ->where('external_id', $data['external_id'])
            ->where('source_platform', $data['source_platform'])
            ->exists();

        if ($existing) {
            return false;
        }

        JobListing::create(array_merge($data, [
            'user_id' => $user->id,
            'fetched_at' => now(),
        ]));

        return true;
    }

    /**
     * Build a search query string from user filters.
     */
    private function buildSearchQuery(JobSearchFilter $filter): string
    {
        $parts = [];

        if (! empty($filter->preferred_titles)) {
            $parts[] = implode(' OR ', $filter->preferred_titles);
        }

        if (! empty($filter->preferred_tech)) {
            $parts[] = implode(' ', array_slice($filter->preferred_tech, 0, 5));
        }

        return implode(' ', $parts) ?: 'software developer';
    }

    /**
     * Check if a job title loosely matches the user filter.
     */
    private function matchesFilter(string $title, JobSearchFilter $filter): bool
    {
        if (empty($filter->preferred_titles) && empty($filter->preferred_tech)) {
            return true;
        }

        $titleLower = Str::lower($title);

        foreach ($filter->preferred_titles ?? [] as $preferred) {
            if (Str::contains($titleLower, Str::lower($preferred))) {
                return true;
            }
        }

        foreach ($filter->preferred_tech ?? [] as $tech) {
            if (Str::contains($titleLower, Str::lower($tech))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Normalize a string for comparison (lowercase, strip special chars).
     */
    private function normalizeString(string $value): string
    {
        $value = Str::lower($value);
        $value = preg_replace('/[^a-z0-9\s]/', '', $value);

        return trim(preg_replace('/\s+/', ' ', $value));
    }

    /**
     * Detect location type from job text.
     */
    private function detectLocationType(string $title, string $description): string
    {
        $text = Str::lower($title.' '.$description);

        if (Str::contains($text, ['hybrid'])) {
            return 'hybrid';
        }

        if (Str::contains($text, ['remote', 'work from home', 'wfh'])) {
            return 'remote';
        }

        return 'onsite';
    }

    /**
     * Extract tech stack keywords from a description.
     */
    private function extractTechStack(string $description): array
    {
        $knownTech = [
            'PHP', 'Laravel', 'JavaScript', 'TypeScript', 'React', 'Vue', 'Angular',
            'Node.js', 'Python', 'Django', 'Flask', 'Java', 'Spring', 'C#', '.NET',
            'Ruby', 'Rails', 'Go', 'Rust', 'Swift', 'Kotlin', 'Flutter', 'Dart',
            'AWS', 'Azure', 'GCP', 'Docker', 'Kubernetes', 'Redis', 'PostgreSQL',
            'MySQL', 'MongoDB', 'GraphQL', 'REST', 'Git', 'CI/CD', 'Terraform',
            'Next.js', 'Nuxt', 'Tailwind', 'Bootstrap', 'Svelte', 'Livewire',
        ];

        $found = [];

        foreach ($knownTech as $tech) {
            if (Str::contains($description, $tech, true)) {
                $found[] = $tech;
            }
        }

        return array_slice(array_unique($found), 0, 15);
    }

    /**
     * Parse a salary value to integer.
     */
    private function parseSalaryValue(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $numeric = (int) preg_replace('/[^0-9]/', '', (string) $value);

        return $numeric > 0 ? $numeric : null;
    }

    /**
     * Parse relative date strings like "3 days ago" to Carbon.
     */
    private function parseRelativeDate(string $relativeDate): ?Carbon
    {
        try {
            return Carbon::parse($relativeDate);
        } catch (\Exception) {
            return null;
        }
    }
}
