<?php

use App\Livewire\Admin\Tasks\Categories\TaskCategoryForm;
use App\Livewire\Admin\Tasks\Categories\TaskCategoryIndex;
use Illuminate\Support\Facades\Route;

Route::get('/tasks/categories', TaskCategoryIndex::class)->name('admin.tasks.categories.index');
Route::get('/tasks/categories/create', TaskCategoryForm::class)->name('admin.tasks.categories.create');
Route::get('/tasks/categories/{taskCategory}/edit', TaskCategoryForm::class)->name('admin.tasks.categories.edit');
