<div>
    {{-- 1. BREADCRUMB --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-400">Email</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">Inbox</span>
    </div>

    {{-- 2. PAGE HEADER --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Email Inbox</h1>
            <p class="text-sm text-gray-500 mt-1">Manage your fetched emails from Gmail.</p>
        </div>
        <button wire:click="syncNow"
                wire:loading.attr="disabled"
                x-data="{ cooldown: false }"
                x-on:click="if(!cooldown){ cooldown = true; setTimeout(() => cooldown = false, 30000) }"
                x-bind:disabled="cooldown"
                class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20 disabled:opacity-50 disabled:cursor-not-allowed">
            <span wire:loading.remove wire:target="syncNow">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            </span>
            <span wire:loading wire:target="syncNow">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            </span>
            <span wire:loading.remove wire:target="syncNow">Sync Now</span>
            <span wire:loading wire:target="syncNow">Syncing...</span>
        </button>
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

    {{-- 3. STAT CARDS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        {{-- Total Emails --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-white mb-1">{{ number_format($stats['total']) }}</p>
            <p class="text-sm text-gray-500">Total Emails</p>
        </div>

        {{-- Unread --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-lg bg-amber-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-white mb-1">{{ number_format($stats['unread']) }}</p>
            <p class="text-sm text-gray-500">Unread</p>
        </div>

        {{-- Important --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-lg bg-red-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-white mb-1">{{ number_format($stats['important']) }}</p>
            <p class="text-sm text-gray-500">Important</p>
        </div>

        {{-- Today --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-white mb-1">{{ number_format($stats['today']) }}</p>
            <p class="text-sm text-gray-500">Today</p>
        </div>
    </div>

    {{-- 4. FILTER BAR --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl p-4 mb-5">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Search by sender, subject..."
                       class="w-full bg-dark-700 border border-dark-600 rounded-lg pl-9 pr-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
            </div>
            <select wire:model.live="filterCategory"
                    class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-primary focus:border-transparent transition-all min-w-[160px]">
                <option value="">All Categories</option>
                <option value="job_response">Job Response</option>
                <option value="freelance">Freelance</option>
                <option value="personal">Personal</option>
                <option value="newsletter">Newsletter</option>
                <option value="other">Other</option>
            </select>
            <select wire:model.live="filterRead"
                    class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-primary focus:border-transparent transition-all min-w-[140px]">
                <option value="">All Emails</option>
                <option value="read">Read</option>
                <option value="unread">Unread</option>
            </select>
        </div>
    </div>

    {{-- 5. EMAIL TABLE --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-dark-700">
                        <th class="px-6 py-4 text-left text-xs font-mono font-medium text-gray-500 uppercase tracking-wider w-10">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">Sender</th>
                        <th class="px-6 py-4 text-left text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                        <th class="px-6 py-4 text-left text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-4 text-left text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">Received</th>
                        <th class="px-6 py-4 text-right text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700/50">
                    @forelse($emails as $email)
                        <tr wire:click="markRead({{ $email->id }})"
                            class="hover:bg-dark-700/30 transition-colors duration-150 cursor-pointer {{ !$email->is_read ? 'bg-dark-700/20' : '' }}">
                            {{-- Status dot --}}
                            <td class="px-6 py-4">
                                @if(!$email->is_read)
                                    <span class="w-2.5 h-2.5 rounded-full bg-primary inline-block" title="Unread"></span>
                                @else
                                    <span class="w-2.5 h-2.5 rounded-full bg-dark-600 inline-block" title="Read"></span>
                                @endif
                            </td>

                            {{-- Sender --}}
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-sm font-medium {{ !$email->is_read ? 'text-white' : 'text-gray-300' }}">
                                        {{ $email->from_name ?? $email->from_email }}
                                    </p>
                                    @if($email->from_name)
                                        <p class="text-xs text-gray-500">{{ $email->from_email }}</p>
                                    @endif
                                </div>
                            </td>

                            {{-- Subject + snippet --}}
                            <td class="px-6 py-4 max-w-md">
                                <p class="text-sm {{ !$email->is_read ? 'text-white font-medium' : 'text-gray-300' }} truncate">
                                    {{ $email->subject ?? '(No Subject)' }}
                                </p>
                                <p class="text-xs text-gray-500 truncate mt-0.5">{{ Str::limit($email->snippet, 80) }}</p>
                            </td>

                            {{-- Category --}}
                            <td class="px-6 py-4">
                                @if($email->category)
                                    @php
                                        $catClasses = match($email->category) {
                                            'job_response' => 'bg-emerald-500/10 text-emerald-400',
                                            'freelance' => 'bg-blue-500/10 text-blue-400',
                                            'personal' => 'bg-primary/10 text-primary-light',
                                            'newsletter' => 'bg-fuchsia-500/10 text-fuchsia-400',
                                            default => 'bg-gray-500/10 text-gray-400',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $catClasses }}">
                                        {{ ucfirst(str_replace('_', ' ', $email->category)) }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-600">--</span>
                                @endif
                            </td>

                            {{-- Received --}}
                            <td class="px-6 py-4 text-sm text-gray-400 whitespace-nowrap">
                                {{ $email->received_at->diffForHumans() }}
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 text-right">
                                @if($email->gmail_link)
                                    <a href="{{ $email->gmail_link }}" target="_blank" rel="noopener noreferrer"
                                       onclick="event.stopPropagation()"
                                       class="inline-flex items-center gap-1.5 text-xs text-primary-light hover:text-white transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        Gmail
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="w-12 h-12 rounded-xl bg-dark-700 flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </div>
                                <h3 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-1">No emails found</h3>
                                <p class="text-sm text-gray-500">Click Sync Now to fetch from Gmail.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($emails->hasPages())
            <div class="px-6 py-4 border-t border-dark-700 flex items-center justify-between">
                <p class="text-sm text-gray-500">Showing {{ $emails->firstItem() }}--{{ $emails->lastItem() }} of {{ $emails->total() }} results</p>
                {{ $emails->links() }}
            </div>
        @endif
    </div>
</div>
