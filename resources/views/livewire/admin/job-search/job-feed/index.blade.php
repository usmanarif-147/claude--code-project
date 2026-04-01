<div x-data="{ showMoreFilters: false }">
    {{-- 1. BREADCRUMB --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-400">Job Search</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">Job Feed</span>
    </div>

    {{-- 2. PAGE HEADER --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Job Feed</h1>
            <p class="text-sm text-gray-500 mt-1">Jobs fetched from your enabled platforms, filtered by your preferences.</p>
        </div>
        <div class="text-right">
            <button wire:click="fetchJobs"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-50 cursor-not-allowed"
                    class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20">
                <svg wire:loading.remove wire:target="fetchJobs" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <svg wire:loading wire:target="fetchJobs" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span wire:loading.remove wire:target="fetchJobs">Fetch New Jobs</span>
                <span wire:loading wire:target="fetchJobs">Fetching...</span>
            </button>
            @if($this->stats['last_fetch_at'])
                <p class="text-xs text-gray-500 mt-1">Last fetched: {{ $this->stats['last_fetch_at']->diffForHumans() }}</p>
            @endif
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success') || session('error'))
        <div x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 6000)"
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
        {{-- Total Jobs --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-500">Total Jobs</span>
                <span class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </span>
            </div>
            <p class="text-3xl font-bold text-white">{{ number_format($this->stats['total_jobs']) }}</p>
        </div>

        {{-- New Today --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-500">New Today</span>
                <span class="w-9 h-9 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                </span>
            </div>
            <p class="text-3xl font-bold text-white">{{ number_format($this->stats['new_today']) }}</p>
        </div>

        {{-- Interested --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-500">Interested</span>
                <span class="w-9 h-9 rounded-lg bg-fuchsia-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-fuchsia-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                </span>
            </div>
            <p class="text-3xl font-bold text-white">{{ number_format($this->stats['interested_count']) }}</p>
        </div>

        {{-- Active Platforms --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-500">Active Platforms</span>
                <span class="w-9 h-9 rounded-lg bg-blue-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="text-3xl font-bold text-white">{{ $this->stats['platforms_active'] }}</p>
        </div>
    </div>

    {{-- 4. FILTER BAR --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl p-4 mb-6">
        {{-- Row 1: Search + Platform + Location Type --}}
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search jobs by title, company, description..."
                       class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
            </div>
            <select wire:model.live="filterPlatform"
                    class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                <option value="">All Platforms</option>
                @foreach(\App\Models\JobSearch\JobListing::ALL_PLATFORMS as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterLocationType"
                    class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                <option value="">All Locations</option>
                <option value="remote">Remote</option>
                <option value="onsite">Onsite</option>
                <option value="hybrid">Hybrid</option>
            </select>
            <button @click="showMoreFilters = !showMoreFilters"
                    class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 text-gray-300 text-sm font-medium rounded-lg px-4 py-2.5 transition-colors whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                <span x-text="showMoreFilters ? 'Less Filters' : 'More Filters'">More Filters</span>
            </button>
        </div>

        {{-- Row 2: More Filters (collapsible) --}}
        <div x-show="showMoreFilters"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="flex flex-col sm:flex-row gap-4 mt-4 pt-4 border-t border-dark-700">
            <select wire:model.live="filterCountry"
                    class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                <option value="">All Countries</option>
                <option value="Pakistan">Pakistan</option>
                <option value="International">International</option>
            </select>
            <select wire:model.live="filterStatus"
                    class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                <option value="">All Statuses</option>
                <option value="interested">Interested</option>
                <option value="not_relevant">Not Relevant</option>
                <option value="unseen">Unseen</option>
            </select>
            <input type="number" wire:model.live.debounce.500ms="filterSalaryMin" placeholder="Min Salary"
                   class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
            <input type="number" wire:model.live.debounce.500ms="filterSalaryMax" placeholder="Max Salary"
                   class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
        </div>
    </div>

    {{-- 5. JOB LISTING CARDS --}}
    @if($this->jobs->total() > 0)
        <div class="space-y-4 mb-6">
            @foreach($this->jobs as $job)
                <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors
                    {{ $job->user_status === 'interested' ? 'border-l-2 border-l-emerald-500' : '' }}
                    {{ $job->user_status === 'not_relevant' ? 'opacity-60' : '' }}">

                    <div class="flex items-start gap-4">
                        {{-- Company Logo / Initial --}}
                        <div class="shrink-0">
                            @if($job->company_logo_url)
                                <img src="{{ $job->company_logo_url }}" alt="{{ $job->company_name }}" class="w-10 h-10 rounded-lg object-cover bg-dark-700">
                            @else
                                <div class="w-10 h-10 rounded-lg bg-dark-700 flex items-center justify-center">
                                    <span class="text-sm font-bold text-gray-400">{{ $job->company_name ? strtoupper(substr($job->company_name, 0, 1)) : '?' }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Job Details --}}
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

                                {{-- Country Badge --}}
                                @if($job->country)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-dark-700 text-gray-300">
                                        {{ $job->country }}
                                    </span>
                                @endif
                            </div>

                            {{-- Location --}}
                            @if($job->location)
                                <p class="text-sm text-gray-500 mt-2">
                                    <svg class="w-3.5 h-3.5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    {{ $job->location }}
                                </p>
                            @endif

                            {{-- Salary --}}
                            @if($job->salary_text || $job->salary_min)
                                <p class="text-sm text-emerald-400 mt-1">
                                    <svg class="w-3.5 h-3.5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $job->salary_text ?? ($job->salary_currency.' '.number_format($job->salary_min).($job->salary_max ? ' - '.number_format($job->salary_max) : '')) }}
                                </p>
                            @endif

                            {{-- Tech Stack --}}
                            @if(!empty($job->tech_stack))
                                <div class="flex flex-wrap gap-1.5 mt-2">
                                    @foreach(array_slice($job->tech_stack, 0, 8) as $tech)
                                        <span class="bg-dark-700 text-gray-300 text-xs px-2 py-0.5 rounded">{{ $tech }}</span>
                                    @endforeach
                                    @if(count($job->tech_stack) > 8)
                                        <span class="text-gray-500 text-xs py-0.5">+{{ count($job->tech_stack) - 8 }} more</span>
                                    @endif
                                </div>
                            @endif

                            {{-- Posted Date --}}
                            @if($job->posted_at)
                                <p class="text-xs text-gray-500 mt-2">Posted {{ $job->posted_at->diffForHumans() }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex items-center gap-2 mt-4 pt-3 border-t border-dark-700/50">
                        {{-- Interested --}}
                        <button wire:click="updateStatus({{ $job->id }}, 'interested')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors
                                    {{ $job->user_status === 'interested' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-dark-700 text-gray-400 hover:text-emerald-400 hover:bg-emerald-500/10' }}">
                            <svg class="w-3.5 h-3.5" fill="{{ $job->user_status === 'interested' ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            Interested
                        </button>

                        {{-- Not Relevant --}}
                        <button wire:click="updateStatus({{ $job->id }}, 'not_relevant')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors
                                    {{ $job->user_status === 'not_relevant' ? 'bg-red-500/10 text-red-400' : 'bg-dark-700 text-gray-400 hover:text-red-400 hover:bg-red-500/10' }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Not Relevant
                        </button>

                        {{-- Hide --}}
                        <button wire:click="hideJob({{ $job->id }})"
                                wire:confirm="Hide this job from your feed? This cannot be undone."
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-dark-700 text-gray-500 hover:text-gray-300 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            Hide
                        </button>

                        {{-- Open Link --}}
                        <a href="{{ $job->job_url }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-dark-700 text-primary-light hover:text-white transition-colors ml-auto">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            Open
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- 6. PAGINATION --}}
        <div class="mt-6">
            {{ $this->jobs->links() }}
        </div>

    @elseif($this->stats['total_jobs'] === 0)
        {{-- 8. EMPTY STATE — First Visit --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl px-6 py-16 text-center">
            <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <h3 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-1">Your job feed is empty</h3>
            <p class="text-sm text-gray-500 mb-6 max-w-md mx-auto">Configure your job search filters in Settings, add API keys, then fetch your first batch of jobs.</p>
            <div class="flex items-center justify-center gap-3">
                @if(Route::has('admin.settings.job-search-filters.edit'))
                    <a href="{{ route('admin.settings.job-search-filters.edit') }}" wire:navigate
                       class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 text-gray-300 text-sm font-medium rounded-lg px-5 py-2.5 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Configure Filters
                    </a>
                @endif
                <button wire:click="fetchJobs"
                        class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Fetch Jobs
                </button>
            </div>
        </div>
    @else
        {{-- 7. EMPTY STATE — No matches --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl px-6 py-16 text-center">
            <div class="w-12 h-12 rounded-xl bg-dark-700 flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <h3 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-1">No jobs found</h3>
            <p class="text-sm text-gray-500 mb-6">Try adjusting your filters or fetch new jobs from your enabled platforms.</p>
            <button wire:click="fetchJobs"
                    class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Fetch New Jobs
            </button>
        </div>
    @endif
</div>
