<?php

namespace App\Http\Controllers;

use App\Services\ProjectBoardExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProjectBoardExportController extends Controller
{
    public function download(Request $request, ProjectBoardExportService $service, string $format, int $boardId): Response
    {
        abort_unless(in_array($format, ['pdf', 'csv', 'md']), 404, 'Invalid export format.');

        return match ($format) {
            'pdf' => $service->exportPdf($boardId, auth()->id()),
            'csv' => $service->exportCsv($boardId, auth()->id()),
            'md' => $service->exportMarkdown($boardId, auth()->id()),
        };
    }
}
