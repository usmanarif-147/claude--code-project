<div x-data="{ pickerOpen: false }" @keydown.escape.window="pickerOpen = false">
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">Blog &amp; Post</span>
    </div>

    {{-- Page header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Blog &amp; Post</h1>
            <p class="text-sm text-gray-500 mt-1">Compose, schedule, and auto-publish posts to LinkedIn.</p>
        </div>
        <button type="button" @click="pickerOpen = true"
           class="inline-flex items-center gap-2 bg-gradient-to-r from-primary via-fuchsia-500 to-orange-500 hover:opacity-90 text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Post
        </button>
    </div>

    {{-- Type-picker modal --}}
    <div x-show="pickerOpen"
         x-transition.opacity
         style="display: none;"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
         @click="pickerOpen = false">
        <div @click.stop
             x-show="pickerOpen"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="bg-dark-800 border border-dark-700 rounded-xl p-6 max-w-2xl w-full mx-4 shadow-2xl">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider">Pick a Post Type</h2>
                <button type="button" @click="pickerOpen = false"
                        class="text-gray-500 hover:text-white transition-colors p-1 rounded-lg hover:bg-dark-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- TEXT --}}
                <a href="{{ route('admin.blog-and-post.text.create') }}" wire:navigate
                   class="group flex flex-col items-center justify-center gap-2 bg-dark-700 hover:bg-dark-600 border border-dark-600 hover:border-primary/50 rounded-xl p-6 transition-all min-h-[150px] text-center">
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-primary/10 text-primary-light group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10"/></svg>
                    </span>
                    <span class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Text Only</span>
                    <span class="text-xs text-gray-500">Caption + hashtags</span>
                </a>

                {{-- IMAGE --}}
                <a href="{{ route('admin.blog-and-post.image.create') }}" wire:navigate
                   class="group flex flex-col items-center justify-center gap-2 bg-dark-700 hover:bg-dark-600 border border-dark-600 hover:border-primary/50 rounded-xl p-6 transition-all min-h-[150px] text-center">
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-fuchsia-500/10 text-fuchsia-400 group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </span>
                    <span class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Text + Image</span>
                    <span class="text-xs text-gray-500">Caption + photo</span>
                </a>

                {{-- VIDEO --}}
                <a href="{{ route('admin.blog-and-post.video.create') }}" wire:navigate
                   class="group flex flex-col items-center justify-center gap-2 bg-dark-700 hover:bg-dark-600 border border-dark-600 hover:border-primary/50 rounded-xl p-6 transition-all min-h-[150px] text-center">
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-cyan-500/10 text-cyan-400 group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    </span>
                    <span class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Text + Video</span>
                    <span class="text-xs text-gray-500">Caption + clip</span>
                </a>

                {{-- ARTICLE --}}
                <a href="{{ route('admin.blog-and-post.article.create') }}" wire:navigate
                   class="group flex flex-col items-center justify-center gap-2 bg-dark-700 hover:bg-dark-600 border border-dark-600 hover:border-primary/50 rounded-xl p-6 transition-all min-h-[150px] text-center">
                    <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-amber-500/10 text-amber-400 group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    </span>
                    <span class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Article Share</span>
                    <span class="text-xs text-gray-500">URL + caption</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success') || session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
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
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-center gap-3 bg-red-500/10 border border-red-500/20 text-red-400 rounded-lg px-4 py-3 text-sm">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p>{{ session('error') }}</p>
                </div>
            @endif
        </div>
    @endif

    {{-- Filters --}}
    @php
        $linkedinConnected = \App\Models\Social\PlatformAccount::where('platform', 'linkedin')
            ->where('token_expires_at', '>', now())
            ->exists();
    @endphp
    <div class="bg-dark-800 border border-dark-700 rounded-xl p-4 mb-6">
        <div class="flex flex-col lg:flex-row gap-4 lg:items-center">
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search title or caption..."
                       class="w-full bg-dark-700 border border-dark-600 rounded-lg pl-9 pr-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
            </div>
            <select wire:model.live="typeFilter"
                    class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm min-w-[150px]">
                <option value="all">All Types</option>
                <option value="text">Text</option>
                <option value="image">Image</option>
                <option value="video">Video</option>
                <option value="article">Article</option>
            </select>
            <select wire:model.live="statusFilter"
                    class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm min-w-[160px]">
                <option value="all">All Status</option>
                <option value="draft">Draft</option>
                <option value="scheduled">Scheduled</option>
                <option value="publishing">Publishing</option>
                <option value="posted">Posted</option>
                <option value="failed">Failed</option>
            </select>
            <div class="flex items-center gap-2 px-3 py-2 rounded-lg bg-dark-700/70 border border-dark-600 text-xs whitespace-nowrap">
                <span class="text-gray-400">LinkedIn:</span>
                @if($linkedinConnected)
                    <span class="inline-flex items-center gap-1.5 text-emerald-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Connected
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 text-red-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>
                        Disconnected
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Table OR Empty state --}}
    @if($posts->isEmpty())
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-16">
            <div class="flex flex-col items-center gap-3 text-center">
                <div class="w-14 h-14 rounded-xl bg-dark-700 flex items-center justify-center">
                    <svg class="w-7 h-7 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-300">No posts yet</p>
                    <p class="text-xs text-gray-500 mt-1">Click + New Post to create your first one.</p>
                </div>
            </div>
        </div>
    @else
        <div class="bg-dark-800 border border-dark-700 rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-dark-700/50">
                            <th class="text-left text-xs font-mono font-medium text-gray-400 uppercase tracking-wider px-6 py-3 w-32">Type</th>
                            <th class="text-left text-xs font-mono font-medium text-gray-400 uppercase tracking-wider px-6 py-3">Title / Snippet</th>
                            <th class="text-left text-xs font-mono font-medium text-gray-400 uppercase tracking-wider px-6 py-3 w-24">Media</th>
                            <th class="text-left text-xs font-mono font-medium text-gray-400 uppercase tracking-wider px-6 py-3 w-36">Scheduled</th>
                            <th class="text-left text-xs font-mono font-medium text-gray-400 uppercase tracking-wider px-6 py-3 w-36">Status</th>
                            <th class="text-right text-xs font-mono font-medium text-gray-400 uppercase tracking-wider px-6 py-3 w-32">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-dark-700/50">
                        @foreach($posts as $post)
                            @php
                                $meta = is_array($post->meta) ? $post->meta : [];
                                $typeIconBg = match($post->type) {
                                    'text' => 'bg-primary/10 text-primary-light',
                                    'image' => 'bg-fuchsia-500/10 text-fuchsia-400',
                                    'video' => 'bg-blue-500/10 text-blue-400',
                                    'article' => 'bg-amber-500/10 text-amber-400',
                                    default => 'bg-gray-500/10 text-gray-400',
                                };
                            @endphp
                            <tr class="hover:bg-dark-700/30 transition-colors">
                                {{-- TYPE --}}
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $typeIconBg }}">
                                        @switch($post->type)
                                            @case('text')
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h10"/></svg>
                                                Text
                                                @break
                                            @case('image')
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                Image
                                                @break
                                            @case('video')
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                                Video
                                                @break
                                            @case('article')
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                                Article
                                                @break
                                            @default
                                                {{ $post->typeLabel() }}
                                        @endswitch
                                    </span>
                                </td>

                                {{-- TITLE / SNIPPET --}}
                                <td class="px-6 py-4">
                                    <div class="text-sm text-white font-medium">{{ $post->title }}</div>
                                    @if($post->caption)
                                        <div class="text-xs text-gray-500 mt-0.5 max-w-[420px] truncate">{{ \Illuminate\Support\Str::limit($post->caption, 80) }}</div>
                                    @endif
                                </td>

                                {{-- MEDIA --}}
                                <td class="px-6 py-4">
                                    @if($post->type === 'image' && !empty($meta['image_path']))
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($meta['image_path']) }}"
                                             alt="thumb"
                                             class="w-12 h-12 rounded-lg object-cover border border-dark-600">
                                    @elseif($post->type === 'video')
                                        <div class="w-12 h-12 rounded-lg bg-dark-700 border border-dark-600 flex items-center justify-center text-blue-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                        </div>
                                    @elseif($post->type === 'article' && !empty($meta['url']))
                                        <a href="{{ $meta['url'] }}" target="_blank" rel="noopener"
                                           class="inline-flex items-center gap-1 text-xs text-primary-light hover:text-white transition-colors"
                                           title="{{ $meta['url'] }}">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            link
                                        </a>
                                    @else
                                        <span class="text-gray-600">&mdash;</span>
                                    @endif
                                </td>

                                {{-- SCHEDULED --}}
                                <td class="px-6 py-4 text-sm text-gray-400">
                                    @if($post->scheduled_at)
                                        <div class="text-white text-xs">{{ $post->scheduled_at->format('M j') }}</div>
                                        <div class="text-xs text-gray-500">{{ $post->scheduled_at->format('H:i') }}</div>
                                    @else
                                        <span class="text-gray-600">&mdash;</span>
                                    @endif
                                </td>

                                {{-- STATUS --}}
                                <td class="px-6 py-4">
                                    @switch($post->status)
                                        @case('posted')
                                            <div class="flex flex-col gap-1">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 w-fit">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                                    Posted
                                                </span>
                                                @if($post->linkedin_post_url)
                                                    <a href="{{ $post->linkedin_post_url }}" target="_blank" rel="noopener"
                                                       class="text-xs text-primary-light hover:text-white transition-colors inline-flex items-center gap-1">
                                                        view
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                    </a>
                                                @endif
                                            </div>
                                            @break
                                        @case('scheduled')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-blue-500/10 text-blue-400">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                                                Scheduled
                                            </span>
                                            @break
                                        @case('publishing')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-fuchsia-500/10 text-fuchsia-400">
                                                <span class="w-1.5 h-1.5 rounded-full bg-fuchsia-400 animate-pulse"></span>
                                                Publishing
                                            </span>
                                            @break
                                        @case('failed')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-500/10 text-red-400">
                                                <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>
                                                Failed
                                            </span>
                                            @break
                                        @default
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                                Draft
                                            </span>
                                    @endswitch
                                </td>

                                {{-- ACTIONS --}}
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($post->status === 'failed')
                                            <button type="button"
                                                    wire:click="retry({{ $post->id }})"
                                                    wire:confirm="Retry publishing this post to LinkedIn?"
                                                    wire:loading.attr="disabled"
                                                    wire:target="retry({{ $post->id }})"
                                                    title="Retry publishing"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-fuchsia-500/10 hover:bg-fuchsia-500/20 text-fuchsia-400 hover:text-fuchsia-300 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                                <svg class="w-4 h-4" wire:loading.remove wire:target="retry({{ $post->id }})" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                <svg class="w-4 h-4 animate-spin" wire:loading wire:target="retry({{ $post->id }})" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                            </button>
                                        @endif
                                        <a href="{{ route('admin.blog-and-post.' . $post->type . '.edit', $post) }}" wire:navigate
                                           title="Edit"
                                           class="text-gray-400 hover:text-primary-light hover:bg-primary/10 p-1.5 rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        <x-admin.confirm-button
                                            title="Delete this post?"
                                            text="This post will be permanently removed."
                                            action="$wire.delete({{ $post->id }})"
                                            confirm-text="Yes, delete it"
                                            class="text-gray-400 hover:text-red-400 transition-colors p-1.5 rounded-lg hover:bg-red-500/10"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </x-admin.confirm-button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($posts->hasPages())
                <div class="px-6 py-4 border-t border-dark-700">
                    {{ $posts->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
