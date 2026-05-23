<?php

use App\Livewire\Admin\BlogAndPost\ArticlePostForm;
use App\Livewire\Admin\BlogAndPost\ImagePostForm;
use App\Livewire\Admin\BlogAndPost\PostIndex;
use App\Livewire\Admin\BlogAndPost\TextPostForm;
use App\Livewire\Admin\BlogAndPost\VideoPostForm;
use Illuminate\Support\Facades\Route;

Route::get('/blog-and-post', PostIndex::class)->name('admin.blog-and-post.index');

// Text post
Route::get('/blog-and-post/create/text', TextPostForm::class)
    ->name('admin.blog-and-post.text.create');
Route::get('/blog-and-post/{post}/edit/text', TextPostForm::class)
    ->name('admin.blog-and-post.text.edit');

// Image post
Route::get('/blog-and-post/create/image', ImagePostForm::class)
    ->name('admin.blog-and-post.image.create');
Route::get('/blog-and-post/{post}/edit/image', ImagePostForm::class)
    ->name('admin.blog-and-post.image.edit');

// Video post
Route::get('/blog-and-post/create/video', VideoPostForm::class)
    ->name('admin.blog-and-post.video.create');
Route::get('/blog-and-post/{post}/edit/video', VideoPostForm::class)
    ->name('admin.blog-and-post.video.edit');

// Article post
Route::get('/blog-and-post/create/article', ArticlePostForm::class)
    ->name('admin.blog-and-post.article.create');
Route::get('/blog-and-post/{post}/edit/article', ArticlePostForm::class)
    ->name('admin.blog-and-post.article.edit');
