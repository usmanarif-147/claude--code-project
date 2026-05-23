<?php

namespace App\Livewire\Admin\Social\Scheduler;

use App\Models\Social\ScheduledPost;
use App\Services\SocialPostService;
use App\Services\SocialPublishingService;
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
    public string $statusFilter = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function delete(SocialPostService $service, int $id): void
    {
        $service->delete(ScheduledPost::findOrFail($id));
        session()->flash('success', 'Scheduled post deleted successfully.');
        $this->redirect(route('admin.social.scheduler.index'), navigate: true);
    }

    /**
     * Re-attempt publishing a previously-failed post.
     */
    public function retry(SocialPublishingService $publisher, int $id): void
    {
        $post = ScheduledPost::findOrFail($id);

        if ($post->status !== 'failed') {
            session()->flash('error', 'Only failed posts can be retried.');

            return;
        }

        $publisher->publish($post);
        $post->refresh();

        if ($post->status === 'posted') {
            session()->flash('success', 'Post published to LinkedIn.');
        } else {
            session()->flash('error', 'Retry failed: '.($post->linkedin_error ?? 'unknown error'));
        }

        $this->redirect(route('admin.social.scheduler.index'), navigate: true);
    }

    public function render()
    {
        $query = ScheduledPost::query()->latest();

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('caption', 'like', '%'.$this->search.'%');
            });
        }

        if (in_array($this->statusFilter, ['draft', 'scheduled', 'publishing', 'posted', 'failed'], true)) {
            $query->where('status', $this->statusFilter);
        }

        return view('livewire.admin.social.scheduler.index', [
            'posts' => $query->paginate(10),
        ]);
    }
}
