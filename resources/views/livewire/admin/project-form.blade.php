<div>
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-white">{{ $project ? 'Edit Project' : 'Create Project' }}</h1>
        <p class="text-gray-500 mt-1">{{ $project ? 'Update project details.' : 'Add a new project to your portfolio.' }}</p>
    </div>

    <form wire:submit="save" class="max-w-3xl space-y-6">
        {{-- Section 1 — Basic Info --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 space-y-5">
            <h2 class="text-lg font-semibold text-white">Basic Info</h2>

            <div>
                <label for="title" class="block text-sm font-medium text-gray-400 mb-1.5">Title <span class="text-red-400">*</span></label>
                <input type="text" id="title" wire:model="title"
                       class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-accent-500 focus:border-transparent"
                       placeholder="e.g. Portfolio Website">
                @error('title') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="short_description" class="block text-sm font-medium text-gray-400 mb-1.5">Short Description <span class="text-red-400">*</span></label>
                <textarea id="short_description" wire:model="short_description" rows="3" maxlength="500"
                          class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-accent-500 focus:border-transparent resize-none"
                          placeholder="A brief summary of the project..."></textarea>
                @error('short_description') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-400 mb-1.5">Description</label>
                <textarea id="description" wire:model="description" rows="6"
                          class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-accent-500 focus:border-transparent resize-none"
                          placeholder="Detailed description of the project..."></textarea>
                @error('description') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Section 2 — Tech Stack --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 space-y-5">
            <h2 class="text-lg font-semibold text-white">Tech Stack</h2>

            <div class="flex gap-2">
                <input type="text" wire:model="techInput" wire:keydown.enter.prevent="addTech"
                       class="flex-1 bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-accent-500 focus:border-transparent text-sm"
                       placeholder="e.g. Laravel, Vue.js, Tailwind CSS">
                <button type="button" wire:click="addTech"
                        class="bg-accent-500 hover:bg-accent-600 text-white font-medium rounded-lg px-4 py-2.5 transition-colors text-sm">
                    Add
                </button>
            </div>
            @error('techInput') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror

            @if (count($tech_stack) > 0)
                <div class="flex flex-wrap gap-2">
                    @foreach($tech_stack as $index => $tech)
                        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm bg-accent-500/10 text-accent-400">
                            {{ $tech }}
                            <button type="button" wire:click="removeTech({{ $index }})" class="text-accent-400/60 hover:text-red-400">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </span>
                    @endforeach
                </div>
            @endif
            @error('tech_stack') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
        </div>

        {{-- Section 3 — Links --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 space-y-5">
            <h2 class="text-lg font-semibold text-white">Links</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="demo_url" class="block text-sm font-medium text-gray-400 mb-1.5">Demo URL</label>
                    <input type="url" id="demo_url" wire:model="demo_url"
                           class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-accent-500 focus:border-transparent"
                           placeholder="https://demo.example.com">
                    @error('demo_url') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="github_url" class="block text-sm font-medium text-gray-400 mb-1.5">GitHub URL</label>
                    <input type="url" id="github_url" wire:model="github_url"
                           class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-accent-500 focus:border-transparent"
                           placeholder="https://github.com/...">
                    @error('github_url') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Section 4 — Images --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 space-y-5">
            <h2 class="text-lg font-semibold text-white">Images</h2>

            {{-- Cover Image --}}
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1.5">Cover Image</label>

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

            {{-- Gallery Images --}}
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1.5">Gallery Images</label>

                {{-- Existing gallery images --}}
                @if (count($existingImages) > 0)
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-3">
                        @foreach ($existingImages as $image)
                            <div class="relative group">
                                <img src="{{ Storage::url($image['image_path']) }}" alt="Gallery image" class="w-full h-24 rounded-lg object-cover">
                                <button type="button" wire:click="removeExistingImage({{ $image['id'] }})"
                                        class="absolute -top-2 -right-2 bg-dark-900 border border-dark-600 rounded-full p-1 text-gray-400 hover:text-red-400 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- New upload previews --}}
                @if ($galleryImages)
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-3">
                        @foreach ($galleryImages as $index => $image)
                            <div class="relative group">
                                <img src="{{ $image->temporaryUrl() }}" alt="Gallery preview" class="w-full h-24 rounded-lg object-cover">
                                <button type="button" wire:click="removeGalleryImage({{ $index }})"
                                        class="absolute -top-2 -right-2 bg-dark-900 border border-dark-600 rounded-full p-1 text-gray-400 hover:text-red-400 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif

                <input type="file" wire:model="galleryImages" multiple accept="image/*"
                       class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:bg-accent-500/10 file:text-accent-400 hover:file:bg-accent-500/20">
                @error('galleryImages.*') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Section 5 — Settings --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 space-y-5">
            <h2 class="text-lg font-semibold text-white">Settings</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="flex items-center pt-2">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="is_featured" class="sr-only peer">
                        <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-accent-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-accent-500"></div>
                        <span class="ml-3 text-sm font-medium text-gray-400">Featured</span>
                    </label>
                </div>

                <div class="flex items-center pt-2">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="is_active" class="sr-only peer">
                        <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-accent-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-accent-500"></div>
                        <span class="ml-3 text-sm font-medium text-gray-400">Active</span>
                    </label>
                </div>

                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-400 mb-1.5">Sort Order</label>
                    <input type="number" id="sort_order" wire:model="sort_order" min="0"
                           class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-accent-500 focus:border-transparent">
                    @error('sort_order') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="completed_at" class="block text-sm font-medium text-gray-400 mb-1.5">Completed At</label>
                    <input type="date" id="completed_at" wire:model="completed_at"
                           class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-accent-500 focus:border-transparent">
                    @error('completed_at') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-3">
            <button type="submit"
                    class="bg-accent-500 hover:bg-accent-600 text-white font-medium rounded-lg px-6 py-2.5 transition-colors flex items-center gap-2">
                <span wire:loading.remove wire:target="save">{{ $project ? 'Update Project' : 'Save Project' }}</span>
                <span wire:loading wire:target="save" class="flex items-center gap-2">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    Saving...
                </span>
            </button>
            <a href="{{ route('admin.projects.index') }}" wire:navigate
               class="text-gray-400 hover:text-white font-medium rounded-lg px-6 py-2.5 transition-colors">
                Cancel
            </a>
        </div>
    </form>
</div>
