<?php

use App\Http\Controllers\DiagramExportController;
use App\Livewire\Admin\ProjectManagement\DesignBoard\DesignBoardIndex;
use Illuminate\Support\Facades\Route;

Route::get('/project-management/design-board', DesignBoardIndex::class)
    ->name('admin.project-management.design-board.index');

Route::get('/project-management/design-board/export/diagram/{diagram}', [DiagramExportController::class, 'exportDiagram'])
    ->name('admin.project-management.design-board.export-diagram');

Route::get('/project-management/design-board/export/all/{boardId}', [DiagramExportController::class, 'exportAll'])
    ->where('boardId', '[0-9]+')
    ->name('admin.project-management.design-board.export-all');
