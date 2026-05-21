<?php

use App\Livewire\Admin\Portfolio\Educations\EducationForm;
use App\Livewire\Admin\Portfolio\Educations\EducationIndex;
use Illuminate\Support\Facades\Route;

Route::get('/educations', EducationIndex::class)->name('admin.educations.index');
Route::get('/educations/create', EducationForm::class)->name('admin.educations.create');
Route::get('/educations/{education}/edit', EducationForm::class)->name('admin.educations.edit');
