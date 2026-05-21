<?php

use App\Livewire\Admin\Portfolio\Strengths\StrengthForm;
use App\Livewire\Admin\Portfolio\Strengths\StrengthIndex;
use Illuminate\Support\Facades\Route;

Route::get('/strengths', StrengthIndex::class)->name('admin.strengths.index');
Route::get('/strengths/create', StrengthForm::class)->name('admin.strengths.create');
Route::get('/strengths/{strength}/edit', StrengthForm::class)->name('admin.strengths.edit');
