<div>
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-400">YouTube</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('admin.youtube.video-ideas.index') }}" wire:navigate class="hover:text-gray-300 transition-colors">Video Ideas</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">{{ $videoIdeaId ? 'Edit' : 'Create' }}</span>
    </div>

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">{{ $videoIdeaId ? 'Edit Video Idea' : 'Create Video Idea' }}</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $videoIdeaId ? 'Update this video idea.' : 'Add a new video idea to your tracker.' }}</p>
        </div>
        <a href="{{ route('admin.youtube.video-ideas.index') }}" wire:navigate
           class="inline-flex items-center gap-2 text-gray-400 hover:text-white text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back
        </a>
    </div>

    <form wire:submit="save">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column (2/3) --}}
            <div class="lg:col-span-2">
                <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
                    <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-5">Idea Details</h2>

                    <div class="space-y-5">
                        {{-- Title --}}
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-300 mb-2">Title <span class="text-red-400">*</span></label>
                            <input type="text" id="title" wire:model="title"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                   placeholder="e.g. How to Build a REST API with Laravel">
                            @error('title') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>

                        {{-- Description --}}
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                            <textarea id="description" wire:model="description" rows="4"
                                      class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                      placeholder="Brief description of the video idea..."></textarea>
                            @error('description') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column (1/3) --}}
            <div class="space-y-6">
                {{-- Settings Card --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
                    <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-5">Settings</h2>

                    <div class="space-y-5">
                        {{-- Priority --}}
                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-300 mb-2">Priority</label>
                            <select id="priority" wire:model="priority"
                                    class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="high">High</option>
                                <option value="medium">Medium</option>
                                <option value="low">Low</option>
                            </select>
                            @error('priority') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>

                        {{-- Status --}}
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-300 mb-2">Status</label>
                            <select id="status" wire:model="status"
                                    class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="idea">Idea</option>
                                <option value="scripting">Scripting</option>
                                <option value="recording">Recording</option>
                                <option value="editing">Editing</option>
                                <option value="published">Published</option>
                            </select>
                            @error('status') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Actions Card --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
                    <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-5">Actions</h2>

                    <div class="space-y-3">
                        <button type="submit"
                                class="w-full bg-primary hover:bg-primary-hover text-white font-medium rounded-lg px-5 py-2.5 transition-colors text-sm flex items-center justify-center gap-2">
                            <span wire:loading.remove wire:target="save">{{ $videoIdeaId ? 'Update Idea' : 'Create Idea' }}</span>
                            <span wire:loading wire:target="save" class="flex items-center gap-2">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                Saving...
                            </span>
                        </button>
                        <a href="{{ route('admin.youtube.video-ideas.index') }}" wire:navigate
                           class="w-full inline-flex items-center justify-center text-gray-400 hover:text-white font-medium rounded-lg px-5 py-2.5 transition-colors text-sm border border-dark-600 hover:border-dark-500">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
