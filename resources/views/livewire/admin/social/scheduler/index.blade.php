<div>
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-500">Social</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">Scheduler</span>
    </div>

    {{-- Page header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Scheduled Posts</h1>
            <p class="text-gray-500 mt-1">Compose, schedule, and track your social media posts.</p>
        </div>
        <a href="{{ route('admin.social.scheduler.create') }}" wire:navigate
           class="bg-primary hover:bg-primary-hover text-white font-medium rounded-lg px-4 py-2.5 transition-colors text-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Post
        </a>
    </div>

    {{-- Flash messages --}}
    @if(session('success') || session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" class="mb-6">
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
    <div class="bg-dark-800 border border-dark-700 rounded-xl p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search posts by title or caption..."
                       class="w-full bg-dark-700 border border-dark-600 rounded-lg pl-9 pr-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
            </div>
            <select wire:model.live="statusFilter"
                    class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm min-w-[160px]">
                <option value="all">All Status</option>
                <option value="draft">Draft</option>
                <option value="scheduled">Scheduled</option>
                <option value="posted">Posted</option>
                <option value="failed">Failed</option>
            </select>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-dark-700/50">
                        <th class="text-left text-xs font-mono font-medium text-gray-400 uppercase tracking-wider px-6 py-3 w-16">Thumb</th>
                        <th class="text-left text-xs font-mono font-medium text-gray-400 uppercase tracking-wider px-6 py-3">Title / Snippet</th>
                        <th class="text-left text-xs font-mono font-medium text-gray-400 uppercase tracking-wider px-6 py-3">Pages</th>
                        <th class="text-left text-xs font-mono font-medium text-gray-400 uppercase tracking-wider px-6 py-3">Scheduled</th>
                        <th class="text-left text-xs font-mono font-medium text-gray-400 uppercase tracking-wider px-6 py-3">Status</th>
                        <th class="text-right text-xs font-mono font-medium text-gray-400 uppercase tracking-wider px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700/50">
                    @forelse ($posts as $post)
                        @php
                            $pageCount = is_array($post->content_pages) ? count($post->content_pages) : 0;
                            $totalPages = $pageCount + 2; // cover + content + final
                        @endphp
                        <tr class="hover:bg-dark-700/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="w-10 h-10 rounded-lg bg-dark-700 border border-dark-600 flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                                    </svg>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-white font-medium">{{ $post->title }}</div>
                                @if ($post->caption)
                                    <div class="text-xs text-gray-400 truncate max-w-[280px]">{{ Str::limit($post->caption, 60) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary-light">
                                    {{ $totalPages }} {{ Str::plural('page', $totalPages) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-400">
                                @if ($post->scheduled_at)
                                    <div class="text-white">{{ $post->scheduled_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $post->scheduled_at->format('H:i') }}</div>
                                @else
                                    <span class="text-gray-500">&mdash;</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @switch($post->status)
                                    @case('posted')
                                        <div class="flex flex-col gap-1">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 w-fit">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                                Posted
                                            </span>
                                            @if ($post->linkedin_post_url)
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
                                    @case('failed')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-red-500/10 text-red-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>
                                            Failed
                                        </span>
                                        @break
                                    @case('publishing')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-fuchsia-500/10 text-fuchsia-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-fuchsia-400 animate-pulse"></span>
                                            Publishing
                                        </span>
                                        @break
                                    @default
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                            Draft
                                        </span>
                                @endswitch
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if ($post->status === 'posted' && $post->linkedin_post_url)
                                        <a href="{{ $post->linkedin_post_url }}" target="_blank" rel="noopener"
                                           class="inline-flex items-center justify-center text-gray-400 hover:text-[#7cb4ee] hover:bg-[#0a66c2]/10 rounded-lg p-1.5 transition-colors"
                                           title="View on LinkedIn">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.852 3.37-1.852 3.601 0 4.267 2.37 4.267 5.455v6.288zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.063 2.063 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                                        </a>
                                    @endif

                                    @if ($post->status === 'failed')
                                        <button wire:click="retry({{ $post->id }})"
                                                wire:confirm="Retry publishing this post to LinkedIn?"
                                                wire:loading.attr="disabled"
                                                wire:target="retry({{ $post->id }})"
                                                class="inline-flex items-center gap-1.5 bg-fuchsia-500/10 hover:bg-fuchsia-500/20 text-fuchsia-400 hover:text-fuchsia-300 text-xs font-medium rounded-lg px-2.5 py-1.5 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                                title="Retry publishing">
                                            <span wire:loading.remove wire:target="retry({{ $post->id }})" class="inline-flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                Retry
                                            </span>
                                            <span wire:loading wire:target="retry({{ $post->id }})" class="inline-flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                                Retrying
                                            </span>
                                        </button>
                                    @endif

                                    <a href="{{ route('admin.social.scheduler.edit', $post) }}" wire:navigate
                                       class="text-gray-400 hover:text-primary-light transition-colors p-1.5 rounded-lg hover:bg-dark-700"
                                       title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <x-admin.confirm-button
                                        title="Delete Scheduled Post?"
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
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-14 h-14 rounded-xl bg-dark-700 flex items-center justify-center">
                                        <svg class="w-7 h-7 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-300">No scheduled posts yet</p>
                                        <p class="text-xs text-gray-500 mt-1">Get started by composing your first social post.</p>
                                    </div>
                                    <a href="{{ route('admin.social.scheduler.create') }}" wire:navigate
                                       class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-4 py-2 transition-colors mt-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        Create your first post
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($posts->hasPages())
            <div class="px-6 py-4 border-t border-dark-700">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</div>
