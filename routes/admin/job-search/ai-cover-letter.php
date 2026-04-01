<?php

use App\Livewire\Admin\JobSearch\AiCoverLetter\AiCoverLetterForm;
use App\Livewire\Admin\JobSearch\AiCoverLetter\AiCoverLetterIndex;
use Illuminate\Support\Facades\Route;

Route::get('/job-search/cover-letters', AiCoverLetterIndex::class)->name('admin.job-search.cover-letters.index');
Route::get('/job-search/cover-letters/create', AiCoverLetterForm::class)->name('admin.job-search.cover-letters.create');
Route::get('/job-search/cover-letters/{coverLetter}/edit', AiCoverLetterForm::class)->name('admin.job-search.cover-letters.edit');
