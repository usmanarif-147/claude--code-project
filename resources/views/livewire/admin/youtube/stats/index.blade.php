<div>
    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="mb-5 flex items-center gap-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl px-5 py-3.5">
            <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-sm text-emerald-400">{{ session('success') }}</p>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-5 flex items-center gap-3 bg-red-500/10 border border-red-500/20 rounded-xl px-5 py-3.5">
            <svg class="w-5 h-5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-sm text-red-400">{{ session('error') }}</p>
        </div>
    @endif

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-400">YouTube</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">Stats</span>
    </div>

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">YouTube Stats</h1>
            <p class="text-sm text-gray-500 mt-1">Monitor your YouTube channel performance.</p>
        </div>
        @if ($isConfigured)
            <button wire:click="refreshStats"
                    wire:loading.attr="disabled"
                    wire:target="refreshStats"
                    class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20 disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="refreshStats">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </span>
                <span wire:loading wire:target="refreshStats">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                </span>
                <span wire:loading.remove wire:target="refreshStats">Refresh</span>
                <span wire:loading wire:target="refreshStats">Refreshing...</span>
            </button>
        @endif
    </div>

    {{-- Not Configured State --}}
    @if (! $isConfigured)
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-8 text-center">
            <div class="w-14 h-14 rounded-xl bg-amber-500/10 flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            </div>
            <h3 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-2">YouTube API Not Configured</h3>
            <p class="text-sm text-gray-400 mb-5 max-w-md mx-auto">Add your Google API key and YouTube channel ID in Settings to start tracking your channel stats.</p>
            <a href="{{ route('admin.settings.api-keys') }}" wire:navigate
               class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Go to API Keys Settings
            </a>
        </div>
    @else
        {{-- Channel Header Card --}}
        @if ($channelStats)
            <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 mb-8 flex items-center gap-4">
                @if ($channelStats->channel_thumbnail_url)
                    <img src="{{ $channelStats->channel_thumbnail_url }}" alt="{{ $channelStats->channel_title }}" class="w-12 h-12 rounded-full shrink-0">
                @else
                    <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                @endif
                <div>
                    <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider">{{ $channelStats->channel_title }}</h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Last updated: {{ $channelStats->fetched_at ? $channelStats->fetched_at->diffForHumans() : 'Never' }}
                    </p>
                </div>
            </div>
        @endif

        {{-- Stats Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            {{-- Subscribers --}}
            <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                    @if (! empty($weeklyComparison['deltas']['subscribers']))
                        @php $delta = $weeklyComparison['deltas']['subscribers']; @endphp
                        <span class="inline-flex items-center gap-1 text-xs font-medium {{ $delta['direction'] === 'up' ? 'text-emerald-400 bg-emerald-500/10' : 'text-red-400 bg-red-500/10' }} px-2 py-1 rounded-full">
                            @if ($delta['direction'] === 'up')
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17l9.2-9.2M17 17V7H7"/></svg>
                            @else
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 7l-9.2 9.2M7 7v10h10"/></svg>
                            @endif
                            {{ $delta['percentage'] > 0 ? '+' : '' }}{{ $delta['percentage'] }}%
                        </span>
                    @endif
                </div>
                <p class="text-3xl font-bold text-white mb-1">{{ $this->formatCompactNumber($channelStats?->subscriber_count ?? 0) }}</p>
                <p class="text-sm text-gray-500">Subscribers</p>
            </div>

            {{-- Total Views --}}
            <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-10 h-10 rounded-lg bg-blue-500/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </div>
                    @if (! empty($weeklyComparison['deltas']['views']))
                        @php $delta = $weeklyComparison['deltas']['views']; @endphp
                        <span class="inline-flex items-center gap-1 text-xs font-medium {{ $delta['direction'] === 'up' ? 'text-emerald-400 bg-emerald-500/10' : 'text-red-400 bg-red-500/10' }} px-2 py-1 rounded-full">
                            @if ($delta['direction'] === 'up')
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17l9.2-9.2M17 17V7H7"/></svg>
                            @else
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 7l-9.2 9.2M7 7v10h10"/></svg>
                            @endif
                            {{ $delta['percentage'] > 0 ? '+' : '' }}{{ $delta['percentage'] }}%
                        </span>
                    @endif
                </div>
                <p class="text-3xl font-bold text-white mb-1">{{ $this->formatCompactNumber($channelStats?->total_view_count ?? 0) }}</p>
                <p class="text-sm text-gray-500">Total Views</p>
            </div>

            {{-- Watch Time --}}
            <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-10 h-10 rounded-lg bg-fuchsia-500/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-fuchsia-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    @if (! empty($weeklyComparison['deltas']['watch_hours']))
                        @php $delta = $weeklyComparison['deltas']['watch_hours']; @endphp
                        <span class="inline-flex items-center gap-1 text-xs font-medium {{ $delta['direction'] === 'up' ? 'text-emerald-400 bg-emerald-500/10' : 'text-red-400 bg-red-500/10' }} px-2 py-1 rounded-full">
                            @if ($delta['direction'] === 'up')
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17l9.2-9.2M17 17V7H7"/></svg>
                            @else
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 7l-9.2 9.2M7 7v10h10"/></svg>
                            @endif
                            {{ $delta['percentage'] > 0 ? '+' : '' }}{{ $delta['percentage'] }}%
                        </span>
                    @endif
                </div>
                <p class="text-3xl font-bold text-white mb-1">{{ number_format($channelStats?->estimated_watch_hours ?? 0) }} <span class="text-base font-normal text-gray-500">hrs</span></p>
                <p class="text-sm text-gray-500">Watch Time</p>
            </div>

            {{-- Revenue --}}
            <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
                <div class="flex items-start justify-between mb-4">
                    <div class="w-10 h-10 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
                <div class="mb-1" x-data="{ editing: false }">
                    <div x-show="!editing" @click="editing = true; $nextTick(() => $refs.revenueInput.focus())" class="cursor-pointer group">
                        <p class="text-3xl font-bold text-white">
                            @if ($channelStats && $channelStats->monthly_revenue !== null)
                                ${{ number_format((float) $channelStats->monthly_revenue, 2) }}
                            @else
                                <span class="text-gray-500 text-lg">Not set</span>
                            @endif
                        </p>
                        <span class="text-xs text-gray-600 group-hover:text-gray-400 transition-colors">Click to edit</span>
                    </div>
                    <div x-show="editing" x-cloak>
                        <div class="flex items-center gap-2">
                            <span class="text-xl font-bold text-gray-400">$</span>
                            <input type="text"
                                   x-ref="revenueInput"
                                   wire:model="monthlyRevenue"
                                   wire:blur="updateRevenue"
                                   wire:keydown.enter="updateRevenue"
                                   @keydown.enter="editing = false"
                                   @blur="editing = false"
                                   placeholder="0.00"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-3 py-1.5 text-white text-lg font-bold placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                        </div>
                    </div>
                </div>
                <p class="text-sm text-gray-500">Revenue (Monthly)</p>
            </div>
        </div>

        {{-- Weekly Comparison Card --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl mb-8">
            <div class="px-6 py-4 border-b border-dark-700">
                <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider">This Week vs Last Week</h2>
            </div>
            <div class="p-6">
                @if (! empty($weeklyComparison['current']) && ! empty($weeklyComparison['previous']))
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-dark-700">
                                    <th class="px-4 py-3 text-left text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">Metric</th>
                                    <th class="px-4 py-3 text-right text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">This Week</th>
                                    <th class="px-4 py-3 text-right text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">Last Week</th>
                                    <th class="px-4 py-3 text-right text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">Change</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-dark-700/50">
                                @php
                                    $current = $weeklyComparison['current'];
                                    $previous = $weeklyComparison['previous'];
                                    $deltas = $weeklyComparison['deltas'];
                                @endphp

                                {{-- Subscribers --}}
                                <tr class="hover:bg-dark-700/30 transition-colors">
                                    <td class="px-4 py-3 text-sm text-gray-300">Subscribers</td>
                                    <td class="px-4 py-3 text-sm text-white text-right font-medium">{{ number_format($current->subscriber_count ?? 0) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-400 text-right">{{ number_format($previous->subscriber_count ?? 0) }}</td>
                                    <td class="px-4 py-3 text-sm text-right">
                                        @if (isset($deltas['subscribers']))
                                            <span class="inline-flex items-center gap-1 {{ $deltas['subscribers']['direction'] === 'up' ? 'text-emerald-400' : 'text-red-400' }}">
                                                @if ($deltas['subscribers']['direction'] === 'up')
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                                @else
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                @endif
                                                {{ $deltas['subscribers']['percentage'] > 0 ? '+' : '' }}{{ $deltas['subscribers']['percentage'] }}%
                                            </span>
                                        @endif
                                    </td>
                                </tr>

                                {{-- Views --}}
                                <tr class="hover:bg-dark-700/30 transition-colors">
                                    <td class="px-4 py-3 text-sm text-gray-300">Views</td>
                                    <td class="px-4 py-3 text-sm text-white text-right font-medium">{{ number_format($current->view_count ?? 0) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-400 text-right">{{ number_format($previous->view_count ?? 0) }}</td>
                                    <td class="px-4 py-3 text-sm text-right">
                                        @if (isset($deltas['views']))
                                            <span class="inline-flex items-center gap-1 {{ $deltas['views']['direction'] === 'up' ? 'text-emerald-400' : 'text-red-400' }}">
                                                @if ($deltas['views']['direction'] === 'up')
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                                @else
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                @endif
                                                {{ $deltas['views']['percentage'] > 0 ? '+' : '' }}{{ $deltas['views']['percentage'] }}%
                                            </span>
                                        @endif
                                    </td>
                                </tr>

                                {{-- Watch Hours --}}
                                <tr class="hover:bg-dark-700/30 transition-colors">
                                    <td class="px-4 py-3 text-sm text-gray-300">Watch Hours</td>
                                    <td class="px-4 py-3 text-sm text-white text-right font-medium">{{ number_format($current->estimated_watch_hours ?? 0) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-400 text-right">{{ number_format($previous->estimated_watch_hours ?? 0) }}</td>
                                    <td class="px-4 py-3 text-sm text-right">
                                        @if (isset($deltas['watch_hours']))
                                            <span class="inline-flex items-center gap-1 {{ $deltas['watch_hours']['direction'] === 'up' ? 'text-emerald-400' : 'text-red-400' }}">
                                                @if ($deltas['watch_hours']['direction'] === 'up')
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                                @else
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                @endif
                                                {{ $deltas['watch_hours']['percentage'] > 0 ? '+' : '' }}{{ $deltas['watch_hours']['percentage'] }}%
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-6">
                        <p class="text-sm text-gray-500">No comparison data yet. Stats will appear after two weekly snapshots.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Recent Videos Table --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-dark-700">
                <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider">Recent Videos</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-dark-700">
                            <th class="px-6 py-4 text-left text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">Video</th>
                            <th class="px-6 py-4 text-left text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">Published</th>
                            <th class="px-6 py-4 text-right text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">Views</th>
                            <th class="px-6 py-4 text-right text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">Likes</th>
                            <th class="px-6 py-4 text-right text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">Comments</th>
                            <th class="px-6 py-4 text-right text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-dark-700/50">
                        @forelse ($recentVideos as $video)
                            <tr class="hover:bg-dark-700/30 transition-colors duration-150">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <a href="https://www.youtube.com/watch?v={{ $video->video_id }}" target="_blank" rel="noopener noreferrer" class="shrink-0">
                                            @if ($video->thumbnail_url)
                                                <img src="{{ $video->thumbnail_url }}" alt="{{ $video->title }}" class="w-20 h-12 rounded-lg object-cover">
                                            @else
                                                <div class="w-20 h-12 rounded-lg bg-dark-700 flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                </div>
                                            @endif
                                        </a>
                                        <div class="min-w-0">
                                            <a href="https://www.youtube.com/watch?v={{ $video->video_id }}" target="_blank" rel="noopener noreferrer"
                                               class="text-sm font-medium text-white hover:text-primary-light transition-colors truncate block max-w-[300px]">
                                                {{ $video->title }}
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-400">{{ $video->published_at->diffForHumans() }}</td>
                                <td class="px-6 py-4 text-sm text-gray-400 text-right">{{ $this->formatCompactNumber($video->view_count) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-400 text-right">{{ $this->formatCompactNumber($video->like_count) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-400 text-right">{{ $this->formatCompactNumber($video->comment_count) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-400 text-right">{{ $this->formatDuration($video->duration) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-12 h-12 rounded-xl bg-dark-700 flex items-center justify-center mb-3">
                                            <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        </div>
                                        <p class="text-sm text-gray-500">No videos found.</p>
                                        <p class="text-xs text-gray-600 mt-1">Click Refresh to fetch your latest videos.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
