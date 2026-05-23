<?php

namespace App\Livewire\Admin\BlogAndPost;

use App\Models\BlogAndPost\Post;
use App\Services\BlogAndPostPublisher;
use App\Services\BlogAndPostService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
class VideoPostForm extends Component
{
    use WithFileUploads;

    public ?Post $post = null;

    public string $title = '';

    public string $caption = '';

    public string $hashtags = '';

    public ?string $scheduled_date = null;

    public ?string $scheduled_time = null;

    public $video = null;

    public ?string $existing_video_path = null;

    public bool $removeVideo = false;

    public function mount(?Post $post = null): void
    {
        if ($post && $post->exists) {
            $this->post = $post;
            $this->title = $post->title;
            $this->caption = $post->caption ?? '';
            $this->hashtags = $post->hashtags ?? '';
            $this->existing_video_path = $post->meta['video_path'] ?? null;

            if ($post->scheduled_at) {
                $this->scheduled_date = $post->scheduled_at->format('Y-m-d');
                $this->scheduled_time = $post->scheduled_at->format('H:i');
            }
        }
    }

    public function markVideoForRemoval(): void
    {
        $this->removeVideo = true;
        $this->video = null;
    }

    public function saveDraft(BlogAndPostService $service): void
    {
        $validated = $this->validateInputs();
        if ($validated === null) {
            return;
        }

        $data = [
            'title' => $validated['title'],
            'caption' => $validated['caption'],
            'hashtags' => $validated['hashtags'] ?? '',
            'type' => Post::TYPE_VIDEO,
            'status' => 'draft',
            'scheduled_at' => null,
        ];

        $this->persist($service, $data, 'Video post saved as draft.');
    }

    public function schedule(BlogAndPostService $service): void
    {
        $validated = $this->validateInputs(includeSchedule: true);
        if ($validated === null) {
            return;
        }

        $when = $this->roundToFifteenMinutes(
            Carbon::parse($validated['scheduled_date'].' '.$validated['scheduled_time'])
        );

        $data = [
            'title' => $validated['title'],
            'caption' => $validated['caption'],
            'hashtags' => $validated['hashtags'] ?? '',
            'type' => Post::TYPE_VIDEO,
            'status' => 'scheduled',
            'scheduled_at' => $when,
        ];

        $this->persist($service, $data, 'Video post scheduled successfully.');
    }

    public function publishNow(BlogAndPostService $service, BlogAndPostPublisher $publisher): void
    {
        $validated = $this->validateInputs();
        if ($validated === null) {
            return;
        }

        $data = [
            'title' => $validated['title'],
            'caption' => $validated['caption'],
            'hashtags' => $validated['hashtags'] ?? '',
            'type' => Post::TYPE_VIDEO,
            'status' => $this->post?->status ?? 'draft',
            'scheduled_at' => null,
        ];

        if ($this->post) {
            $service->update($this->post, $data, $this->video, $this->removeVideo);
        } else {
            $this->post = $service->create($data, $this->video);
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

    private function validateInputs(bool $includeSchedule = false): ?array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'caption' => 'required|string',
            'hashtags' => 'nullable|string',
            'video' => 'nullable|file|mimetypes:video/mp4|max:102400',
        ];

        if ($includeSchedule) {
            $rules['scheduled_date'] = 'required|date';
            $rules['scheduled_time'] = ['required', 'string', 'regex:/^\d{2}:\d{2}$/'];
        }

        $validated = $this->validate($rules);

        $hasExistingVideo = $this->existing_video_path && ! $this->removeVideo;

        if (! $this->post && ! $this->video) {
            $this->addError('video', 'A video is required for video posts.');

            return null;
        }

        if ($this->post && ! $this->video && ! $hasExistingVideo) {
            $this->addError('video', 'A video is required for video posts.');

            return null;
        }

        return $validated;
    }

    private function persist(BlogAndPostService $service, array $data, string $successMessage): void
    {
        if ($this->post) {
            $service->update($this->post, $data, $this->video, $this->removeVideo);
        } else {
            $service->create($data, $this->video);
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
        return view('livewire.admin.blog-and-post.video-form');
    }
}
