<?php

use App\Livewire\Admin\TechnologyForm;
use App\Livewire\Admin\TechnologyIndex;
use Illuminate\Support\Facades\Route;

Route::get('/technologies', TechnologyIndex::class)->name('admin.technologies.index');
Route::get('/technologies/create', TechnologyForm::class)->name('admin.technologies.create');
Route::get('/technologies/{technology}/edit', TechnologyForm::class)->name('admin.technologies.edit');
