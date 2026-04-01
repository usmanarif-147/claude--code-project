<?php

use App\Livewire\Admin\Tasks\RecurringTasks\RecurringTaskForm;
use App\Livewire\Admin\Tasks\RecurringTasks\RecurringTaskIndex;
use Illuminate\Support\Facades\Route;

Route::get('/tasks/recurring-tasks', RecurringTaskIndex::class)->name('admin.tasks.recurring.index');
Route::get('/tasks/recurring-tasks/create', RecurringTaskForm::class)->name('admin.tasks.recurring.create');
Route::get('/tasks/recurring-tasks/{recurringTask}/edit', RecurringTaskForm::class)->name('admin.tasks.recurring.edit');
