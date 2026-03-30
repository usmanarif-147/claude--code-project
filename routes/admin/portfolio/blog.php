<?php

use App\Livewire\Admin\BlogPostForm;
use App\Livewire\Admin\BlogPostIndex;
use Illuminate\Support\Facades\Route;

Route::get('/blog', BlogPostIndex::class)->name('admin.blog.index');
Route::get('/blog/create', BlogPostForm::class)->name('admin.blog.create');
Route::get('/blog/{blogPost}/edit', BlogPostForm::class)->name('admin.blog.edit');
