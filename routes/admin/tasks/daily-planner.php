<?php

use App\Livewire\Admin\Tasks\DailyPlanner\DailyPlannerIndex;
use Illuminate\Support\Facades\Route;

Route::get('/tasks/planner', DailyPlannerIndex::class)->name('admin.tasks.planner.index');
