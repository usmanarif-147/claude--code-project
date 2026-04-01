<?php

use App\Livewire\Admin\Tasks\AiPrioritization\AiPrioritizationIndex;
use Illuminate\Support\Facades\Route;

Route::get('/tasks/ai-prioritization', AiPrioritizationIndex::class)->name('admin.tasks.ai-prioritization.index');
