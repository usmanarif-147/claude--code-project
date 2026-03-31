<?php

namespace App\Http\Controllers;

use App\Models\Blog\BlogPost;
use App\Models\Experience\Experience;
use App\Models\Profile;
use App\Models\Project\Project;
use App\Models\Skill;
use App\Models\Technology;
use App\Models\Testimonial;
use App\Models\User;
use App\Services\ResumeService;
use Illuminate\Http\Request;

class PortfolioController extends Controller
{
    public function index()
    {
        $user = User::first();
        $profile = Profile::where('user_id', $user->id)->first();

        return view('welcome', [
            'user' => $user,
            'profile' => $profile,
            'skills' => Skill::query()->active()->ordered()->get(),
            'technologies' => Technology::groupedByCategory(),
            'workExperiences' => Experience::query()->active()->ordered()->work()->with('responsibilities')->get(),
            'education' => Experience::query()->active()->ordered()->education()->get(),
            'projects' => Project::query()->active()->ordered()->get(),
            'testimonials' => Testimonial::query()->visible()->ordered()->get(),
            'blogPosts' => BlogPost::query()->published()->latest('published_at')->take(3)->get(),
        ]);
    }

    public function downloadResume(ResumeService $service, Request $request, string $template = 'modern')
    {
        $validTemplates = array_keys($service->getAvailableTemplates());

        if (! in_array($template, $validTemplates)) {
            abort(404);
        }

        $user = User::first();

        return $service->download($template, $request, $user);
    }
}
