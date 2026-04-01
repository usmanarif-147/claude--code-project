<div>
    {{-- 1. BREADCRUMB --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-400">Tasks</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">AI Prioritization</span>
    </div>

    {{-- 2. PAGE HEADER --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">AI Prioritization</h1>
            <p class="text-sm text-gray-500 mt-1">Let AI analyze your tasks and suggest the optimal order</p>
        </div>
        <div class="text-right">
            <button wire:click="analyze"
                    wire:loading.attr="disabled"
                    wire:target="analyze"
                    @if(!$hasApiKey) disabled @endif
                    class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20 disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="analyze">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </span>
                <span wire:loading wire:target="analyze">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                </span>
                <span wire:loading.remove wire:target="analyze">Prioritize My Tasks</span>
                <span wire:loading wire:target="analyze">Analyzing...</span>
            </button>
            @if($provider || $lastAnalyzedAt)
                <p class="text-xs text-gray-500 mt-2">
                    @if($provider)
                        <span class="capitalize">{{ $provider }}</span>
                    @endif
                    @if($lastAnalyzedAt)
                        <span class="mx-1">&middot;</span> Last run: {{ $lastAnalyzedAt }}
                    @endif
                </p>
            @endif
        </div>
    </div>

    {{-- FLASH MESSAGES --}}
    @if(session('success') || session('error'))
        <div x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 4000)"
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

    {{-- 3. NO API KEY STATE --}}
    @if(!$hasApiKey)
        <div class="bg-gradient-to-br from-primary/20 to-fuchsia-600/20 border border-primary/30 rounded-xl p-8 text-center">
            <div class="w-14 h-14 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            </div>
            <h2 class="text-lg font-mono font-bold text-white uppercase tracking-wider mb-2">API Key Required</h2>
            <p class="text-gray-400 text-sm mb-6 max-w-md mx-auto">Configure a Claude or OpenAI API key in Settings to use AI prioritization.</p>
            <a href="{{ route('admin.settings.api-keys') }}" wire:navigate
               class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Go to Settings
            </a>
        </div>
    @else
        {{-- 4. ERROR STATE --}}
        @if($error)
            <div class="flex items-center gap-3 bg-red-500/10 border border-red-500/20 text-red-400 rounded-xl px-5 py-4 text-sm mb-6">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <p>{{ $error }}</p>
                <button wire:click="analyze" class="ml-auto text-sm font-medium text-red-300 hover:text-white transition-colors">
                    Try Again
                </button>
            </div>
        @endif

        {{-- 5. STAT CARDS --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            {{-- Today's Tasks --}}
            <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm text-gray-500">Today's Tasks</span>
                    <span class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </span>
                </div>
                <p class="text-3xl font-bold text-white">{{ $todaysTasks->count() }}</p>
            </div>

            {{-- Completed --}}
            <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm text-gray-500">Completed Today</span>
                    <span class="w-9 h-9 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <p class="text-3xl font-bold text-white">{{ $completedToday }}</p>
            </div>

            {{-- Overdue --}}
            <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm text-gray-500">Overdue</span>
                    <span class="w-9 h-9 rounded-lg bg-red-500/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </span>
                </div>
                <p class="text-3xl font-bold text-white">{{ $overdueTasks->count() }}</p>
            </div>

            {{-- AI Provider --}}
            <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm text-gray-500">AI Provider</span>
                    <span class="w-9 h-9 rounded-lg bg-fuchsia-500/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-fuchsia-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </span>
                </div>
                <p class="text-3xl font-bold text-white capitalize">{{ $provider ?? 'N/A' }}</p>
            </div>
        </div>

        {{-- 6. OVERDUE WARNINGS --}}
        @if($overdueTasks->isNotEmpty())
            <div class="bg-amber-500/10 border border-amber-500/20 rounded-xl p-5 mb-6">
                <div class="flex items-center gap-2 mb-4">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <h2 class="text-base font-mono font-semibold text-amber-400 uppercase tracking-wider">Overdue Tasks</h2>
                </div>
                <div class="space-y-3">
                    @foreach($overdueTasks as $task)
                        <div class="flex items-center justify-between bg-dark-800/50 rounded-lg px-4 py-3">
                            <div class="flex items-center gap-3">
                                <span class="text-sm text-white font-medium">{{ $task->title }}</span>
                                @if($task->category)
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium"
                                          style="background-color: {{ $task->category->color }}20; color: {{ $task->category->color }};">
                                        {{ $task->category->name }}
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center gap-4 text-sm">
                                <span class="text-gray-500">Due: {{ $task->due_date->format('M j, Y') }}</span>
                                <span class="text-red-400 font-medium">{{ now()->startOfDay()->diffInDays($task->due_date) }} days overdue</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- 7. AI RESULTS --}}
        @if($result)
            {{-- 7a. START HERE Card --}}
            @if(isset($result['start_with']))
                @php
                    $startTask = $todaysTasks->firstWhere('id', $result['start_with']['task_id']);
                @endphp
                @if($startTask)
                    <div class="bg-gradient-to-br from-primary/20 to-fuchsia-600/20 border border-primary/30 rounded-xl p-6 mb-6">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-primary/20 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs font-mono font-medium text-primary-light uppercase tracking-widest mb-1">Start Here</p>
                                <h3 class="text-lg font-mono font-bold text-white uppercase tracking-wider mb-1">{{ $startTask->title }}</h3>
                                <div class="flex items-center gap-2 mb-2">
                                    @if($startTask->category)
                                        <span class="px-2.5 py-1 rounded-full text-xs font-medium"
                                              style="background-color: {{ $startTask->category->color }}20; color: {{ $startTask->category->color }};">
                                            {{ $startTask->category->name }}
                                        </span>
                                    @endif
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium
                                        {{ $startTask->priority === 'urgent' ? 'bg-red-500/10 text-red-400' : '' }}
                                        {{ $startTask->priority === 'high' ? 'bg-red-500/10 text-red-400' : '' }}
                                        {{ $startTask->priority === 'medium' ? 'bg-amber-500/10 text-amber-400' : '' }}
                                        {{ $startTask->priority === 'low' ? 'bg-blue-500/10 text-blue-400' : '' }}">
                                        {{ ucfirst($startTask->priority) }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-400 italic">{{ $result['start_with']['reasoning'] }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            {{-- Focus Suggestion --}}
            @if(!empty($result['focus_suggestion']))
                <div class="bg-dark-800 border border-dark-700 rounded-xl px-5 py-4 mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        </div>
                        <p class="text-sm text-gray-300">{{ $result['focus_suggestion'] }}</p>
                    </div>
                </div>
            @endif

            {{-- 7b. PRIORITIZED TASK LIST --}}
            <div class="bg-dark-800 border border-dark-700 rounded-xl">
                <div class="flex items-center justify-between px-6 py-4 border-b border-dark-700">
                    <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider">Suggested Order</h2>
                    <span class="text-xs text-gray-500">{{ count($result['prioritized_tasks']) }} tasks</span>
                </div>
                <div class="divide-y divide-dark-700/50">
                    @foreach(collect($result['prioritized_tasks'])->sortBy('rank') as $item)
                        @php
                            $task = $todaysTasks->firstWhere('id', $item['task_id']);
                        @endphp
                        @if($task)
                            <div class="flex items-center gap-4 px-6 py-4 hover:bg-dark-700/30 transition-colors">
                                {{-- Rank Number --}}
                                <span class="text-2xl font-bold text-white w-8 text-center shrink-0">{{ $item['rank'] }}</span>

                                {{-- Task Info --}}
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-white font-medium truncate">{{ $task->title }}</p>
                                    @if(!empty($item['reasoning']))
                                        <p class="text-xs text-gray-500 italic mt-0.5 truncate">{{ $item['reasoning'] }}</p>
                                    @endif
                                </div>

                                {{-- Badges --}}
                                <div class="flex items-center gap-2 shrink-0">
                                    @if($task->category)
                                        <span class="px-2.5 py-1 rounded-full text-xs font-medium"
                                              style="background-color: {{ $task->category->color }}20; color: {{ $task->category->color }};">
                                            {{ $task->category->name }}
                                        </span>
                                    @endif
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium
                                        {{ $task->priority === 'urgent' ? 'bg-red-500/10 text-red-400' : '' }}
                                        {{ $task->priority === 'high' ? 'bg-red-500/10 text-red-400' : '' }}
                                        {{ $task->priority === 'medium' ? 'bg-amber-500/10 text-amber-400' : '' }}
                                        {{ $task->priority === 'low' ? 'bg-blue-500/10 text-blue-400' : '' }}">
                                        {{ ucfirst($task->priority) }}
                                    </span>
                                    @if($task->due_date)
                                        <span class="text-xs text-gray-500">{{ $task->due_date->format('M j') }}</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                {{-- 7c. APPLY ORDER BUTTON --}}
                <div class="px-6 py-4 border-t border-dark-700">
                    <button wire:click="applyOrder"
                            wire:loading.attr="disabled"
                            wire:target="applyOrder"
                            class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="applyOrder">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        <span wire:loading wire:target="applyOrder">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        </span>
                        <span wire:loading.remove wire:target="applyOrder">Apply This Order</span>
                        <span wire:loading wire:target="applyOrder">Applying...</span>
                    </button>
                </div>
            </div>
        @elseif($todaysTasks->isEmpty() && !$isLoading)
            {{-- 8. EMPTY STATE --}}
            <div class="bg-dark-800 border border-dark-700 rounded-xl p-12 text-center">
                <div class="w-14 h-14 rounded-full bg-dark-700 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </div>
                <h3 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-2">No Tasks for Today</h3>
                <p class="text-sm text-gray-500 mb-6">Add tasks in the Daily Planner to get AI suggestions.</p>
                <a href="{{ route('admin.tasks.planner.index') }}" wire:navigate
                   class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 text-gray-300 hover:text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Go to Daily Planner
                </a>
            </div>
        @endif
    @endif
</div>
