<?php

use App\Livewire\Admin\Social\Scheduler\PostForm;
use App\Livewire\Admin\Social\Scheduler\PostIndex;
use Illuminate\Support\Facades\Route;

Route::get('/social/scheduler', PostIndex::class)->name('admin.social.scheduler.index');
Route::get('/social/scheduler/create', PostForm::class)->name('admin.social.scheduler.create');
Route::get('/social/scheduler/{scheduledPost}/edit', PostForm::class)->name('admin.social.scheduler.edit');
