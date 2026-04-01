<?php

namespace App\Livewire\Admin\Youtube\VideoIdeas;

use App\Models\VideoIdea;
use App\Services\VideoIdeaService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class VideoIdeaForm extends Component
{
    public ?int $videoIdeaId = null;

    public string $title = '';

    public string $description = '';

    public string $priority = 'medium';

    public string $status = 'idea';

    public function mount(?VideoIdea $videoIdea = null): void
    {
        if ($videoIdea && $videoIdea->exists) {
            $this->videoIdeaId = $videoIdea->id;
            $this->title = $videoIdea->title;
            $this->description = $videoIdea->description ?? '';
            $this->priority = $videoIdea->priority;
            $this->status = $videoIdea->status;
        }
    }

    public function save(VideoIdeaService $service): void
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'priority' => 'required|in:high,medium,low',
            'status' => 'required|in:idea,scripting,recording,editing,published',
        ]);

        if ($this->videoIdeaId) {
            $videoIdea = VideoIdea::findOrFail($this->videoIdeaId);
            $service->update($videoIdea, $validated);
            $message = 'Video idea updated successfully.';
        } else {
            $service->store($validated);
            $message = 'Video idea created successfully.';
        }

        session()->flash('success', $message);
        $this->redirect(route('admin.youtube.video-ideas.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.youtube.video-ideas.form');
    }
}
