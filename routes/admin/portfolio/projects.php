<?php

use App\Livewire\Admin\ProjectForm;
use App\Livewire\Admin\ProjectIndex;
use Illuminate\Support\Facades\Route;

Route::get('/projects', ProjectIndex::class)->name('admin.projects.index');
Route::get('/projects/create', ProjectForm::class)->name('admin.projects.create');
Route::get('/projects/{project}/edit', ProjectForm::class)->name('admin.projects.edit');
