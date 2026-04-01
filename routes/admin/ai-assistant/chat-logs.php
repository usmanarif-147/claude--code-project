<?php

use App\Livewire\Admin\AiAssistant\ChatLogs\ChatLogIndex;
use Illuminate\Support\Facades\Route;

Route::get('/ai-assistant/chat-logs', ChatLogIndex::class)->name('admin.ai-assistant.chat-logs.index');
