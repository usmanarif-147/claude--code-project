<?php

namespace App\Livewire\Admin\Youtube\Subscriptions;

use App\Models\YouTube\YouTubeSavedVideo;
use App\Services\YouTubeSubscriptionService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class VideoFeedIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $filterChannel = '';

    #[Url]
    public string $filterDateFrom = '';

    #[Url]
    public string $filterDateTo = '';

    #[Url]
    public string $filterCategory = '';

    #[Url]
    public string $filterLanguage = '';

    #[Url]
    public string $filterDuration = '';

    #[Url]
    public string $search = '';

    public string $selectedVideoId = '';

    public bool $showPlayer = false;

    public ?array $selectedVideoData = null;

    public array $savedVideoIds = [];

    public Collection $subscriptions;

    public int $newVideoCount = 0;

    public function mount(YouTubeSubscriptionService $service): void
    {
        $userId = auth()->id();
        $this->subscriptions = $service->getSubscriptions($userId);
        $this->newVideoCount = $service->getNewVideoCount($userId);
        $this->loadSavedVideoIds();
    }

    public function syncFeed(): void
    {
        $service = app(YouTubeSubscriptionService::class);
        $result = $service->syncAllSubscriptions(auth()->id());
        $this->newVideoCount = $service->getNewVideoCount(auth()->id());
        $this->loadSavedVideoIds();
        session()->flash('success', "Synced {$result['synced']} channels, found {$result['new_videos']} new videos.");
    }

    public function playVideo(string $videoId): void
    {
        $service = app(YouTubeSubscriptionService::class);
        $video = $service->getVideoDetails(auth()->id(), $videoId);

        if ($video) {
            $this->selectedVideoId = $videoId;
            $this->selectedVideoData = [
                'title' => $video->title,
                'channel_title' => $video->channel_title,
                'description' => $video->description,
                'published_at' => $video->published_at?->format('M j, Y'),
                'view_count' => $video->view_count,
                'like_count' => $video->like_count,
                'comment_count' => $video->comment_count,
            ];
            $this->showPlayer = true;

            if ($video->is_new) {
                $video->update(['is_new' => false]);
                $this->newVideoCount = max(0, $this->newVideoCount - 1);
            }
        }
    }

    public function closePlayer(): void
    {
        $this->showPlayer = false;
        $this->selectedVideoId = '';
        $this->selectedVideoData = null;
    }

    public function saveVideo(string $videoId): void
    {
        $service = app(YouTubeSubscriptionService::class);

        try {
            $service->saveVideo(auth()->id(), $videoId);
            $this->savedVideoIds[] = $videoId;
            session()->flash('success', 'Video saved to favorites.');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function unsaveVideo(string $videoId): void
    {
        $service = app(YouTubeSubscriptionService::class);
        $saved = YouTubeSavedVideo::where('user_id', auth()->id())
            ->where('video_id', $videoId)
            ->first();

        if ($saved) {
            $service->unsaveVideo(auth()->id(), $saved->id);
            $this->savedVideoIds = array_values(array_diff($this->savedVideoIds, [$videoId]));
            session()->flash('success', 'Video removed from favorites.');
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['filterChannel', 'filterDateFrom', 'filterDateTo', 'filterCategory', 'filterLanguage', 'filterDuration', 'search']);
        $this->resetPage();
    }

    public function markAllAsRead(): void
    {
        $service = app(YouTubeSubscriptionService::class);
        $service->markVideosAsRead(auth()->id());
        $this->newVideoCount = 0;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterChannel(): void
    {
        $this->resetPage();
    }

    public function updatingFilterDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingFilterDateTo(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCategory(): void
    {
        $this->resetPage();
    }

    public function updatingFilterLanguage(): void
    {
        $this->resetPage();
    }

    public function updatingFilterDuration(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $service = app(YouTubeSubscriptionService::class);

        $videos = $service->getVideoFeed(auth()->id(), [
            'channel_id' => $this->filterChannel,
            'date_from' => $this->filterDateFrom,
            'date_to' => $this->filterDateTo,
            'category_id' => $this->filterCategory,
            'language' => $this->filterLanguage,
            'duration' => $this->filterDuration,
            'search' => $this->search,
        ]);

        return view('livewire.admin.youtube.subscriptions.video-feed', [
            'videos' => $videos,
            'categories' => YouTubeSubscriptionService::categoryMap(),
        ]);
    }

    private function loadSavedVideoIds(): void
    {
        $this->savedVideoIds = YouTubeSavedVideo::where('user_id', auth()->id())
            ->pluck('video_id')
            ->toArray();
    }
}
