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
class ImagePostForm extends Component
{
    use WithFileUploads;

    public ?Post $post = null;

    public string $title = '';

    public string $caption = '';

    public string $hashtags = '';

    public ?string $scheduled_date = null;

    public ?string $scheduled_time = null;

    public $image = null;

    public ?string $existing_image_path = null;

    public bool $removeImage = false;

    public function mount(?Post $post = null): void
    {
        if ($post && $post->exists) {
            $this->post = $post;
            $this->title = $post->title;
            $this->caption = $post->caption ?? '';
            $this->hashtags = $post->hashtags ?? '';
            $this->existing_image_path = $post->meta['image_path'] ?? null;

            if ($post->scheduled_at) {
                $this->scheduled_date = $post->scheduled_at->format('Y-m-d');
                $this->scheduled_time = $post->scheduled_at->format('H:i');
            }
        }
    }

    public function markImageForRemoval(): void
    {
        $this->removeImage = true;
        $this->image = null;
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
            'type' => Post::TYPE_IMAGE,
            'status' => 'draft',
            'scheduled_at' => null,
        ];

        $this->persist($service, $data, 'Image post saved as draft.');
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
            'type' => Post::TYPE_IMAGE,
            'status' => 'scheduled',
            'scheduled_at' => $when,
        ];

        $this->persist($service, $data, 'Image post scheduled successfully.');
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
            'type' => Post::TYPE_IMAGE,
            'status' => $this->post?->status ?? 'draft',
            'scheduled_at' => null,
        ];

        if ($this->post) {
            $service->update($this->post, $data, $this->image, $this->removeImage);
        } else {
            $this->post = $service->create($data, $this->image);
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
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ];

        if ($includeSchedule) {
            $rules['scheduled_date'] = 'required|date';
            $rules['scheduled_time'] = ['required', 'string', 'regex:/^\d{2}:\d{2}$/'];
        }

        $validated = $this->validate($rules);

        // New posts must have an image. Existing posts must have either an
        // already-stored image (and not be removing it) or a fresh upload.
        $hasExistingImage = $this->existing_image_path && ! $this->removeImage;

        if (! $this->post && ! $this->image) {
            $this->addError('image', 'An image is required for image posts.');

            return null;
        }

        if ($this->post && ! $this->image && ! $hasExistingImage) {
            $this->addError('image', 'An image is required for image posts.');

            return null;
        }

        return $validated;
    }

    private function persist(BlogAndPostService $service, array $data, string $successMessage): void
    {
        if ($this->post) {
            $service->update($this->post, $data, $this->image, $this->removeImage);
        } else {
            $service->create($data, $this->image);
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
        return view('livewire.admin.blog-and-post.image-form');
    }
}
