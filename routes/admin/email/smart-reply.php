<?php

use App\Livewire\Admin\Email\SmartReply\SmartReplyForm;
use App\Livewire\Admin\Email\SmartReply\SmartReplyIndex;
use Illuminate\Support\Facades\Route;

Route::get('/email/smart-reply', SmartReplyIndex::class)->name('admin.email.smart-reply.index');
Route::get('/email/smart-reply/create/{email?}', SmartReplyForm::class)->name('admin.email.smart-reply.create');
Route::get('/email/smart-reply/{smartReplyDraft}/edit', SmartReplyForm::class)->name('admin.email.smart-reply.edit');
