<div>
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('admin.blog-and-post.index') }}" wire:navigate class="hover:text-gray-300 transition-colors">Blog &amp; Post</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">{{ $post?->exists ? 'Edit' : 'New' }} Image Post</span>
    </div>

    {{-- Page header --}}
    <div class="flex items-center justify-between mb-6 gap-4 flex-wrap">
        <div>
            <div class="flex items-center gap-3 flex-wrap">
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-fuchsia-500/10 text-fuchsia-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </span>
                <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">
                    {{ $post?->exists ? 'Edit Image Post' : 'New Image Post' }}
                </h1>
            </div>
            <p class="text-gray-500 mt-1">A caption plus a single photo. LinkedIn renders the image inline.</p>
        </div>
        <a href="{{ route('admin.blog-and-post.index') }}" wire:navigate
           class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 text-gray-300 text-sm font-medium rounded-lg px-4 py-2.5 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back
        </a>
    </div>

    {{-- Flash messages --}}
    @if (session('success') || session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" class="mb-6">
            @if (session('success'))
                <div class="flex items-center gap-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-lg px-4 py-3 text-sm">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p>{{ session('success') }}</p>
                </div>
            @endif
            @if (session('error'))
                <div class="flex items-center gap-3 bg-red-500/10 border border-red-500/20 text-red-400 rounded-lg px-4 py-3 text-sm">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p>{{ session('error') }}</p>
                </div>
            @endif
        </div>
    @endif

    {{-- LinkedIn status banners --}}
    @if ($post?->exists && $post?->linkedin_post_url)
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3 bg-emerald-500/10 border border-emerald-500/20 rounded-lg px-4 py-3">
            <div class="flex items-center gap-2 text-sm text-emerald-400">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="font-medium">Posted to LinkedIn</span>
            </div>
            <a href="{{ $post->linkedin_post_url }}" target="_blank" rel="noopener"
               class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-300 hover:text-white bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 rounded-md px-3 py-1.5 transition-colors">
                View on LinkedIn
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        </div>
    @endif

    @if ($post?->exists && $post?->status === 'failed' && $post?->linkedin_error)
        <div class="mb-6 flex items-start gap-3 bg-red-500/10 border border-red-500/20 rounded-lg px-4 py-3">
            <svg class="w-5 h-5 shrink-0 text-red-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div class="text-sm">
                <p class="text-red-400 font-medium">Publishing failed</p>
                <p class="text-red-300/80 mt-0.5 break-words">{{ $post->linkedin_error }}</p>
                <p class="text-xs text-red-300/60 mt-1">Click Publish Now to retry.</p>
            </div>
        </div>
    @endif

    {{-- Form card --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 space-y-6">
        {{-- Title --}}
        <div>
            <label for="title" class="block text-sm font-medium text-gray-300 mb-2">
                Title <span class="text-red-400">*</span>
            </label>
            <input type="text" id="title" wire:model="title" maxlength="255"
                   placeholder="e.g. Laravel 12 features at a glance"
                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
            <p class="mt-1 text-xs text-gray-500">Admin label only — not published to LinkedIn.</p>
            @error('title') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
        </div>

        {{-- Caption --}}
        <div>
            <label for="caption" class="block text-sm font-medium text-gray-300 mb-2">
                Caption <span class="text-red-400">*</span>
            </label>
            <textarea id="caption" wire:model="caption" rows="8"
                      placeholder="Write the post body that LinkedIn will display..."
                      class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent resize-y transition-all duration-200"></textarea>
            <p class="mt-1 text-xs text-gray-500">Supports emoji, line breaks, bullets, inline URLs.</p>
            @error('caption') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
        </div>

        {{-- Image section --}}
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">
                Image <span class="text-red-400">*</span>
            </label>

            @if ($existing_image_path && ! $removeImage && ! $image)
                {{-- Existing image preview --}}
                <div class="flex items-start gap-4 p-4 bg-dark-700/50 border border-dark-600 rounded-lg">
                    <img src="{{ Storage::url($existing_image_path) }}" alt="Current image"
                         class="w-32 h-32 object-cover rounded-lg border border-dark-600 shrink-0">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-300 font-medium">Current image</p>
                        <p class="text-xs text-gray-500 mt-0.5 break-all">{{ basename($existing_image_path) }}</p>
                        <button type="button" wire:click="markImageForRemoval"
                                class="mt-3 inline-flex items-center gap-1.5 bg-red-500/10 hover:bg-red-500/20 text-red-400 hover:text-red-300 text-xs font-medium rounded-lg px-3 py-1.5 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Remove
                        </button>
                    </div>
                </div>
            @elseif ($removeImage)
                <div class="flex items-center justify-between gap-3 p-4 bg-amber-500/5 border border-amber-500/20 rounded-lg">
                    <p class="text-sm text-amber-400 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Image will be removed on save.
                    </p>
                    <button type="button" wire:click="$set('removeImage', false)"
                            class="text-xs font-medium text-primary-light hover:text-white underline underline-offset-2 transition-colors">
                        Undo
                    </button>
                </div>
            @endif

            {{-- File input area --}}
            <label for="image" class="mt-3 flex flex-col items-center justify-center gap-2 p-6 bg-dark-700/30 border-2 border-dashed border-dark-600 hover:border-primary/50 hover:bg-dark-700/50 rounded-lg cursor-pointer transition-colors">
                <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                <span class="text-sm font-medium text-gray-300">Click to choose an image</span>
                <span class="text-xs text-gray-500">JPG or PNG, max 5 MB, recommended 1200&times;627 or 1200&times;1200</span>
                <input type="file" id="image" wire:model="image" accept="image/jpeg,image/png" class="sr-only">
            </label>

            {{-- Upload state --}}
            <div wire:loading wire:target="image" class="mt-2 inline-flex items-center gap-2 text-xs text-primary-light">
                <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                Uploading...
            </div>
            @if ($image)
                <div wire:loading.remove wire:target="image" class="mt-2 inline-flex items-center gap-2 text-xs text-emerald-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ $image->getClientOriginalName() }} selected
                </div>
                <div wire:loading.remove wire:target="image">
                    <img src="{{ $image->temporaryUrl() }}" alt="New image preview"
                         class="mt-3 w-full max-w-md rounded-lg border border-dark-600">
                </div>
            @endif

            @error('image') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
        </div>

        {{-- Hashtags --}}
        <div>
            <label for="hashtags" class="block text-sm font-medium text-gray-300 mb-2">Hashtags</label>
            <input type="text" id="hashtags" wire:model="hashtags"
                   placeholder="laravel php webdev"
                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
            <p class="mt-1 text-xs text-gray-500">Space-separated. # added automatically.</p>
            @error('hashtags') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
        </div>

        {{-- Schedule --}}
        <div class="pt-2 border-t border-dark-700">
            <div class="mb-4">
                <h3 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Schedule</h3>
                <p class="text-xs text-gray-500 mt-1">Optional — leave empty for Save Draft. Required to Schedule.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="scheduled_date" class="block text-sm font-medium text-gray-300 mb-2">Date</label>
                    <input type="date" id="scheduled_date" wire:model="scheduled_date"
                           class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                    @error('scheduled_date') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="scheduled_time" class="block text-sm font-medium text-gray-300 mb-2">Time</label>
                    <input type="time" id="scheduled_time" wire:model="scheduled_time" step="900"
                           class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                    @error('scheduled_time') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-500">Slots: :00 :15 :30 :45 (15-min cron resolution)</p>
        </div>
    </div>

    {{-- Action bar (sticky) --}}
    <div class="sticky bottom-0 mt-6 -mx-4 px-4 py-4 bg-dark-900/95 backdrop-blur border-t border-dark-700 z-10">
        <div class="flex items-center justify-end flex-wrap gap-3">
            <a href="{{ route('admin.blog-and-post.index') }}" wire:navigate
               class="text-gray-400 hover:text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-colors">
                Cancel
            </a>
            <button wire:click="saveDraft" wire:loading.attr="disabled" wire:target="saveDraft"
                    class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 border border-dark-600 text-gray-300 hover:text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="saveDraft" class="inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Save Draft
                </span>
                <span wire:loading wire:target="saveDraft" class="inline-flex items-center gap-2">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Saving...
                </span>
            </button>
            @if ($post?->exists)
                <button type="button"
                        wire:click="publishNow"
                        wire:confirm="Publish this post to LinkedIn right now?"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                        wire:target="publishNow"
                        class="inline-flex items-center gap-2 bg-gradient-to-r from-primary via-fuchsia-500 to-orange-500 hover:opacity-90 text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-opacity shadow-lg shadow-fuchsia-500/20 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="publishNow" class="inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        Publish Now
                    </span>
                    <span wire:loading wire:target="publishNow" class="inline-flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Publishing&hellip;
                    </span>
                </button>
            @endif
            <button wire:click="schedule" wire:loading.attr="disabled" wire:target="schedule"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-primary via-fuchsia-500 to-orange-500 hover:from-primary-hover hover:via-fuchsia-400 hover:to-orange-400 text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200 shadow-lg shadow-fuchsia-500/20 disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="schedule" class="inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Schedule
                </span>
                <span wire:loading wire:target="schedule" class="inline-flex items-center gap-2">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Scheduling...
                </span>
            </button>
        </div>
    </div>
</div>
