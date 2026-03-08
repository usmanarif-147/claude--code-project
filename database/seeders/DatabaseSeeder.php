<?php

namespace Database\Seeders;

use App\Models\Experience;
use App\Models\ExperienceResponsibility;
use App\Models\Profile;
use App\Models\Skill;
use App\Models\Technology;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Usman Arif',
            'email' => 'usmanarif.9219@gmail.com',
            'password' => '11223344',
        ]);

        // Profile
        Profile::create([
            'user_id' => $user->id,
            'tagline' => 'Full-Stack Developer',
            'bio' => "I'm a dedicated Full-Stack Developer with 4+ years of professional experience in building robust web applications. My expertise lies in the Laravel ecosystem, where I leverage tools like Livewire, Filament, and Tailwind CSS to create seamless user experiences.\n\nI thrive on transforming complex business requirements into elegant, maintainable code. Whether it's architecting a new system from scratch or optimizing an existing application, I bring a problem-solving mindset and attention to detail.",
            'secondary_email' => 'usman@example.com',
            'location' => 'Lahore, Pakistan',
            'availability_status' => 'Open to opportunities',
        ]);

        // Skills (soft skills / strengths)
        $skills = [
            ['title' => 'Problem Solving', 'icon' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z', 'sort_order' => 0],
            ['title' => 'Creativity', 'icon' => 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01', 'sort_order' => 1],
            ['title' => 'Adaptability', 'icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15', 'sort_order' => 2],
            ['title' => 'Optimization', 'icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'sort_order' => 3],
        ];

        foreach ($skills as $skill) {
            Skill::create($skill);
        }

        // Technologies
        $technologies = [
            // Frontend
            ['name' => 'HTML5', 'category' => 'frontend', 'sort_order' => 0],
            ['name' => 'CSS3', 'category' => 'frontend', 'sort_order' => 1],
            ['name' => 'JavaScript', 'category' => 'frontend', 'sort_order' => 2],
            ['name' => 'Alpine.js', 'category' => 'frontend', 'sort_order' => 3],
            ['name' => 'Livewire', 'category' => 'frontend', 'sort_order' => 4],
            ['name' => 'Tailwind CSS', 'category' => 'frontend', 'sort_order' => 5],
            ['name' => 'Bootstrap', 'category' => 'frontend', 'sort_order' => 6],
            ['name' => 'jQuery', 'category' => 'frontend', 'sort_order' => 7],
            // Backend
            ['name' => 'PHP', 'category' => 'backend', 'sort_order' => 0],
            ['name' => 'Laravel', 'category' => 'backend', 'sort_order' => 1],
            ['name' => 'Filament', 'category' => 'backend', 'sort_order' => 2],
            ['name' => 'REST APIs', 'category' => 'backend', 'sort_order' => 3],
            ['name' => 'Python', 'category' => 'backend', 'sort_order' => 4],
            ['name' => 'Node.js', 'category' => 'backend', 'sort_order' => 5],
            // Database & Tools
            ['name' => 'MySQL', 'category' => 'database_tools', 'sort_order' => 0],
            ['name' => 'PostgreSQL', 'category' => 'database_tools', 'sort_order' => 1],
            ['name' => 'Redis', 'category' => 'database_tools', 'sort_order' => 2],
            ['name' => 'Git', 'category' => 'database_tools', 'sort_order' => 3],
            ['name' => 'Docker', 'category' => 'database_tools', 'sort_order' => 4],
            ['name' => 'Linux', 'category' => 'database_tools', 'sort_order' => 5],
            ['name' => 'AWS', 'category' => 'database_tools', 'sort_order' => 6],
            ['name' => 'CI/CD', 'category' => 'database_tools', 'sort_order' => 7],
        ];

        foreach ($technologies as $tech) {
            Technology::create($tech);
        }

        // Experiences
        $horizam = Experience::create([
            'role' => 'Full-Stack Developer',
            'company' => 'Horizam',
            'start_date' => '2022-01-01',
            'end_date' => null,
            'is_current' => true,
            'sort_order' => 0,
        ]);

        foreach ([
            'Built and maintained multiple Laravel web applications with Livewire and Filament admin panels',
            'Implemented REST APIs, payment integrations, and third-party service integrations',
            'Optimized database queries and application performance for large-scale systems',
        ] as $i => $desc) {
            ExperienceResponsibility::create([
                'experience_id' => $horizam->id,
                'description' => $desc,
                'sort_order' => $i,
            ]);
        }

        $softenica = Experience::create([
            'role' => 'Software Developer',
            'company' => 'Softenica',
            'start_date' => '2021-01-01',
            'end_date' => '2022-01-01',
            'is_current' => false,
            'sort_order' => 1,
        ]);

        foreach ([
            'Developed web applications using Laravel and Vue.js',
            'Collaborated with cross-functional teams to deliver client projects on schedule',
            'Participated in code reviews and implemented best practices for code quality',
        ] as $i => $desc) {
            ExperienceResponsibility::create([
                'experience_id' => $softenica->id,
                'description' => $desc,
                'sort_order' => $i,
            ]);
        }
    }
}
