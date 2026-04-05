<x-layouts.app>
    <div class="min-h-screen bg-dark-950">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            {{-- Header --}}
            <div class="text-center mb-12">
                <h1 class="text-3xl font-mono font-bold text-white uppercase tracking-wider mb-3">
                    {{ $board->name }}
                </h1>
                @if($board->description)
                    <p class="text-gray-400 max-w-2xl mx-auto">{{ $board->description }}</p>
                @endif
                <div class="mt-4 flex items-center justify-center gap-2 text-sm text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    <span>Read-only view</span>
                </div>
            </div>

            {{-- Columns --}}
            @if($board->columns->isEmpty())
                <div class="bg-dark-900 border border-white/[0.04] rounded-2xl p-16 text-center">
                    <p class="text-gray-500">No tasks yet.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($board->columns as $column)
                        <div class="bg-dark-900 border border-white/[0.04] rounded-2xl overflow-hidden">
                            {{-- Column Header --}}
                            <div class="px-5 py-4 border-b border-white/[0.04] flex items-center gap-3">
                                <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $column->color ?? '#7c3aed' }}"></span>
                                <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">
                                    {{ $column->name }}
                                </h2>
                                <span class="ml-auto text-xs text-gray-500 bg-dark-800 px-2 py-0.5 rounded-full">
                                    {{ $column->tasks->count() }}
                                </span>
                            </div>

                            {{-- Tasks --}}
                            <div class="p-4 space-y-3">
                                @forelse($column->tasks as $task)
                                    <div class="bg-dark-800 border border-white/[0.04] rounded-2xl p-4">
                                        {{-- Title --}}
                                        <div class="flex items-start gap-2 mb-2">
                                            @if($task->completed_at)
                                                <svg class="w-4 h-4 text-emerald-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            @else
                                                <div class="w-4 h-4 rounded-full border-2 border-gray-600 mt-0.5 shrink-0"></div>
                                            @endif
                                            <span class="text-sm {{ $task->completed_at ? 'line-through text-gray-500' : 'text-white' }}">
                                                {{ $task->title }}
                                            </span>
                                        </div>

                                        {{-- Meta --}}
                                        <div class="flex flex-wrap items-center gap-2 pl-6">
                                            {{-- Priority --}}
                                            @php
                                                $priorityStyles = match($task->priority) {
                                                    'urgent' => 'bg-red-500/10 text-red-400',
                                                    'high' => 'bg-amber-500/10 text-amber-400',
                                                    'medium' => 'bg-blue-500/10 text-blue-400',
                                                    'low' => 'bg-gray-500/10 text-gray-400',
                                                    default => 'bg-gray-500/10 text-gray-400',
                                                };
                                            @endphp
                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $priorityStyles }}">
                                                {{ ucfirst($task->priority) }}
                                            </span>

                                            {{-- Target Date --}}
                                            @if($task->target_date)
                                                <span class="text-xs text-gray-500 flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                    {{ $task->target_date->format('M j, Y') }}
                                                </span>
                                            @endif
                                        </div>

                                        {{-- Tags --}}
                                        @if(!empty($task->tags))
                                            <div class="flex flex-wrap gap-1 mt-2 pl-6">
                                                @foreach($task->tags as $tag)
                                                    <span class="px-1.5 py-0.5 rounded text-xs bg-accent/10 text-accent-light">{{ $tag }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="text-center py-6">
                                        <p class="text-xs text-gray-600">No tasks in this column</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Footer --}}
            <div class="text-center mt-12 text-xs text-gray-600">
                Shared project board
            </div>
        </div>
    </div>
</x-layouts.app>
