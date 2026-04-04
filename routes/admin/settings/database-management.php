<?php

use App\Livewire\Admin\Settings\DatabaseManagement\DatabaseManagementIndex;
use Illuminate\Support\Facades\Route;

Route::get('/settings/database-management', DatabaseManagementIndex::class)->name('admin.settings.database-management');
