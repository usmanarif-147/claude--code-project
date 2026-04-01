<?php

namespace App\Livewire\Admin\Youtube\VideoIdeas;

use App\Models\VideoIdea;
use App\Services\VideoIdeaService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class VideoIdeaIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $filterStatus = '';

    #[Url]
    public string $filterPriority = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterPriority(): void
    {
        $this->resetPage();
    }

    public function delete(VideoIdeaService $service, int $id): void
    {
        $videoIdea = VideoIdea::findOrFail($id);

        $service->delete($videoIdea);
        session()->flash('success', 'Video idea deleted successfully.');
    }

    public function moveToCalendar(VideoIdeaService $service, int $id): void
    {
        $videoIdea = VideoIdea::findOrFail($id);

        try {
            $service->moveToContentCalendar($videoIdea);
            session()->flash('success', 'Video idea moved to content calendar.');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $service = app(VideoIdeaService::class);

        return view('livewire.admin.youtube.video-ideas.index', [
            'videoIdeas' => $service->list([
                'search' => $this->search,
                'status' => $this->filterStatus,
                'priority' => $this->filterPriority,
            ]),
        ]);
    }
}
