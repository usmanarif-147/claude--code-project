<?php

namespace App\Livewire\Admin\Personal\NotesScratchpad;

use App\Models\Note;
use App\Services\NoteService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class NotesScratchpadIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function togglePin(NoteService $service, int $noteId): void
    {
        $note = Note::findOrFail($noteId);
        $updated = $service->togglePin($note);

        session()->flash('success', $updated->is_pinned ? 'Note pinned.' : 'Note unpinned.');
    }

    public function delete(NoteService $service, int $noteId): void
    {
        $service->deleteNote(Note::findOrFail($noteId));
        session()->flash('success', 'Note deleted.');
    }

    public function render(NoteService $service)
    {
        return view('livewire.admin.personal.notes-scratchpad.index', [
            'notes' => $service->getFilteredNotes($this->search),
        ]);
    }
}
