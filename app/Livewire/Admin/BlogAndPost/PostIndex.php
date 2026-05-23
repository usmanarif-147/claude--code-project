<?php

namespace App\Livewire\Admin\BlogAndPost;

use App\Models\BlogAndPost\Post;
use App\Services\BlogAndPostPublisher;
use App\Services\BlogAndPostService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class PostIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $typeFilter = 'all';

    #[Url]
    public string $statusFilter = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function delete(BlogAndPostService $service, int $id): void
    {
        $service->delete(Post::findOrFail($id));
        session()->flash('success', 'Post deleted successfully.');
        $this->redirect(route('admin.blog-and-post.index'), navigate: true);
    }

    public function retry(BlogAndPostPublisher $publisher, int $id): void
    {
        $post = Post::findOrFail($id);

        if ($post->status !== 'failed') {
            session()->flash('error', 'Only failed posts can be retried.');
            $this->redirect(route('admin.blog-and-post.index'), navigate: true);

            return;
        }

        $publisher->publish($post);
        $post->refresh();

        if ($post->status === 'posted') {
            session()->flash('success', 'Post published to LinkedIn.');
        } else {
            session()->flash('error', 'Retry failed: '.($post->linkedin_error ?? 'unknown error'));
        }

        $this->redirect(route('admin.blog-and-post.index'), navigate: true);
    }

    public function render()
    {
        $query = Post::query()->orderByDesc('created_at');

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('caption', 'like', '%'.$this->search.'%');
            });
        }

        if (in_array($this->typeFilter, [Post::TYPE_TEXT, Post::TYPE_IMAGE, Post::TYPE_VIDEO, Post::TYPE_ARTICLE], true)) {
            $query->where('type', $this->typeFilter);
        }

        if (in_array($this->statusFilter, ['draft', 'scheduled', 'publishing', 'posted', 'failed'], true)) {
            $query->where('status', $this->statusFilter);
        }

        return view('livewire.admin.blog-and-post.index', [
            'posts' => $query->paginate(10),
        ]);
    }
}
