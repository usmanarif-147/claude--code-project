<div>
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-400">YouTube</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">Video Feed</span>
    </div>

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Video Feed</h1>
            @if($newVideoCount > 0)
                <span class="bg-primary text-white text-xs font-medium px-2.5 py-1 rounded-full">{{ $newVideoCount }} new</span>
            @endif
        </div>
        <div class="flex items-center gap-3">
            @if($newVideoCount > 0)
                <button wire:click="markAllAsRead"
                        class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 text-gray-300 hover:text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-colors border border-dark-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Mark All Read
                </button>
            @endif
            <button wire:click="syncFeed"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20 disabled:opacity-50">
                <svg class="w-4 h-4" wire:loading.class="animate-spin" wire:target="syncFeed" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span wire:loading.remove wire:target="syncFeed">Sync Feed</span>
                <span wire:loading wire:target="syncFeed">Syncing...</span>
            </button>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success') || session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="mb-6">
            @if(session('success'))
                <div class="flex items-center gap-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-lg px-4 py-3 text-sm">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p>{{ session('success') }}</p>
                    <button @click="show = false" class="ml-auto text-emerald-400/60 hover:text-emerald-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-center gap-3 bg-red-500/10 border border-red-500/20 text-red-400 rounded-lg px-4 py-3 text-sm">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p>{{ session('error') }}</p>
                    <button @click="show = false" class="ml-auto text-red-400/60 hover:text-red-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
            @endif
        </div>
    @endif

    {{-- Filter Bar --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl p-4 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
            {{-- Channel Filter --}}
            <select wire:model.live="filterChannel"
                    class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
                <option value="">All Channels</option>
                @foreach($subscriptions as $sub)
                    <option value="{{ $sub->id }}">{{ $sub->channel_title }}</option>
                @endforeach
            </select>

            {{-- Category Filter --}}
            <select wire:model.live="filterCategory"
                    class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
                <option value="">All Categories</option>
                @foreach($categories as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>

            {{-- Language Filter --}}
            <select wire:model.live="filterLanguage"
                    class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
                <option value="">All Languages</option>
                <option value="en">English</option>
                <option value="es">Spanish</option>
                <option value="hi">Hindi</option>
                <option value="ur">Urdu</option>
                <option value="ar">Arabic</option>
                <option value="fr">French</option>
                <option value="de">German</option>
                <option value="ja">Japanese</option>
                <option value="ko">Korean</option>
                <option value="pt">Portuguese</option>
            </select>

            {{-- Search --}}
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search videos..."
                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
        </div>

        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
            {{-- Date From --}}
            <div class="flex items-center gap-2">
                <label class="text-xs text-gray-500 whitespace-nowrap">From</label>
                <input type="date" wire:model.live="filterDateFrom"
                       class="bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>

            {{-- Date To --}}
            <div class="flex items-center gap-2">
                <label class="text-xs text-gray-500 whitespace-nowrap">To</label>
                <input type="date" wire:model.live="filterDateTo"
                       class="bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
            </div>

            {{-- Duration Toggle Buttons --}}
            <div class="flex items-center gap-1 bg-dark-700 rounded-lg p-1 border border-dark-600">
                <button wire:click="$set('filterDuration', '')"
                        class="px-3 py-1.5 text-xs font-medium rounded-md transition-colors {{ $filterDuration === '' ? 'bg-primary text-white' : 'text-gray-400 hover:text-white' }}">
                    All
                </button>
                <button wire:click="$set('filterDuration', 'short')"
                        class="px-3 py-1.5 text-xs font-medium rounded-md transition-colors {{ $filterDuration === 'short' ? 'bg-primary text-white' : 'text-gray-400 hover:text-white' }}">
                    Short &lt;4m
                </button>
                <button wire:click="$set('filterDuration', 'medium')"
                        class="px-3 py-1.5 text-xs font-medium rounded-md transition-colors {{ $filterDuration === 'medium' ? 'bg-primary text-white' : 'text-gray-400 hover:text-white' }}">
                    Medium 4-20m
                </button>
                <button wire:click="$set('filterDuration', 'long')"
                        class="px-3 py-1.5 text-xs font-medium rounded-md transition-colors {{ $filterDuration === 'long' ? 'bg-primary text-white' : 'text-gray-400 hover:text-white' }}">
                    Long &gt;20m
                </button>
            </div>

            {{-- Clear Filters --}}
            @if($filterChannel || $filterDateFrom || $filterDateTo || $filterCategory || $filterLanguage || $filterDuration || $search)
                <button wire:click="clearFilters"
                        class="text-xs text-gray-400 hover:text-white transition-colors underline whitespace-nowrap">
                    Clear Filters
                </button>
            @endif
        </div>
    </div>

    {{-- Video Grid --}}
    @if($videos->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5 mb-6">
            @foreach($videos as $video)
                <div class="bg-dark-800 border border-dark-700 rounded-xl overflow-hidden group hover:border-dark-600 transition-colors">
                    {{-- Thumbnail --}}
                    <div class="relative cursor-pointer" wire:click="playVideo('{{ $video->video_id }}')">
                        @if($video->thumbnail_url)
                            <img src="{{ $video->thumbnail_url }}" alt="{{ $video->title }}" class="w-full aspect-video object-cover">
                        @else
                            <div class="w-full aspect-video bg-dark-700 flex items-center justify-center">
                                <svg class="w-12 h-12 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                        @endif

                        {{-- Play Overlay --}}
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-colors flex items-center justify-center">
                            <div class="opacity-0 group-hover:opacity-100 transition-opacity bg-black/60 rounded-full p-3">
                                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            </div>
                        </div>

                        {{-- Duration Badge --}}
                        @if($video->duration)
                            <span class="absolute bottom-2 right-2 bg-black/80 text-white text-xs font-medium rounded px-1.5 py-0.5">
                                {{ \App\Services\YouTubeSubscriptionService::formatDuration($video->duration) }}
                            </span>
                        @endif

                        {{-- NEW Badge --}}
                        @if($video->is_new)
                            <span class="absolute top-2 left-2 bg-primary text-white text-xs font-medium rounded-full px-2 py-0.5">NEW</span>
                        @endif
                    </div>

                    {{-- Video Info --}}
                    <div class="p-3">
                        <p class="text-xs text-gray-400 mb-1 truncate">{{ $video->channel_title }}</p>
                        <h3 class="text-sm font-medium text-white line-clamp-2 mb-2 leading-snug">{{ $video->title }}</h3>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                <span>{{ number_format($video->view_count) }} views</span>
                                <span>&middot;</span>
                                <span>{{ $video->published_at?->diffForHumans() }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                {{-- Play Button --}}
                                <button wire:click="playVideo('{{ $video->video_id }}')"
                                        class="p-1.5 text-gray-500 hover:text-white transition-colors rounded-lg hover:bg-dark-700"
                                        title="Play">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </button>

                                {{-- Save/Unsave Heart --}}
                                @if(in_array($video->video_id, $savedVideoIds))
                                    <button wire:click="unsaveVideo('{{ $video->video_id }}')"
                                            class="p-1.5 text-red-400 hover:text-red-300 transition-colors rounded-lg hover:bg-dark-700"
                                            title="Remove from favorites">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/></svg>
                                    </button>
                                @else
                                    <button wire:click="saveVideo('{{ $video->video_id }}')"
                                            class="p-1.5 text-gray-500 hover:text-red-400 transition-colors rounded-lg hover:bg-dark-700"
                                            title="Save to favorites">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $videos->links() }}
        </div>
    @else
        {{-- Empty State --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-12 text-center">
            <svg class="w-16 h-16 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
            <h3 class="text-lg font-mono font-bold text-white uppercase tracking-wider mb-2">No Videos Found</h3>
            <p class="text-gray-400 text-sm mb-4">
                @if($filterChannel || $filterDateFrom || $filterDateTo || $filterCategory || $filterLanguage || $filterDuration || $search)
                    No videos match your current filters. Try adjusting or clearing them.
                @else
                    Subscribe to channels and sync your feed to see videos here.
                @endif
            </p>
            @if($filterChannel || $filterDateFrom || $filterDateTo || $filterCategory || $filterLanguage || $filterDuration || $search)
                <button wire:click="clearFilters"
                        class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 text-gray-300 hover:text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-colors border border-dark-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Clear Filters
                </button>
            @endif
        </div>
    @endif

    {{-- YouTube Player Modal --}}
    @if($showPlayer && $selectedVideoId)
        <div x-data="{ showDescription: false }"
             x-init="document.body.classList.add('overflow-hidden')"
             x-on:keydown.escape.window="$wire.closePlayer()"
             class="fixed inset-0 z-50 flex items-center justify-center p-4">

            {{-- Overlay --}}
            <div class="absolute inset-0 bg-black/70" wire:click="closePlayer"></div>

            {{-- Modal --}}
            <div class="relative w-full max-w-4xl bg-dark-800 border border-dark-700 rounded-xl overflow-hidden shadow-2xl z-10">
                {{-- Close Button --}}
                <button wire:click="closePlayer"
                        class="absolute top-3 right-3 z-20 bg-black/60 hover:bg-black/80 text-white rounded-full p-2 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>

                {{-- YouTube Iframe --}}
                <iframe src="https://www.youtube.com/embed/{{ $selectedVideoId }}?autoplay=1"
                        allowfullscreen
                        allow="autoplay; encrypted-media"
                        class="w-full aspect-video rounded-t-xl"></iframe>

                {{-- Video Details --}}
                @if($selectedVideoData)
                    <div class="p-5">
                        <h2 class="text-lg font-medium text-white mb-1">{{ $selectedVideoData['title'] }}</h2>
                        <p class="text-sm text-gray-400 mb-3">{{ $selectedVideoData['channel_title'] }} &middot; {{ $selectedVideoData['published_at'] }}</p>

                        {{-- Stats --}}
                        <div class="flex items-center gap-4 text-sm text-gray-400 mb-4">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                {{ number_format($selectedVideoData['view_count']) }} views
                            </span>
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/></svg>
                                {{ number_format($selectedVideoData['like_count']) }} likes
                            </span>
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                {{ number_format($selectedVideoData['comment_count']) }} comments
                            </span>
                        </div>

                        {{-- Description (collapsible) --}}
                        @if($selectedVideoData['description'])
                            <div class="border-t border-dark-700 pt-3">
                                <button @click="showDescription = !showDescription"
                                        class="flex items-center gap-2 text-sm text-gray-400 hover:text-white transition-colors mb-2">
                                    <svg class="w-4 h-4 transition-transform" :class="showDescription && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    <span x-text="showDescription ? 'Hide Description' : 'Show Description'"></span>
                                </button>
                                <div x-show="showDescription" x-collapse>
                                    <div class="max-h-48 overflow-y-auto text-sm text-gray-400 whitespace-pre-line pr-2">{{ $selectedVideoData['description'] }}</div>
                                </div>
                            </div>
                        @endif

                        {{-- Actions --}}
                        <div class="flex items-center gap-3 mt-4 pt-3 border-t border-dark-700">
                            @if(in_array($selectedVideoId, $savedVideoIds))
                                <button wire:click="unsaveVideo('{{ $selectedVideoId }}')"
                                        class="inline-flex items-center gap-2 bg-red-500/10 hover:bg-red-500/20 text-red-400 text-sm font-medium rounded-lg px-4 py-2 transition-colors border border-red-500/20">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z"/></svg>
                                    Unsave
                                </button>
                            @else
                                <button wire:click="saveVideo('{{ $selectedVideoId }}')"
                                        class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 text-gray-300 hover:text-white text-sm font-medium rounded-lg px-4 py-2 transition-colors border border-dark-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                                    Save
                                </button>
                            @endif

                            <a href="https://www.youtube.com/watch?v={{ $selectedVideoId }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 text-gray-300 hover:text-white text-sm font-medium rounded-lg px-4 py-2 transition-colors border border-dark-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                Open on YouTube
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
