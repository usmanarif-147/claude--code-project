<?php

use App\Livewire\Admin\AiAssistant\PrivateChat\PrivateChatIndex;
use Illuminate\Support\Facades\Route;

Route::get('/ai-assistant/chat', PrivateChatIndex::class)->name('admin.ai-assistant.chat.index');
