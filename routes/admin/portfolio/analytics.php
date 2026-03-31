<?php

use App\Livewire\Admin\Portfolio\Analytics;
use Illuminate\Support\Facades\Route;

Route::get('/analytics', Analytics::class)->name('admin.analytics');
