<?php

namespace App\Livewire\Admin;

use App\Models\BlogPost;
use App\Services\BlogPostService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
class BlogPostForm extends Component
{
    use WithFileUploads;

    public ?BlogPost $blogPost = null;

    public string $title = '';

    public string $excerpt = '';

    public string $content = '';

    public string $status = 'draft';

    public string $meta_title = '';

    public string $meta_description = '';

    // Tags
    public array $tags = [];

    public string $tagInput = '';

    // Image handling
    public $coverImage = null;

    public ?string $existingCoverImage = null;

    public bool $removeCover = false;

    public function mount(?BlogPost $blogPost = null): void
    {
        if ($blogPost && $blogPost->exists) {
            $this->blogPost = $blogPost;
            $this->title = $blogPost->title;
            $this->excerpt = $blogPost->excerpt ?? '';
            $this->content = $blogPost->content ?? '';
            $this->status = $blogPost->status;
            $this->meta_title = $blogPost->meta_title ?? '';
            $this->meta_description = $blogPost->meta_description ?? '';
            $this->existingCoverImage = $blogPost->cover_image;
            $this->tags = $blogPost->tags->pluck('tag')->toArray();
        }
    }

    public function addTag(): void
    {
        $tag = trim($this->tagInput);

        if ($tag !== '') {
            $this->tags[] = $tag;
            $this->tagInput = '';
        }
    }

    public function removeTag(int $index): void
    {
        array_splice($this->tags, $index, 1);
    }

    public function removeCoverImage(): void
    {
        $this->coverImage = null;
        $this->existingCoverImage = null;
        $this->removeCover = true;
    }

    public function saveDraft(BlogPostService $service): void
    {
        $this->status = 'draft';
        $this->savePost($service);
    }

    public function publish(BlogPostService $service): void
    {
        $this->status = 'published';
        $this->savePost($service);
    }

    private function savePost(BlogPostService $service): void
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'coverImage' => 'nullable|image|max:2048|mimes:jpg,jpeg,png,webp',
            'tags' => 'array',
            'tags.*' => 'string|max:50',
        ]);

        $postData = collect($validated)->except(['coverImage', 'tags'])->toArray();
        $postData['status'] = $this->status;

        if ($this->status === 'published') {
            $postData['published_at'] = now();
        } else {
            $postData['published_at'] = null;
        }

        if ($this->removeCover && ! $this->coverImage) {
            $postData['cover_image'] = null;
        }

        if ($this->blogPost) {
            $service->update(
                $this->blogPost,
                $postData,
                $this->coverImage,
                $this->tags,
            );
            $message = 'Blog post updated successfully.';
        } else {
            $service->create(
                $postData,
                $this->coverImage,
                $this->tags,
            );
            $message = 'Blog post created successfully.';
        }

        session()->flash('success', $message);
        $this->redirect(route('admin.blog.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.blog-post-form');
    }
}
