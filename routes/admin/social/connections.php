<?php

use App\Livewire\Admin\Social\Connections\ConnectionsIndex;
use Illuminate\Support\Facades\Route;

Route::get('/social/connections', ConnectionsIndex::class)->name('admin.social.connections.index');
