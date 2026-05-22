<?php

use App\Livewire\Admin\Portfolio\ResumeBuilder\ResumeBuilderIndex;
use Illuminate\Support\Facades\Route;

Route::get('/resume-builder', ResumeBuilderIndex::class)->name('admin.resume-builder');
