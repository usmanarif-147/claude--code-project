<div>
    {{-- 1. BREADCRUMB --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-400">Email</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">Recruiter Alerts</span>
    </div>

    {{-- 2. PAGE HEADER --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Recruiter Alerts</h1>
            <p class="text-sm text-gray-500 mt-1">AI-detected emails from recruiters, hiring managers, and freelance clients.</p>
        </div>
        <div class="flex items-center gap-3">
            <button wire:click="scanEmails"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20 disabled:opacity-50">
                <span wire:loading.remove wire:target="scanEmails">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <span wire:loading wire:target="scanEmails">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                </span>
                <span wire:loading.remove wire:target="scanEmails">Scan Emails</span>
                <span wire:loading wire:target="scanEmails">Scanning...</span>
            </button>
            <a href="{{ route('admin.email.recruiter-alerts.settings') }}" wire:navigate
               class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 border border-dark-600 text-gray-300 hover:text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Settings
            </a>
        </div>
    </div>

    {{-- 3. STAT CARDS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        {{-- Total Alerts --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-white mb-1">{{ $stats['total'] ?? 0 }}</p>
            <p class="text-sm text-gray-500">Total Alerts</p>
        </div>

        {{-- Unread --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-lg bg-amber-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-white mb-1">{{ $stats['unread'] ?? 0 }}</p>
            <p class="text-sm text-gray-500">Unread</p>
        </div>

        {{-- Urgent --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-lg bg-red-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-white mb-1">{{ $stats['urgent'] ?? 0 }}</p>
            <p class="text-sm text-gray-500">Urgent</p>
        </div>

        {{-- Last 24h --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-white mb-1">{{ $stats['recent_24h'] ?? 0 }}</p>
            <p class="text-sm text-gray-500">Last 24h</p>
        </div>
    </div>

    {{-- 4. FILTER BAR --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl p-4 mb-5">
        <div class="flex flex-col lg:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Search by subject or sender..."
                       class="w-full bg-dark-700 border border-dark-600 rounded-lg pl-9 pr-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
            </div>
            <select wire:model.live="filterType"
                    class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-primary focus:border-transparent transition-all min-w-[160px]">
                <option value="">All Types</option>
                <option value="recruiter">Recruiter</option>
                <option value="hiring_manager">Hiring Manager</option>
                <option value="freelance_client">Freelance Client</option>
            </select>
            <select wire:model.live="filterUrgency"
                    class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-primary focus:border-transparent transition-all min-w-[130px]">
                <option value="">All Urgency</option>
                <option value="normal">Normal</option>
                <option value="urgent">Urgent</option>
            </select>
            <select wire:model.live="filterStatus"
                    class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-primary focus:border-transparent transition-all min-w-[130px]">
                <option value="">All Status</option>
                <option value="unread">Unread</option>
                <option value="read">Read</option>
                <option value="dismissed">Dismissed</option>
            </select>
            <div class="flex items-center gap-2">
                <button wire:click="markAllAsRead"
                        class="inline-flex items-center gap-1.5 bg-dark-700 hover:bg-dark-600 border border-dark-600 text-gray-300 hover:text-white text-xs font-medium rounded-lg px-3 py-2.5 transition-all duration-200 whitespace-nowrap">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Mark All Read
                </button>
                <x-admin.confirm-button
                    title="Dismiss All Alerts?"
                    text="All current recruiter alerts will be marked as dismissed."
                    action="$wire.dismissAll()"
                    confirm-text="Yes, dismiss all"
                    class="inline-flex items-center gap-1.5 bg-red-500/10 hover:bg-red-500/20 text-red-400 hover:text-red-300 text-xs font-medium rounded-lg px-3 py-2.5 transition-all duration-200 whitespace-nowrap"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    Dismiss All
                </x-admin.confirm-button>
            </div>
        </div>
    </div>

    {{-- 5. ALERTS LIST --}}
    <div class="space-y-4">
        @forelse($alerts as $alert)
            @php
                $borderColor = match($alert->alert_type) {
                    'recruiter' => 'border-l-primary',
                    'hiring_manager' => 'border-l-blue-500',
                    'freelance_client' => 'border-l-emerald-500',
                    default => 'border-l-gray-500',
                };
                $typeBadge = match($alert->alert_type) {
                    'recruiter' => ['bg-primary/10 text-primary-light', 'Recruiter'],
                    'hiring_manager' => ['bg-blue-500/10 text-blue-400', 'Hiring Manager'],
                    'freelance_client' => ['bg-emerald-500/10 text-emerald-400', 'Freelance Client'],
                    default => ['bg-gray-500/10 text-gray-400', 'Unknown'],
                };
            @endphp
            <div class="bg-dark-800 border border-dark-700 {{ $borderColor }} border-l-4 rounded-xl p-5 hover:border-dark-600 transition-colors">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        {{-- Type + Urgency badges --}}
                        <div class="flex items-center gap-2 mb-2">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $typeBadge[0] }}">
                                {{ $typeBadge[1] }}
                            </span>
                            @if($alert->urgency === 'urgent')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-red-500/10 text-red-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-400 animate-pulse"></span>
                                    Urgent
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-500/10 text-gray-400">
                                    Normal
                                </span>
                            @endif
                            @if(!$alert->is_read)
                                <span class="w-2 h-2 rounded-full bg-primary-light"></span>
                            @endif
                        </div>

                        {{-- Subject --}}
                        <h3 class="text-sm {{ $alert->is_read ? 'text-gray-300 font-normal' : 'text-white font-semibold' }} truncate mb-1">
                            {{ $alert->email->subject ?? '(No Subject)' }}
                        </h3>

                        {{-- Sender --}}
                        <p class="text-xs text-gray-400 mb-2">
                            {{ $alert->email->from_name ?? '' }}
                            @if($alert->email->from_name && $alert->email->from_email)
                                &lt;{{ $alert->email->from_email }}&gt;
                            @elseif($alert->email->from_email)
                                {{ $alert->email->from_email }}
                            @endif
                        </p>

                        {{-- Snippet --}}
                        @if($alert->email->snippet)
                            <p class="text-xs text-gray-500 line-clamp-2 mb-3">{{ $alert->email->snippet }}</p>
                        @endif

                        {{-- Confidence + Signals + Date --}}
                        <div class="flex flex-wrap items-center gap-4">
                            {{-- Confidence score --}}
                            @if($alert->confidence_score)
                                <div class="flex items-center gap-2">
                                    <div class="w-20 bg-dark-700 rounded-full h-1.5">
                                        <div class="bg-gradient-to-r from-primary to-fuchsia-500 h-1.5 rounded-full" style="width: {{ min($alert->confidence_score, 100) }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-400">{{ number_format($alert->confidence_score, 0) }}%</span>
                                </div>
                            @endif

                            {{-- Detected signals --}}
                            @if($alert->detected_signals)
                                <p class="text-xs text-gray-500">{{ implode(', ', $alert->detected_signals) }}</p>
                            @endif

                            {{-- Received date --}}
                            <span class="text-xs text-gray-500">{{ $alert->created_at->diffForHumans() }}</span>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-1 shrink-0">
                        @if($alert->is_read)
                            <button wire:click="markAsUnread({{ $alert->id }})"
                                    class="p-2 text-gray-400 hover:text-amber-400 hover:bg-amber-500/10 rounded-lg transition-all duration-200"
                                    title="Mark as Unread">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </button>
                        @else
                            <button wire:click="markAsRead({{ $alert->id }})"
                                    class="p-2 text-gray-400 hover:text-primary-light hover:bg-primary/10 rounded-lg transition-all duration-200"
                                    title="Mark as Read">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5"/></svg>
                            </button>
                        @endif
                        <button wire:click="dismiss({{ $alert->id }})"
                                class="p-2 text-gray-400 hover:text-amber-400 hover:bg-amber-500/10 rounded-lg transition-all duration-200"
                                title="Dismiss">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                        <x-admin.confirm-button
                            title="Delete Alert?"
                            text="This recruiter alert will be permanently removed."
                            action="$wire.deleteAlert({{ $alert->id }})"
                            confirm-text="Yes, delete it"
                            class="p-2 text-gray-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-all duration-200"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </x-admin.confirm-button>
                    </div>
                </div>
            </div>
        @empty
            {{-- Empty state --}}
            <div class="bg-dark-800 border border-dark-700 rounded-xl px-6 py-16 text-center">
                <div class="w-12 h-12 rounded-xl bg-dark-700 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </div>
                <h3 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-1">No alerts found</h3>
                <p class="text-sm text-gray-500 mb-4">Try scanning your emails to detect recruiter messages.</p>
                <button wire:click="scanEmails"
                        class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Scan Emails
                </button>
            </div>
        @endforelse
    </div>

    {{-- 6. PAGINATION --}}
    @if($alerts->hasPages())
        <div class="mt-6 flex items-center justify-between">
            <p class="text-sm text-gray-500">Showing {{ $alerts->firstItem() }}--{{ $alerts->lastItem() }} of {{ $alerts->total() }} results</p>
            {{ $alerts->links() }}
        </div>
    @endif
</div>
