<?php

use App\Livewire\Admin\Tasks\ProjectBoard\ProjectBoardIndex;
use Illuminate\Support\Facades\Route;

Route::get('/tasks/project-board', ProjectBoardIndex::class)
    ->name('admin.tasks.project-board.index');
