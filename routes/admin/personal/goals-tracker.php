<?php

use App\Livewire\Admin\Personal\GoalsTracker\GoalForm;
use App\Livewire\Admin\Personal\GoalsTracker\GoalIndex;
use Illuminate\Support\Facades\Route;

Route::get('/personal/goals-tracker', GoalIndex::class)->name('admin.personal.goals-tracker.index');
Route::get('/personal/goals-tracker/create', GoalForm::class)->name('admin.personal.goals-tracker.create');
Route::get('/personal/goals-tracker/{goal}/edit', GoalForm::class)->name('admin.personal.goals-tracker.edit');
