<?php

use App\Livewire\Admin\Settings\ApiKeys\ApiKeysIndex;
use Illuminate\Support\Facades\Route;

Route::get('/settings/api-keys', ApiKeysIndex::class)->name('admin.settings.api-keys');
