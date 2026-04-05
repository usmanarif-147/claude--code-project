<div>
    {{-- 1. BREADCRUMB --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-400">Job Search</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">AI Match Scoring</span>
    </div>

    {{-- 2. PAGE HEADER --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">AI Match Scoring</h1>
            <p class="text-sm text-gray-500 mt-1">AI analyzes job listings against your skills and preferences.</p>
        </div>
        <div class="flex items-center gap-3">
            <button wire:click="scoreAllUnscored"
                    wire:loading.attr="disabled"
                    wire:target="scoreAllUnscored"
                    @if(!$this->provider) disabled @endif
                    class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20 disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="scoreAllUnscored">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </span>
                <span wire:loading wire:target="scoreAllUnscored">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                </span>
                <span wire:loading.remove wire:target="scoreAllUnscored">Score Unscored</span>
                <span wire:loading wire:target="scoreAllUnscored">Scoring...</span>
            </button>

            <x-admin.confirm-button
                    title="Re-Score All Jobs?"
                    text="Re-score all jobs? This will overwrite existing scores."
                    action="$wire.rescoreAll()"
                    confirm-text="Yes, re-score all"
                    wire:loading.attr="disabled"
                    wire:target="rescoreAll"
                    @if(!$this->provider) disabled @endif
                    class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 text-gray-300 text-sm font-medium rounded-lg px-5 py-2.5 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="rescoreAll">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </span>
                <span wire:loading wire:target="rescoreAll">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                </span>
                <span wire:loading.remove wire:target="rescoreAll">Re-Score All</span>
                <span wire:loading wire:target="rescoreAll">Re-Scoring...</span>
            </x-admin.confirm-button>
        </div>
    </div>

    {{-- AI Provider Badge --}}
    @if($this->provider)
        <div class="mb-6">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-primary/10 text-primary-light">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                AI Provider: <span class="capitalize">{{ $this->provider }}</span>
            </span>
        </div>
    @else
        {{-- No Provider Warning --}}
        <div class="bg-amber-500/10 border border-amber-500/20 rounded-xl p-4 mb-6">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div class="flex-1">
                    <p class="text-sm text-amber-400 font-medium">No AI provider configured</p>
                    <p class="text-xs text-gray-500 mt-0.5">Add a Claude or OpenAI API key in Settings to enable scoring.</p>
                </div>
                <a href="{{ route('admin.settings.api-keys') }}" wire:navigate
                   class="inline-flex items-center gap-1.5 text-sm font-medium text-amber-400 hover:text-amber-300 transition-colors">
                    Go to Settings
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    @endif

    {{-- FLASH MESSAGES --}}
    @if(session('success') || session('error'))
        <div x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 5000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="mb-6">
            @if(session('success'))
                <div class="flex items-center gap-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-lg px-4 py-3 text-sm">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p>{{ session('success') }}</p>
                    <button @click="show = false" class="ml-auto text-emerald-400/60 hover:text-emerald-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-center gap-3 bg-red-500/10 border border-red-500/20 text-red-400 rounded-lg px-4 py-3 text-sm">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p>{{ session('error') }}</p>
                    <button @click="show = false" class="ml-auto text-red-400/60 hover:text-red-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            @endif
        </div>
    @endif

    {{-- 3. STAT CARDS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        {{-- Average Score --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-500">Average Score</span>
                <span class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </span>
            </div>
            <p class="text-3xl font-bold text-white">{{ $this->stats['average_score'] }}%</p>
        </div>

        {{-- High Matches --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-500">High Matches</span>
                <span class="w-9 h-9 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/></svg>
                </span>
            </div>
            <p class="text-3xl font-bold text-white">{{ $this->stats['high_match_count'] }}</p>
        </div>

        {{-- Scored Jobs --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-500">Scored Jobs</span>
                <span class="w-9 h-9 rounded-lg bg-blue-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="text-3xl font-bold text-white">{{ $this->stats['total_scored'] }}</p>
        </div>

        {{-- Unscored Jobs --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-500">Unscored Jobs</span>
                <span class="w-9 h-9 rounded-lg bg-amber-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="text-3xl font-bold text-white">{{ $this->stats['total_unscored'] }}</p>
        </div>
    </div>

    {{-- 4. FILTER BAR --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl p-4 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            {{-- Search --}}
            <div>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       placeholder="Search jobs..."
                       class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-sm text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
            </div>

            {{-- Platform --}}
            <div>
                <select wire:model.live="filterPlatform"
                        class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                    <option value="">All Platforms</option>
                    @foreach(\App\Models\JobSearch\JobListing::ALL_PLATFORMS as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Location Type --}}
            <div>
                <select wire:model.live="filterLocationType"
                        class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                    <option value="">All Locations</option>
                    <option value="remote">Remote</option>
                    <option value="onsite">Onsite</option>
                    <option value="hybrid">Hybrid</option>
                </select>
            </div>

            {{-- Min Score --}}
            <div>
                <select wire:model.live="filterMinScore"
                        class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                    <option value="">All Scores</option>
                    <option value="50">50%+</option>
                    <option value="60">60%+</option>
                    <option value="70">70%+</option>
                    <option value="80">80%+</option>
                    <option value="90">90%+</option>
                </select>
            </div>

            {{-- Sort By --}}
            <div>
                <select wire:model.live="sortBy"
                        class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-sm text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                    <option value="score">Best Match</option>
                    <option value="posted_at">Newest</option>
                    <option value="company_name">Company Name</option>
                </select>
            </div>
        </div>
    </div>

    {{-- 5. JOB LISTING CARDS WITH SCORES --}}
    @if($this->jobs->total() > 0)
        <div class="space-y-4">
            @foreach($this->jobs as $job)
                <div x-data="{ expanded: false }" class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
                    <div class="flex items-start gap-5">
                        {{-- Left Section --}}
                        <div class="flex-1 min-w-0">
                            {{-- Title --}}
                            <a href="{{ $job->job_url }}" target="_blank" rel="noopener noreferrer"
                               class="text-base font-medium text-white hover:text-primary-light transition-colors">
                                {{ $job->title }}
                            </a>

                            {{-- Company --}}
                            @if($job->company_name)
                                <p class="text-sm text-gray-400 mt-0.5">{{ $job->company_name }}</p>
                            @endif

                            {{-- Badges Row --}}
                            <div class="flex flex-wrap items-center gap-2 mt-2">
                                {{-- Platform Badge --}}
                                @php
                                    $platformClasses = match($job->source_platform) {
                                        'jsearch' => 'bg-blue-500/10 text-blue-400',
                                        'remoteok' => 'bg-emerald-500/10 text-emerald-400',
                                        'remotive' => 'bg-fuchsia-500/10 text-fuchsia-400',
                                        'adzuna' => 'bg-amber-500/10 text-amber-400',
                                        'rozee' => 'bg-cyan-500/10 text-cyan-400',
                                        'mustakbil' => 'bg-purple-500/10 text-purple-400',
                                        default => 'bg-gray-500/10 text-gray-400',
                                    };
                                    $platformLabel = \App\Models\JobSearch\JobListing::ALL_PLATFORMS[$job->source_platform] ?? ucfirst($job->source_platform);
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $platformClasses }}">
                                    {{ $platformLabel }}
                                </span>

                                {{-- Location Type Badge --}}
                                @if($job->location_type)
                                    @php
                                        $locationClasses = match($job->location_type) {
                                            'remote' => 'bg-emerald-500/10 text-emerald-400',
                                            'onsite' => 'bg-amber-500/10 text-amber-400',
                                            'hybrid' => 'bg-blue-500/10 text-blue-400',
                                            default => 'bg-gray-500/10 text-gray-400',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $locationClasses }}">
                                        {{ ucfirst($job->location_type) }}
                                    </span>
                                @endif
                            </div>

                            {{-- Tech Stack --}}
                            @if($job->tech_stack && is_array($job->tech_stack) && count($job->tech_stack) > 0)
                                <div class="flex flex-wrap gap-1.5 mt-2">
                                    @foreach(array_slice($job->tech_stack, 0, 8) as $tech)
                                        <span class="px-2 py-0.5 rounded bg-dark-700 text-gray-300 text-xs">{{ $tech }}</span>
                                    @endforeach
                                    @if(count($job->tech_stack) > 8)
                                        <span class="px-2 py-0.5 rounded bg-dark-700 text-gray-500 text-xs">+{{ count($job->tech_stack) - 8 }} more</span>
                                    @endif
                                </div>
                            @endif

                            {{-- Salary --}}
                            @if($job->salary_text || $job->salary_min)
                                <p class="text-sm text-emerald-400 mt-2">
                                    <svg class="w-3.5 h-3.5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $job->salary_text ?? ($job->salary_currency.' '.number_format($job->salary_min).($job->salary_max ? ' - '.number_format($job->salary_max) : '')) }}
                                </p>
                            @endif

                            {{-- Posted Date --}}
                            @if($job->posted_at)
                                <p class="text-xs text-gray-500 mt-1.5">Posted {{ \Carbon\Carbon::parse($job->posted_at)->diffForHumans() }}</p>
                            @endif
                        </div>

                        {{-- Right Section: Score Display --}}
                        <div class="flex flex-col items-center gap-2 shrink-0">
                            @if($job->match_score !== null)
                                @php
                                    $score = (int) $job->match_score;
                                    $scoreClasses = match(true) {
                                        $score >= 80 => 'text-emerald-400',
                                        $score >= 60 => 'text-amber-400',
                                        default => 'text-red-400',
                                    };
                                    $ringClasses = match(true) {
                                        $score >= 80 => 'stroke-emerald-500',
                                        $score >= 60 => 'stroke-amber-500',
                                        default => 'stroke-red-500',
                                    };
                                    // SVG circle math: radius=36, circumference=226.2
                                    $circumference = 226.2;
                                    $dashOffset = $circumference - ($circumference * $score / 100);
                                @endphp
                                <div class="relative w-20 h-20">
                                    <svg class="w-20 h-20 -rotate-90" viewBox="0 0 80 80">
                                        <circle cx="40" cy="40" r="36" fill="none" stroke-width="4" class="stroke-dark-700"/>
                                        <circle cx="40" cy="40" r="36" fill="none" stroke-width="4"
                                                class="{{ $ringClasses }}"
                                                stroke-linecap="round"
                                                stroke-dasharray="{{ $circumference }}"
                                                stroke-dashoffset="{{ $dashOffset }}"/>
                                    </svg>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <span class="text-lg font-bold {{ $scoreClasses }}">{{ $score }}%</span>
                                    </div>
                                </div>
                            @else
                                <div class="relative w-20 h-20">
                                    <svg class="w-20 h-20 -rotate-90" viewBox="0 0 80 80">
                                        <circle cx="40" cy="40" r="36" fill="none" stroke-width="4" class="stroke-dark-700"/>
                                    </svg>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <span class="text-sm font-medium text-gray-500">N/A</span>
                                    </div>
                                </div>
                            @endif

                            {{-- Re-score Button --}}
                            <button wire:click="rescoreJob({{ $job->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="rescoreJob({{ $job->id }})"
                                    class="text-gray-500 hover:text-primary-light transition-colors p-1"
                                    title="Re-score this job">
                                <span wire:loading.remove wire:target="rescoreJob({{ $job->id }})">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                </span>
                                <span wire:loading wire:target="rescoreJob({{ $job->id }})">
                                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                </span>
                            </button>
                        </div>
                    </div>

                    {{-- Expandable Section: Score Breakdown --}}
                    @if($job->match_score !== null && $job->match_explanation)
                        <div class="mt-3 pt-3 border-t border-dark-700/50">
                            <button @click="expanded = !expanded"
                                    class="flex items-center gap-1.5 text-xs text-gray-500 hover:text-gray-300 transition-colors">
                                <svg class="w-3.5 h-3.5 transition-transform" :class="expanded && 'rotate-90'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                <span x-text="expanded ? 'Hide details' : 'Why this score'"></span>
                            </button>

                            <div x-show="expanded"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 x-cloak
                                 class="mt-3 space-y-3">
                                {{-- Explanation --}}
                                <p class="text-sm text-gray-400">{{ $job->match_explanation }}</p>

                                {{-- Matched Skills --}}
                                @php
                                    $matchedSkills = is_string($job->match_matched_skills) ? json_decode($job->match_matched_skills, true) : ($job->match_matched_skills ?? []);
                                    $missingSkills = is_string($job->match_missing_skills) ? json_decode($job->match_missing_skills, true) : ($job->match_missing_skills ?? []);
                                    $bonusFactors = is_string($job->match_bonus_factors) ? json_decode($job->match_bonus_factors, true) : ($job->match_bonus_factors ?? []);
                                @endphp

                                @if(!empty($matchedSkills))
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 mb-1.5">Matched Skills</p>
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach($matchedSkills as $skill)
                                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400">{{ $skill }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- Missing Skills --}}
                                @if(!empty($missingSkills))
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 mb-1.5">Missing Skills</p>
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach($missingSkills as $skill)
                                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-red-500/10 text-red-400">{{ $skill }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- Bonus Factors --}}
                                @if(!empty($bonusFactors))
                                    <div>
                                        <p class="text-xs font-medium text-gray-500 mb-1.5">Bonus Factors</p>
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach($bonusFactors as $factor)
                                                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-blue-500/10 text-blue-400">{{ $factor }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- 6. PAGINATION --}}
        <div class="mt-6">
            {{ $this->jobs->links() }}
        </div>

    @elseif($this->stats['total_scored'] === 0 && $this->stats['total_unscored'] === 0)
        {{-- 7. EMPTY STATE: No jobs at all --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-12 text-center">
            <div class="w-14 h-14 rounded-full bg-dark-700 flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
            </div>
            <h3 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-2">No Jobs to Score</h3>
            <p class="text-sm text-gray-500 mb-6">Fetch job listings from the Job Feed first, then come back to score them.</p>
            <a href="{{ route('admin.job-search.feed.index') }}" wire:navigate
               class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Go to Job Feed
            </a>
        </div>
    @else
        {{-- 8. EMPTY STATE: No jobs match filters --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-12 text-center">
            <div class="w-14 h-14 rounded-full bg-dark-700 flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            </div>
            <h3 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-2">No Jobs Match Your Filters</h3>
            <p class="text-sm text-gray-500">Try lowering the minimum score or adjusting other filters.</p>
        </div>
    @endif
</div>
