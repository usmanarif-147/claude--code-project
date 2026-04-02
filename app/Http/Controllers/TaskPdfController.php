<?php

namespace App\Http\Controllers;

use App\Services\TaskPdfService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TaskPdfController extends Controller
{
    public function download(Request $request, TaskPdfService $service): Response
    {
        $request->validate([
            'period' => 'required|in:day,week,month',
            'date' => 'required|date_format:Y-m-d',
            'type' => 'nullable|in:all,personal,project',
        ]);

        return $service->download(
            userId: auth()->id(),
            period: $request->input('period'),
            date: $request->input('date'),
            taskType: $request->input('type', 'all')
        );
    }
}
