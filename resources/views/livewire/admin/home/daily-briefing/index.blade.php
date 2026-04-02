<div>
    {{-- Breadcrumb --}}
    <div class="mb-6 flex items-center gap-2 text-sm">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="text-gray-500 hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-500">Home</span>
        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-primary-light">Daily Briefing</span>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mb-6 flex items-center gap-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl px-4 py-3">
            <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="text-sm text-emerald-300">{{ session('success') }}</span>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">{{ $greeting }}</h1>
        <p class="text-gray-500 mt-1">{{ now()->format('l, F j, Y') }}</p>
    </div>

    {{-- Quick Stats Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5 mb-8">
        {{-- Tasks Done This Week --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-500">Tasks this week</span>
                <span class="w-9 h-9 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="text-3xl font-bold text-white">{{ $quickStats['tasks_completed_this_week'] ?? 0 }}</p>
        </div>

        {{-- Unread Emails --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-500">Unread emails</span>
                <span class="w-9 h-9 rounded-lg bg-blue-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </span>
            </div>
            <p class="text-3xl font-bold text-white">{{ $quickStats['unread_emails'] ?? 0 }}</p>
        </div>

        {{-- New Job Matches --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-500">New matches (24h)</span>
                <span class="w-9 h-9 rounded-lg bg-amber-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m8 0H8m8 0h2a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2h2"/></svg>
                </span>
            </div>
            <p class="text-3xl font-bold text-white">{{ $quickStats['new_job_matches'] ?? 0 }}</p>
        </div>

        {{-- Goals Progress --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-500">Avg goal progress</span>
                <span class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                </span>
            </div>
            <p class="text-3xl font-bold text-white">{{ $quickStats['active_goals_progress'] ?? 0 }}%</p>
        </div>

        {{-- Monthly Expenses --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-500">Spent this month</span>
                <span class="w-9 h-9 rounded-lg bg-fuchsia-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-fuchsia-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="text-3xl font-bold text-white">${{ number_format($quickStats['month_expenses'] ?? 0) }}</p>
        </div>
    </div>

    {{-- Two-Column Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT COLUMN (2/3) --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Today's Tasks Card --}}
            <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-lg font-mono font-semibold text-white uppercase tracking-wider">Today's Tasks</h2>
                    <a href="{{ route('admin.tasks.planner.index') }}" wire:navigate class="text-xs text-primary-light hover:underline">View All</a>
                </div>

                @if ($todayTasks->isNotEmpty())
                    <div class="space-y-3">
                        @foreach ($todayTasks as $task)
                            <div class="flex items-center gap-3 py-2 px-3 rounded-lg hover:bg-dark-700/50 transition-colors group">
                                {{-- Checkbox --}}
                                <button wire:click="completeTask({{ $task->id }})" class="shrink-0 w-5 h-5 rounded border transition-colors flex items-center justify-center {{ $task->status === 'completed' ? 'bg-emerald-500 border-emerald-500' : 'border-dark-600 hover:border-primary group-hover:border-gray-500' }}">
                                    @if ($task->status === 'completed')
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    @endif
                                </button>

                                {{-- Task Info --}}
                                <div class="flex-1 min-w-0">
                                    <span class="text-sm {{ $task->status === 'completed' ? 'text-gray-500 line-through' : 'text-white' }}">{{ $task->title }}</span>
                                    @if ($task->category)
                                        <span class="text-xs text-gray-500 ml-2">{{ $task->category->name }}</span>
                                    @endif
                                </div>

                                {{-- Priority Badge --}}
                                @php
                                    $priorityColors = [
                                        'urgent' => 'bg-red-500/10 text-red-400',
                                        'high' => 'bg-red-500/10 text-red-400',
                                        'medium' => 'bg-amber-500/10 text-amber-400',
                                        'low' => 'bg-emerald-500/10 text-emerald-400',
                                    ];
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $priorityColors[$task->priority] ?? 'bg-gray-500/10 text-gray-400' }}">
                                    {{ ucfirst($task->priority) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        <p class="text-gray-500 text-sm">No tasks for today. Enjoy your free time!</p>
                        <a href="{{ route('admin.tasks.planner.index') }}" wire:navigate class="text-xs text-primary-light hover:underline mt-2 inline-block">Go to Daily Planner</a>
                    </div>
                @endif
            </div>

            {{-- New Job Matches Card --}}
            <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-lg font-mono font-semibold text-white uppercase tracking-wider">New Job Matches</h2>
                    <a href="{{ route('admin.job-search.feed.index') }}" wire:navigate class="text-xs text-primary-light hover:underline">View All</a>
                </div>

                @if ($newJobMatches->isNotEmpty())
                    <div class="space-y-3">
                        @foreach ($newJobMatches as $job)
                            <div class="flex items-center justify-between py-2.5 px-3 rounded-lg hover:bg-dark-700/50 transition-colors">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm text-white font-medium truncate">{{ $job->title }}</p>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $job->company_name }}</p>
                                </div>
                                <div class="flex items-center gap-3 shrink-0 ml-3">
                                    @php
                                        $platformLabel = \App\Models\JobSearch\JobListing::ALL_PLATFORMS[$job->source_platform] ?? ucfirst($job->source_platform);
                                        $shortPlatform = explode(' ', $platformLabel)[0];
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-blue-500/10 text-blue-400">{{ $shortPlatform }}</span>
                                    <span class="text-xs text-gray-500">{{ $job->fetched_at?->diffForHumans(short: true) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m8 0H8m8 0h2a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2h2"/></svg>
                        <p class="text-gray-500 text-sm">No new job matches in the last 24 hours.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- RIGHT COLUMN (1/3) --}}
        <div class="space-y-6">

            {{-- Email Summary Card --}}
            <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-lg font-mono font-semibold text-white uppercase tracking-wider">Email Overview</h2>
                    <a href="{{ route('admin.email.inbox.index') }}" wire:navigate class="text-xs text-primary-light hover:underline">View Inbox</a>
                </div>

                @if (($emailSummary['total'] ?? 0) > 0)
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-dark-700/50 rounded-lg p-3 text-center">
                            <svg class="w-5 h-5 text-blue-400 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <p class="text-xl font-bold text-white">{{ $emailSummary['today'] ?? 0 }}</p>
                            <p class="text-xs text-gray-500">Today</p>
                        </div>
                        <div class="bg-dark-700/50 rounded-lg p-3 text-center">
                            <svg class="w-5 h-5 text-amber-400 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <p class="text-xl font-bold text-white">{{ $emailSummary['unread'] ?? 0 }}</p>
                            <p class="text-xs text-gray-500">Unread</p>
                        </div>
                        <div class="bg-dark-700/50 rounded-lg p-3 text-center">
                            <svg class="w-5 h-5 text-red-400 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <p class="text-xl font-bold text-white">{{ $emailSummary['important'] ?? 0 }}</p>
                            <p class="text-xs text-gray-500">Important</p>
                        </div>
                        <div class="bg-dark-700/50 rounded-lg p-3 text-center">
                            <svg class="w-5 h-5 text-gray-400 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                            <p class="text-xl font-bold text-white">{{ $emailSummary['total'] ?? 0 }}</p>
                            <p class="text-xs text-gray-500">Total</p>
                        </div>
                    </div>
                @else
                    <div class="text-center py-6">
                        <svg class="w-10 h-10 text-gray-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <p class="text-gray-500 text-sm">No email data available. Connect your inbox.</p>
                    </div>
                @endif
            </div>

            {{-- Recruiter Alerts Card --}}
            <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-lg font-mono font-semibold text-white uppercase tracking-wider">Recruiter Alerts</h2>
                    <a href="{{ route('admin.email.recruiter-alerts.index') }}" wire:navigate class="text-xs text-primary-light hover:underline">View All</a>
                </div>

                @if ($pendingAlerts->isNotEmpty())
                    <div class="space-y-3">
                        @foreach ($pendingAlerts as $alert)
                            <div class="flex items-start gap-3 py-2 px-3 rounded-lg hover:bg-dark-700/50 transition-colors">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-white truncate">{{ $alert->email->from_name ?? $alert->email->from_email ?? 'Unknown' }}</p>
                                    <p class="text-xs text-gray-400 truncate mt-0.5">{{ $alert->email->subject ?? 'No subject' }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ $alert->created_at?->diffForHumans(short: true) }}</p>
                                </div>
                                <button wire:click="dismissAlert({{ $alert->id }})" class="shrink-0 p-1 text-gray-500 hover:text-red-400 transition-colors" title="Dismiss">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6">
                        <svg class="w-10 h-10 text-gray-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <p class="text-gray-500 text-sm">No pending recruiter alerts. Check back later!</p>
                    </div>
                @endif
            </div>

            {{-- Active Goals Card --}}
            <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-lg font-mono font-semibold text-white uppercase tracking-wider">Active Goals</h2>
                    <a href="{{ route('admin.personal.goals-tracker.index') }}" wire:navigate class="text-xs text-primary-light hover:underline">View All</a>
                </div>

                @if ($activeGoals->isNotEmpty())
                    <div class="space-y-4">
                        @foreach ($activeGoals as $goal)
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm text-white">{{ $goal->title }}</span>
                                    <span class="text-xs text-primary-light">{{ $goal->progress }}%</span>
                                </div>
                                @if ($goal->target_date)
                                    <p class="text-xs text-gray-500 mb-2">Target: {{ $goal->target_date->format('M j, Y') }}</p>
                                @endif
                                <div class="w-full bg-dark-700 rounded-full h-2">
                                    <div class="bg-gradient-to-r from-primary to-fuchsia-500 h-2 rounded-full transition-all duration-300" style="width: {{ $goal->progress }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6">
                        <svg class="w-10 h-10 text-gray-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                        <p class="text-gray-500 text-sm">No active goals. Set some targets to track your progress!</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
