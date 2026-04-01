<div>
    {{-- 1. BREADCRUMB --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-400">Tasks</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">Daily Planner</span>
    </div>

    {{-- 2. PAGE HEADER --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Daily Planner</h1>
            <p class="text-gray-500 mt-1">{{ \Carbon\Carbon::parse($selectedDate)->format('l, F j, Y') }}</p>
        </div>
        @if($stats['total'] > 0 && $stats['completed'] < $stats['total'])
            <button wire:click="moveIncompleteToTomorrow" wire:confirm="Move all incomplete tasks to tomorrow?"
                    class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 text-gray-300 text-sm font-medium rounded-lg px-4 py-2.5 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                Move Incomplete to Tomorrow
            </button>
        @endif
    </div>

    {{-- Flash Messages --}}
    @if(session('success') || session('error') || session('info'))
        <div x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 4000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="mb-6">
            @if(session('success'))
                <div class="flex items-center gap-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-lg px-4 py-3 text-sm">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p>{{ session('success') }}</p>
                    <button @click="show = false" class="ml-auto text-emerald-400/60 hover:text-emerald-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-center gap-3 bg-red-500/10 border border-red-500/20 text-red-400 rounded-lg px-4 py-3 text-sm">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p>{{ session('error') }}</p>
                    <button @click="show = false" class="ml-auto text-red-400/60 hover:text-red-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            @endif
            @if(session('info'))
                <div class="flex items-center gap-3 bg-blue-500/10 border border-blue-500/20 text-blue-400 rounded-lg px-4 py-3 text-sm">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p>{{ session('info') }}</p>
                    <button @click="show = false" class="ml-auto text-blue-400/60 hover:text-blue-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            @endif
        </div>
    @endif

    {{-- 3. DATE NAVIGATION BAR --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl p-4 mb-6">
        <div class="flex items-center justify-between">
            <button wire:click="goToPreviousDay" class="text-gray-400 hover:text-white transition-colors p-2 rounded-lg hover:bg-dark-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <div class="flex items-center gap-3">
                <span class="text-white font-medium">{{ \Carbon\Carbon::parse($selectedDate)->format('D, M j, Y') }}</span>
                @if($selectedDate !== now()->format('Y-m-d'))
                    <button wire:click="goToToday" class="text-xs text-primary-light hover:text-white bg-primary/10 hover:bg-primary/20 px-3 py-1 rounded-full transition-colors">
                        Today
                    </button>
                @endif
            </div>
            <button wire:click="goToNextDay" class="text-gray-400 hover:text-white transition-colors p-2 rounded-lg hover:bg-dark-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    </div>

    {{-- 4. PROGRESS SECTION --}}
    @if($stats['total'] > 0)
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 mb-6">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm text-gray-300">
                    @if($stats['percentage'] === 100)
                        <span class="text-emerald-400 font-medium">All {{ $stats['total'] }} tasks completed! Great job!</span>
                    @else
                        <span class="text-white font-medium">{{ $stats['completed'] }}</span>
                        <span class="text-gray-400">of</span>
                        <span class="text-white font-medium">{{ $stats['total'] }}</span>
                        <span class="text-gray-400">tasks completed</span>
                    @endif
                </p>
                <span class="text-sm font-medium {{ $stats['percentage'] === 100 ? 'text-emerald-400' : 'text-gray-400' }}">
                    {{ $stats['percentage'] }}%
                </span>
            </div>
            <div class="w-full bg-dark-700 rounded-full h-2.5">
                <div class="h-2.5 rounded-full transition-all duration-500 {{ $stats['percentage'] === 100 ? 'bg-emerald-500' : 'bg-gradient-to-r from-primary to-fuchsia-500' }}"
                     style="width: {{ $stats['percentage'] }}%"></div>
            </div>
        </div>
    @endif

    {{-- 5. FILTER BAR --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-4">
            <select wire:model.live="statusFilter"
                    class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                <option value="all">All Status</option>
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="completed">Completed</option>
            </select>
            <select wire:model.live="priorityFilter"
                    class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                <option value="all">All Priorities</option>
                <option value="urgent">Urgent</option>
                <option value="high">High</option>
                <option value="medium">Medium</option>
                <option value="low">Low</option>
            </select>
            <select wire:model.live="categoryFilter"
                    class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                <option value="all">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- 6. INLINE ADD TASK FORM --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl p-4 mb-6">
        <form wire:submit="addTask" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input type="text" wire:model="newTaskTitle" placeholder="What needs to be done?"
                       class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                @error('newTaskTitle')
                    <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <select wire:model="newTaskPriority"
                    class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
            </select>
            <select wire:model="newTaskCategoryId"
                    class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                <option value="">No Category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-colors whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add
            </button>
        </form>
    </div>

    {{-- 7. TASK LIST --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl overflow-hidden">
        @forelse($tasks as $task)
            <div class="px-6 py-4 border-b border-dark-700/50 hover:bg-dark-700/30 transition-colors {{ $task->status === 'completed' ? 'opacity-60' : '' }}">
                @if($editingTaskId === $task->id)
                    {{-- 8. INLINE EDIT ROW --}}
                    <form wire:submit="saveEdit" class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                        <div class="flex-1 w-full">
                            <input type="text" wire:model="editTitle"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                            @error('editTitle')
                                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <select wire:model="editPriority"
                                class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                        <select wire:model="editCategoryId"
                                class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                            <option value="">No Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <div class="flex items-center gap-2">
                            <button type="submit"
                                    class="bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-colors">
                                Save
                            </button>
                            <button type="button" wire:click="cancelEdit"
                                    class="bg-dark-700 hover:bg-dark-600 text-gray-300 text-sm font-medium rounded-lg px-4 py-2.5 transition-colors">
                                Cancel
                            </button>
                        </div>
                    </form>
                @else
                    {{-- TASK DISPLAY ROW --}}
                    <div class="flex items-center gap-4">
                        {{-- Toggle Complete Checkbox --}}
                        <button wire:click="toggleComplete({{ $task->id }})" class="shrink-0">
                            @if($task->status === 'completed')
                                <span class="flex items-center justify-center w-6 h-6 rounded-full bg-emerald-500/20 border-2 border-emerald-500 text-emerald-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </span>
                            @else
                                <span class="flex items-center justify-center w-6 h-6 rounded-full border-2 border-dark-600 hover:border-primary transition-colors"></span>
                            @endif
                        </button>

                        {{-- Task Title --}}
                        <span class="flex-1 text-sm {{ $task->status === 'completed' ? 'line-through text-gray-500' : 'text-white' }}">
                            {{ $task->title }}
                        </span>

                        {{-- Priority Badge --}}
                        @php
                            $priorityClasses = match($task->priority) {
                                'urgent' => 'bg-red-500/10 text-red-400',
                                'high' => 'bg-amber-500/10 text-amber-400',
                                'medium' => 'bg-blue-500/10 text-blue-400',
                                'low' => 'bg-gray-500/10 text-gray-400',
                                default => 'bg-gray-500/10 text-gray-400',
                            };
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $priorityClasses }}">
                            {{ ucfirst($task->priority) }}
                        </span>

                        {{-- Category Badge --}}
                        @if($task->category)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary-light">
                                {{ $task->category->name }}
                            </span>
                        @endif

                        {{-- Action Buttons --}}
                        <div class="flex items-center gap-1">
                            <button wire:click="startEditing({{ $task->id }})"
                                    class="text-gray-400 hover:text-primary-light transition-colors p-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button wire:click="deleteTask({{ $task->id }})" wire:confirm="Are you sure you want to delete this task?"
                                    class="text-gray-400 hover:text-red-400 transition-colors p-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        @empty
            {{-- 9. EMPTY STATE --}}
            <div class="px-6 py-16 text-center">
                <div class="w-12 h-12 rounded-xl bg-dark-700 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <h3 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-1">No tasks for this day</h3>
                <p class="text-sm text-gray-500">Add your first task using the form above.</p>
            </div>
        @endforelse
    </div>
</div>
