<?php

use App\Livewire\Admin\ProjectManagement\WeeklyReview\WeeklyReviewIndex;
use Illuminate\Support\Facades\Route;

Route::get('/project-management/weekly-review', WeeklyReviewIndex::class)->name('admin.project-management.weekly-review.index');
