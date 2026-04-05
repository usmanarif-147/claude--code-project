<?php

namespace App\Http\Controllers;

use App\Models\ProjectManagement\Diagram;
use App\Services\DesignBoardService;
use Symfony\Component\HttpFoundation\Response;

class DiagramExportController extends Controller
{
    public function exportDiagram(Diagram $diagram, DesignBoardService $service): Response
    {
        return $service->exportDiagramPdf($diagram);
    }

    public function exportAll(int $boardId, DesignBoardService $service): Response
    {
        return $service->exportAllDiagramsPdf($boardId, auth()->id());
    }
}
