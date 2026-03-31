<div>
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('admin.skills.index') }}" wire:navigate class="hover:text-gray-300 transition-colors">Skills</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">{{ $skill ? 'Edit' : 'Create' }} Skill</span>
    </div>

    <div class="mb-8">
        <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">{{ $skill ? 'Edit Skill' : 'Create Skill' }}</h1>
        <p class="text-gray-500 mt-1">{{ $skill ? 'Update skill details.' : 'Add a new skill to your portfolio.' }}</p>
    </div>

    <form wire:submit="save" class="max-w-2xl">
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 space-y-5">
            <div>
                <label for="title" class="block text-sm font-medium text-gray-300 mb-1.5">Title <span class="text-red-400">*</span></label>
                <input type="text" id="title" wire:model="title"
                       class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent"
                       placeholder="e.g. Laravel">
                @error('title') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            {{-- Category --}}
            <div>
                <label for="category" class="block text-sm font-medium text-gray-300 mb-2">Category</label>
                <input type="text" id="category" wire:model="category"
                       placeholder="e.g. Frontend, Backend, DevOps, AI"
                       class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
                @error('category') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            {{-- Proficiency --}}
            <div x-data="{ proficiency: @entangle('proficiency') }">
                <label for="proficiency" class="block text-sm font-medium text-gray-300 mb-2">
                    Proficiency <span class="text-primary-light" x-text="proficiency + '%'"></span>
                </label>
                <input type="range" id="proficiency" x-model="proficiency" wire:model="proficiency"
                       min="0" max="100" step="5"
                       class="w-full h-2 bg-dark-700 rounded-lg appearance-none cursor-pointer accent-primary">
                @error('proficiency') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="icon" class="block text-sm font-medium text-gray-300 mb-1.5">Icon (SVG path data)</label>
                <textarea id="icon" wire:model="icon" rows="3"
                          class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent font-mono text-sm"
                          placeholder='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M..."/>'></textarea>
                @error('icon') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror

                @if ($icon)
                    <div class="mt-3 p-4 bg-dark-700 rounded-lg">
                        <p class="text-xs text-gray-500 mb-2">Preview:</p>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-8 h-8 text-primary-light">
                            {!! $icon !!}
                        </svg>
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-300 mb-1.5">Sort Order</label>
                    <input type="number" id="sort_order" wire:model="sort_order" min="0"
                           class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent">
                    @error('sort_order') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center pt-6">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="is_active" class="sr-only peer">
                        <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        <span class="ml-3 text-sm font-medium text-gray-400">Active</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="mt-6 flex items-center gap-3">
            <button type="submit"
                    class="bg-primary hover:bg-primary-hover text-white font-medium rounded-lg px-6 py-2.5 transition-colors flex items-center gap-2">
                <span wire:loading.remove wire:target="save">{{ $skill ? 'Update Skill' : 'Create Skill' }}</span>
                <span wire:loading wire:target="save" class="flex items-center gap-2">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    Saving...
                </span>
            </button>
            <a href="{{ route('admin.skills.index') }}" wire:navigate
               class="text-gray-400 hover:text-white font-medium rounded-lg px-6 py-2.5 transition-colors">
                Cancel
            </a>
        </div>
    </form>
</div>
