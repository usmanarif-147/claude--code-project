<?php

namespace Database\Seeders;

use App\Models\Blog\BlogPost;
use App\Models\Blog\BlogPostTag;
use App\Models\Experience\Experience;
use App\Models\Experience\ExperienceResponsibility;
use App\Models\Profile;
use App\Models\Project\Project;
use App\Models\Skill;
use App\Models\Technology;
use App\Models\Testimonial;
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

        // Education
        Experience::create([
            'role' => 'B.S. Software Engineering',
            'company' => 'University of Management and Technology (UMT)',
            'description' => 'Lahore, Pakistan',
            'start_date' => '2016-09-01',
            'end_date' => '2021-06-01',
            'is_current' => false,
            'type' => 'education',
            'sort_order' => 0,
        ]);

        // Projects
        $project1 = Project::create([
            'title' => 'Autotheory',
            'slug' => 'autotheory',
            'short_description' => 'A comprehensive automotive theory learning platform with interactive quizzes, progress tracking, and adaptive learning paths.',
            'description' => 'Built to help users prepare for driving theory exams efficiently. Features include mock tests, progress analytics, and spaced repetition.',
            'tech_stack' => ['Laravel', 'Livewire', 'Tailwind CSS', 'MySQL'],
            'is_featured' => true,
            'sort_order' => 0,
            'completed_at' => '2024-06-15',
        ]);

        $project2 = Project::create([
            'title' => 'Workforce & Job Management System',
            'slug' => 'workforce-job-management',
            'short_description' => 'An enterprise workforce management platform for scheduling, job tracking, employee management, and reporting.',
            'description' => 'Features real-time dashboards, automated notifications, role-based access control, and comprehensive reporting.',
            'tech_stack' => ['Laravel', 'Filament', 'Alpine.js', 'REST API'],
            'is_featured' => true,
            'sort_order' => 1,
            'completed_at' => '2024-01-20',
        ]);

        $project3 = Project::create([
            'title' => 'E-Commerce Analytics Dashboard',
            'slug' => 'ecommerce-analytics',
            'short_description' => 'Real-time analytics dashboard for e-commerce businesses with sales tracking, inventory management, and customer insights.',
            'description' => 'A data-driven dashboard providing actionable insights for online stores.',
            'tech_stack' => ['Laravel', 'Livewire', 'Chart.js', 'PostgreSQL', 'Redis'],
            'is_featured' => false,
            'sort_order' => 2,
            'completed_at' => '2023-08-10',
        ]);

        // Testimonials
        Testimonial::create([
            'client_name' => 'Sarah Mitchell',
            'company' => 'TechStart Solutions',
            'review' => 'Usman delivered an exceptional web application that exceeded our expectations. His attention to detail and deep understanding of Laravel made the development process smooth and efficient.',
            'rating' => 5,
            'is_visible' => true,
            'sort_order' => 0,
            'received_at' => '2024-03-15',
        ]);

        Testimonial::create([
            'client_name' => 'James Rodriguez',
            'company' => 'DataFlow Inc.',
            'review' => 'Working with Usman was a great experience. He built our workforce management system on time and within budget. The code quality was outstanding.',
            'rating' => 5,
            'is_visible' => true,
            'sort_order' => 1,
            'received_at' => '2024-01-20',
        ]);

        Testimonial::create([
            'client_name' => 'Emily Chen',
            'company' => 'Bright Learning',
            'review' => 'Usman transformed our outdated platform into a modern, responsive web application. His expertise in Livewire and Tailwind CSS really shines through in the final product.',
            'rating' => 4,
            'is_visible' => true,
            'sort_order' => 2,
            'received_at' => '2023-11-05',
        ]);

        // Blog Posts
        $post1 = BlogPost::create([
            'title' => 'Building Scalable Laravel Applications with Livewire',
            'slug' => 'building-scalable-laravel-applications-with-livewire',
            'excerpt' => 'Learn how to architect Laravel applications that scale gracefully using Livewire components and best practices.',
            'content' => '<p>Laravel and Livewire together form a powerful combination for building modern web applications without the complexity of a separate frontend framework.</p><p>In this article, we explore patterns for building scalable, maintainable Livewire applications.</p>',
            'status' => 'published',
            'published_at' => '2024-02-15 10:00:00',
            'reading_time_minutes' => 8,
        ]);

        BlogPostTag::create(['blog_post_id' => $post1->id, 'tag' => 'Laravel']);
        BlogPostTag::create(['blog_post_id' => $post1->id, 'tag' => 'Livewire']);

        $post2 = BlogPost::create([
            'title' => 'Optimizing Database Queries in Large-Scale Applications',
            'slug' => 'optimizing-database-queries-large-scale',
            'excerpt' => 'Practical tips for identifying and fixing N+1 queries, using eager loading, and leveraging database indexes.',
            'content' => '<p>Performance optimization is crucial for applications handling thousands of users. This guide covers the most impactful database optimizations you can make in Laravel.</p>',
            'status' => 'published',
            'published_at' => '2024-01-10 14:00:00',
            'reading_time_minutes' => 6,
        ]);

        BlogPostTag::create(['blog_post_id' => $post2->id, 'tag' => 'Performance']);
        BlogPostTag::create(['blog_post_id' => $post2->id, 'tag' => 'Database']);
    }
}
