<div>
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-400">YouTube</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">Saved Videos</span>
    </div>

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Saved Videos</h1>
            <span class="bg-primary text-white text-xs font-medium px-2.5 py-1 rounded-full">{{ $savedVideos->total() }}</span>
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

    {{-- Search Bar --}}
    <div class="mb-6">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search saved videos..."
               class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
    </div>

    {{-- Video List --}}
    @if($savedVideos->count() > 0)
        <div class="space-y-4 mb-6">
            @foreach($savedVideos as $video)
                <div class="bg-dark-800 border border-dark-700 rounded-xl p-4 hover:border-dark-600 transition-colors">
                    <div class="flex gap-4">
                        {{-- Thumbnail --}}
                        <div class="relative shrink-0 cursor-pointer w-48"
                             wire:click="playVideo('{{ $video->video_id }}', '{{ addslashes($video->title) }}', '{{ addslashes($video->channel_title) }}', '{{ addslashes($video->description ?? '') }}', '{{ $video->published_at?->format('M j, Y') }}', {{ $video->view_count ?? 0 }})">
                            @if($video->thumbnail_url)
                                <img src="{{ $video->thumbnail_url }}" alt="{{ $video->title }}" class="w-full aspect-video object-cover rounded-lg">
                            @else
                                <div class="w-full aspect-video bg-dark-700 rounded-lg flex items-center justify-center">
                                    <svg class="w-10 h-10 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                            @endif

                            {{-- Duration Badge --}}
                            @if($video->duration)
                                <span class="absolute bottom-2 right-2 bg-black/80 text-white text-xs font-medium rounded px-1.5 py-0.5">
                                    {{ \App\Services\YouTubeSubscriptionService::formatDuration($video->duration) }}
                                </span>
                            @endif
                        </div>

                        {{-- Video Info --}}
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-gray-400 mb-1">{{ $video->channel_title }}</p>
                            <h3 class="text-sm font-medium text-white mb-2 line-clamp-2 leading-snug">{{ $video->title }}</h3>

                            <div class="flex items-center gap-3 text-xs text-gray-500 mb-3">
                                <span>Saved on {{ $video->saved_at?->format('M j, Y') }}</span>
                                <span>&middot;</span>
                                <span>{{ number_format($video->view_count ?? 0) }} views</span>
                            </div>

                            {{-- Notes Section --}}
                            @if($editingNoteId === $video->id)
                                <div class="mb-3">
                                    <textarea wire:model="noteText" rows="3" placeholder="Add a note..."
                                              class="w-full bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent text-sm"></textarea>
                                    <div class="flex items-center gap-2 mt-2">
                                        <button wire:click="saveNote"
                                                class="inline-flex items-center gap-1.5 bg-primary hover:bg-primary-hover text-white text-xs font-medium rounded-lg px-3 py-1.5 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            Save Note
                                        </button>
                                        <button wire:click="cancelNote"
                                                class="inline-flex items-center gap-1.5 bg-dark-700 hover:bg-dark-600 text-gray-300 hover:text-white text-xs font-medium rounded-lg px-3 py-1.5 transition-colors border border-dark-600">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            @elseif($video->notes)
                                <p class="text-sm text-gray-400 italic mb-3">{{ $video->notes }}</p>
                            @endif

                            {{-- Action Buttons --}}
                            <div class="flex items-center gap-2">
                                <button wire:click="playVideo('{{ $video->video_id }}', '{{ addslashes($video->title) }}', '{{ addslashes($video->channel_title) }}', '{{ addslashes($video->description ?? '') }}', '{{ $video->published_at?->format('M j, Y') }}', {{ $video->view_count ?? 0 }})"
                                        class="inline-flex items-center gap-1.5 bg-dark-700 hover:bg-dark-600 text-gray-300 hover:text-white text-xs font-medium rounded-lg px-3 py-1.5 transition-colors border border-dark-600">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Play
                                </button>
                                <button wire:click="editNote({{ $video->id }}, '{{ addslashes($video->notes ?? '') }}')"
                                        class="inline-flex items-center gap-1.5 bg-dark-700 hover:bg-dark-600 text-gray-300 hover:text-white text-xs font-medium rounded-lg px-3 py-1.5 transition-colors border border-dark-600">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    {{ $video->notes ? 'Edit Note' : 'Add Note' }}
                                </button>
                                <button wire:click="unsaveVideo({{ $video->id }})"
                                        wire:confirm="Remove this video from your saved list?"
                                        class="inline-flex items-center gap-1.5 bg-red-500/10 hover:bg-red-500/20 text-red-400 text-xs font-medium rounded-lg px-3 py-1.5 transition-colors border border-red-500/20">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $savedVideos->links() }}
        </div>
    @else
        {{-- Empty State --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-12 text-center">
            <svg class="w-16 h-16 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            <h3 class="text-lg font-mono font-bold text-white uppercase tracking-wider mb-2">No Saved Videos Yet</h3>
            <p class="text-gray-400 text-sm mb-4">
                @if($search)
                    No saved videos match your search. Try a different term.
                @else
                    Save videos from your feed to watch later or keep for reference.
                @endif
            </p>
            @if($search)
                <button wire:click="$set('search', '')"
                        class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 text-gray-300 hover:text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-colors border border-dark-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Clear Search
                </button>
            @else
                <a href="{{ route('admin.youtube.video-feed') }}" wire:navigate
                   class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    Browse Video Feed
                </a>
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
