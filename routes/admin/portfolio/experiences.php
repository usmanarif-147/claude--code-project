<?php

use App\Livewire\Admin\Portfolio\Experiences\ExperienceForm;
use App\Livewire\Admin\Portfolio\Experiences\ExperienceIndex;
use Illuminate\Support\Facades\Route;

Route::get('/experiences', ExperienceIndex::class)->name('admin.experiences.index');
Route::get('/experiences/create', ExperienceForm::class)->name('admin.experiences.create');
Route::get('/experiences/{experience}/edit', ExperienceForm::class)->name('admin.experiences.edit');
