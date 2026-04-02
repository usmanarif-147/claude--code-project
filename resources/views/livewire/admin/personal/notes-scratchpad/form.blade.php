<div>
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-400">Personal</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('admin.personal.notes-scratchpad.index') }}" wire:navigate class="hover:text-gray-300 transition-colors">Notes / Scratchpad</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">{{ $noteId ? 'Edit' : 'Create' }}</span>
    </div>

    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">{{ $noteId ? 'Edit Note' : 'Create Note' }}</h1>
            <p class="text-gray-500 mt-1">{{ $noteId ? 'Update your note.' : 'Jot down a new note.' }}</p>
        </div>
        <a href="{{ route('admin.personal.notes-scratchpad.index') }}" wire:navigate
           class="text-gray-400 hover:text-white font-medium rounded-lg px-4 py-2.5 transition-colors text-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back
        </a>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mb-6 flex items-center gap-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl px-4 py-3">
            <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="text-sm text-emerald-400">{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 flex items-center gap-3 bg-red-500/10 border border-red-500/20 rounded-xl px-4 py-3">
            <svg class="w-5 h-5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="text-sm text-red-400">{{ session('error') }}</span>
        </div>
    @endif

    <form wire:submit="save">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column: Note Details --}}
            <div class="lg:col-span-2">
                <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 space-y-5">
                    <h2 class="text-lg font-mono font-bold text-white uppercase tracking-wider">Note Details</h2>

                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-300 mb-1.5">Title <span class="text-red-400">*</span></label>
                        <input type="text" id="title" wire:model="title"
                               class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="Note title">
                        @error('title') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="content" class="block text-sm font-medium text-gray-300 mb-1.5">Content</label>
                        <textarea id="content" wire:model="content" rows="12"
                                  class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent"
                                  placeholder="Write anything..."></textarea>
                        @error('content') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Right Column: Actions --}}
            <div class="lg:col-span-1">
                <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 space-y-4">
                    <h2 class="text-lg font-mono font-bold text-white uppercase tracking-wider">Actions</h2>

                    <button type="submit"
                            class="w-full bg-primary hover:bg-primary-hover text-white font-medium rounded-lg px-5 py-2.5 transition-colors flex items-center justify-center gap-2">
                        <span wire:loading.remove wire:target="save">{{ $noteId ? 'Update Note' : 'Save Note' }}</span>
                        <span wire:loading wire:target="save" class="flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            Saving...
                        </span>
                    </button>

                    <a href="{{ route('admin.personal.notes-scratchpad.index') }}" wire:navigate
                       class="w-full inline-flex items-center justify-center bg-dark-700 hover:bg-dark-600 text-gray-300 font-medium rounded-lg px-5 py-2.5 transition-colors">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
