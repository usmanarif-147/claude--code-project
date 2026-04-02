<?php

use App\Livewire\Admin\Personal\NotesScratchpad\NotesScratchpadForm;
use App\Livewire\Admin\Personal\NotesScratchpad\NotesScratchpadIndex;
use Illuminate\Support\Facades\Route;

Route::get('/personal/notes-scratchpad', NotesScratchpadIndex::class)->name('admin.personal.notes-scratchpad.index');
Route::get('/personal/notes-scratchpad/create', NotesScratchpadForm::class)->name('admin.personal.notes-scratchpad.create');
Route::get('/personal/notes-scratchpad/{note}/edit', NotesScratchpadForm::class)->name('admin.personal.notes-scratchpad.edit');
