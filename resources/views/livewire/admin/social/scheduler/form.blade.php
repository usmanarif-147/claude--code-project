<div>
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-500">Social</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('admin.social.scheduler.index') }}" wire:navigate class="hover:text-gray-300 transition-colors">Scheduler</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">{{ $scheduledPost?->exists ? 'Edit' : 'Create' }} Post</span>
    </div>

    {{-- Page header --}}
    <div class="flex items-center justify-between mb-6 gap-4 flex-wrap">
        <div>
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">
                    {{ $scheduledPost?->exists ? 'Edit Post' : 'Create Post' }}
                </h1>
                @if ($scheduledPost?->exists && $scheduledPost->status === 'posted')
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Posted
                    </span>
                @endif
            </div>
            <p class="text-gray-500 mt-1">
                {{ $scheduledPost?->exists ? 'Update your carousel post details below.' : 'Compose a multi-page carousel post for LinkedIn.' }}
            </p>
            @if ($scheduledPost?->exists && $scheduledPost->linkedin_post_url)
                <a href="{{ $scheduledPost->linkedin_post_url }}" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-1.5 mt-2 text-xs text-primary-light hover:text-white transition-colors">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.852 3.37-1.852 3.601 0 4.267 2.37 4.267 5.455v6.288zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.063 2.063 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                    View on LinkedIn
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            @endif
        </div>
        <a href="{{ route('admin.social.scheduler.index') }}" wire:navigate
           class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 text-gray-300 text-sm font-medium rounded-lg px-4 py-2.5 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back
        </a>
    </div>

    {{-- Template Picker Strip --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Template</h2>
                <p class="text-xs text-gray-500 mt-0.5">Pick a visual style. The preview on the right updates instantly.</p>
            </div>
            <a href="{{ route('admin.social.templates.index') }}" wire:navigate
               class="text-xs text-primary-light hover:text-white transition-colors inline-flex items-center gap-1">
                Browse all
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="flex flex-wrap gap-3">
            @foreach (config('social-templates', []) as $slug => $meta)
                <button type="button" wire:click="$set('template_slug', '{{ $slug }}')"
                        wire:key="tpl-pick-{{ $slug }}"
                        class="relative inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border text-sm font-medium transition-all duration-200
                               {{ $template_slug === $slug
                                   ? 'border-primary bg-primary/10 text-primary-light ring-2 ring-primary/40'
                                   : 'border-dark-600 bg-dark-700 text-gray-300 hover:border-dark-500 hover:text-white' }}">
                    @if ($template_slug === $slug)
                        <svg class="w-4 h-4 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    @else
                        <span class="w-4 h-4 rounded-full border border-dark-500"></span>
                    @endif
                    {{ $meta['label'] ?? ucfirst($slug) }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- 2-column layout: left = compose, right = preview --}}
    <div class="grid grid-cols-1 xl:grid-cols-5 gap-6">
        {{-- ============================================================
             LEFT COLUMN — COMPOSE
             ============================================================ --}}
        <div class="xl:col-span-3 space-y-6">
            {{-- Section 1 — Title --}}
            <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 space-y-5">
                <div>
                    <h2 class="text-lg font-mono font-semibold text-white uppercase tracking-wider">Basic Info</h2>
                    <p class="text-xs text-gray-500 mt-1">Internal label for organizing your posts — not shown publicly.</p>
                </div>

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-300 mb-1.5">Title <span class="text-red-400">*</span></label>
                    <input type="text" id="title" wire:model.live.debounce.400ms="title"
                           class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                           placeholder="e.g. Async PHP traps">
                    @error('title') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Section 2 — Caption & Hashtags --}}
            <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 space-y-5">
                <div>
                    <h2 class="text-lg font-mono font-semibold text-white uppercase tracking-wider">Post Caption</h2>
                    <p class="text-xs text-gray-500 mt-1">Text that appears with your carousel on LinkedIn.</p>
                </div>

                <div>
                    <label for="caption" class="block text-sm font-medium text-gray-300 mb-1.5">Caption <span class="text-red-400">*</span></label>
                    <textarea id="caption" wire:model.live.debounce.400ms="caption" rows="5"
                              class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent resize-none text-sm"
                              placeholder="Write your post caption here..."></textarea>
                    @error('caption') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="hashtags" class="block text-sm font-medium text-gray-300 mb-1.5">Hashtags</label>
                    <input type="text" id="hashtags" wire:model.live.debounce.400ms="hashtags"
                           class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                           placeholder="e.g. #php #laravel #webdev">
                    <p class="mt-1 text-xs text-gray-500">Separate hashtags with spaces.</p>
                    @error('hashtags') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Section 3 — PDF Carousel Pages --}}
            <div>
                <div class="mb-4">
                    <h2 class="text-lg font-mono font-semibold text-white uppercase tracking-wider">PDF Carousel Pages</h2>
                    <p class="text-xs text-gray-500 mt-1">Compose the slides for your carousel. The first and final pages are required and locked.</p>
                </div>

                {{-- First Page (locked) --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 space-y-4 mb-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-primary/10 text-primary-light text-xs font-mono font-semibold">1</span>
                            <h3 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">First Page (Cover)</h3>
                        </div>
                        <button type="button" wire:click="focusPage(0)"
                                class="text-xs text-primary-light hover:text-white transition-colors inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Preview
                        </button>
                    </div>
                    <div>
                        <textarea id="cover_page" wire:model.live.debounce.400ms="cover_page" rows="4"
                                  class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent resize-none text-sm"
                                  placeholder="Write your cover slide content..."></textarea>
                        @error('cover_page') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Content Pages --}}
                @foreach ($content_pages as $index => $page)
                    <div wire:key="content-page-{{ $index }}" class="bg-dark-800 border border-dark-700 rounded-xl p-6 space-y-4 mb-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-primary/10 text-primary-light text-xs font-mono font-semibold">{{ $index + 2 }}</span>
                                <h3 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Content Page {{ $index + 1 }}</h3>
                            </div>
                            <div class="flex items-center gap-1">
                                <button type="button" wire:click="focusPage({{ $index + 1 }})"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-primary-light hover:bg-dark-700 transition-colors"
                                        title="Preview this page">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                                <button type="button" wire:click="moveContentPageUp({{ $index }})"
                                        @if($index === 0) disabled @endif
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-primary-light hover:bg-dark-700 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                                        title="Move up">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                </button>
                                <button type="button" wire:click="moveContentPageDown({{ $index }})"
                                        @if($index === count($content_pages) - 1) disabled @endif
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-primary-light hover:bg-dark-700 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                                        title="Move down">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <button type="button" wire:click="removeContentPage({{ $index }})"
                                        @if(count($content_pages) <= 1) disabled @endif
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-red-400 hover:bg-red-500/10 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                                        title="Remove">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                        <div>
                            <textarea wire:model.live.debounce.400ms="content_pages.{{ $index }}" rows="4"
                                      class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent resize-none text-sm"
                                      placeholder="Write content for this slide..."></textarea>
                            @error('content_pages.'.$index) <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>
                @endforeach

                {{-- Add Content Page button --}}
                <button type="button" wire:click="addContentPage"
                        class="w-full bg-dark-800 border border-dashed border-dark-600 hover:border-primary/50 hover:bg-dark-700/50 text-gray-400 hover:text-primary-light rounded-xl px-4 py-3 text-sm font-medium transition-colors flex items-center justify-center gap-2 mb-4">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Content Page
                </button>
                @error('content_pages') <p class="mb-4 text-sm text-red-400">{{ $message }}</p> @enderror

                {{-- Final Page (locked) --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-md bg-primary/10 text-primary-light text-xs font-mono font-semibold">{{ count($content_pages) + 2 }}</span>
                            <h3 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Final Page (CTA)</h3>
                        </div>
                        <button type="button" wire:click="focusPage({{ $this->totalPages() - 1 }})"
                                class="text-xs text-primary-light hover:text-white transition-colors inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Preview
                        </button>
                    </div>
                    <div>
                        <textarea id="final_page" wire:model.live.debounce.400ms="final_page" rows="4"
                                  class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent resize-none text-sm"
                                  placeholder="Write your closing call-to-action..."></textarea>
                        @error('final_page') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Section 4 — Schedule --}}
            <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 space-y-5">
                <div>
                    <h2 class="text-lg font-mono font-semibold text-white uppercase tracking-wider">Schedule</h2>
                    <p class="text-xs text-gray-500 mt-1">Pick when this post should publish. Time rounds to the nearest 15-minute slot.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="scheduled_date" class="block text-sm font-medium text-gray-300 mb-1.5">Date</label>
                        <input type="date" id="scheduled_date" wire:model="scheduled_date"
                               class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                        @error('scheduled_date') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="scheduled_time" class="block text-sm font-medium text-gray-300 mb-1.5">Time</label>
                        <input type="time" id="scheduled_time" wire:model="scheduled_time" step="900"
                               class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                        @error('scheduled_time') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>
                <p class="text-xs text-gray-500">Available slots: :00, :15, :30, :45</p>
            </div>

            {{-- Actions --}}
            <div class="flex items-center flex-wrap gap-3">
                <a href="{{ route('admin.social.scheduler.index') }}" wire:navigate
                   class="text-gray-400 hover:text-white font-medium rounded-lg px-6 py-2.5 transition-colors">
                    Cancel
                </a>
                <button wire:click="saveDraft" wire:loading.attr="disabled" wire:target="saveDraft"
                        class="bg-transparent border border-dark-600 text-gray-300 hover:bg-dark-700 font-medium rounded-lg px-6 py-2.5 transition-colors flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="saveDraft">Save Draft</span>
                    <span wire:loading wire:target="saveDraft" class="flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        Saving...
                    </span>
                </button>
                @if ($scheduledPost?->exists)
                    <button wire:click="publishNow"
                            wire:confirm="Publish this post to LinkedIn right now?"
                            wire:loading.attr="disabled" wire:target="publishNow"
                            class="inline-flex items-center gap-2 bg-gradient-to-r from-primary via-fuchsia-500 to-orange-500 hover:from-primary-hover hover:via-fuchsia-400 hover:to-orange-400 text-white font-medium rounded-lg px-6 py-2.5 transition-all duration-200 shadow-lg shadow-fuchsia-500/20 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="publishNow" class="inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                            Publish Now
                        </span>
                        <span wire:loading wire:target="publishNow" class="inline-flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            Publishing...
                        </span>
                    </button>
                @endif
                <button wire:click="schedule" wire:loading.attr="disabled" wire:target="schedule"
                        class="bg-primary hover:bg-primary-hover text-white font-medium rounded-lg px-6 py-2.5 transition-colors flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg shadow-primary/20">
                    <span wire:loading.remove wire:target="schedule">Schedule</span>
                    <span wire:loading wire:target="schedule" class="flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        Scheduling...
                    </span>
                </button>
            </div>
        </div>

        {{-- ============================================================
             RIGHT COLUMN — LIVE PREVIEW (sticky on desktop)
             ============================================================ --}}
        <div class="xl:col-span-2">
            <div class="xl:sticky xl:top-6 space-y-4">
                {{-- Preview card --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl overflow-hidden">
                    {{-- Header with prev/next arrows --}}
                    <div class="flex items-center justify-between px-5 py-3 border-b border-dark-700">
                        <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Live Preview</h2>
                        @php
                            $totalPages = $this->totalPages();
                            $currentNum = $focusedPageIndex + 1;
                            $atStart = $focusedPageIndex <= 0;
                            $atEnd = $focusedPageIndex >= $totalPages - 1;
                        @endphp
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="focusPage({{ $focusedPageIndex - 1 }})"
                                    @if($atStart) disabled @endif
                                    class="p-1.5 rounded-lg text-gray-400 hover:text-primary-light hover:bg-dark-700 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                                    title="Previous page">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <span class="text-xs font-mono text-gray-400 min-w-[60px] text-center">
                                {{ $currentNum }} / {{ $totalPages }}
                            </span>
                            <button type="button" wire:click="focusPage({{ $focusedPageIndex + 1 }})"
                                    @if($atEnd) disabled @endif
                                    class="p-1.5 rounded-lg text-gray-400 hover:text-primary-light hover:bg-dark-700 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                                    title="Next page">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Iframe preview (1200x1200 scaled down) --}}
                    <div class="relative bg-dark-900 border-b border-dark-700">
                        <div class="w-full aspect-square overflow-hidden relative"
                             wire:loading.class="opacity-60"
                             wire:target="cover_page,content_pages,final_page,caption,hashtags,title,template_slug,focusPage">
                            <iframe
                                srcdoc="{{ $this->previewHtml(app(\App\Services\SocialPdfRenderService::class)) }}"
                                class="block border-0 absolute top-0 left-0"
                                style="width: 1200px; height: 1200px; transform: scale(0.4); transform-origin: top left;"
                                sandbox="allow-same-origin"
                                scrolling="no"
                                tabindex="-1"
                                aria-hidden="true"
                                title="Carousel page preview"></iframe>
                            {{-- subtle loading shimmer --}}
                            <div wire:loading
                                 wire:target="cover_page,content_pages,final_page,template_slug,focusPage"
                                 class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                <span class="text-xs font-mono text-primary-light bg-dark-900/80 px-3 py-1 rounded-full">Updating...</span>
                            </div>
                        </div>
                    </div>

                    {{-- Thumbnail strip --}}
                    <div class="p-4">
                        <p class="text-[10px] font-mono uppercase tracking-widest text-gray-500 mb-2">Pages</p>
                        <div class="flex flex-wrap gap-2">
                            @for ($i = 0; $i < $totalPages; $i++)
                                @php
                                    if ($i === 0) {
                                        $label = 'Cover';
                                    } elseif ($i === $totalPages - 1) {
                                        $label = 'Final';
                                    } else {
                                        $label = 'Page '.$i;
                                    }
                                    $active = $i === $focusedPageIndex;
                                @endphp
                                <button type="button" wire:click="focusPage({{ $i }})"
                                        wire:key="thumb-{{ $i }}"
                                        class="px-3 py-2 rounded-lg text-xs font-medium border transition-all duration-150
                                               {{ $active
                                                   ? 'border-primary bg-primary/15 text-primary-light ring-1 ring-primary/40'
                                                   : 'border-dark-600 bg-dark-700 text-gray-400 hover:text-white hover:border-dark-500' }}">
                                    <span class="block text-[10px] font-mono uppercase tracking-wider opacity-70">{{ $i + 1 }}</span>
                                    {{ $label }}
                                </button>
                            @endfor
                        </div>
                    </div>
                </div>

                {{-- Caption preview (mock LinkedIn post card) --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl overflow-hidden">
                    <div class="px-5 py-3 border-b border-dark-700 flex items-center justify-between">
                        <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Caption Preview</h2>
                        <span class="inline-flex items-center gap-1 text-[10px] font-medium text-blue-400 bg-blue-500/10 px-2 py-0.5 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                            LinkedIn
                        </span>
                    </div>
                    <div class="p-5">
                        <div class="flex items-start gap-3 mb-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-fuchsia-500 flex items-center justify-center shrink-0">
                                <span class="text-white font-bold text-sm">UA</span>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-white">Usman Arif</p>
                                <p class="text-xs text-gray-500">Now • Public</p>
                            </div>
                        </div>
                        @if (trim($caption) === '' && trim($hashtags) === '')
                            <p class="text-sm text-gray-500 italic">Your caption will appear here as you type...</p>
                        @else
                            <p class="text-sm text-gray-200 whitespace-pre-wrap leading-relaxed">{{ $caption }}</p>
                            @if (trim($hashtags) !== '')
                                <p class="mt-3 text-sm text-blue-400 break-words">{{ $hashtags }}</p>
                            @endif
                        @endif
                    </div>
                </div>

                {{-- Download PDF button --}}
                @if ($scheduledPost?->exists)
                    <button wire:click="downloadPdf" wire:loading.attr="disabled" wire:target="downloadPdf"
                            class="w-full inline-flex items-center justify-center gap-2 bg-gradient-to-r from-primary to-fuchsia-600 hover:from-primary-hover hover:to-fuchsia-500 text-white text-sm font-medium rounded-lg px-5 py-3 transition-all duration-200 shadow-lg shadow-primary/20 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="downloadPdf" class="inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Download PDF
                        </span>
                        <span wire:loading wire:target="downloadPdf" class="inline-flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            Rendering PDF...
                        </span>
                    </button>
                @else
                    <div class="relative group">
                        <button type="button" disabled
                                class="w-full inline-flex items-center justify-center gap-2 bg-dark-700 text-gray-500 text-sm font-medium rounded-lg px-5 py-3 cursor-not-allowed border border-dark-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Download PDF
                        </button>
                        <div class="absolute -top-9 left-1/2 -translate-x-1/2 px-2 py-1 bg-dark-950 text-gray-300 text-[10px] rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap">
                            Save the post first
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
