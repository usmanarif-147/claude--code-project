<div>
    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mb-6 px-4 py-3 bg-emerald-500/10 border border-emerald-500/20 rounded-lg text-emerald-400 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-6 px-4 py-3 bg-red-500/10 border border-red-500/20 rounded-lg text-red-400 text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-400">Tasks</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('admin.tasks.recurring.index') }}" wire:navigate class="hover:text-gray-300 transition-colors">Recurring Tasks</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">{{ $recurringTask ? 'Edit' : 'Create' }}</span>
    </div>

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">{{ $recurringTask ? 'Edit Recurring Task' : 'Create Recurring Task' }}</h1>
            <p class="text-gray-500 mt-1">{{ $recurringTask ? 'Update the recurring task template.' : 'Set up a new recurring task template.' }}</p>
        </div>
        <a href="{{ route('admin.tasks.recurring.index') }}" wire:navigate
           class="text-gray-400 hover:text-white font-medium rounded-lg px-4 py-2.5 transition-colors text-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back
        </a>
    </div>

    {{-- Form --}}
    <form wire:submit="save">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            {{-- Main Content --}}
            <div class="xl:col-span-2 space-y-6">
                {{-- Task Details Card --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
                    <h2 class="text-lg font-mono font-bold text-white uppercase tracking-wider mb-5">Task Details</h2>

                    <div class="space-y-5">
                        {{-- Title --}}
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-300 mb-1.5">Title <span class="text-red-400">*</span></label>
                            <input type="text" id="title" wire:model="title"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="e.g. Review daily stand-up notes">
                            @error('title') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>

                        {{-- Description --}}
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-300 mb-1.5">Description</label>
                            <textarea id="description" wire:model="description" rows="4"
                                      class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent"
                                      placeholder="Optional details about this recurring task..."></textarea>
                            @error('description') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Schedule Card --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl p-6" x-data="{ freq: @entangle('frequency') }">
                    <h2 class="text-lg font-mono font-bold text-white uppercase tracking-wider mb-5">Schedule</h2>

                    <div class="space-y-5">
                        {{-- Frequency --}}
                        <div>
                            <label for="frequency" class="block text-sm font-medium text-gray-300 mb-1.5">Frequency <span class="text-red-400">*</span></label>
                            <select id="frequency" wire:model.live="frequency" x-model="freq"
                                    class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                            @error('frequency') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>

                        {{-- Day of Week (shown only for weekly) --}}
                        <div x-show="freq === 'weekly'" x-transition>
                            <label for="day_of_week" class="block text-sm font-medium text-gray-300 mb-1.5">Day of Week <span class="text-red-400">*</span></label>
                            <select id="day_of_week" wire:model="day_of_week"
                                    class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">Select a day</option>
                                <option value="0">Sunday</option>
                                <option value="1">Monday</option>
                                <option value="2">Tuesday</option>
                                <option value="3">Wednesday</option>
                                <option value="4">Thursday</option>
                                <option value="5">Friday</option>
                                <option value="6">Saturday</option>
                            </select>
                            @error('day_of_week') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>

                        {{-- Day of Month (shown only for monthly) --}}
                        <div x-show="freq === 'monthly'" x-transition>
                            <label for="day_of_month" class="block text-sm font-medium text-gray-300 mb-1.5">Day of Month <span class="text-red-400">*</span></label>
                            <input type="number" id="day_of_month" wire:model="day_of_month" min="1" max="31"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="1-31">
                            @error('day_of_month') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Settings Card --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
                    <h2 class="text-lg font-mono font-bold text-white uppercase tracking-wider mb-5">Settings</h2>

                    <div class="space-y-5">
                        {{-- Category --}}
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-300 mb-1.5">Category</label>
                            <select id="category_id" wire:model="category_id"
                                    class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">No Category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>

                        {{-- Priority --}}
                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-300 mb-1.5">Priority <span class="text-red-400">*</span></label>
                            <select id="priority" wire:model="priority"
                                    class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                            @error('priority') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>

                        {{-- Status Toggle --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1.5">Status</label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="is_active" class="sr-only peer">
                                <div class="w-11 h-6 bg-dark-600 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                <span class="ms-3 text-sm text-gray-400">{{ $is_active ? 'Active' : 'Paused' }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Submit Card --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
                    <button type="submit"
                            class="w-full bg-primary hover:bg-primary-hover text-white font-medium rounded-lg px-5 py-2.5 transition-colors flex items-center justify-center gap-2">
                        <span wire:loading.remove wire:target="save">{{ $recurringTask ? 'Update Recurring Task' : 'Create Recurring Task' }}</span>
                        <span wire:loading wire:target="save" class="flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            Saving...
                        </span>
                    </button>
                    <a href="{{ route('admin.tasks.recurring.index') }}" wire:navigate
                       class="mt-3 w-full inline-block text-center text-gray-400 hover:text-white font-medium rounded-lg px-5 py-2.5 transition-colors text-sm">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
