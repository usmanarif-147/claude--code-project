<?php

use App\Livewire\Admin\JobSearch\JobFeed\JobFeedIndex;
use Illuminate\Support\Facades\Route;

Route::get('/job-search/feed', JobFeedIndex::class)->name('admin.job-search.feed.index');
