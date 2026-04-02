<?php

use App\Livewire\Admin\Home\DailyBriefing\DailyBriefingIndex;
use Illuminate\Support\Facades\Route;

Route::get('/home/daily-briefing', DailyBriefingIndex::class)->name('admin.home.daily-briefing');
