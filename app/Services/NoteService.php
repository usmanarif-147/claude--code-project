<?php

namespace App\Services;

use App\Models\Note;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NoteService
{
    public function getFilteredNotes(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = Note::query()
            ->orderByDesc('is_pinned')
            ->orderByDesc('updated_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%'.$search.'%')
                    ->orWhere('content', 'like', '%'.$search.'%');
            });
        }

        return $query->paginate($perPage);
    }

    public function createNote(array $data): Note
    {
        return Note::create($data);
    }

    public function updateNote(Note $note, array $data): Note
    {
        $note->update($data);

        return $note;
    }

    public function deleteNote(Note $note): void
    {
        $note->delete();
    }

    public function togglePin(Note $note): Note
    {
        $note->is_pinned = ! $note->is_pinned;
        $note->save();

        return $note;
    }
}
