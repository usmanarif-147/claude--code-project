<?php

namespace App\Http\Controllers;

use App\Services\ResumeService;
use Illuminate\Http\Request;

class ResumeController extends Controller
{
    public function download(Request $request, ResumeService $service, string $template = 'modern')
    {
        $validTemplates = array_keys($service->getAvailableTemplates());

        if (! in_array($template, $validTemplates)) {
            abort(404);
        }

        return $service->download($template, $request);
    }
}
