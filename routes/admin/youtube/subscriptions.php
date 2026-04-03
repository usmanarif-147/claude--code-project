<?php

use App\Livewire\Admin\Youtube\Subscriptions\SavedVideoIndex;
use App\Livewire\Admin\Youtube\Subscriptions\SubscriptionIndex;
use App\Livewire\Admin\Youtube\Subscriptions\VideoFeedIndex;
use Illuminate\Support\Facades\Route;

Route::get('/youtube/subscriptions', SubscriptionIndex::class)->name('admin.youtube.subscriptions.index');
Route::get('/youtube/subscriptions/feed', VideoFeedIndex::class)->name('admin.youtube.subscriptions.feed');
Route::get('/youtube/subscriptions/saved', SavedVideoIndex::class)->name('admin.youtube.subscriptions.saved');
