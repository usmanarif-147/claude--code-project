<div>
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('admin.educations.index') }}" wire:navigate class="hover:text-gray-300 transition-colors">Education</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">{{ $education ? 'Edit' : 'Create' }} Education</span>
    </div>

    <div class="mb-8">
        <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">{{ $education ? 'Edit Education' : 'Create Education' }}</h1>
        <p class="text-gray-500 mt-1">{{ $education ? 'Update education details.' : 'Add a new education entry.' }}</p>
    </div>

    <form wire:submit="save" class="max-w-2xl">
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 space-y-5">
            <div>
                <label for="degree_title" class="block text-sm font-medium text-gray-300 mb-1.5">Degree Title <span class="text-red-400">*</span></label>
                <input type="text" id="degree_title" wire:model="degree_title"
                       class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent"
                       placeholder="e.g. Bachelor of Science in Computer Science">
                @error('degree_title') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="institution" class="block text-sm font-medium text-gray-300 mb-1.5">Institution <span class="text-red-400">*</span></label>
                <input type="text" id="institution" wire:model="institution"
                       class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent"
                       placeholder="e.g. MIT">
                @error('institution') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-300 mb-1.5">Start Date</label>
                    <input type="date" id="start_date" wire:model="start_date"
                           class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    @error('start_date') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-300 mb-1.5">End Date</label>
                    <input type="date" id="end_date" wire:model="end_date"
                           class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    @error('end_date') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="sort_order" class="block text-sm font-medium text-gray-300 mb-1.5">Sort Order</label>
                <input type="number" id="sort_order" wire:model="sort_order" min="0"
                       class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent">
                @error('sort_order') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-6 flex items-center gap-3">
            <button type="submit"
                    class="bg-primary hover:bg-primary-hover text-white font-medium rounded-lg px-6 py-2.5 transition-colors flex items-center gap-2">
                <span wire:loading.remove wire:target="save">{{ $education ? 'Update Education' : 'Create Education' }}</span>
                <span wire:loading wire:target="save" class="flex items-center gap-2">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    Saving...
                </span>
            </button>
            <a href="{{ route('admin.educations.index') }}" wire:navigate
               class="text-gray-400 hover:text-white font-medium rounded-lg px-6 py-2.5 transition-colors">
                Cancel
            </a>
        </div>
    </form>
</div>
