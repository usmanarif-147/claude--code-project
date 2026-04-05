<?php

use App\Http\Controllers\ProjectBoardExportController;
use Illuminate\Support\Facades\Route;

Route::get('/project-management/project-board/export/{format}/{boardId}', [ProjectBoardExportController::class, 'download'])
    ->where('format', 'pdf|csv|md')
    ->where('boardId', '[0-9]+')
    ->name('admin.project-management.project-board.export');
