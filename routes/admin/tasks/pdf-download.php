<?php

use App\Http\Controllers\TaskPdfController;
use Illuminate\Support\Facades\Route;

Route::get('/tasks/pdf/download', [TaskPdfController::class, 'download'])
    ->name('admin.tasks.pdf.download');
