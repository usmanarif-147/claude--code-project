<?php

namespace App\Livewire\Admin;

use App\Models\Blog\BlogPost;
use App\Models\Experience\Experience;
use App\Models\PortfolioVisitor;
use App\Models\Project\Project;
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
            ['label' => 'Skills', 'value' => Skill::count(), 'icon' => 'lightbulb', 'bg' => 'bg-primary/10', 'text' => 'text-primary-light'],
            ['label' => 'Technologies', 'value' => Technology::count(), 'icon' => 'code', 'bg' => 'bg-cyan-500/10', 'text' => 'text-cyan-400'],
            ['label' => 'Experiences', 'value' => Experience::count(), 'icon' => 'briefcase', 'bg' => 'bg-blue-500/10', 'text' => 'text-blue-400'],
            ['label' => 'Profile Complete', 'value' => $profileCompletion.'%', 'icon' => 'user', 'bg' => 'bg-emerald-500/10', 'text' => 'text-emerald-400'],
            ['label' => 'Projects', 'value' => Project::active()->count(), 'icon' => 'project', 'bg' => 'bg-fuchsia-500/10', 'text' => 'text-fuchsia-400'],
            ['label' => 'Blog Posts', 'value' => $totalBlogPosts, 'icon' => 'pencil', 'subtitle' => $publishedBlogPosts.' published, '.$draftBlogPosts.' draft', 'bg' => 'bg-amber-500/10', 'text' => 'text-amber-400'],
            ['label' => 'Testimonials', 'value' => Testimonial::visible()->count(), 'icon' => 'chat', 'bg' => 'bg-emerald-500/10', 'text' => 'text-emerald-400'],
            ['label' => 'Visitors This Month', 'value' => PortfolioVisitor::thisMonth()->count(), 'icon' => 'chart', 'bg' => 'bg-blue-500/10', 'text' => 'text-blue-400'],
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
