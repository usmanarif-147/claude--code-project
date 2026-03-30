<div>
    {{-- Trix Editor Assets --}}
    <link rel="stylesheet" href="https://unpkg.com/trix@2.1.8/dist/trix.css">
    <style>
        trix-editor {
            background-color: #1a1a2e;
            border-color: #2a2a3e;
            color: #e5e7eb;
            min-height: 300px;
        }
        trix-editor:focus {
            outline: none;
            ring: 2px;
            ring-color: #6366f1;
        }
        trix-toolbar .trix-button {
            background-color: #12121a;
            border-color: #2a2a3e;
            color: #9ca3af;
        }
        trix-toolbar .trix-button:hover {
            background-color: #1a1a2e;
            color: #e5e7eb;
        }
        trix-toolbar .trix-button.trix-active {
            background-color: #6366f1;
            color: white;
        }
        trix-toolbar .trix-button-group {
            border-color: #2a2a3e;
        }
        /* Hide file upload button from Trix toolbar */
        trix-toolbar .trix-button-group--file-tools {
            display: none;
        }
    </style>
    <script src="https://unpkg.com/trix@2.1.8/dist/trix.umd.min.js"></script>

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-white">{{ $blogPost ? 'Edit Post' : 'Create Post' }}</h1>
        <p class="text-gray-500 mt-1">{{ $blogPost ? 'Update your blog post.' : 'Write a new blog article.' }}</p>
    </div>

    <div class="max-w-3xl space-y-6">
        {{-- Section 1 — Basic Info --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 space-y-5">
            <h2 class="text-lg font-semibold text-white">Basic Info</h2>

            <div>
                <label for="title" class="block text-sm font-medium text-gray-400 mb-1.5">Title <span class="text-red-400">*</span></label>
                <input type="text" id="title" wire:model="title"
                       class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-accent-500 focus:border-transparent"
                       placeholder="e.g. Getting Started with Laravel">
                @error('title') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="excerpt" class="block text-sm font-medium text-gray-400 mb-1.5">Excerpt</label>
                <textarea id="excerpt" wire:model="excerpt" rows="2" maxlength="500"
                          class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-accent-500 focus:border-transparent resize-none"
                          placeholder="A brief summary of the post..."></textarea>
                @error('excerpt') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Section 2 — Content --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 space-y-5">
            <h2 class="text-lg font-semibold text-white">Content</h2>

            <div>
                <div wire:ignore>
                    <input id="content" type="hidden" value="{{ $content }}">
                    <trix-editor input="content"
                        class="trix-content bg-dark-700 border border-dark-600 rounded-lg text-white min-h-[300px] prose prose-invert max-w-none"
                        x-data
                        x-on:trix-change="$wire.set('content', $event.target.value)">
                    </trix-editor>
                </div>
                @error('content') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Section 3 — Cover Image --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 space-y-5">
            <h2 class="text-lg font-semibold text-white">Cover Image</h2>

            <div>
                @if ($existingCoverImage)
                    <div class="mb-3 relative inline-block">
                        <img src="{{ Storage::url($existingCoverImage) }}" alt="Cover image" class="w-40 h-28 rounded-lg object-cover">
                        <button type="button" wire:click="removeCoverImage"
                                class="absolute -top-2 -right-2 bg-dark-900 border border-dark-600 rounded-full p-1 text-gray-400 hover:text-red-400 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                @endif

                @if ($coverImage)
                    <div class="mb-3 relative inline-block">
                        <img src="{{ $coverImage->temporaryUrl() }}" alt="Cover preview" class="w-40 h-28 rounded-lg object-cover">
                        <button type="button" wire:click="$set('coverImage', null)"
                                class="absolute -top-2 -right-2 bg-dark-900 border border-dark-600 rounded-full p-1 text-gray-400 hover:text-red-400 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                @endif

                @if (!$coverImage && !$existingCoverImage)
                    <input type="file" wire:model="coverImage" accept="image/*"
                           class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:bg-accent-500/10 file:text-accent-400 hover:file:bg-accent-500/20">
                @endif

                @error('coverImage') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Section 4 — Tags --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 space-y-5">
            <h2 class="text-lg font-semibold text-white">Tags</h2>

            <div class="flex gap-2">
                <input type="text" wire:model="tagInput" wire:keydown.enter.prevent="addTag"
                       class="flex-1 bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-accent-500 focus:border-transparent text-sm"
                       placeholder="e.g. Laravel, PHP, Tutorial">
                <button type="button" wire:click="addTag"
                        class="bg-accent-500 hover:bg-accent-600 text-white font-medium rounded-lg px-4 py-2.5 transition-colors text-sm">
                    Add
                </button>
            </div>
            @error('tagInput') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror

            @if (count($tags) > 0)
                <div class="flex flex-wrap gap-2">
                    @foreach($tags as $index => $tag)
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm bg-accent-500/10 text-accent-400">
                            {{ $tag }}
                            <button type="button" wire:click="removeTag({{ $index }})" class="text-accent-400/60 hover:text-red-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </span>
                    @endforeach
                </div>
            @endif
            @error('tags') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
        </div>

        {{-- Section 5 — SEO --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 space-y-5">
            <h2 class="text-lg font-semibold text-white">SEO</h2>

            <div>
                <label for="meta_title" class="block text-sm font-medium text-gray-400 mb-1.5">Meta Title</label>
                <input type="text" id="meta_title" wire:model="meta_title" maxlength="255"
                       class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-accent-500 focus:border-transparent"
                       placeholder="SEO title (defaults to post title if empty)">
                @error('meta_title') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="meta_description" class="block text-sm font-medium text-gray-400 mb-1.5">Meta Description</label>
                <textarea id="meta_description" wire:model="meta_description" rows="2" maxlength="500"
                          class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-accent-500 focus:border-transparent resize-none"
                          placeholder="Brief description for search engines..."></textarea>
                @error('meta_description') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-3">
            @if($blogPost && $blogPost->status === 'published')
                <button wire:click="saveDraft"
                        class="bg-transparent border border-dark-600 text-gray-300 hover:bg-dark-700 font-medium rounded-lg px-6 py-2.5 transition-colors flex items-center gap-2">
                    <span wire:loading.remove wire:target="saveDraft">Unpublish & Save Draft</span>
                    <span wire:loading wire:target="saveDraft" class="flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        Saving...
                    </span>
                </button>
            @else
                <button wire:click="saveDraft"
                        class="bg-transparent border border-dark-600 text-gray-300 hover:bg-dark-700 font-medium rounded-lg px-6 py-2.5 transition-colors flex items-center gap-2">
                    <span wire:loading.remove wire:target="saveDraft">Save Draft</span>
                    <span wire:loading wire:target="saveDraft" class="flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        Saving...
                    </span>
                </button>
                <button wire:click="publish"
                        class="bg-accent-500 hover:bg-accent-600 text-white font-medium rounded-lg px-6 py-2.5 transition-colors flex items-center gap-2">
                    <span wire:loading.remove wire:target="publish">Publish</span>
                    <span wire:loading wire:target="publish" class="flex items-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        Publishing...
                    </span>
                </button>
            @endif
            <a href="{{ route('admin.blog.index') }}" wire:navigate
               class="text-gray-400 hover:text-white font-medium rounded-lg px-6 py-2.5 transition-colors">
                Cancel
            </a>
        </div>
    </div>
</div>
