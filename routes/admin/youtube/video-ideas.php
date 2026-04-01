<?php

use App\Livewire\Admin\Youtube\VideoIdeas\VideoIdeaForm;
use App\Livewire\Admin\Youtube\VideoIdeas\VideoIdeaIndex;
use Illuminate\Support\Facades\Route;

Route::get('/youtube/video-ideas', VideoIdeaIndex::class)->name('admin.youtube.video-ideas.index');
Route::get('/youtube/video-ideas/create', VideoIdeaForm::class)->name('admin.youtube.video-ideas.create');
Route::get('/youtube/video-ideas/{videoIdea}/edit', VideoIdeaForm::class)->name('admin.youtube.video-ideas.edit');
