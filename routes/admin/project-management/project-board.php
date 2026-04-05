<?php

use App\Livewire\Admin\ProjectManagement\ProjectBoard\ProjectBoardIndex;
use Illuminate\Support\Facades\Route;

Route::get('/project-management/project-board', ProjectBoardIndex::class)
    ->name('admin.project-management.project-board.index');
