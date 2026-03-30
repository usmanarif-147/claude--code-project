<?php

namespace App\Livewire\Admin;

use App\Models\BlogPost;
use App\Models\Experience;
use App\Models\PortfolioVisitor;
use App\Models\Project;
use App\Models\Skill;
use App\Models\Technology;
use App\Models\Testimonial;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Dashboard extends Component
{
    public function render()
    {
        $profile = auth()->user()->profile;
        $profileFields = ['tagline', 'bio', 'profile_image', 'secondary_email', 'phone', 'location', 'linkedin_url', 'github_url', 'availability_status'];
        $filledFields = 0;

        if ($profile) {
            foreach ($profileFields as $field) {
                if (! empty($profile->$field)) {
                    $filledFields++;
                }
            }
        }

        $profileCompletion = round(($filledFields / count($profileFields)) * 100);

        $totalBlogPosts = BlogPost::count();
        $publishedBlogPosts = BlogPost::published()->count();
        $draftBlogPosts = BlogPost::draft()->count();

        $stats = [
            ['label' => 'Skills', 'value' => Skill::count(), 'icon' => 'lightbulb'],
            ['label' => 'Technologies', 'value' => Technology::count(), 'icon' => 'code'],
            ['label' => 'Experiences', 'value' => Experience::count(), 'icon' => 'briefcase'],
            ['label' => 'Profile Complete', 'value' => $profileCompletion.'%', 'icon' => 'user'],
            ['label' => 'Projects', 'value' => Project::active()->count(), 'icon' => 'project'],
            ['label' => 'Blog Posts', 'value' => $totalBlogPosts, 'icon' => 'pencil', 'subtitle' => $publishedBlogPosts.' published, '.$draftBlogPosts.' draft'],
            ['label' => 'Testimonials', 'value' => Testimonial::visible()->count(), 'icon' => 'chat'],
            ['label' => 'Visitors This Month', 'value' => PortfolioVisitor::thisMonth()->count(), 'icon' => 'chart'],
        ];

        $skills = Skill::active()->ordered()->get();
        $experience = Experience::active()->ordered()->get();

        return view('livewire.admin.dashboard', [
            'stats' => $stats,
            'skills' => $skills,
            'experience' => $experience,
        ]);
    }
}
