<?php

use App\Livewire\Admin\Youtube\ContentCalendar\ContentCalendarForm;
use App\Livewire\Admin\Youtube\ContentCalendar\ContentCalendarIndex;
use Illuminate\Support\Facades\Route;

Route::get('/youtube/content-calendar', ContentCalendarIndex::class)->name('admin.youtube.content-calendar.index');
Route::get('/youtube/content-calendar/create', ContentCalendarForm::class)->name('admin.youtube.content-calendar.create');
Route::get('/youtube/content-calendar/{contentCalendarItem}/edit', ContentCalendarForm::class)->name('admin.youtube.content-calendar.edit');
