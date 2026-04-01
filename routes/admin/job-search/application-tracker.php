<?php

use App\Livewire\Admin\JobSearch\ApplicationTracker\ApplicationTrackerForm;
use App\Livewire\Admin\JobSearch\ApplicationTracker\ApplicationTrackerIndex;
use Illuminate\Support\Facades\Route;

Route::get('/job-search/applications', ApplicationTrackerIndex::class)->name('admin.job-search.applications.index');
Route::get('/job-search/applications/create', ApplicationTrackerForm::class)->name('admin.job-search.applications.create');
Route::get('/job-search/applications/{jobApplication}/edit', ApplicationTrackerForm::class)->name('admin.job-search.applications.edit');
