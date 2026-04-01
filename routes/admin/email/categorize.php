<?php

use App\Livewire\Admin\Email\Categories\EmailCategoryForm;
use App\Livewire\Admin\Email\Categories\EmailCategoryIndex;
use App\Livewire\Admin\Email\Categorize\CategorizeDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/email/categories', EmailCategoryIndex::class)->name('admin.email.categories.index');
Route::get('/email/categories/create', EmailCategoryForm::class)->name('admin.email.categories.create');
Route::get('/email/categories/{emailCategory}/edit', EmailCategoryForm::class)->name('admin.email.categories.edit');
Route::get('/email/categorize', CategorizeDashboard::class)->name('admin.email.categorize.index');
