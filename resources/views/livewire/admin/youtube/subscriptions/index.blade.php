<div>
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-400">YouTube</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">Subscriptions</span>
    </div>

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Subscribed Channels</h1>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary-light">
                    {{ $filteredSubscriptions->count() }}
                </span>
            </div>
            <p class="text-sm text-gray-500 mt-1">Manage your YouTube channel subscriptions.</p>
        </div>
        <div class="flex items-center gap-2">
            <button wire:click="importSubscriptions"
                    wire:loading.attr="disabled"
                    wire:target="importSubscriptions"
                    class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 text-gray-300 text-sm font-medium rounded-lg px-4 py-2.5 transition-colors disabled:opacity-50">
                <svg wire:loading.remove wire:target="importSubscriptions" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <svg wire:loading wire:target="importSubscriptions" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                <span wire:loading.remove wire:target="importSubscriptions">Import My Subscriptions</span>
                <span wire:loading wire:target="importSubscriptions">Importing...</span>
            </button>
            <button wire:click="refreshAll"
                    wire:loading.attr="disabled"
                    wire:target="refreshAll"
                    class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20 disabled:opacity-50">
                <svg wire:loading.remove wire:target="refreshAll" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <svg wire:loading wire:target="refreshAll" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                <span wire:loading.remove wire:target="refreshAll">Sync All</span>
                <span wire:loading wire:target="refreshAll">Syncing...</span>
            </button>
        </div>
    </div>

    {{-- Flash Messages --}}
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

    {{-- Add Channel Card --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 mb-6">
        <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider mb-4">Add Channel</h2>
        <form wire:submit="subscribe" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input type="text"
                       wire:model="newChannelInput"
                       placeholder="Enter channel URL, @handle, or channel ID..."
                       class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                @error('newChannelInput')
                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit"
                    wire:loading.attr="disabled"
                    wire:target="subscribe"
                    class="inline-flex items-center justify-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-colors disabled:opacity-50 whitespace-nowrap">
                <svg wire:loading.remove wire:target="subscribe" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <svg wire:loading wire:target="subscribe" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                <span wire:loading.remove wire:target="subscribe">Subscribe</span>
                <span wire:loading wire:target="subscribe">Subscribing...</span>
            </button>
        </form>
    </div>

    {{-- Search Bar --}}
    @if($this->subscriptions->count() > 0)
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-4 mb-6">
            <input type="text"
                   wire:model.live.debounce.300ms="search"
                   placeholder="Search subscribed channels..."
                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
        </div>
    @endif

    {{-- Channel Grid --}}
    @if($filteredSubscriptions->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($filteredSubscriptions as $subscription)
                <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
                    {{-- Channel Header --}}
                    <div class="flex items-start gap-3 mb-4">
                        @if($subscription->channel_thumbnail_url)
                            <img src="{{ $subscription->channel_thumbnail_url }}"
                                 alt="{{ $subscription->channel_title }}"
                                 class="w-16 h-16 rounded-full object-cover shrink-0">
                        @else
                            <div class="w-16 h-16 rounded-full bg-dark-700 flex items-center justify-center shrink-0">
                                <span class="text-xl font-bold text-primary-light">{{ strtoupper(substr($subscription->channel_title, 0, 1)) }}</span>
                            </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <h3 class="text-sm font-medium text-white truncate" title="{{ $subscription->channel_title }}">
                                {{ $subscription->channel_title }}
                            </h3>
                            <p class="text-xs text-gray-400 mt-0.5">
                                @if($subscription->subscriber_count)
                                    @if($subscription->subscriber_count >= 1000000)
                                        {{ round($subscription->subscriber_count / 1000000, 1) }}M
                                    @elseif($subscription->subscriber_count >= 1000)
                                        {{ round($subscription->subscriber_count / 1000, 1) }}K
                                    @else
                                        {{ $subscription->subscriber_count }}
                                    @endif
                                    subscribers
                                @else
                                    No subscriber data
                                @endif
                            </p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ number_format($subscription->video_count) }} videos
                            </p>
                        </div>
                    </div>

                    {{-- Meta Info --}}
                    <div class="space-y-1.5 mb-4">
                        @if($subscription->last_video_at)
                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                <span>Last video {{ $subscription->last_video_at->diffForHumans() }}</span>
                            </div>
                        @endif
                        @if($subscription->synced_at)
                            <div class="flex items-center gap-2 text-xs text-gray-600">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span>Synced {{ $subscription->synced_at->diffForHumans() }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 pt-3 border-t border-dark-700">
                        @if(Route::has('admin.youtube.subscriptions.feed'))
                            <a href="{{ route('admin.youtube.subscriptions.feed', ['filterChannel' => $subscription->id]) }}" wire:navigate
                               class="inline-flex items-center gap-1.5 text-xs text-gray-400 hover:text-primary-light transition-colors px-2 py-1.5 rounded-lg hover:bg-dark-700"
                               title="View Videos">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Videos</span>
                            </a>
                        @endif

                        <button wire:click="refreshChannel({{ $subscription->id }})"
                                wire:loading.attr="disabled"
                                wire:target="refreshChannel({{ $subscription->id }})"
                                class="inline-flex items-center gap-1.5 text-xs text-gray-400 hover:text-primary-light transition-colors px-2 py-1.5 rounded-lg hover:bg-dark-700 disabled:opacity-50"
                                title="Refresh">
                            <svg wire:loading.remove wire:target="refreshChannel({{ $subscription->id }})" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <svg wire:loading wire:target="refreshChannel({{ $subscription->id }})" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            <span>Refresh</span>
                        </button>

                        <x-admin.confirm-button
                            title="Unsubscribe?"
                            text="Are you sure you want to unsubscribe from {{ $subscription->channel_title }}?"
                            action="$wire.unsubscribe({{ $subscription->id }})"
                            confirm-text="Yes, delete it"
                            class="inline-flex items-center gap-1.5 text-xs text-gray-400 hover:text-red-400 transition-colors px-2 py-1.5 rounded-lg hover:bg-red-500/10 ml-auto"
                            title="Unsubscribe">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            <span>Unsub</span>
                        </x-admin.confirm-button>
                    </div>
                </div>
            @endforeach
        </div>
    @elseif($this->subscriptions->count() === 0)
        {{-- Empty State --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl py-16 text-center">
            <div class="w-12 h-12 rounded-xl bg-dark-700 flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
            </div>
            <h3 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-1">No subscriptions yet</h3>
            <p class="text-sm text-gray-500 mb-4">Add your first YouTube channel to start tracking new videos.</p>
        </div>
    @else
        {{-- No search results --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl py-16 text-center">
            <div class="w-12 h-12 rounded-xl bg-dark-700 flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <h3 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-1">No channels found</h3>
            <p class="text-sm text-gray-500">No channels match your search "{{ $search }}".</p>
        </div>
    @endif
</div>
