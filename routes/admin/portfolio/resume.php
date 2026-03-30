<?php

use App\Http\Controllers\ResumeController;
use App\Livewire\Admin\ResumeGenerator;
use Illuminate\Support\Facades\Route;

Route::get('/resume', ResumeGenerator::class)->name('admin.resume');
Route::get('/resume/download/{template}', [ResumeController::class, 'download'])->name('admin.resume.download');
