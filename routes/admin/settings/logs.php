<?php

use App\Livewire\Admin\Settings\Logs\LogsIndex;
use Illuminate\Support\Facades\Route;

Route::get('/settings/logs', LogsIndex::class)->name('admin.settings.logs');
