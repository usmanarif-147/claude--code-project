<div>
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-white">{{ $experience ? 'Edit Experience' : 'Create Experience' }}</h1>
        <p class="text-gray-500 mt-1">{{ $experience ? 'Update experience details.' : 'Add a new work experience.' }}</p>
    </div>

    <form wire:submit="save" class="max-w-3xl space-y-6">
        {{-- Main Details --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 space-y-5">
            <h2 class="text-lg font-semibold text-white">Experience Details</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-400 mb-1.5">Role <span class="text-red-400">*</span></label>
                    <input type="text" id="role" wire:model="role"
                           class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-accent-500 focus:border-transparent"
                           placeholder="e.g. Senior Developer">
                    @error('role') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="company" class="block text-sm font-medium text-gray-400 mb-1.5">Company <span class="text-red-400">*</span></label>
                    <input type="text" id="company" wire:model="company"
                           class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-accent-500 focus:border-transparent"
                           placeholder="e.g. Acme Inc.">
                    @error('company') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-400 mb-1.5">Start Date <span class="text-red-400">*</span></label>
                    <input type="date" id="start_date" wire:model="start_date"
                           class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-accent-500 focus:border-transparent">
                    @error('start_date') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-400 mb-1.5">End Date</label>
                    <input type="date" id="end_date" wire:model="end_date"
                           class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-accent-500 focus:border-transparent disabled:opacity-50 disabled:cursor-not-allowed"
                           {{ $is_current ? 'disabled' : '' }}>
                    @error('end_date') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center gap-6">
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" wire:model.live="is_current" class="sr-only peer">
                    <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-accent-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-accent-500"></div>
                    <span class="ml-3 text-sm font-medium text-gray-400">Currently working here</span>
                </label>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-400 mb-1.5">Sort Order</label>
                    <input type="number" id="sort_order" wire:model="sort_order" min="0"
                           class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-accent-500 focus:border-transparent">
                    @error('sort_order') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center pt-6">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="is_active" class="sr-only peer">
                        <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-accent-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-accent-500"></div>
                        <span class="ml-3 text-sm font-medium text-gray-400">Active</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- Responsibilities --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-semibold text-white">Responsibilities</h2>
                <button type="button" wire:click="addResponsibility"
                        class="text-accent-400 hover:text-accent-300 text-sm font-medium flex items-center gap-1 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Responsibility
                </button>
            </div>

            @if (count($responsibilities) > 0)
                <div class="space-y-4">
                    @foreach ($responsibilities as $index => $responsibility)
                        <div class="flex gap-3 items-start" wire:key="resp-{{ $index }}">
                            <div class="w-20 shrink-0">
                                <label class="block text-xs font-medium text-gray-500 mb-1">Order</label>
                                <input type="number" wire:model="responsibilities.{{ $index }}.sort_order" min="0"
                                       class="w-full bg-dark-700 border border-dark-600 rounded-lg px-3 py-2.5 text-white text-sm focus:ring-2 focus:ring-accent-500 focus:border-transparent">
                            </div>
                            <div class="flex-1">
                                <label class="block text-xs font-medium text-gray-500 mb-1">Description</label>
                                <textarea wire:model="responsibilities.{{ $index }}.description" rows="2"
                                          class="w-full bg-dark-700 border border-dark-600 rounded-lg px-3 py-2.5 text-white placeholder-gray-500 text-sm focus:ring-2 focus:ring-accent-500 focus:border-transparent"
                                          placeholder="Describe the responsibility..."></textarea>
                                @error("responsibilities.{$index}.description") <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                            </div>
                            <button type="button" wire:click="removeResponsibility({{ $index }})"
                                    class="mt-6 text-gray-400 hover:text-red-400 transition-colors p-1 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-sm text-center py-4">No responsibilities added yet. Click "Add Responsibility" to begin.</p>
            @endif
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-3">
            <button type="submit"
                    class="bg-accent-500 hover:bg-accent-600 text-white font-medium rounded-lg px-6 py-2.5 transition-colors flex items-center gap-2">
                <span wire:loading.remove wire:target="save">{{ $experience ? 'Update Experience' : 'Create Experience' }}</span>
                <span wire:loading wire:target="save" class="flex items-center gap-2">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    Saving...
                </span>
            </button>
            <a href="{{ route('admin.experiences.index') }}" wire:navigate
               class="text-gray-400 hover:text-white font-medium rounded-lg px-6 py-2.5 transition-colors">
                Cancel
            </a>
        </div>
    </form>
</div>
