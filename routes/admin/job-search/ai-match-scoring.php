<?php

use App\Livewire\Admin\JobSearch\AiMatchScoring\AiMatchScoringIndex;
use Illuminate\Support\Facades\Route;

Route::get('/job-search/ai-match-scoring', AiMatchScoringIndex::class)->name('admin.job-search.ai-match-scoring.index');
