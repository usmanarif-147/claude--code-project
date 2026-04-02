<div>
    {{-- 1. BREADCRUMB --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-400">YouTube</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">Content Calendar</span>
    </div>

    {{-- 2. PAGE HEADER --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Content Calendar</h1>
            <p class="text-sm text-gray-500 mt-1">Plan your video and blog publishing schedule.</p>
        </div>
        <a href="{{ route('admin.youtube.content-calendar.create') }}" wire:navigate
           class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Content
        </a>
    </div>

    {{-- Flash Messages --}}
    @if(session('success') || session('error'))
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
        </div>
    @endif

    {{-- 3. STAT CARDS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        {{-- Total Items --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-white mb-1">{{ $monthStats['total'] ?? 0 }}</p>
            <p class="text-sm text-gray-500">Total Items</p>
        </div>

        {{-- Planned --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-lg bg-amber-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-white mb-1">{{ $monthStats['planned'] ?? 0 }}</p>
            <p class="text-sm text-gray-500">Planned</p>
        </div>

        {{-- Published --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-white mb-1">{{ $monthStats['published'] ?? 0 }}</p>
            <p class="text-sm text-gray-500">Published</p>
        </div>

        {{-- Gap Weeks --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-lg bg-red-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-white mb-1">{{ count($gapWeeks) }}</p>
            <p class="text-sm text-gray-500">Gap Weeks</p>
        </div>
    </div>

    {{-- 4. CALENDAR NAVIGATION BAR --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl p-4 mb-6">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <button wire:click="previousMonth" class="text-gray-400 hover:text-white transition-colors p-2 rounded-lg hover:bg-dark-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <div class="flex items-center gap-3">
                <h2 class="text-lg font-mono font-bold text-white uppercase tracking-wider">
                    {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
                </h2>
                @if($year !== now()->year || $month !== now()->month)
                    <button wire:click="goToToday" class="text-xs text-primary-light hover:text-white bg-primary/10 hover:bg-primary/20 px-3 py-1 rounded-full transition-colors">
                        Today
                    </button>
                @endif
            </div>
            <div class="flex items-center gap-3">
                <select wire:model.live="filterType"
                        class="bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
                    <option value="">All Types</option>
                    <option value="video">Video</option>
                    <option value="blog">Blog</option>
                </select>
                <button wire:click="nextMonth" class="text-gray-400 hover:text-white transition-colors p-2 rounded-lg hover:bg-dark-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- 5. CALENDAR GRID --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl overflow-hidden mb-6"
         x-data="{
             dragItemId: null,
             dragOver: null,
             startDrag(e, itemId) {
                 this.dragItemId = itemId;
                 e.dataTransfer.effectAllowed = 'move';
                 e.dataTransfer.setData('text/plain', itemId);
             },
             onDragOver(e, date) {
                 e.preventDefault();
                 this.dragOver = date;
             },
             onDragLeave() {
                 this.dragOver = null;
             },
             onDrop(e, date) {
                 e.preventDefault();
                 this.dragOver = null;
                 if (this.dragItemId) {
                     $wire.reschedule(this.dragItemId, date);
                     this.dragItemId = null;
                 }
             }
         }">
        {{-- Day Name Headers --}}
        <div class="grid grid-cols-7 border-b border-dark-700">
            @foreach($dayNames as $dayName)
                <div class="px-2 py-3 text-center">
                    <span class="text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">{{ $dayName }}</span>
                </div>
            @endforeach
        </div>

        {{-- Calendar Day Cells --}}
        <div class="grid grid-cols-7">
            @foreach($startOfCalendar->toPeriod($endOfCalendar, '1 day') as $currentDay)
                @php
                    $dateStr = $currentDay->format('Y-m-d');
                    $isCurrentMonth = $currentDay->month === $month;
                    $isToday = $dateStr === $today;
                    $isGapDate = in_array($dateStr, $gapDates);
                    $dayItems = $itemsByDate->get($dateStr, collect());
                @endphp
                <div class="min-h-[120px] border-b border-r border-dark-700/50 p-1.5 {{ !$isCurrentMonth ? 'opacity-30' : '' }} {{ $isGapDate && $isCurrentMonth ? 'bg-red-500/5' : '' }}"
                     :class="{ 'bg-primary/5': dragOver === '{{ $dateStr }}' }"
                     @dragover.prevent="onDragOver($event, '{{ $dateStr }}')"
                     @dragleave="onDragLeave()"
                     @drop="onDrop($event, '{{ $dateStr }}')">

                    {{-- Day Number --}}
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-medium {{ $isToday ? 'bg-primary text-white w-6 h-6 rounded-full flex items-center justify-center' : 'text-gray-400 px-1' }}">
                            {{ $currentDay->day }}
                        </span>
                    </div>

                    {{-- Content Items --}}
                    @foreach($dayItems as $item)
                        @php
                            $isVideo = $item->type === 'video';
                            $itemBg = $isVideo ? 'bg-primary/10 border-l-2 border-primary' : 'bg-blue-500/10 border-l-2 border-blue-400';
                            if ($item->color) {
                                $itemBg = 'border-l-2';
                            }
                        @endphp
                        <div class="group relative mb-1 px-1.5 py-1 rounded text-xs cursor-grab {{ $itemBg }} hover:opacity-80 transition-opacity"
                             @if($item->color) style="border-left-color: {{ $item->color }}; background-color: {{ $item->color }}15;" @endif
                             draggable="true"
                             @dragstart="startDrag($event, {{ $item->id }})">

                            <div class="flex items-center gap-1">
                                {{-- Type Icon --}}
                                @if($isVideo)
                                    <svg class="w-3 h-3 shrink-0 text-fuchsia-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                @else
                                    <svg class="w-3 h-3 shrink-0 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                @endif

                                {{-- Title --}}
                                <span class="truncate text-gray-300 font-medium">{{ Str::limit($item->title, 18) }}</span>

                                {{-- Status Dot --}}
                                <span class="shrink-0 w-1.5 h-1.5 rounded-full {{ $item->is_published ? 'bg-emerald-400' : 'bg-amber-400' }}"></span>
                            </div>

                            {{-- Hover Actions --}}
                            <div class="hidden group-hover:flex items-center gap-0.5 absolute top-0 right-0 bg-dark-800 border border-dark-700 rounded shadow-lg p-0.5 z-10">
                                <button wire:click="togglePublished({{ $item->id }})"
                                        class="p-1 text-gray-400 hover:text-emerald-400 transition-colors" title="Toggle published">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </button>
                                <a href="{{ route('admin.youtube.content-calendar.edit', $item) }}" wire:navigate
                                   class="p-1 text-gray-400 hover:text-primary-light transition-colors" title="Edit">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <button wire:click="delete({{ $item->id }})" wire:confirm="Delete this content item?"
                                        class="p-1 text-gray-400 hover:text-red-400 transition-colors" title="Delete">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>

        {{-- Empty State --}}
        @if($items->isEmpty())
            <div class="px-6 py-16 text-center">
                <div class="w-12 h-12 rounded-xl bg-dark-700 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <h3 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-1">No content planned this month</h3>
                <p class="text-sm text-gray-500 mb-4">Start planning your content schedule.</p>
                <a href="{{ route('admin.youtube.content-calendar.create') }}" wire:navigate
                   class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Content
                </a>
            </div>
        @endif
    </div>

    {{-- 7. GAP INDICATOR --}}
    @if(count($gapWeeks) > 0)
        <div class="bg-amber-500/10 border border-amber-500/20 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                <div>
                    <h3 class="text-sm font-medium text-amber-400 mb-1">Weeks with no content</h3>
                    <p class="text-sm text-gray-400">
                        @foreach($gapWeeks as $index => $weekStart)
                            Week of {{ \Carbon\Carbon::parse($weekStart)->format('M j') }}@if(!$loop->last), @endif
                        @endforeach
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>
