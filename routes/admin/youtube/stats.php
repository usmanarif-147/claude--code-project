<?php

use App\Livewire\Admin\Youtube\Stats\YouTubeStatsIndex;
use Illuminate\Support\Facades\Route;

Route::get('/youtube/stats', YouTubeStatsIndex::class)->name('admin.youtube.stats.index');
