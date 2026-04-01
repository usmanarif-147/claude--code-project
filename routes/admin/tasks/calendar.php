<?php

use App\Livewire\Admin\Tasks\Calendar\CalendarIndex;
use Illuminate\Support\Facades\Route;

Route::get('/tasks/calendar', CalendarIndex::class)->name('admin.tasks.calendar.index');
