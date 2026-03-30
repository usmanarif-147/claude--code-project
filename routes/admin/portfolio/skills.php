<?php

use App\Livewire\Admin\SkillForm;
use App\Livewire\Admin\SkillIndex;
use Illuminate\Support\Facades\Route;

Route::get('/skills', SkillIndex::class)->name('admin.skills.index');
Route::get('/skills/create', SkillForm::class)->name('admin.skills.create');
Route::get('/skills/{skill}/edit', SkillForm::class)->name('admin.skills.edit');
