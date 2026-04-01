<?php

use App\Livewire\Admin\JobSearch\SavedSearches\SavedSearchForm;
use App\Livewire\Admin\JobSearch\SavedSearches\SavedSearchIndex;
use Illuminate\Support\Facades\Route;

Route::get('/job-search/saved-searches', SavedSearchIndex::class)->name('admin.job-search.saved-searches.index');
Route::get('/job-search/saved-searches/create', SavedSearchForm::class)->name('admin.job-search.saved-searches.create');
Route::get('/job-search/saved-searches/{savedSearch}/edit', SavedSearchForm::class)->name('admin.job-search.saved-searches.edit');
