<div>
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('admin.tasks.daily-planner.index') }}" wire:navigate class="hover:text-gray-300 transition-colors">Tasks</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">Project Board</span>
    </div>

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Project Board</h1>
            <p class="text-sm text-gray-500 mt-1">Manage project tasks with Kanban boards</p>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm"
             x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" x-transition>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-sm"
             x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition>
            {{ session('error') }}
        </div>
    @endif

    {{-- Control Bar --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl p-4 mb-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            {{-- Left: Board Selector + Search --}}
            <div class="flex items-center gap-3">
                <select wire:change="selectBoard($event.target.value)"
                        class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">Select Board</option>
                    @foreach ($boards as $board)
                        <option value="{{ $board->id }}" @selected($board->id === $selectedBoardId)>{{ $board->name }}</option>
                    @endforeach
                </select>

                <div class="relative">
                    <svg class="w-4 h-4 text-gray-500 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text"
                           wire:model.live.debounce.300ms="search"
                           placeholder="Search tasks..."
                           class="bg-dark-700 border border-dark-600 rounded-lg pl-10 pr-4 py-2.5 text-white text-sm placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent w-56">
                </div>
            </div>

            {{-- Right: Filters + Actions --}}
            <div class="flex items-center gap-3">
                <select wire:model.live="priorityFilter"
                        class="bg-dark-700 border border-dark-600 rounded-lg px-3 py-2.5 text-white text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="all">All Priorities</option>
                    <option value="urgent">Urgent</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>

                <select wire:model.live="categoryFilter"
                        class="bg-dark-700 border border-dark-600 rounded-lg px-3 py-2.5 text-white text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="all">All Categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>

                <button wire:click="openNewBoardModal"
                        class="bg-primary hover:bg-primary-hover text-white font-medium rounded-lg px-5 py-2.5 text-sm transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Board
                </button>

                @if ($selectedBoardId)
                    <button wire:click="deleteBoard({{ $selectedBoardId }})"
                            wire:confirm="Are you sure you want to delete this board? All columns and tasks will be permanently removed."
                            class="bg-red-500/10 hover:bg-red-500/20 text-red-400 font-medium rounded-lg px-4 py-2.5 text-sm transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Kanban Board --}}
    @if ($selectedBoard)
        <div class="flex gap-5 overflow-x-auto pb-4" style="height: calc(100vh - 280px);">
            @foreach ($selectedBoard->columns as $column)
                <div class="w-80 shrink-0 bg-dark-800 border border-dark-700 rounded-xl flex flex-col max-h-full">
                    {{-- Column Header --}}
                    <div class="px-4 py-3 border-b border-dark-700 shrink-0 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $column->color ?? '#7c3aed' }}"></span>
                            <h3 class="font-mono font-semibold text-white uppercase tracking-wider text-sm">{{ $column->name }}</h3>
                            <span class="bg-dark-700 text-gray-400 text-xs px-2 py-0.5 rounded-full">{{ $column->tasks->count() }}</span>
                        </div>
                        <button wire:click="deleteColumn({{ $column->id }})"
                                wire:confirm="Delete this column? It must be empty first."
                                class="text-gray-600 hover:text-red-400 transition-colors p-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Task List --}}
                    <div class="flex-1 overflow-y-auto p-3 space-y-3 min-h-0"
                         data-sortable-column="{{ $column->id }}"
                         wire:ignore.self>
                        @foreach ($column->tasks as $task)
                            <div class="bg-dark-700 border border-dark-600 rounded-lg p-3 cursor-grab"
                                 data-task-card
                                 data-task-id="{{ $task->id }}">
                                {{-- Task Title --}}
                                <button wire:click="openTaskModal(null, {{ $task->id }})"
                                        class="text-sm font-medium text-white hover:text-primary-light transition-colors text-left w-full">
                                    {{ $task->title }}
                                </button>

                                {{-- Description Snippet --}}
                                @if ($task->description)
                                    <p class="text-xs text-gray-500 line-clamp-2 mt-1">{{ $task->description }}</p>
                                @endif

                                {{-- Meta Row: Priority + Category + Date --}}
                                <div class="flex flex-wrap items-center gap-2 mt-2">
                                    {{-- Priority Badge --}}
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                        @switch($task->priority)
                                            @case('urgent') bg-red-500/10 text-red-400 @break
                                            @case('high') bg-amber-500/10 text-amber-400 @break
                                            @case('medium') bg-blue-500/10 text-blue-400 @break
                                            @case('low') bg-emerald-500/10 text-emerald-400 @break
                                        @endswitch">
                                        {{ ucfirst($task->priority) }}
                                    </span>

                                    {{-- Category Badge --}}
                                    @if ($task->category)
                                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary-light">
                                            {{ $task->category->name }}
                                        </span>
                                    @endif

                                    {{-- Target Date --}}
                                    @if ($task->target_date)
                                        <span class="text-xs text-gray-500 flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            {{ $task->target_date->format('M d') }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Tags --}}
                                @if (!empty($task->tags))
                                    <div class="flex flex-wrap gap-1 mt-2">
                                        @foreach ($task->tags as $tag)
                                            <span class="px-1.5 py-0.5 rounded text-xs bg-dark-600 text-gray-400">{{ $tag }}</span>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Cross-Board Move Buttons --}}
                                @if ($boards->count() > 1)
                                    @php
                                        $boardIds = $boards->pluck('id')->values()->toArray();
                                        $currentIndex = array_search($selectedBoard->id, $boardIds);
                                        $prevBoardId = $currentIndex > 0 ? $boardIds[$currentIndex - 1] : null;
                                        $nextBoardId = $currentIndex < count($boardIds) - 1 ? $boardIds[$currentIndex + 1] : null;
                                    @endphp
                                    <div class="flex items-center gap-1 mt-2 pt-2 border-t border-dark-600">
                                        @if ($prevBoardId)
                                            <button wire:click="moveTaskToBoard({{ $task->id }}, {{ $prevBoardId }})"
                                                    title="Move to {{ $boards->firstWhere('id', $prevBoardId)->name }}"
                                                    class="text-gray-600 hover:text-primary-light transition-colors p-1 rounded hover:bg-dark-600">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                                </svg>
                                            </button>
                                        @endif
                                        @if ($nextBoardId)
                                            <button wire:click="moveTaskToBoard({{ $task->id }}, {{ $nextBoardId }})"
                                                    title="Move to {{ $boards->firstWhere('id', $nextBoardId)->name }}"
                                                    class="text-gray-600 hover:text-primary-light transition-colors p-1 rounded hover:bg-dark-600">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                @endif

                                {{-- Delete Task --}}
                                <div class="flex justify-end mt-1">
                                    <button wire:click="deleteTask({{ $task->id }})"
                                            wire:confirm="Delete this task?"
                                            class="text-gray-600 hover:text-red-400 transition-colors p-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Add Task Button --}}
                    <div class="px-3 py-2 border-t border-dark-700 shrink-0">
                        <button wire:click="openTaskModal({{ $column->id }})"
                                class="text-sm text-gray-500 hover:text-primary-light transition-colors w-full text-left flex items-center gap-2 py-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Task
                        </button>
                    </div>
                </div>
            @endforeach

            {{-- Add Column Button --}}
            <div class="w-80 shrink-0">
                <button wire:click="openColumnModal"
                        class="w-full h-32 bg-dark-800/50 border border-dashed border-dark-600 rounded-xl flex flex-col items-center justify-center gap-2 text-gray-500 hover:text-primary-light hover:border-primary/30 transition-colors">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span class="text-sm font-medium">Add Column</span>
                </button>
            </div>
        </div>
    @elseif ($boards->isEmpty())
        {{-- Empty State --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-12 text-center">
            <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7"/>
                </svg>
            </div>
            <h3 class="text-lg font-mono font-semibold text-white uppercase tracking-wider mb-2">No Project Boards Yet</h3>
            <p class="text-gray-500 mb-6">Create your first project board to start organizing tasks with Kanban columns.</p>
            <button wire:click="openNewBoardModal"
                    class="bg-primary hover:bg-primary-hover text-white font-medium rounded-lg px-6 py-2.5 text-sm transition-colors inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Create Your First Board
            </button>
        </div>
    @endif

    {{-- New Board Modal --}}
    @if ($showNewBoardModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center"
             x-data="{ show: @entangle('showNewBoardModal') }"
             x-show="show"
             x-transition>
            <div class="fixed inset-0 bg-dark-950/80" wire:click="$set('showNewBoardModal', false)"></div>
            <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 w-full max-w-md relative z-10">
                <h2 class="text-lg font-mono font-semibold text-white uppercase tracking-wider mb-5">Add Board</h2>

                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-300 mb-2 block">Board Name <span class="text-red-400">*</span></label>
                        <input type="text"
                               wire:model="newBoardName"
                               placeholder="e.g. Website Redesign"
                               class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                        @error('newBoardName') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-300 mb-2 block">Description</label>
                        <textarea wire:model="newBoardDescription"
                                  placeholder="Optional description..."
                                  rows="3"
                                  class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent text-sm resize-none"></textarea>
                        @error('newBoardDescription') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button wire:click="$set('showNewBoardModal', false)"
                            class="px-4 py-2.5 text-sm font-medium text-gray-400 hover:text-white transition-colors">
                        Cancel
                    </button>
                    <button wire:click="createBoard"
                            class="bg-primary hover:bg-primary-hover text-white font-medium rounded-lg px-5 py-2.5 text-sm transition-colors">
                        Add Board
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Add Column Modal --}}
    @if ($showColumnModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center"
             x-data="{ show: @entangle('showColumnModal') }"
             x-show="show"
             x-transition>
            <div class="fixed inset-0 bg-dark-950/80" wire:click="$set('showColumnModal', false)"></div>
            <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 w-full max-w-md relative z-10">
                <h2 class="text-lg font-mono font-semibold text-white uppercase tracking-wider mb-5">Add Column</h2>

                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-300 mb-2 block">Column Name <span class="text-red-400">*</span></label>
                        <input type="text"
                               wire:model="newColumnName"
                               placeholder="e.g. Testing"
                               class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                        @error('newColumnName') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-300 mb-2 block">Color</label>
                        <div class="flex items-center gap-3">
                            <input type="color"
                                   wire:model="newColumnColor"
                                   class="w-10 h-10 rounded-lg border border-dark-600 bg-dark-700 cursor-pointer">
                            <input type="text"
                                   wire:model="newColumnColor"
                                   placeholder="#7c3aed"
                                   class="flex-1 bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                        </div>
                        @error('newColumnColor') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button wire:click="$set('showColumnModal', false)"
                            class="px-4 py-2.5 text-sm font-medium text-gray-400 hover:text-white transition-colors">
                        Cancel
                    </button>
                    <button wire:click="addColumn"
                            class="bg-primary hover:bg-primary-hover text-white font-medium rounded-lg px-5 py-2.5 text-sm transition-colors">
                        Add Column
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Task Modal --}}
    @if ($showTaskModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center"
             x-data="{ show: @entangle('showTaskModal') }"
             x-show="show"
             x-transition>
            <div class="fixed inset-0 bg-dark-950/80" wire:click="closeTaskModal"></div>
            <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 w-full max-w-lg relative z-10 max-h-[90vh] overflow-y-auto">
                <h2 class="text-lg font-mono font-semibold text-white uppercase tracking-wider mb-5">
                    {{ $editingTaskId ? 'Edit Task' : 'Add Task' }}
                </h2>

                <div class="space-y-4">
                    {{-- Row 1: Title + Task ID --}}
                    <div class="flex gap-4">
                        <div class="flex-1">
                            <label class="text-sm font-medium text-gray-300 mb-2 block">Task Name <span class="text-red-400">*</span></label>
                            <input type="text"
                                   wire:model="taskTitle"
                                   placeholder="Enter task name"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                            @error('taskTitle') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        @if ($editingTaskId)
                            <div class="w-24">
                                <label class="text-sm font-medium text-gray-300 mb-2 block">Task ID</label>
                                <input type="text"
                                       value="#{{ $editingTaskId }}"
                                       readonly
                                       class="w-full bg-dark-700/50 border border-dark-600 rounded-lg px-4 py-2.5 text-gray-500 text-sm cursor-not-allowed">
                            </div>
                        @endif
                    </div>

                    {{-- Row 2: Description --}}
                    <div>
                        <label class="text-sm font-medium text-gray-300 mb-2 block">Description</label>
                        <textarea wire:model="taskDescription"
                                  placeholder="Task description..."
                                  rows="3"
                                  class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent text-sm resize-none"></textarea>
                        @error('taskDescription') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Row 3: Images --}}
                    <div>
                        <label class="text-sm font-medium text-gray-300 mb-2 block">Images</label>

                        {{-- Existing Images --}}
                        @if (!empty($existingImages))
                            <div class="flex flex-wrap gap-2 mb-3">
                                @foreach ($existingImages as $image)
                                    <div class="relative group">
                                        <img src="{{ asset('storage/' . $image['image_path']) }}"
                                             alt="Task image"
                                             class="w-20 h-20 object-cover rounded-lg border border-dark-600">
                                        <button wire:click="removeExistingImage({{ $image['id'] }})"
                                                class="absolute -top-1 -right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity">
                                            &times;
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="border-2 border-dashed border-dark-600 rounded-lg p-4 text-center hover:border-primary/30 transition-colors">
                            <input type="file"
                                   wire:model="taskImages"
                                   multiple
                                   accept="image/*"
                                   class="hidden"
                                   id="taskImageUpload">
                            <label for="taskImageUpload" class="cursor-pointer">
                                <svg class="w-8 h-8 text-gray-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-sm text-gray-500">Click to upload images (max 5, 2MB each)</p>
                            </label>
                        </div>

                        {{-- Upload Preview --}}
                        @if (!empty($taskImages))
                            <div class="flex flex-wrap gap-2 mt-2">
                                @foreach ($taskImages as $index => $image)
                                    <div class="relative">
                                        <img src="{{ $image->temporaryUrl() }}"
                                             alt="Upload preview"
                                             class="w-16 h-16 object-cover rounded-lg border border-dark-600">
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @error('taskImages.*') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Row 4: Target Date + Tags --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-300 mb-2 block">Target Date</label>
                            <input type="date"
                                   wire:model="taskTargetDate"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-300 mb-2 block">Tags</label>
                            <div class="flex gap-2">
                                <input type="text"
                                       wire:model="tagInput"
                                       wire:keydown.enter.prevent="addTag"
                                       placeholder="Add tag..."
                                       class="flex-1 bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                                <button wire:click="addTag"
                                        class="bg-dark-700 hover:bg-dark-600 text-gray-400 rounded-lg px-3 py-2.5 text-sm transition-colors">
                                    +
                                </button>
                            </div>
                            @if (!empty($taskTags))
                                <div class="flex flex-wrap gap-1 mt-2">
                                    @foreach ($taskTags as $index => $tag)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs bg-primary/10 text-primary-light">
                                            {{ $tag }}
                                            <button wire:click="removeTag({{ $index }})" class="hover:text-red-400">&times;</button>
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Row 5: Priority + Category --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-300 mb-2 block">Priority <span class="text-red-400">*</span></label>
                            <select wire:model="taskPriority"
                                    class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-300 mb-2 block">Category</label>
                            <select wire:model="taskCategoryId"
                                    class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                                <option value="">No Category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Modal Buttons --}}
                <div class="flex justify-end gap-3 mt-6">
                    <button wire:click="closeTaskModal"
                            class="px-4 py-2.5 text-sm font-medium text-gray-400 hover:text-white transition-colors">
                        Cancel
                    </button>
                    @if ($editingTaskId)
                        <button wire:click="updateTask"
                                class="bg-primary hover:bg-primary-hover text-white font-medium rounded-lg px-5 py-2.5 text-sm transition-colors">
                            Update Task
                        </button>
                    @else
                        <button wire:click="createTask"
                                class="bg-primary hover:bg-primary-hover text-white font-medium rounded-lg px-5 py-2.5 text-sm transition-colors">
                            Add Task
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
<script>
    document.addEventListener('livewire:navigated', () => {
        initSortable();
    });

    document.addEventListener('livewire:morph', () => {
        setTimeout(() => initSortable(), 100);
    });

    function initSortable() {
        document.querySelectorAll('[data-sortable-column]').forEach(el => {
            if (el._sortable) el._sortable.destroy();
            el._sortable = new Sortable(el, {
                group: 'kanban',
                animation: 150,
                ghostClass: 'opacity-30',
                dragClass: 'rotate-2',
                handle: '[data-task-card]',
                onEnd: function (evt) {
                    const taskId = parseInt(evt.item.dataset.taskId);
                    const targetColumnId = parseInt(evt.to.dataset.sortableColumn);
                    const newPosition = evt.newIndex;
                    Livewire.find(evt.item.closest('[wire\\:id]').getAttribute('wire:id'))
                        .call('moveTask', taskId, targetColumnId, newPosition);
                }
            });
        });
    }
</script>
@endpush
