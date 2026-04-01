<?php

use App\Livewire\Admin\JobSearch\ApplicationStats\ApplicationStatsIndex;
use Illuminate\Support\Facades\Route;

Route::get('/job-search/application-stats', ApplicationStatsIndex::class)->name('admin.job-search.application-stats.index');
