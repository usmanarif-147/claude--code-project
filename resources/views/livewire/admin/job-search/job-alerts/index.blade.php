<div>
    {{-- 1. BREADCRUMB --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-400">Job Search</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">Job Alerts</span>
    </div>

    {{-- 2. PAGE HEADER --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Job Alerts</h1>
            <p class="text-sm text-gray-500 mt-1">Notifications for jobs matching above your score threshold.</p>
        </div>
        <div class="flex items-center gap-3">
            <button wire:click="markAllAsRead"
                    wire:loading.attr="disabled"
                    @if($this->unreadCount === 0) disabled @endif
                    class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 border border-dark-600 text-gray-300 hover:text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Mark All as Read
            </button>
            <a href="{{ route('admin.job-search.alerts.settings') }}" wire:navigate
               class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 border border-dark-600 text-gray-300 hover:text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Settings
            </a>
        </div>
    </div>

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
        {{-- Total Alerts --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-500">Total Alerts</span>
                <span class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </span>
            </div>
            <p class="text-3xl font-bold text-white mb-1">{{ $this->stats['total_alerts'] }}</p>
            <p class="text-sm text-gray-500">All time notifications</p>
        </div>

        {{-- Unread --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-500">Unread</span>
                <span class="w-9 h-9 rounded-lg bg-amber-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </span>
            </div>
            <p class="text-3xl font-bold text-white mb-1">{{ $this->stats['unread_count'] }}</p>
            <p class="text-sm text-gray-500">Pending review</p>
        </div>

        {{-- High Match This Week --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-500">This Week</span>
                <span class="w-9 h-9 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9c0 .768.293 1.536.879 2.121z"/></svg>
                </span>
            </div>
            <p class="text-3xl font-bold text-white mb-1">{{ $this->stats['high_match_this_week'] }}</p>
            <p class="text-sm text-gray-500">High-match alerts</p>
        </div>

        {{-- Avg Match Score --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-500">Avg Match Score</span>
                <span class="w-9 h-9 rounded-lg bg-fuchsia-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-fuchsia-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </span>
            </div>
            <p class="text-3xl font-bold text-white mb-1">{{ $this->stats['avg_match_score'] }}%</p>
            <p class="text-sm text-gray-500">Average alert score</p>
        </div>
    </div>

    {{-- 4. FILTER BAR --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl p-4 mb-5">
        <div class="flex flex-col sm:flex-row gap-3">
            <select wire:model.live="filterStatus"
                    class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-primary focus:border-transparent transition-all min-w-[140px]">
                <option value="">All Status</option>
                <option value="unread">Unread</option>
                <option value="read">Read</option>
            </select>
            <div class="flex items-center gap-2 flex-1">
                <label class="text-xs text-gray-500 whitespace-nowrap">From:</label>
                <input type="date" wire:model.live="filterDateFrom"
                       class="flex-1 bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
            </div>
            <div class="flex items-center gap-2 flex-1">
                <label class="text-xs text-gray-500 whitespace-nowrap">To:</label>
                <input type="date" wire:model.live="filterDateTo"
                       class="flex-1 bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
            </div>
        </div>
    </div>

    {{-- 5. NOTIFICATION LIST --}}
    @if($this->stats['total_alerts'] === 0 && $filterStatus === '' && $filterDateFrom === '' && $filterDateTo === '')
        {{-- Empty State: No notifications at all --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-12 text-center">
            <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </div>
            <h3 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-2">No alerts yet</h3>
            <p class="text-sm text-gray-500 mb-6 max-w-md mx-auto">When jobs match above your score threshold, you'll see notifications here. Make sure alerts are enabled in Settings.</p>
            <a href="{{ route('admin.job-search.alerts.settings') }}" wire:navigate
               class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Configure Alert Settings
            </a>
        </div>
    @else
        <div class="space-y-3">
            @forelse($this->notifications as $notification)
                <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors {{ !$notification->is_read ? 'border-l-2 border-l-primary' : '' }}">
                    <div class="flex items-start gap-4">
                        {{-- Unread indicator --}}
                        <div class="mt-2 shrink-0">
                            @if(!$notification->is_read)
                                <div class="w-2 h-2 rounded-full bg-primary"></div>
                            @else
                                <div class="w-2 h-2 rounded-full bg-dark-600"></div>
                            @endif
                        </div>

                        {{-- Main content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-4 mb-1">
                                <div class="min-w-0">
                                    {{-- Job Title --}}
                                    @if($notification->jobListing)
                                        <a href="{{ $notification->jobListing->job_url }}" target="_blank" rel="noopener noreferrer"
                                           class="text-base font-medium text-white hover:text-primary-light transition-colors truncate block">
                                            {{ $notification->jobListing->title }}
                                        </a>
                                        <p class="text-sm text-gray-400">{{ $notification->jobListing->company_name }}</p>
                                    @else
                                        <p class="text-base font-medium text-gray-500">[Job listing removed]</p>
                                    @endif
                                </div>

                                {{-- Match Score Badge --}}
                                @if($notification->match_score >= 90)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gradient-to-r from-primary via-fuchsia-500 to-orange-500 text-white shrink-0">
                                        {{ $notification->match_score }}%
                                    </span>
                                @elseif($notification->match_score >= 80)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 shrink-0">
                                        {{ $notification->match_score }}%
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 shrink-0">
                                        {{ $notification->match_score }}%
                                    </span>
                                @endif
                            </div>

                            {{-- Match Summary --}}
                            @if($notification->match_summary)
                                <p class="text-sm text-gray-400 mt-1 line-clamp-2" x-data="{ expanded: false }">
                                    <span x-show="!expanded">{{ \Illuminate\Support\Str::limit($notification->match_summary, 150) }}</span>
                                    <span x-show="expanded" x-cloak>{{ $notification->match_summary }}</span>
                                    @if(strlen($notification->match_summary) > 150)
                                        <button @click="expanded = !expanded" class="text-primary-light hover:text-white text-xs ml-1 transition-colors">
                                            <span x-show="!expanded">Show more</span>
                                            <span x-show="expanded">Show less</span>
                                        </button>
                                    @endif
                                </p>
                            @endif

                            {{-- Meta row --}}
                            <div class="flex items-center gap-4 mt-3">
                                <span class="text-xs text-gray-500">{{ $notification->notified_at->diffForHumans() }}</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                    {{ $notification->notified_via === 'both' ? 'bg-primary/10 text-primary-light' : 'bg-dark-700 text-gray-400' }}">
                                    {{ ucfirst($notification->notified_via) }}
                                </span>
                            </div>

                            {{-- Action buttons --}}
                            <div class="flex items-center gap-2 mt-3">
                                @if($notification->is_read)
                                    <button wire:click="markAsUnread({{ $notification->id }})"
                                            class="inline-flex items-center gap-1.5 text-xs text-gray-400 hover:text-white transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        Mark as Unread
                                    </button>
                                @else
                                    <button wire:click="markAsRead({{ $notification->id }})"
                                            class="inline-flex items-center gap-1.5 text-xs text-gray-400 hover:text-white transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Mark as Read
                                    </button>
                                @endif

                                @if($notification->jobListing)
                                    <a href="{{ $notification->jobListing->job_url }}" target="_blank" rel="noopener noreferrer"
                                       class="inline-flex items-center gap-1.5 text-xs text-primary-light hover:text-white transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        View Job
                                    </a>
                                @endif

                                <x-admin.confirm-button
                                    title="Dismiss Alert?"
                                    text="Dismiss this notification? This cannot be undone."
                                    action="$wire.dismiss({{ $notification->id }})"
                                    confirm-text="Yes, dismiss it"
                                    class="inline-flex items-center gap-1.5 text-xs text-gray-500 hover:text-red-400 transition-colors ml-auto">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Dismiss
                                </x-admin.confirm-button>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                {{-- Empty State: No notifications match filters --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl p-12 text-center">
                    <div class="w-16 h-16 rounded-full bg-dark-700 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </div>
                    <h3 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-2">No notifications found</h3>
                    <p class="text-sm text-gray-500">Try adjusting your filters.</p>
                </div>
            @endforelse
        </div>

        {{-- 6. PAGINATION --}}
        @if($this->notifications->hasPages())
            <div class="mt-6">
                {{ $this->notifications->links() }}
            </div>
        @endif
    @endif
</div>
