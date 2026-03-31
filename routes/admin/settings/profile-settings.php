<?php

use App\Livewire\Admin\Settings\ProfileSettings\ProfileSettingsEdit;
use Illuminate\Support\Facades\Route;

Route::get('/settings/profile', ProfileSettingsEdit::class)->name('admin.settings.profile');
