<div>
    {{-- 1. BREADCRUMB --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-400">Project Management</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">Calendar</span>
    </div>

    {{-- 2. PAGE HEADER --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Calendar</h1>
            <p class="text-gray-500 mt-1">{{ $periodLabel }}</p>
        </div>
        <button wire:click="goToToday"
                class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 text-gray-300 text-sm font-medium rounded-lg px-4 py-2.5 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Today
        </button>
    </div>

    {{-- 3. STAT CARDS ROW --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8"
         x-data="{ shown: false }"
         x-intersect="shown = true">
        {{-- Total Tasks --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6"
             x-show="shown" x-transition:enter="transition ease-out duration-500 delay-[0ms]" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-500">Total Tasks</span>
                <span class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </span>
            </div>
            <p class="text-3xl font-bold text-white">{{ $stats['total'] }}</p>
        </div>

        {{-- Completed --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6"
             x-show="shown" x-transition:enter="transition ease-out duration-500 delay-[100ms]" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-500">Completed</span>
                <span class="w-9 h-9 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="text-3xl font-bold text-white">{{ $stats['completed'] }}</p>
        </div>

        {{-- Overdue --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6"
             x-show="shown" x-transition:enter="transition ease-out duration-500 delay-[200ms]" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-500">Overdue</span>
                <span class="w-9 h-9 rounded-lg bg-red-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="text-3xl font-bold text-white">{{ $stats['overdue'] }}</p>
        </div>

        {{-- Busiest Day --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6"
             x-show="shown" x-transition:enter="transition ease-out duration-500 delay-[300ms]" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-500">Busiest Day</span>
                <span class="w-9 h-9 rounded-lg bg-amber-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </span>
            </div>
            <p class="text-3xl font-bold text-white">
                {{ $stats['busiestDay'] ? \Carbon\Carbon::parse($stats['busiestDay'])->format('D, M j') : '—' }}
            </p>
        </div>
    </div>

    {{-- 4. CALENDAR TOOLBAR --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl px-4 py-3 mb-6">
        <div class="flex items-center justify-between">
            {{-- Previous Period --}}
            <button wire:click="previousPeriod" class="text-gray-400 hover:text-white transition-colors p-2 rounded-lg hover:bg-dark-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>

            {{-- Period Label --}}
            <span class="text-white font-medium">{{ $periodLabel }}</span>

            {{-- Next Period + Board Filter + View Mode Toggle --}}
            <div class="flex items-center gap-3">
                <button wire:click="nextPeriod" class="text-gray-400 hover:text-white transition-colors p-2 rounded-lg hover:bg-dark-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>

                {{-- Board Filter --}}
                <select wire:model.live="boardFilter"
                        class="bg-dark-700 border border-dark-600 rounded-lg px-3 py-1.5 text-white text-xs focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="all">All Projects</option>
                    @foreach($boards as $board)
                        <option value="{{ $board->id }}">{{ $board->name }}</option>
                    @endforeach
                </select>

                {{-- View Mode Toggle --}}
                <div class="flex bg-dark-700 rounded-lg p-1">
                    <button wire:click="setViewMode('month')"
                            class="px-3 py-1.5 rounded-md text-xs font-medium transition-colors {{ $viewMode === 'month' ? 'bg-primary text-white' : 'text-gray-400 hover:text-white' }}">
                        Month
                    </button>
                    <button wire:click="setViewMode('week')"
                            class="px-3 py-1.5 rounded-md text-xs font-medium transition-colors {{ $viewMode === 'week' ? 'bg-primary text-white' : 'text-gray-400 hover:text-white' }}">
                        Week
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- 5. MONTHLY CALENDAR GRID --}}
    @if($viewMode === 'month')
        <div x-data x-show="true" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             class="bg-dark-800 border border-dark-700 rounded-xl overflow-hidden mb-6">
            {{-- Day-of-week header --}}
            <div class="grid grid-cols-7 bg-dark-700">
                @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $dayName)
                    <div class="text-xs font-mono font-medium text-gray-500 uppercase tracking-wider py-3 text-center">
                        {{ $dayName }}
                    </div>
                @endforeach
            </div>

            {{-- Calendar grid --}}
            <div class="grid grid-cols-7">
                @foreach($calendarDays as $day)
                    <div wire:click="openDayModal('{{ $day['date'] }}')"
                         x-data="{ hover: false }"
                         @mouseenter="hover = true"
                         @mouseleave="hover = false"
                         class="relative h-24 p-2 border-b border-r border-dark-700/50 cursor-pointer transition-colors"
                         :class="hover ? 'bg-dark-700/30' : ''">
                        {{-- Day number --}}
                        <div class="mb-1">
                            @if($day['isToday'])
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-primary text-white text-sm font-medium">
                                    {{ $day['day'] }}
                                </span>
                            @else
                                <span class="text-sm {{ $day['isCurrentMonth'] ? 'text-gray-300' : 'text-gray-600' }}">
                                    {{ $day['day'] }}
                                </span>
                            @endif
                        </div>

                        {{-- Task dots --}}
                        @if($day['tasks']->isNotEmpty())
                            <div class="flex flex-wrap gap-1">
                                @foreach($day['tasks']->take(3) as $task)
                                    <span class="w-1.5 h-1.5 rounded-full shrink-0" style="background-color: {{ $task->column?->color ?? '#7c3aed' }}"></span>
                                @endforeach
                            </div>
                            @if($day['tasks']->count() > 3)
                                <span class="text-xs text-gray-500 mt-0.5 block">+{{ $day['tasks']->count() - 3 }} more</span>
                            @endif
                        @endif

                        {{-- Tooltip --}}
                        <div x-show="hover && {{ $day['tasks']->count() }}"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="absolute z-10 bottom-full left-1/2 -translate-x-1/2 mb-1 bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-xs text-gray-300 whitespace-nowrap shadow-lg pointer-events-none">
                            {{ $day['tasks']->count() }} task{{ $day['tasks']->count() !== 1 ? 's' : '' }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- 6. WEEKLY CALENDAR GRID --}}
    @if($viewMode === 'week')
        <div x-data x-show="true" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             class="bg-dark-800 border border-dark-700 rounded-xl overflow-hidden mb-6">
            <div class="grid grid-cols-7">
                {{-- Column headers --}}
                @foreach($calendarDays as $day)
                    <div class="text-center py-3 border-b border-r border-dark-700/50 {{ $day['isToday'] ? 'bg-primary/5' : 'bg-dark-700' }}">
                        <span class="text-xs font-mono font-medium uppercase tracking-wider {{ $day['isToday'] ? 'text-primary-light' : 'text-gray-500' }}">
                            {{ $day['dayName'] }} {{ $day['day'] }}
                        </span>
                    </div>
                @endforeach

                {{-- Column bodies --}}
                @foreach($calendarDays as $day)
                    <div wire:click="openDayModal('{{ $day['date'] }}')"
                         class="min-h-64 p-2 border-r border-dark-700/50 cursor-pointer overflow-y-auto hover:bg-dark-700/10 transition-colors">
                        @forelse($day['tasks'] as $task)
                            <div class="rounded-lg px-2 py-1.5 mb-1 text-xs {{ $task->completed_at ? 'opacity-50' : '' }}"
                                 style="border-left: 3px solid {{ $task->column?->color ?? '#7c3aed' }}; background-color: {{ $task->column?->color ?? '#7c3aed' }}15">
                                <span class="{{ $task->completed_at ? 'line-through text-gray-500' : 'text-gray-300' }} truncate block">
                                    {{ $task->title }}
                                </span>
                            </div>
                        @empty
                            <div class="h-full flex items-center justify-center border-2 border-dashed border-dark-700/30 rounded-lg min-h-16">
                                <span class="text-xs text-gray-600">No tasks</span>
                            </div>
                        @endforelse
                    </div>
                @endforeach
            </div>
        </div>
    @endif


    {{-- 8. DAY TASKS MODAL --}}
    @if($showDayModal)
        <div
            x-data="{ open: @entangle('showDayModal') }"
            x-show="open"
            x-cloak
            x-on:keydown.escape.window="open = false"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
        >
            {{-- Backdrop --}}
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                x-on:click="open = false"
                class="absolute inset-0 bg-dark-950/80 backdrop-blur-sm"
            ></div>

            {{-- Modal Panel --}}
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                class="relative w-full max-w-lg bg-dark-800 border border-dark-700 rounded-xl shadow-2xl shadow-black/50 overflow-hidden"
            >
                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-dark-700">
                    <div>
                        <h3 class="text-base font-mono font-semibold text-white uppercase tracking-wider">
                            {{ \Carbon\Carbon::parse($selectedDayDate)->format('l, M j') }}
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ count($selectedDayTasks) }} {{ Str::plural('task', count($selectedDayTasks)) }}
                        </p>
                    </div>
                    <button wire:click="closeDayModal"
                            class="text-gray-500 hover:text-gray-300 transition-colors p-1 rounded-lg hover:bg-dark-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="px-6 py-4 max-h-96 overflow-y-auto">
                    @if(count($selectedDayTasks) === 0)
                        {{-- Empty State --}}
                        <div class="flex flex-col items-center justify-center py-10 text-center">
                            <div class="w-12 h-12 rounded-full bg-dark-700 flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <p class="text-sm text-gray-500">No tasks for this day</p>
                            <p class="text-xs text-gray-600 mt-1">Enjoy the free time!</p>
                        </div>
                    @else
                        <div>
                            <h4 class="text-xs font-mono font-medium text-gray-500 uppercase tracking-widest mb-3">
                                Project Tasks
                                <span class="text-gray-600 ml-1">({{ count($selectedDayTasks) }})</span>
                            </h4>
                            <div class="space-y-1.5">
                                @foreach($selectedDayTasks as $task)
                                    <div class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-dark-700/50 transition-colors">
                                        {{-- Completion indicator --}}
                                        <div class="shrink-0 w-5 h-5 rounded-md {{ $task['completed'] ? 'bg-emerald-500/10' : 'bg-primary/10' }} flex items-center justify-center">
                                            @if($task['completed'])
                                                <svg class="w-3 h-3 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            @else
                                                <svg class="w-3 h-3 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7"/>
                                                </svg>
                                            @endif
                                        </div>

                                        {{-- Task Info --}}
                                        <div class="flex-1 min-w-0">
                                            <span class="text-sm block truncate {{ $task['completed'] ? 'line-through text-gray-500' : 'text-gray-300' }}">
                                                {{ $task['title'] }}
                                            </span>
                                        </div>

                                        {{-- Badges --}}
                                        <div class="flex items-center gap-1.5 shrink-0">
                                            {{-- Priority Badge --}}
                                            @switch($task['priority'])
                                                @case('urgent')
                                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-500/10 text-red-400">Urgent</span>
                                                    @break
                                                @case('high')
                                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400">High</span>
                                                    @break
                                                @case('medium')
                                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-500/10 text-blue-400">Med</span>
                                                    @break
                                                @case('low')
                                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-500/10 text-gray-400">Low</span>
                                                    @break
                                            @endswitch

                                            {{-- Board Badge --}}
                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary-light">
                                                {{ $task['board_name'] }}
                                            </span>

                                            {{-- Column Badge --}}
                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-dark-700 text-gray-400">
                                                {{ $task['column_name'] }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-between px-6 py-4 border-t border-dark-700 bg-dark-800">
                    <button wire:click="closeDayModal"
                            class="text-sm text-gray-500 hover:text-gray-300 transition-colors">
                        Close
                    </button>
                    <a href="{{ route('admin.project-management.project-board.index') }}" wire:navigate
                       class="inline-flex items-center gap-2 text-sm font-medium text-primary-light hover:text-white transition-colors">
                        Open Project Board
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
