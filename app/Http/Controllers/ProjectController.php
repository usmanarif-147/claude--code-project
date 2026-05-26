<?php

namespace App\Http\Controllers;

use App\Support\DummyProjectData;

class ProjectController extends Controller
{
    public function index()
    {
        return view('projects.index', [
            // BACKEND: swap for Project::active()->ordered()->paginate(12)
            'projects' => DummyProjectData::paginate(12),
        ]);
    }

    public function show(string $slug)
    {
        // BACKEND: swap for Project::active()->where('slug', $slug)->with('images')->firstOrFail()
        $project = DummyProjectData::find($slug);

        abort_if($project === null, 404);

        return view('projects.show', [
            'project' => $project,
        ]);
    }
}
