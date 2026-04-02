<?php

namespace App\Livewire\Admin\Personal\NotesScratchpad;

use App\Models\Note;
use App\Services\NoteService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class NotesScratchpadForm extends Component
{
    public ?int $noteId = null;

    public string $title = '';

    public string $content = '';

    public function mount(?Note $note = null): void
    {
        if ($note && $note->exists) {
            $this->noteId = $note->id;
            $this->title = $note->title;
            $this->content = $note->content ?? '';
        }
    }

    public function save(NoteService $service): void
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string|max:50000',
        ]);

        if ($this->noteId) {
            $service->updateNote(Note::findOrFail($this->noteId), $validated);
            $message = 'Note updated successfully.';
        } else {
            $service->createNote($validated);
            $message = 'Note created successfully.';
        }

        session()->flash('success', $message);
        $this->redirect(route('admin.personal.notes-scratchpad.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.personal.notes-scratchpad.form');
    }
}
