<?php

use App\Livewire\Admin\ProjectManagement\Calendar\CalendarIndex;
use Illuminate\Support\Facades\Route;

Route::get('/project-management/calendar', CalendarIndex::class)->name('admin.project-management.calendar.index');
