<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * TEMPORARY frontend placeholder data for the public project pages.
 *
 * ⚠️  This class exists ONLY so the public project UI can be built and reviewed
 *     before the real data layer is wired up. It performs NO database access.
 *
 * ── BACKEND HANDOFF ───────────────────────────────────────────────────────
 * To switch to real data, delete this class and replace the call sites with
 * the equivalent Project queries:
 *   - take(3)      →  Project::active()->ordered()->take(3)->get()
 *   - paginate(12) →  Project::active()->ordered()->paginate(12)
 *   - find($slug)  →  Project::active()->where('slug', $slug)->with('images')->firstOrFail()
 *   - count()      →  Project::active()->count()
 * The views/components expect these exact fields (mirrors the Project model).
 * The `gallery` array maps to the Project `images` relation later.
 * ──────────────────────────────────────────────────────────────────────────
 */
class DummyProjectData
{
    /** Full set of placeholder projects, in display order. */
    public static function all(): Collection
    {
        return collect(self::raw())
            ->map(fn (array $project) => (object) $project)
            ->values();
    }

    public static function take(int $limit): Collection
    {
        return self::all()->take($limit);
    }

    public static function count(): int
    {
        return self::all()->count();
    }

    public static function find(string $slug): ?object
    {
        return self::all()->firstWhere('slug', $slug);
    }

    public static function paginate(int $perPage = 12): LengthAwarePaginator
    {
        $all = self::all();
        $page = LengthAwarePaginator::resolveCurrentPage();

        return new LengthAwarePaginator(
            $all->forPage($page, $perPage)->values(),
            $all->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function raw(): array
    {
        // [title, tech[], hasDemo, hasGithub, isFeatured, completedDaysAgo]
        $projects = [
            ['Portfolio CMS', ['Laravel', 'Livewire', 'Tailwind'], true, true, true, 35],
            ['TaskFlow — Team Task Manager', ['Laravel', 'Vue', 'MySQL'], true, true, true, 70],
            ['DevMetrics Dashboard', ['Laravel', 'Alpine', 'Chart.js'], true, true, true, 110],
            ['Snippet Vault', ['Laravel', 'Livewire'], false, true, false, 145],
            ['Markdown Notes API', ['Laravel', 'Sanctum'], true, true, false, 180],
            ['E-Commerce Storefront', ['Laravel', 'Stripe', 'Tailwind'], true, false, false, 210],
            ['Realtime Chat App', ['Laravel', 'Reverb', 'Alpine'], true, true, false, 240],
            ['Invoice Generator', ['Laravel', 'DomPDF'], true, true, false, 275],
            ['URL Shortener', ['Laravel', 'Redis'], true, true, false, 305],
            ['Weather PWA', ['JavaScript', 'PWA', 'API'], true, true, false, 335],
            ['Recipe Finder', ['Vue', 'Tailwind', 'API'], true, false, false, 360],
            ['Fitness Tracker', ['Laravel', 'Livewire', 'Chart.js'], false, true, false, 400],
            ['Job Board', ['Laravel', 'MySQL', 'Tailwind'], true, true, false, 430],
            ['Crypto Price Alerts', ['Laravel', 'Queues', 'API'], true, true, false, 470],
            ['Blog Engine', ['Laravel', 'Livewire', 'Tailwind'], true, true, false, 510],
        ];

        return collect($projects)->map(function (array $row) {
            [$title, $tech, $hasDemo, $hasGithub, $featured, $daysAgo] = $row;
            $slug = Str::slug($title);

            return [
                'title' => $title,
                'slug' => $slug,
                'short_description' => self::shortDescription($title),
                'description' => self::body($title),
                'cover_image' => "https://picsum.photos/seed/proj-{$slug}/800/450",
                'gallery' => [
                    "https://picsum.photos/seed/proj-{$slug}-1/1200/700",
                    "https://picsum.photos/seed/proj-{$slug}-2/1200/700",
                ],
                'tech_stack' => $tech,
                'demo_url' => $hasDemo ? "https://example.com/{$slug}" : null,
                'github_url' => $hasGithub ? "https://github.com/usman/{$slug}" : null,
                'is_featured' => $featured,
                'completed_at' => Carbon::now()->subDays($daysAgo),
            ];
        })->all();
    }

    private static function shortDescription(string $title): string
    {
        return "{$title} — a placeholder project entry showcasing the layout. "
            .'Real content slots in here once the data layer is connected.';
    }

    private static function body(string $title): string
    {
        return <<<HTML
<p>This is placeholder copy for <strong>{$title}</strong>, used while the public project
pages are being designed. It describes the problem the project solves, the approach taken,
and the results.</p>

<h2>The problem</h2>
<p>Every good project starts with a real need. This section frames who it's for and what
pain point it removes, in plain language a non-technical reader can follow.</p>

<h2>How it works</h2>
<ul>
    <li>A clean, focused interface built for the core workflow.</li>
    <li>A well-structured backend with business logic in service classes.</li>
    <li>Sensible defaults, with room to grow as requirements change.</li>
</ul>

<p>Under the hood it leans on boring, dependable tools so it stays easy to maintain.</p>

<h3>What I'd do next</h3>
<p>A short note on planned improvements — caching, more tests, and a few UX refinements —
to show the project is a living thing rather than a finished demo.</p>

<blockquote>Ship something small that works, then make it better.</blockquote>
HTML;
    }
}
