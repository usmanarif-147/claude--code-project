<div>
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

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-400">Job Search</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">Saved Searches</span>
    </div>

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Saved Searches</h1>
            <p class="text-sm text-gray-500 mt-1">Create and manage reusable job search filter presets.</p>
        </div>
        <a href="{{ route('admin.job-search.saved-searches.create') }}" wire:navigate
           class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Search
        </a>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl p-4 mb-5">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Search by name..."
                       class="w-full bg-dark-700 border border-dark-600 rounded-lg pl-9 pr-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
            </div>
            <select wire:model.live="filterStatus"
                    class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-primary focus:border-transparent transition-all min-w-[140px]">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-dark-700">
                        <th class="px-6 py-4 text-left text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-4 text-left text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">Filters</th>
                        <th class="px-6 py-4 text-left text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">Platforms</th>
                        <th class="px-6 py-4 text-left text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700/50">
                    @forelse($savedSearches as $savedSearch)
                        <tr class="hover:bg-dark-700/30 transition-colors duration-150 group">
                            {{-- Name --}}
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-sm font-medium text-white">{{ $savedSearch->name }}</p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        @if($savedSearch->location_type)
                                            {{ ucfirst($savedSearch->location_type) }}
                                        @endif
                                        @if($savedSearch->preferred_tech)
                                            @if($savedSearch->location_type) &middot; @endif
                                            {{ count($savedSearch->preferred_tech) }} tech
                                        @endif
                                        @if($savedSearch->enabled_platforms)
                                            @if($savedSearch->location_type || $savedSearch->preferred_tech) &middot; @endif
                                            {{ count($savedSearch->enabled_platforms) }} platforms
                                        @endif
                                    </p>
                                </div>
                            </td>

                            {{-- Filters --}}
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1.5">
                                    @if($savedSearch->location_type)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-500/10 text-blue-400">
                                            {{ ucfirst($savedSearch->location_type) }}
                                        </span>
                                    @endif
                                    @if($savedSearch->preferred_titles)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-fuchsia-500/10 text-fuchsia-400">
                                            {{ count($savedSearch->preferred_titles) }} {{ Str::plural('title', count($savedSearch->preferred_titles)) }}
                                        </span>
                                    @endif
                                    @if($savedSearch->preferred_tech)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400">
                                            {{ count($savedSearch->preferred_tech) }} tech
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Platforms --}}
                            <td class="px-6 py-4">
                                @if($savedSearch->enabled_platforms)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary-light">
                                        {{ count($savedSearch->enabled_platforms) }} {{ Str::plural('platform', count($savedSearch->enabled_platforms)) }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-500">All</span>
                                @endif
                            </td>

                            {{-- Status Toggle --}}
                            <td class="px-6 py-4">
                                <button wire:click="toggleActive({{ $savedSearch->id }})" class="inline-flex items-center gap-1.5 cursor-pointer">
                                    @if($savedSearch->is_active)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-500/10 text-gray-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span>
                                            Inactive
                                        </span>
                                    @endif
                                </button>
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.job-search.saved-searches.edit', $savedSearch) }}" wire:navigate
                                       class="p-2 text-gray-400 hover:text-primary-light hover:bg-primary/10 rounded-lg transition-all duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <x-admin.confirm-button
                                        title="Delete Saved Search?"
                                        text="Are you sure you want to delete this saved search?"
                                        action="$wire.delete({{ $savedSearch->id }})"
                                        confirm-text="Yes, delete it"
                                        class="p-2 text-gray-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-all duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </x-admin.confirm-button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-14 h-14 rounded-xl bg-primary/10 flex items-center justify-center">
                                        <svg class="w-7 h-7 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-mono font-medium text-gray-300 uppercase tracking-wider">No saved searches yet</p>
                                        <p class="text-xs text-gray-500 mt-1">Create your first saved search to start automating job discovery.</p>
                                    </div>
                                    <a href="{{ route('admin.job-search.saved-searches.create') }}" wire:navigate
                                       class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-4 py-2 transition-all duration-200 shadow-lg shadow-primary/20 mt-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        Create Saved Search
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($savedSearches->hasPages())
            <div class="px-6 py-4 border-t border-dark-700 flex items-center justify-between">
                <p class="text-sm text-gray-500">Showing {{ $savedSearches->firstItem() }}&ndash;{{ $savedSearches->lastItem() }} of {{ $savedSearches->total() }} results</p>
                {{ $savedSearches->links() }}
            </div>
        @endif
    </div>
</div>
