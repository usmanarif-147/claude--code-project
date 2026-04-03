<?php

namespace App\Livewire\Admin\Youtube\Subscriptions;

use App\Services\YouTubeSubscriptionService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class SavedVideoIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    public string $selectedVideoId = '';

    public bool $showPlayer = false;

    public ?array $selectedVideoData = null;

    public ?int $editingNoteId = null;

    public string $noteText = '';

    public function playVideo(string $videoId, string $title, string $channelTitle, ?string $description, ?string $publishedAt, int $viewCount): void
    {
        $this->selectedVideoId = $videoId;
        $this->selectedVideoData = [
            'title' => $title,
            'channel_title' => $channelTitle,
            'description' => $description,
            'published_at' => $publishedAt,
            'view_count' => $viewCount,
        ];
        $this->showPlayer = true;
    }

    public function closePlayer(): void
    {
        $this->showPlayer = false;
        $this->selectedVideoId = '';
        $this->selectedVideoData = null;
    }

    public function unsaveVideo(int $savedVideoId): void
    {
        $service = app(YouTubeSubscriptionService::class);
        $service->unsaveVideo(auth()->id(), $savedVideoId);
        session()->flash('success', 'Video removed from favorites.');
    }

    public function editNote(int $savedVideoId, ?string $currentNote): void
    {
        $this->editingNoteId = $savedVideoId;
        $this->noteText = $currentNote ?? '';
    }

    public function saveNote(): void
    {
        if ($this->editingNoteId) {
            $service = app(YouTubeSubscriptionService::class);
            $service->updateSavedVideoNotes(auth()->id(), $this->editingNoteId, $this->noteText);
            $this->editingNoteId = null;
            $this->noteText = '';
            session()->flash('success', 'Note saved.');
        }
    }

    public function cancelNote(): void
    {
        $this->editingNoteId = null;
        $this->noteText = '';
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $service = app(YouTubeSubscriptionService::class);

        return view('livewire.admin.youtube.subscriptions.saved-videos', [
            'savedVideos' => $service->getSavedVideos(auth()->id(), $this->search ?: null),
        ]);
    }
}
