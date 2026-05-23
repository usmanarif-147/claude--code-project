<?php

namespace App\Livewire\Admin\BlogAndPost;

use App\Models\BlogAndPost\Post;
use App\Services\BlogAndPostPublisher;
use App\Services\BlogAndPostService;
use App\Services\OgTagFetcher;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class ArticlePostForm extends Component
{
    public ?Post $post = null;

    public string $title = '';

    public string $caption = '';

    public string $hashtags = '';

    public string $article_url = '';

    public ?string $scheduled_date = null;

    public ?string $scheduled_time = null;

    public array $ogPreview = [
        'ok' => false,
        'title' => null,
        'description' => null,
        'image' => null,
        'site_name' => null,
        'error' => null,
    ];

    public function mount(?Post $post = null): void
    {
        if ($post && $post->exists) {
            $this->post = $post;
            $this->title = $post->title;
            $this->caption = $post->caption ?? '';
            $this->hashtags = $post->hashtags ?? '';
            $this->article_url = $post->meta['url'] ?? '';

            if ($post->scheduled_at) {
                $this->scheduled_date = $post->scheduled_at->format('Y-m-d');
                $this->scheduled_time = $post->scheduled_at->format('H:i');
            }

            if ($this->article_url !== '') {
                $this->ogPreview = app(OgTagFetcher::class)->fetch($this->article_url);
            }
        }
    }

    public function updatedArticleUrl(OgTagFetcher $fetcher): void
    {
        if ($this->article_url === '') {
            $this->ogPreview = [
                'ok' => false,
                'title' => null,
                'description' => null,
                'image' => null,
                'site_name' => null,
                'error' => null,
            ];

            return;
        }

        if (! filter_var($this->article_url, FILTER_VALIDATE_URL)) {
            $this->ogPreview = [
                'ok' => false,
                'title' => null,
                'description' => null,
                'image' => null,
                'site_name' => null,
                'error' => 'Invalid URL',
            ];

            return;
        }

        $this->ogPreview = $fetcher->fetch($this->article_url);
    }

    public function saveDraft(BlogAndPostService $service): void
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'caption' => 'required|string',
            'hashtags' => 'nullable|string',
            'article_url' => 'required|url|max:500',
        ]);

        $data = [
            'title' => $validated['title'],
            'caption' => $validated['caption'],
            'hashtags' => $validated['hashtags'] ?? '',
            'article_url' => $validated['article_url'],
            'type' => Post::TYPE_ARTICLE,
            'status' => 'draft',
            'scheduled_at' => null,
        ];

        $this->persist($service, $data, 'Article post saved as draft.');
    }

    public function schedule(BlogAndPostService $service): void
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'caption' => 'required|string',
            'hashtags' => 'nullable|string',
            'article_url' => 'required|url|max:500',
            'scheduled_date' => 'required|date',
            'scheduled_time' => ['required', 'string', 'regex:/^\d{2}:\d{2}$/'],
        ]);

        $when = $this->roundToFifteenMinutes(
            Carbon::parse($validated['scheduled_date'].' '.$validated['scheduled_time'])
        );

        $data = [
            'title' => $validated['title'],
            'caption' => $validated['caption'],
            'hashtags' => $validated['hashtags'] ?? '',
            'article_url' => $validated['article_url'],
            'type' => Post::TYPE_ARTICLE,
            'status' => 'scheduled',
            'scheduled_at' => $when,
        ];

        $this->persist($service, $data, 'Article post scheduled successfully.');
    }

    public function publishNow(BlogAndPostService $service, BlogAndPostPublisher $publisher): void
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'caption' => 'required|string',
            'hashtags' => 'nullable|string',
            'article_url' => 'required|url|max:500',
        ]);

        $data = [
            'title' => $validated['title'],
            'caption' => $validated['caption'],
            'hashtags' => $validated['hashtags'] ?? '',
            'article_url' => $validated['article_url'],
            'type' => Post::TYPE_ARTICLE,
            'status' => $this->post?->status ?? 'draft',
            'scheduled_at' => null,
        ];

        if ($this->post) {
            $service->update($this->post, $data);
        } else {
            $this->post = $service->create($data);
        }

        $this->post->refresh();
        $publisher->publish($this->post);
        $this->post->refresh();

        if ($this->post->status === 'posted') {
            session()->flash('success', 'Published to LinkedIn: '.($this->post->linkedin_post_url ?? 'success'));
        } else {
            session()->flash('error', 'Publish failed: '.($this->post->linkedin_error ?? 'unknown error'));
        }

        $this->redirect(route('admin.blog-and-post.index'), navigate: true);
    }

    private function persist(BlogAndPostService $service, array $data, string $successMessage): void
    {
        if ($this->post) {
            $service->update($this->post, $data);
        } else {
            $service->create($data);
        }

        session()->flash('success', $successMessage);
        $this->redirect(route('admin.blog-and-post.index'), navigate: true);
    }

    private function roundToFifteenMinutes(Carbon $when): Carbon
    {
        $minutes = (int) $when->minute;
        $remainder = $minutes % 15;

        if ($remainder === 0) {
            return $when->copy()->second(0);
        }

        $adjustment = $remainder < 8 ? -$remainder : (15 - $remainder);

        return $when->copy()->second(0)->addMinutes($adjustment);
    }

    public function render()
    {
        return view('livewire.admin.blog-and-post.article-form');
    }
}
