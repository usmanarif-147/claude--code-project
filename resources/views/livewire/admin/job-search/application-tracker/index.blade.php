<div>
    {{-- 1. BREADCRUMB --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-400">Job Search</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">Applications</span>
    </div>

    {{-- 2. PAGE HEADER --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Applications</h1>
            <p class="text-gray-500 mt-1">Track your job applications through their lifecycle.</p>
        </div>
        <a href="{{ route('admin.job-search.applications.create') }}" wire:navigate
           class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Application
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

    {{-- 3. STAT CARDS ROW --}}
    @php
        $statCards = [
            ['key' => 'saved', 'label' => 'Saved', 'bg' => 'bg-blue-500/10', 'text' => 'text-blue-400', 'icon' => 'M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z'],
            ['key' => 'applied', 'label' => 'Applied', 'bg' => 'bg-primary/10', 'text' => 'text-primary-light', 'icon' => 'M12 19l9 2-9-18-9 18 9-2zm0 0v-8'],
            ['key' => 'interview', 'label' => 'Interview', 'bg' => 'bg-amber-500/10', 'text' => 'text-amber-400', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
            ['key' => 'offer', 'label' => 'Offer', 'bg' => 'bg-emerald-500/10', 'text' => 'text-emerald-400', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['key' => 'rejected', 'label' => 'Rejected', 'bg' => 'bg-red-500/10', 'text' => 'text-red-400', 'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
        ];
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
        @foreach($statCards as $card)
            <div class="bg-dark-800 border border-dark-700 rounded-xl p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 {{ $card['bg'] }} rounded-lg flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 {{ $card['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $card['icon'] }}"/></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-white">{{ $statusCounts[$card['key']] ?? 0 }}</p>
                        <p class="text-xs text-gray-500">{{ $card['label'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- 4. SEARCH BAR --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl p-4 mb-6">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" wire:model.live.debounce.300ms="search"
                   class="w-full bg-dark-700 border border-dark-600 rounded-lg pl-10 pr-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                   placeholder="Search by company or position...">
        </div>
    </div>

    {{-- 5. KANBAN BOARD --}}
    @php
        $totalApps = collect($applications)->flatten()->count();
        $columnConfig = [
            'saved'     => ['label' => 'Saved',     'border' => 'border-t-blue-500',    'badge_bg' => 'bg-blue-500/10',    'badge_text' => 'text-blue-400'],
            'applied'   => ['label' => 'Applied',   'border' => 'border-t-primary',     'badge_bg' => 'bg-primary/10',     'badge_text' => 'text-primary-light'],
            'interview' => ['label' => 'Interview', 'border' => 'border-t-amber-500',   'badge_bg' => 'bg-amber-500/10',   'badge_text' => 'text-amber-400'],
            'offer'     => ['label' => 'Offer',     'border' => 'border-t-emerald-500', 'badge_bg' => 'bg-emerald-500/10', 'badge_text' => 'text-emerald-400'],
            'rejected'  => ['label' => 'Rejected',  'border' => 'border-t-red-500',     'badge_bg' => 'bg-red-500/10',     'badge_text' => 'text-red-400'],
        ];
    @endphp

    @if($totalApps === 0 && !$search)
        {{-- EMPTY STATE --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl px-6 py-16 text-center">
            <div class="w-16 h-16 rounded-xl bg-dark-700 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <h3 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-1">No applications yet</h3>
            <p class="text-sm text-gray-500 mb-6">Start tracking your job applications by creating your first one.</p>
            <a href="{{ route('admin.job-search.applications.create') }}" wire:navigate
               class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Your First Application
            </a>
        </div>
    @else
        <div x-data="kanbanBoard()" class="flex gap-4 overflow-x-auto pb-4" style="min-height: 500px;">
            @foreach($columnConfig as $status => $config)
                <div class="flex-shrink-0 w-72 xl:w-80 flex flex-col">
                    {{-- Column --}}
                    <div class="bg-dark-800 border border-dark-700 {{ $config['border'] }} border-t-2 rounded-xl flex flex-col h-full">
                        {{-- Column Header --}}
                        <div class="p-4 border-b border-dark-700/50">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">{{ $config['label'] }}</h3>
                                <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $config['badge_bg'] }} {{ $config['badge_text'] }}">
                                    {{ count($applications[$status] ?? []) }}
                                </span>
                            </div>
                        </div>

                        {{-- Column Body (Droppable Zone) --}}
                        <div class="p-3 flex-1 space-y-3 overflow-y-auto"
                             data-status="{{ $status }}"
                             x-on:dragover.prevent="onDragOver($event)"
                             x-on:dragleave="onDragLeave($event)"
                             x-on:drop="onDrop($event, '{{ $status }}')">

                            @forelse($applications[$status] ?? [] as $app)
                                {{-- Application Card --}}
                                <div class="bg-dark-700 rounded-lg p-4 cursor-grab active:cursor-grabbing hover:bg-dark-600/80 transition-colors group border border-transparent hover:border-dark-600"
                                     draggable="true"
                                     data-id="{{ $app->id }}"
                                     x-on:dragstart="onDragStart($event, {{ $app->id }})"
                                     x-on:dragend="onDragEnd($event)">

                                    {{-- Card Header: Company + Actions --}}
                                    <div class="flex items-start justify-between gap-2 mb-2">
                                        <h4 class="text-sm font-semibold text-white leading-tight">{{ $app->company }}</h4>
                                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity shrink-0">
                                            <a href="{{ route('admin.job-search.applications.edit', $app->id) }}" wire:navigate
                                               class="text-gray-400 hover:text-primary-light transition-colors p-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </a>
                                            <button wire:click="deleteApplication({{ $app->id }})" wire:confirm="Are you sure you want to delete this application?"
                                                    class="text-gray-400 hover:text-red-400 transition-colors p-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Position --}}
                                    <p class="text-xs text-gray-400 mb-3">{{ $app->position }}</p>

                                    {{-- Meta Row --}}
                                    <div class="flex items-center flex-wrap gap-2">
                                        @if($app->applied_date)
                                            <span class="inline-flex items-center gap-1 text-xs text-gray-500">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                {{ $app->applied_date->format('M j, Y') }}
                                            </span>
                                        @endif
                                        @if($app->salary_offered)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400">
                                                {{ $app->salary_offered }}
                                            </span>
                                        @endif
                                    </div>

                                    {{-- URL indicator --}}
                                    @if($app->url)
                                        <div class="mt-2">
                                            <a href="{{ $app->url }}" target="_blank" rel="noopener noreferrer"
                                               class="inline-flex items-center gap-1 text-xs text-primary-light hover:text-white transition-colors"
                                               @click.stop>
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                View Posting
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <div class="flex items-center justify-center h-24 text-center">
                                    <p class="text-xs text-gray-600">No applications</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@script
<script>
    Alpine.data('kanbanBoard', () => ({
        draggedId: null,

        onDragStart(event, id) {
            this.draggedId = id;
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', id);
            event.target.classList.add('opacity-50');
        },

        onDragEnd(event) {
            event.target.classList.remove('opacity-50');
            this.draggedId = null;
            // Remove all drop zone highlights
            document.querySelectorAll('[data-status]').forEach(el => {
                el.classList.remove('bg-primary/5');
            });
        },

        onDragOver(event) {
            event.preventDefault();
            event.dataTransfer.dropEffect = 'move';
            event.currentTarget.classList.add('bg-primary/5');
        },

        onDragLeave(event) {
            event.currentTarget.classList.remove('bg-primary/5');
        },

        onDrop(event, newStatus) {
            event.preventDefault();
            event.currentTarget.classList.remove('bg-primary/5');

            const id = parseInt(event.dataTransfer.getData('text/plain'));
            if (!id) return;

            // Calculate new sort order based on drop position
            const dropZone = event.currentTarget;
            const cards = Array.from(dropZone.querySelectorAll('[data-id]'));
            let newSortOrder = cards.length;

            // Find position based on mouse Y
            for (let i = 0; i < cards.length; i++) {
                const rect = cards[i].getBoundingClientRect();
                const midY = rect.top + rect.height / 2;
                if (event.clientY < midY) {
                    newSortOrder = i;
                    break;
                }
            }

            $wire.updateCardStatus(id, newStatus, newSortOrder);
        }
    }));
</script>
@endscript
