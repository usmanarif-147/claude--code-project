<div>
    {{-- 1. BREADCRUMB --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-400">Email</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">Morning Digest</span>
    </div>

    {{-- 2. PAGE HEADER --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Morning Digest</h1>
            <p class="text-sm text-gray-500 mt-1">AI-generated summary of your emails.</p>
        </div>
        <div class="flex items-center gap-3">
            <input type="date" wire:model.live="selectedDate"
                   max="{{ today()->toDateString() }}"
                   class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
            <button wire:click="generateDigest"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20 disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="generateDigest">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </span>
                <span wire:loading wire:target="generateDigest">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                </span>
                <span wire:loading.remove wire:target="generateDigest">Generate Digest</span>
                <span wire:loading wire:target="generateDigest">Generating...</span>
            </button>
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

    {{-- 3. TWO COLUMN LAYOUT --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        {{-- LEFT COLUMN (2/3) --}}
        <div class="xl:col-span-2 space-y-6">
            {{-- Current Digest Card --}}
            <div class="bg-dark-800 border border-dark-700 rounded-xl">
                <div class="flex items-center justify-between px-6 py-4 border-b border-dark-700">
                    <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider">
                        Digest for {{ \Carbon\Carbon::parse($selectedDate)->format('M j, Y') }}
                    </h2>
                    @if($currentDigest)
                        @php
                            $statusClasses = match($currentDigest->status) {
                                'completed' => 'bg-emerald-500/10 text-emerald-400',
                                'generating' => 'bg-amber-500/10 text-amber-400',
                                'failed' => 'bg-red-500/10 text-red-400',
                                default => 'bg-gray-500/10 text-gray-400',
                            };
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $statusClasses }}">
                            @if($currentDigest->status === 'generating')
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                            @elseif($currentDigest->status === 'completed')
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            @elseif($currentDigest->status === 'failed')
                                <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>
                            @else
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                            @endif
                            {{ ucfirst($currentDigest->status) }}
                        </span>
                    @endif
                </div>
                <div class="p-6">
                    @if($currentDigest && $currentDigest->status === 'completed')
                        {{-- Time range subtitle --}}
                        @if($currentDigest->period_start && $currentDigest->period_end)
                            <p class="text-xs text-gray-500 mb-4">
                                {{ $currentDigest->period_start->format('g:i A') }} -- {{ $currentDigest->period_end->format('g:i A') }}
                            </p>
                        @endif

                        {{-- AI Summary --}}
                        <div class="mb-6">
                            <p class="text-sm text-gray-300 leading-relaxed">{{ $currentDigest->summary }}</p>
                        </div>

                        {{-- Category Sections --}}
                        @if($currentDigest->highlights && count($currentDigest->highlights) > 0)
                            @php
                                $grouped = collect($currentDigest->highlights)->groupBy('category');
                                $categoryLabels = [
                                    'job_response' => 'Job Responses',
                                    'freelance' => 'Freelance Inquiries',
                                    'personal' => 'Personal',
                                    'newsletter' => 'Newsletters',
                                    'other' => 'Other',
                                ];
                            @endphp

                            @foreach($categoryLabels as $catKey => $catLabel)
                                @if($grouped->has($catKey))
                                    <div class="mb-5">
                                        <h3 class="text-sm font-mono font-semibold text-white uppercase tracking-wider mb-3">{{ $catLabel }}</h3>
                                        <div class="space-y-3">
                                            @foreach($grouped[$catKey] as $highlight)
                                                <div class="bg-dark-700/50 rounded-lg p-4 hover:bg-dark-700 transition-colors">
                                                    <div class="flex items-start justify-between gap-3">
                                                        <div class="min-w-0 flex-1">
                                                            <p class="text-sm">
                                                                <span class="text-white font-medium">{{ $highlight['from_name'] }}</span>
                                                                <span class="text-gray-400 mx-1">--</span>
                                                                <span class="text-gray-300">{{ $highlight['subject'] ?? '(No Subject)' }}</span>
                                                            </p>
                                                            @if(!empty($highlight['ai_summary']))
                                                                <p class="text-xs text-gray-400 mt-1">{{ $highlight['ai_summary'] }}</p>
                                                            @endif
                                                        </div>
                                                        @if(!empty($highlight['gmail_link']))
                                                            <a href="{{ $highlight['gmail_link'] }}" target="_blank" rel="noopener noreferrer"
                                                               class="shrink-0 text-xs text-primary-light hover:text-white transition-colors flex items-center gap-1">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                                Gmail
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @else
                            <p class="text-sm text-gray-500">No email highlights for this digest.</p>
                        @endif

                    @elseif($currentDigest && $currentDigest->status === 'failed')
                        <div class="text-center py-8">
                            <div class="w-12 h-12 rounded-xl bg-red-500/10 flex items-center justify-center mx-auto mb-4">
                                <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <h3 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-1">Generation Failed</h3>
                            <p class="text-sm text-gray-500">{{ $currentDigest->error_message ?? 'An unknown error occurred.' }}</p>
                        </div>

                    @elseif($currentDigest && $currentDigest->status === 'generating')
                        <div class="text-center py-8">
                            <svg class="animate-spin w-8 h-8 text-primary-light mx-auto mb-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            <h3 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-1">Generating Digest</h3>
                            <p class="text-sm text-gray-500">Please wait while the AI processes your emails...</p>
                        </div>

                    @else
                        {{-- No digest --}}
                        <div class="text-center py-8">
                            <div class="w-12 h-12 rounded-xl bg-dark-700 flex items-center justify-center mx-auto mb-4">
                                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <h3 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-1">No Digest Yet</h3>
                            <p class="text-sm text-gray-500">No digest generated yet for this date. Click "Generate Digest" to create one.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN (1/3) --}}
        <div class="space-y-6">
            {{-- Stats Card --}}
            <div class="bg-dark-800 border border-dark-700 rounded-xl">
                <div class="px-6 py-4 border-b border-dark-700">
                    <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider">Stats</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-400">Total Emails</span>
                        <span class="text-sm font-semibold text-white">{{ $currentDigest->total_emails ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-400">Unread</span>
                        <span class="text-sm font-semibold text-amber-400">{{ $unreadCount }}</span>
                    </div>
                    @if($currentDigest && $currentDigest->categories_breakdown)
                        <div class="pt-3 border-t border-dark-700">
                            <p class="text-xs font-mono font-medium text-gray-500 uppercase tracking-widest mb-3">Categories</p>
                            @php
                                $catColors = [
                                    'job_response' => 'bg-emerald-500',
                                    'freelance' => 'bg-blue-500',
                                    'personal' => 'bg-primary',
                                    'newsletter' => 'bg-fuchsia-500',
                                    'other' => 'bg-gray-500',
                                ];
                                $totalForBar = array_sum($currentDigest->categories_breakdown);
                            @endphp
                            @foreach($currentDigest->categories_breakdown as $catName => $catCount)
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full {{ $catColors[$catName] ?? 'bg-gray-500' }}"></span>
                                        <span class="text-sm text-gray-300">{{ ucfirst(str_replace('_', ' ', $catName)) }}</span>
                                    </div>
                                    <span class="text-sm text-gray-400">{{ $catCount }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Digest History Card --}}
            <div class="bg-dark-800 border border-dark-700 rounded-xl">
                <div class="px-6 py-4 border-b border-dark-700">
                    <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider">Digest History</h2>
                </div>
                <div class="divide-y divide-dark-700/50">
                    @forelse($digestHistory as $digest)
                        <button wire:click="viewDigest({{ $digest->id }})"
                                class="w-full flex items-center justify-between px-6 py-3 hover:bg-dark-700/30 transition-colors text-left {{ $digest->digest_date->toDateString() === $selectedDate ? 'bg-primary/5 border-l-2 border-primary' : '' }}">
                            <div>
                                <p class="text-sm {{ $digest->digest_date->toDateString() === $selectedDate ? 'text-white font-medium' : 'text-gray-300' }}">
                                    {{ $digest->digest_date->format('M j, Y') }}
                                </p>
                                <p class="text-xs text-gray-500">{{ $digest->total_emails }} emails</p>
                            </div>
                            @php
                                $hStatusClasses = match($digest->status) {
                                    'completed' => 'bg-emerald-500/10 text-emerald-400',
                                    'generating' => 'bg-amber-500/10 text-amber-400',
                                    'failed' => 'bg-red-500/10 text-red-400',
                                    default => 'bg-gray-500/10 text-gray-400',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $hStatusClasses }}">
                                {{ ucfirst($digest->status) }}
                            </span>
                        </button>
                    @empty
                        <div class="px-6 py-8 text-center">
                            <p class="text-sm text-gray-500">No digests generated yet.</p>
                        </div>
                    @endforelse
                </div>
                @if($digestHistory->hasPages())
                    <div class="px-6 py-3 border-t border-dark-700">
                        {{ $digestHistory->links() }}
                    </div>
                @endif
            </div>

            {{-- Last Sync Info Card --}}
            <div class="bg-dark-800 border border-dark-700 rounded-xl">
                <div class="px-6 py-4 border-b border-dark-700">
                    <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider">Last Sync</h2>
                </div>
                <div class="p-6">
                    @if($lastSync)
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-400">Synced</span>
                                <span class="text-sm text-gray-300">{{ $lastSync->synced_at->diffForHumans() }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-400">Fetched</span>
                                <span class="text-sm text-white font-medium">{{ $lastSync->emails_fetched }} emails</span>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-gray-500">No sync has been performed yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
