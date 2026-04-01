<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — Usman Arif</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-dark-900 text-gray-300 font-sans antialiased" x-data="{ sidebarOpen: false }">

    {{-- Mobile sidebar overlay --}}
    <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/50 z-40 lg:hidden" @click="sidebarOpen = false"></div>

    {{-- Sidebar --}}
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-50 w-64 bg-dark-800 border-r border-dark-700 flex flex-col transition-transform duration-300 lg:translate-x-0">

        {{-- Brand --}}
        <div class="h-16 flex items-center px-6 border-b border-dark-700">
            <a href="/" class="text-xl font-bold text-white hover:text-primary-light transition-colors">
                UA<span class="text-primary-light">.</span>
            </a>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-4 py-6 space-y-1">
            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white hover:bg-dark-700' }} transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0a1 1 0 01-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 01-1 1h-2z"/>
                </svg>
                Dashboard
            </a>

            <a href="{{ route('admin.files.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.files.*') ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white hover:bg-dark-700' }} transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
                File Manager
            </a>

            {{-- Portfolio collapsible menu --}}
            @php
                $portfolioActive = request()->routeIs('admin.profile.*') || request()->routeIs('admin.skills.*') || request()->routeIs('admin.technologies.*') || request()->routeIs('admin.experiences.*') || request()->routeIs('admin.projects.*') || request()->routeIs('admin.testimonials.*') || request()->routeIs('admin.blog.*') || request()->routeIs('admin.analytics') || request()->routeIs('admin.resume*');
            @endphp
            <div x-data="{ portfolioOpen: {{ $portfolioActive ? 'true' : 'false' }} }">
                <button @click="portfolioOpen = !portfolioOpen"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ $portfolioActive ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white hover:bg-dark-700' }} transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    Portfolio
                    <svg class="w-4 h-4 ml-auto transition-transform duration-200" :class="portfolioOpen ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>

                <div x-show="portfolioOpen"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-1"
                     class="mt-1 space-y-1">
                    <a href="{{ route('admin.profile.edit') }}"
                       class="flex items-center gap-3 pl-10 pr-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.profile.*') ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white hover:bg-dark-700' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Profile
                    </a>

                    <a href="{{ route('admin.skills.index') }}"
                       class="flex items-center gap-3 pl-10 pr-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.skills.*') ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white hover:bg-dark-700' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                        Skills
                    </a>

                    <a href="{{ route('admin.technologies.index') }}"
                       class="flex items-center gap-3 pl-10 pr-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.technologies.*') ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white hover:bg-dark-700' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5"/>
                        </svg>
                        Technologies
                    </a>

                    <a href="{{ route('admin.experiences.index') }}"
                       class="flex items-center gap-3 pl-10 pr-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.experiences.*') ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white hover:bg-dark-700' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m8 0H8m8 0h2a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2h2"/>
                        </svg>
                        Experiences
                    </a>

                    <a href="{{ route('admin.projects.index') }}"
                       class="flex items-center gap-3 pl-10 pr-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.projects.*') ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white hover:bg-dark-700' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        Projects
                    </a>

                    <a href="{{ route('admin.testimonials.index') }}"
                       class="flex items-center gap-3 pl-10 pr-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.testimonials.*') ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white hover:bg-dark-700' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        Testimonials
                    </a>

                    <a href="{{ route('admin.blog.index') }}"
                       class="flex items-center gap-3 pl-10 pr-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.blog.*') ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white hover:bg-dark-700' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Blog
                    </a>

                    <a href="{{ route('admin.analytics') }}"
                       class="flex items-center gap-3 pl-10 pr-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.analytics') ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white hover:bg-dark-700' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Analytics
                    </a>

                    <a href="{{ route('admin.resume') }}"
                       class="flex items-center gap-3 pl-10 pr-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.resume*') ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white hover:bg-dark-700' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Resume
                    </a>
                </div>
            </div>

            {{-- Tasks collapsible menu --}}
            @php
                $tasksActive = request()->routeIs('admin.tasks.*');
            @endphp
            <div x-data="{ tasksOpen: {{ $tasksActive ? 'true' : 'false' }} }">
                <button @click="tasksOpen = !tasksOpen"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ $tasksActive ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white hover:bg-dark-700' }} transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    Tasks
                    <svg class="w-4 h-4 ml-auto transition-transform duration-200" :class="tasksOpen ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>

                <div x-show="tasksOpen"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-1"
                     class="mt-1 space-y-1">
                    <a href="{{ route('admin.tasks.planner.index') }}"
                       class="flex items-center gap-3 pl-10 pr-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.tasks.planner.*') ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white hover:bg-dark-700' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Daily Planner
                    </a>

                    <a href="{{ route('admin.tasks.categories.index') }}"
                       class="flex items-center gap-3 pl-10 pr-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.tasks.categories.*') ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white hover:bg-dark-700' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        Categories
                    </a>

                    <a href="{{ route('admin.tasks.recurring.index') }}"
                       class="flex items-center gap-3 pl-10 pr-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.tasks.recurring.*') ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white hover:bg-dark-700' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Recurring Tasks
                    </a>

                    <a href="{{ route('admin.tasks.calendar.index') }}"
                       class="flex items-center gap-3 pl-10 pr-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.tasks.calendar.*') ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white hover:bg-dark-700' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Calendar
                    </a>

                    <a href="{{ route('admin.tasks.ai-prioritization.index') }}"
                       class="flex items-center gap-3 pl-10 pr-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.tasks.ai-prioritization.*') ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white hover:bg-dark-700' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                        AI Prioritization
                    </a>

                    <a href="{{ route('admin.tasks.weekly-review.index') }}"
                       class="flex items-center gap-3 pl-10 pr-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.tasks.weekly-review.*') ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white hover:bg-dark-700' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Weekly Review
                    </a>
                </div>
            </div>

            {{-- Job Search collapsible menu --}}
            @php
                $jobSearchActive = request()->routeIs('admin.job-search.*');
            @endphp
            <div x-data="{ jobSearchOpen: {{ $jobSearchActive ? 'true' : 'false' }} }">
                <button @click="jobSearchOpen = !jobSearchOpen"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ $jobSearchActive ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white hover:bg-dark-700' }} transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Job Search
                    <svg class="w-4 h-4 ml-auto transition-transform duration-200" :class="jobSearchOpen ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>

                <div x-show="jobSearchOpen"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-1"
                     class="mt-1 space-y-1">
                    <a href="{{ route('admin.job-search.feed.index') }}"
                       class="flex items-center gap-3 pl-10 pr-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.job-search.feed.*') ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white hover:bg-dark-700' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                        </svg>
                        Job Feed
                    </a>

                    <a href="{{ route('admin.job-search.saved-searches.index') }}"
                       class="flex items-center gap-3 pl-10 pr-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.job-search.saved-searches.*') ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white hover:bg-dark-700' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                        </svg>
                        Saved Searches
                    </a>

                    <a href="{{ route('admin.job-search.applications.index') }}"
                       class="flex items-center gap-3 pl-10 pr-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.job-search.applications.*') ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white hover:bg-dark-700' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        Application Tracker
                    </a>

                    <a href="{{ route('admin.job-search.ai-match-scoring.index') }}"
                       class="flex items-center gap-3 pl-10 pr-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.job-search.ai-match-scoring.*') ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white hover:bg-dark-700' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                        AI Match Scoring
                    </a>

                    <a href="{{ route('admin.job-search.cover-letters.index') }}"
                       class="flex items-center gap-3 pl-10 pr-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.job-search.cover-letters.*') ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white hover:bg-dark-700' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        AI Cover Letter
                    </a>

                    <a href="{{ route('admin.job-search.alerts.index') }}"
                       class="flex items-center gap-3 pl-10 pr-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.job-search.alerts.*') ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white hover:bg-dark-700' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        Job Alerts
                    </a>

                    <a href="{{ route('admin.job-search.application-stats.index') }}"
                       class="flex items-center gap-3 pl-10 pr-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.job-search.application-stats.*') ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white hover:bg-dark-700' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Application Stats
                    </a>
                </div>
            </div>

            {{-- Settings collapsible menu --}}
            @php
                $settingsActive = request()->routeIs('admin.settings.*');
            @endphp
            <div x-data="{ settingsOpen: {{ $settingsActive ? 'true' : 'false' }} }">
                <button @click="settingsOpen = !settingsOpen"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ $settingsActive ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white hover:bg-dark-700' }} transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Settings
                    <svg class="w-4 h-4 ml-auto transition-transform duration-200" :class="settingsOpen ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>

                <div x-show="settingsOpen"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-1"
                     class="mt-1 space-y-1">
                    <a href="{{ route('admin.settings.profile') }}"
                       class="flex items-center gap-3 pl-10 pr-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.settings.profile') ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white hover:bg-dark-700' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Profile Settings
                    </a>

                    <a href="{{ route('admin.settings.api-keys') }}"
                       class="flex items-center gap-3 pl-10 pr-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.settings.api-keys') ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white hover:bg-dark-700' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        API Keys
                    </a>

                    <a href="{{ route('admin.settings.job-search-filters') }}"
                       class="flex items-center gap-3 pl-10 pr-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.settings.job-search-filters') ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white hover:bg-dark-700' }} transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Job Search Filters
                    </a>
                </div>
            </div>
        </nav>

        {{-- User info --}}
        <div class="px-4 py-4 border-t border-dark-700">
            <div class="flex items-center gap-3 px-3 py-2">
                <div class="w-9 h-9 rounded-full bg-primary flex items-center justify-center text-white font-semibold text-sm">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}" class="mt-2">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-400 hover:text-red-400 hover:bg-dark-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h5a2 2 0 012 2v1"/>
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    {{-- Mobile header --}}
    <div class="lg:hidden fixed top-0 left-0 right-0 z-30 h-16 bg-dark-800 border-b border-dark-700 flex items-center px-4">
        <button @click="sidebarOpen = true" class="text-gray-400 hover:text-white">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>
        <span class="ml-4 text-lg font-bold text-white">UA<span class="text-primary-light">.</span></span>
    </div>

    {{-- Main content --}}
    <main class="lg:ml-64 min-h-screen pt-16 lg:pt-0">
        <div class="p-6 lg:p-8">
            {{-- Flash Messages --}}
            @if (session('success') || session('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                     x-transition:leave="transition ease-in duration-300"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-2"
                     class="mb-6">
                    @if (session('success'))
                        <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-lg px-4 py-3 text-emerald-400 text-sm flex items-center gap-2">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="bg-red-500/10 border border-red-500/20 rounded-lg px-4 py-3 text-red-400 text-sm flex items-center gap-2">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ session('error') }}
                        </div>
                    @endif
                </div>
            @endif

            {{ $slot }}
        </div>
    </main>

    <livewire:admin.tasks.quick-capture.quick-capture />

    @livewireScripts
</body>
</html>
