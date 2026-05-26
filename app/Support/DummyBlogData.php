<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * TEMPORARY frontend placeholder data for the public blog pages.
 *
 * ⚠️  This class exists ONLY so the public blog UI can be built and reviewed
 *     before the real data layer is wired up. It performs NO database access.
 *
 * ── BACKEND HANDOFF ───────────────────────────────────────────────────────
 * To switch to real data, delete this class and replace the call sites with
 * the equivalent BlogPost queries:
 *   - latest(3)    →  BlogPost::published()->latest('published_at')->take(3)->get()
 *   - paginate(12) →  BlogPost::published()->latest('published_at')->paginate(12)
 *   - find($slug)  →  BlogPost::published()->where('slug', $slug)->firstOrFail()
 *   - count()      →  BlogPost::published()->count()
 * The views/components expect these exact fields (mirrors the BlogPost model).
 * ──────────────────────────────────────────────────────────────────────────
 */
class DummyBlogData
{
    /** Full set of placeholder posts, newest first. */
    public static function all(): Collection
    {
        return collect(self::raw())
            ->map(fn (array $post) => (object) $post)
            ->sortByDesc('published_at')
            ->values();
    }

    public static function latest(int $limit): Collection
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
        $posts = [
            ['Building a Livewire 4 Dashboard from Scratch', ['Laravel', 'Livewire'], 7, 1842, 4],
            ['Tailwind CSS 4: What Actually Changed', ['CSS', 'Tailwind'], 5, 3120, 11],
            ['Structuring Large Laravel Apps with Service Classes', ['Laravel', 'Architecture'], 9, 2456, 19],
            ['A Practical Guide to Eloquent Query Scopes', ['Laravel', 'Eloquent'], 6, 1530, 27],
            ['Deploying Laravel on a VPS the Right Way', ['DevOps', 'Laravel'], 11, 4087, 33],
            ['Why I Moved from Vue to Alpine.js', ['JavaScript', 'Alpine'], 4, 980, 41],
            ["Writing Tests You'll Actually Maintain", ['Testing', 'PHP'], 8, 1265, 52],
            ['Understanding PHP 8.2 Readonly Classes', ['PHP'], 5, 1710, 60],
            ['Designing Dark-Mode-First Interfaces', ['Design', 'CSS'], 6, 2890, 68],
            ['Queues and Jobs: Background Work in Laravel', ['Laravel', 'Queues'], 7, 1340, 77],
            ['The Repository Pattern Is Usually Overkill', ['Architecture', 'PHP'], 6, 2210, 85],
            ['Optimizing Database Indexes for Read-Heavy Apps', ['Database', 'Performance'], 10, 1995, 94],
            ['Building a REST API with Laravel Sanctum', ['Laravel', 'API'], 8, 3402, 102],
            ['Frontend Performance: Shipping Less JavaScript', ['Performance', 'JavaScript'], 7, 2615, 113],
            ['From Idea to Deployed in a Weekend', ['Productivity'], 5, 1120, 125],
        ];

        return collect($posts)->map(function (array $row, int $i) {
            [$title, $tags, $readingTime, $views, $daysAgo] = $row;
            $slug = \Illuminate\Support\Str::slug($title);

            return [
                'title' => $title,
                'slug' => $slug,
                'excerpt' => self::excerpt($title),
                'content' => self::body($title),
                'cover_image' => "https://picsum.photos/seed/{$slug}/800/450",
                'published_at' => Carbon::now()->subDays($daysAgo),
                'reading_time_minutes' => $readingTime,
                'view_count' => $views,
                'tags' => $tags,
            ];
        })->all();
    }

    private static function excerpt(string $title): string
    {
        return "A hands-on, practical look at \"{$title}\" — the decisions, trade-offs, "
            .'and patterns that held up in real projects, with code you can copy.';
    }

    private static function body(string $title): string
    {
        return <<<HTML
<p>This article is a placeholder used while the public blog pages are being designed.
It walks through <strong>{$title}</strong> from first principles, then gets practical fast.</p>

<h2>Why this matters</h2>
<p>Every project eventually hits the same wall: the easy approach stops scaling and you
need a pattern that holds up. Here we look at what actually works in production rather
than what looks clever in a demo.</p>

<ul>
    <li>Start with the simplest thing that could work.</li>
    <li>Measure before you optimise — guesses are expensive.</li>
    <li>Prefer boring, well-understood tools over novelty.</li>
</ul>

<h2>A quick example</h2>
<p>The snippet below shows the core idea in a few lines:</p>
<pre><code>public function handle(Request \$request): Response
{
    return Cache::remember('key', now()->addHour(), function () {
        return \$this->service->compute();
    });
}</code></pre>

<h3>What to watch out for</h3>
<p>The most common mistake is reaching for abstraction too early. Wait until the third
time you copy-paste something before you extract it — by then the right shape is obvious.</p>

<blockquote>Make it work, make it right, make it fast — in that order.</blockquote>

<p>That's the short version. The full write-up will land here once the content pipeline
is connected; for now this stands in so the layout and reading experience can be reviewed.</p>
HTML;
    }
}
