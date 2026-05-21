<?php

namespace App\Http\Controllers;

use App\Models\Blog\BlogPost;
use App\Models\Education;
use App\Models\Experience\Experience;
use App\Models\Profile;
use App\Models\Project\Project;
use App\Models\Skill\Skill;
use App\Models\Strength;
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
            'skills' => Strength::query()->active()->ordered()->get(),
            'technologies' => Skill::groupedByCategory(),
            'workExperiences' => Experience::query()->active()->ordered()->with('responsibilities')->get(),
            'education' => Education::query()->ordered()->get(),
            'projects' => Project::query()->active()->ordered()->get(),
            'testimonials' => Testimonial::query()->visible()->ordered()->get(),
            'blogPosts' => BlogPost::query()->published()->latest('published_at')->take(3)->get(),
        ]);
    }

    public function downloadResume(ResumeService $service, Request $request, string $template = 'modern')
    {
        if (! $service->isValidTemplate($template)) {
            abort(404);
        }

        $user = User::first();

        return $service->download($template, $request, $user);
    }
}
