<?php

use App\Livewire\Admin\Email\Inbox\EmailInboxIndex;
use Illuminate\Support\Facades\Route;

Route::get('/email/inbox', EmailInboxIndex::class)->name('admin.email.inbox.index');
