<?php

use App\Livewire\Admin\Tasks\WeeklyReview\WeeklyReviewIndex;
use Illuminate\Support\Facades\Route;

Route::get('/tasks/weekly-review', WeeklyReviewIndex::class)->name('admin.tasks.weekly-review.index');
