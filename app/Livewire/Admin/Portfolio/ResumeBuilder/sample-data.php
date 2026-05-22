<?php

/**
 * Resume Builder — sample / dummy data for end-to-end PDF testing.
 *
 * Loaded by ResumeBuilderIndex::loadSampleData() when the user clicks the
 * "Load Sample Data" button. Pure in-memory; not persisted anywhere.
 *
 * Every string is sized to stay within FIELD_LIMITS in ResumeBuilderIndex.
 * Item counts match ITEM_LIMITS (3 jobs / 3 bullets, 3 projects / 3 bullets,
 * 5 skill groups, 6 strengths, 4 achievements, 2 educations).
 */

return [
    'header' => [
        'name' => 'Usman Arif',
        'tagline' => 'Software Engineer | Laravel & Full-Stack',
        'phone' => '+923364238599',
        'email' => 'usmanarif.9219@gmail.com',
        'location' => 'Lahore, Pakistan',
        'github' => 'github.com/usmanarif-147',
    ],

    'profile' => 'Software Engineer with 5+ years of experience building Laravel applications in production. Strong in backend development, REST APIs, database design, and automated testing. Adaptable to new tools and team workflows.',

    'experiences' => [
        [
            'company' => 'JSYS Tech',
            'role' => 'Software Engineer — Full Stack',
            'start' => '2025-08',
            'end' => '',
            'is_current' => true,
            'bullets' => [
                'Built a real-time chat system using WebSocket with JWT auth and Kafka domain events.',
                'Developed WebRTC voice/video calling via LiveKit with SFU streaming and call recording.',
                'Wrote Playwright E2E tests and shipped features via Jenkins CI/CD with Git branching.',
            ],
        ],
        [
            'company' => 'Horizam',
            'role' => 'Software Engineer — Full Stack',
            'start' => '2022-01',
            'end' => '2025-08',
            'is_current' => false,
            'bullets' => [
                'Worked on 7 full-cycle web apps covering schema design, Redis caching, and REST APIs.',
                'Migrated 2 legacy CodeIgniter apps to Laravel with full database redesign and Pest tests.',
                'Integrated Stripe Connect, FCM push notifications, and third-party payment APIs.',
            ],
        ],
        [
            'company' => 'Softenica',
            'role' => 'Junior Laravel Developer',
            'start' => '2021-01',
            'end' => '2022-01',
            'is_current' => false,
            'bullets' => [
                'Built REST APIs and backend services for client Laravel apps across diverse domains.',
                'Introduced automated testing practices to improve reliability and prevent regressions.',
                'Optimized SQL queries and refactored legacy database structures for production scale.',
            ],
        ],
    ],

    'projects' => [
        [
            'title' => 'RehabSuite — Real-Time Chat',
            'subtitle' => 'JSYS Tech · Multi-tenant Physiotherapy Platform',
            'bullets' => [
                'WebSocket chat engine with JWT auth, Kafka events, read receipts, and presence tracking.',
                'LiveKit WebRTC voice & video calls with room token management and SFU streaming.',
                'Async transcription via Assembly AI linked to chat timeline and edit-audit log.',
            ],
            'tech' => 'Laravel 12, Rust/Axum, Kafka, LiveKit, PostgreSQL, Kong Gateway, Docker',
        ],
        [
            'title' => 'Autotheory — Multi-Vendor Platform',
            'subtitle' => 'autotheory.com',
            'bullets' => [
                'Multi-role platform for auto shops with vendor onboarding and service booking flow.',
                'Stripe Connect split payments and Redis caching for high-traffic catalog queries.',
                'FCM push notifications for order updates and real-time vendor dashboards.',
            ],
            'tech' => 'Laravel, Livewire, Vue.js, Stripe Connect, FCM, Redis, MySQL',
        ],
        [
            'title' => 'Workforce & Job Management',
            'subtitle' => 'thislinks.co.uk',
            'bullets' => [
                'Role-based job and employee management with real-time activity dashboards.',
                'Performance tracking with daily FCM push notifications to mobile teams.',
                'Filament admin panel for HR with role permissions and full audit logs.',
            ],
            'tech' => 'Laravel, Livewire, Filament, Alpine.js, Bootstrap, FCM, MySQL',
        ],
    ],

    'skill_groups' => [
        [
            'category' => 'Backend & Frontend',
            'tags' => ['PHP', 'Laravel', 'Livewire', 'Rust', 'Vue.js', 'Alpine.js', 'JavaScript', 'HTML/CSS'],
        ],
        [
            'category' => 'Real-Time',
            'tags' => ['WebSocket', 'WebRTC', 'LiveKit', 'FCM'],
        ],
        [
            'category' => 'Architecture',
            'tags' => ['Microservices', 'Event-Driven', 'Kafka', 'gRPC', 'Redis', 'MySQL', 'PostgreSQL'],
        ],
        [
            'category' => 'DevOps & Testing',
            'tags' => ['Docker', 'Nginx', 'Linux', 'CI/CD', 'Pest', 'PHPUnit', 'Playwright'],
        ],
        [
            'category' => 'Tools & PM',
            'tags' => ['Git', 'GitHub', 'Jira', 'Jenkins'],
        ],
    ],

    'strengths' => [
        'API Design',
        'Problem Solving',
        'Clean Code',
        'Real-Time Features',
        'Adaptability',
        'Team Collaboration',
    ],

    'achievements' => [
        'Delivered 7 full-cycle Laravel applications across different domains.',
        'Contributed to a production real-time chat and calling system.',
        'Successfully migrated 2 legacy CodeIgniter applications to Laravel.',
        'Completed 10+ freelance Laravel projects across diverse clients.',
    ],

    'educations' => [
        [
            'degree' => 'B.S. Software Engineering',
            'institution' => 'University of Management and Technology',
            'start' => '2016-09',
            'end' => '2021-06',
        ],
        [
            'degree' => 'Intermediate (FSc Pre-Eng)',
            'institution' => 'Punjab College of IT',
            'start' => '2014-09',
            'end' => '2016-06',
        ],
    ],
];
