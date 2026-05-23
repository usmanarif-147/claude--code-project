<?php

use App\Livewire\Admin\Social\Templates\TemplateIndex;
use Illuminate\Support\Facades\Route;

Route::get('/social/templates', TemplateIndex::class)->name('admin.social.templates.index');
