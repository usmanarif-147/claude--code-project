<div>
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="hover:text-gray-300">Personal</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('admin.personal.goals-tracker.index') }}" wire:navigate class="hover:text-gray-300 transition-colors">Goals Tracker</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">{{ $goalId ? 'Edit' : 'Create' }} Goal</span>
    </div>

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">
                {{ $goalId ? 'Edit Goal' : 'Create Goal' }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ $goalId ? 'Update the details below.' : 'Fill in the details to create a new goal.' }}
            </p>
        </div>
        <a href="{{ route('admin.personal.goals-tracker.index') }}" wire:navigate
           class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 text-gray-300 text-sm font-medium rounded-lg px-4 py-2.5 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back
        </a>
    </div>

    <form wire:submit="save">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            {{-- Left Column (2/3) --}}
            <div class="xl:col-span-2 space-y-6">
                {{-- Goal Details Card --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl">
                    <div class="px-6 py-4 border-b border-dark-700">
                        <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Goal Details</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Define what you want to achieve.</p>
                    </div>
                    <div class="p-6 space-y-5">
                        {{-- Title --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Title <span class="text-red-400">*</span>
                            </label>
                            <input type="text" wire:model="title"
                                   placeholder="e.g. Learn Rust programming"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                            @error('title')
                                <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Description --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                            <textarea wire:model="description" rows="4"
                                      placeholder="Describe your goal in detail..."
                                      class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent resize-none transition-all duration-200"></textarea>
                            @error('description')
                                <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            {{-- Category --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    Category <span class="text-red-400">*</span>
                                </label>
                                <select wire:model="category"
                                        class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                                    <option value="career">Career</option>
                                    <option value="financial">Financial</option>
                                    <option value="learning">Learning</option>
                                    <option value="health">Health</option>
                                    <option value="personal">Personal</option>
                                </select>
                                @error('category')
                                    <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Target Date --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">
                                    Target Date <span class="text-red-400">*</span>
                                </label>
                                <input type="date" wire:model="target_date"
                                       class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                                @error('target_date')
                                    <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column (1/3) --}}
            <div class="space-y-6">
                {{-- Progress Card (only when editing) --}}
                @if($goalId)
                    <div class="bg-dark-800 border border-dark-700 rounded-xl">
                        <div class="px-6 py-4 border-b border-dark-700">
                            <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Progress</h2>
                        </div>
                        <div class="p-6" x-data="{ progress: @entangle('progress') }">
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-sm font-medium text-gray-300">Current Progress</label>
                                <span class="text-sm font-semibold text-primary-light" x-text="progress + '%'"></span>
                            </div>
                            <input type="range" x-model="progress" wire:model.live="progress"
                                   min="0" max="100" step="5"
                                   class="w-full h-2 bg-dark-700 rounded-full appearance-none cursor-pointer accent-primary">
                            <div class="w-full bg-dark-700 rounded-full h-1.5 mt-3">
                                <div class="bg-gradient-to-r from-primary to-fuchsia-500 h-1.5 rounded-full transition-all duration-300"
                                     :style="'width: ' + progress + '%'"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Status Card --}}
                    <div class="bg-dark-800 border border-dark-700 rounded-xl">
                        <div class="px-6 py-4 border-b border-dark-700">
                            <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Status</h2>
                        </div>
                        <div class="p-6 space-y-4">
                            {{-- Current Status Badge --}}
                            <div>
                                @if($status === 'active')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>Active
                                    </span>
                                @elseif($status === 'completed')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary-light">
                                        <span class="w-1.5 h-1.5 rounded-full bg-primary-light"></span>Completed
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-500/10 text-gray-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span>Abandoned
                                    </span>
                                @endif
                            </div>

                            {{-- Action Buttons --}}
                            @if($status === 'active')
                                <div class="flex flex-col gap-2">
                                    <x-admin.confirm-button
                                        title="Mark Goal as Completed?"
                                        text="This goal will be marked as completed."
                                        action="$wire.markCompleted"
                                        confirm-text="Yes, mark completed"
                                        class="inline-flex items-center justify-center gap-2 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 hover:text-emerald-300 text-sm font-medium rounded-lg px-4 py-2.5 transition-all duration-200"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Mark Completed
                                    </x-admin.confirm-button>
                                    <x-admin.confirm-button
                                        title="Abandon Goal?"
                                        text="This goal will be marked as abandoned."
                                        action="$wire.markAbandoned"
                                        confirm-text="Yes, abandon it"
                                        class="inline-flex items-center justify-center gap-2 bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 hover:text-amber-300 text-sm font-medium rounded-lg px-4 py-2.5 transition-all duration-200"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                        Abandon
                                    </x-admin.confirm-button>
                                </div>
                            @elseif($status === 'completed' || $status === 'abandoned')
                                <x-admin.confirm-button
                                    title="Reopen Goal?"
                                    text="This goal will be moved back to active status."
                                    action="$wire.reopen"
                                    confirm-text="Yes, reopen it"
                                    class="inline-flex items-center justify-center gap-2 w-full bg-primary/10 hover:bg-primary/20 text-primary-light hover:text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-all duration-200"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    Reopen
                                </x-admin.confirm-button>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Save Card --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl">
                    <div class="px-6 py-4 border-b border-dark-700">
                        <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Actions</h2>
                    </div>
                    <div class="p-6 space-y-3">
                        <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20 disabled:opacity-50 disabled:cursor-not-allowed"
                                wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="save">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <span wire:loading wire:target="save">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            </span>
                            <span wire:loading.remove wire:target="save">{{ $goalId ? 'Update Goal' : 'Create Goal' }}</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </button>
                        <a href="{{ route('admin.personal.goals-tracker.index') }}" wire:navigate
                           class="w-full inline-flex items-center justify-center gap-2 bg-dark-700 hover:bg-dark-600 border border-dark-600 text-gray-300 hover:text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
