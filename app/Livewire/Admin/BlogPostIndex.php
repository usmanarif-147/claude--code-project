<?php

namespace App\Livewire\Admin;

use App\Models\BlogPost;
use App\Services\BlogPostService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class BlogPostIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function delete(BlogPostService $service, int $id): void
    {
        $service->delete(BlogPost::findOrFail($id));
        session()->flash('success', 'Blog post deleted successfully.');
    }

    public function render()
    {
        $query = BlogPost::query()->with('tags')->latest();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('excerpt', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->statusFilter === 'draft') {
            $query->where('status', 'draft');
        } elseif ($this->statusFilter === 'published') {
            $query->where('status', 'published');
        }

        return view('livewire.admin.blog-post-index', [
            'posts' => $query->paginate(10),
        ]);
    }
}
