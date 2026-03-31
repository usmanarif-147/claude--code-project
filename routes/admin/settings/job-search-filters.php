<?php

use App\Livewire\Admin\Settings\JobSearchFilters\JobSearchFiltersEdit;
use Illuminate\Support\Facades\Route;

Route::get('/settings/job-search-filters', JobSearchFiltersEdit::class)->name('admin.settings.job-search-filters');
