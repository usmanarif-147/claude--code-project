<?php

use App\Livewire\Admin\Email\Templates\EmailTemplateForm;
use App\Livewire\Admin\Email\Templates\EmailTemplateIndex;
use Illuminate\Support\Facades\Route;

Route::get('/email/templates', EmailTemplateIndex::class)->name('admin.email.templates.index');
Route::get('/email/templates/create', EmailTemplateForm::class)->name('admin.email.templates.create');
Route::get('/email/templates/{emailTemplate}/edit', EmailTemplateForm::class)->name('admin.email.templates.edit');
