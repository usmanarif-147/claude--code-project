<?php

use App\Livewire\Admin\Portfolio\Categories\CategoryForm;
use App\Livewire\Admin\Portfolio\Categories\CategoryIndex;
use Illuminate\Support\Facades\Route;

Route::get('/categories', CategoryIndex::class)->name('admin.categories.index');
Route::get('/categories/create', CategoryForm::class)->name('admin.categories.create');
Route::get('/categories/{category}/edit', CategoryForm::class)->name('admin.categories.edit');
