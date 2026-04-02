<?php

use App\Livewire\Admin\Personal\Bookmarks\BookmarkIndex;
use Illuminate\Support\Facades\Route;

Route::get('/personal/bookmarks', BookmarkIndex::class)->name('admin.personal.bookmarks.index');
