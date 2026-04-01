<div>
    @php
        $colorMap = [
            'emerald' => ['bg' => 'bg-emerald-500', 'text' => 'text-emerald-400', 'bgLight' => 'bg-emerald-500/10'],
            'blue' => ['bg' => 'bg-blue-500', 'text' => 'text-blue-400', 'bgLight' => 'bg-blue-500/10'],
            'amber' => ['bg' => 'bg-amber-500', 'text' => 'text-amber-400', 'bgLight' => 'bg-amber-500/10'],
            'primary' => ['bg' => 'bg-primary', 'text' => 'text-primary-light', 'bgLight' => 'bg-primary/10'],
            'gray' => ['bg' => 'bg-gray-500', 'text' => 'text-gray-400', 'bgLight' => 'bg-gray-500/10'],
            'red' => ['bg' => 'bg-red-500', 'text' => 'text-red-400', 'bgLight' => 'bg-red-500/10'],
            'fuchsia' => ['bg' => 'bg-fuchsia-500', 'text' => 'text-fuchsia-400', 'bgLight' => 'bg-fuchsia-500/10'],
            'cyan' => ['bg' => 'bg-cyan-500', 'text' => 'text-cyan-400', 'bgLight' => 'bg-cyan-500/10'],
        ];
    @endphp

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-400">Email</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">Categorization</span>
    </div>

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Email Categorization</h1>
            <p class="text-sm text-gray-500 mt-1">AI-powered email classification and manual corrections.</p>
        </div>
        <button wire:click="categorizeAll"
                class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20 disabled:opacity-50 disabled:cursor-not-allowed"
                wire:loading.attr="disabled" wire:target="categorizeAll">
            <span wire:loading.remove wire:target="categorizeAll">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </span>
            <span wire:loading wire:target="categorizeAll">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            </span>
            <span wire:loading.remove wire:target="categorizeAll">Categorize All</span>
            <span wire:loading wire:target="categorizeAll">Processing...</span>
        </button>
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

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        {{-- Total Categorized --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-white mb-1">{{ number_format($totalCategorized) }}</p>
            <p class="text-sm text-gray-500">Total Categorized</p>
        </div>

        {{-- Uncategorized --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-lg bg-amber-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-white mb-1">{{ number_format($uncategorizedCount) }}</p>
            <p class="text-sm text-gray-500">Uncategorized</p>
        </div>

        {{-- AI Accuracy --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-lg bg-blue-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-white mb-1">{{ $accuracyRate }}%</p>
            <p class="text-sm text-gray-500">AI Accuracy</p>
        </div>

        {{-- Total Corrections --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-lg bg-fuchsia-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-fuchsia-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-white mb-1">{{ number_format($totalCorrections) }}</p>
            <p class="text-sm text-gray-500">Total Corrections</p>
        </div>
    </div>

    {{-- Category Filter Tabs --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl p-4 mb-5">
        <div class="flex flex-wrap gap-2">
            {{-- All Tab --}}
            <button wire:click="filterByCategory(null)"
                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-medium transition-all duration-200 {{ $selectedCategoryId === null ? 'bg-primary/10 text-primary-light' : 'bg-dark-700 text-gray-400 hover:text-white hover:bg-dark-600' }}">
                All
                <span class="text-xs opacity-75">{{ array_sum($categoryStats) + $uncategorizedCount }}</span>
            </button>

            {{-- Category Tabs --}}
            @foreach($categories as $cat)
                @php $catColors = $colorMap[$cat->color] ?? $colorMap['gray']; @endphp
                <button wire:click="filterByCategory({{ $cat->id }})"
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-medium transition-all duration-200 {{ $selectedCategoryId === $cat->id ? 'bg-primary/10 text-primary-light' : 'bg-dark-700 text-gray-400 hover:text-white hover:bg-dark-600' }}">
                    <span class="w-2 h-2 rounded-full {{ $catColors['bg'] }}"></span>
                    {{ $cat->name }}
                    <span class="text-xs opacity-75">{{ $categoryStats[$cat->id] ?? 0 }}</span>
                </button>
            @endforeach

            {{-- Uncategorized Tab --}}
            <button wire:click="filterByCategory(0)"
                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-medium transition-all duration-200 {{ $selectedCategoryId === 0 ? 'bg-primary/10 text-primary-light' : 'bg-dark-700 text-gray-400 hover:text-white hover:bg-dark-600' }}">
                <span class="w-2 h-2 rounded-full bg-gray-500"></span>
                Uncategorized
                <span class="text-xs opacity-75">{{ $uncategorizedCount }}</span>
            </button>
        </div>
    </div>

    {{-- Search Bar --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl p-4 mb-5">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Search by subject or sender..."
                   class="w-full bg-dark-700 border border-dark-600 rounded-lg pl-9 pr-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
        </div>
    </div>

    {{-- Email List Table --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-dark-700">
                        <th class="px-6 py-4 text-left text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">From</th>
                        <th class="px-6 py-4 text-left text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                        <th class="px-6 py-4 text-left text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">Received</th>
                        <th class="px-6 py-4 text-left text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700/50">
                    @forelse($emails as $email)
                        <tr class="hover:bg-dark-700/30 transition-colors duration-150">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-sm font-medium text-white">{{ $email->from_name ?: 'Unknown' }}</p>
                                    <p class="text-xs text-gray-500">{{ $email->from_email }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-300 truncate max-w-xs">{{ $email->subject ?: '(no subject)' }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-400">{{ $email->received_at?->format('M d, Y') }}</p>
                                <p class="text-xs text-gray-500">{{ $email->received_at?->format('h:i A') }}</p>
                            </td>
                            <td class="px-6 py-4">
                                <select wire:change="reassignCategory({{ $email->id }}, $event.target.value)"
                                        class="bg-dark-700 border border-dark-600 rounded-lg px-3 py-1.5 text-sm text-white focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                    <option value="" disabled {{ !$email->category_id ? 'selected' : '' }}>Uncategorized</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ $email->category_id == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-6 h-6 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </div>
                                <h3 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-2">No Emails Found</h3>
                                <p class="text-sm text-gray-500">No emails match the current filters.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($emails->hasPages())
            <div class="px-6 py-4 border-t border-dark-700 flex items-center justify-between">
                <p class="text-sm text-gray-500">Showing {{ $emails->firstItem() }}-{{ $emails->lastItem() }} of {{ $emails->total() }} results</p>
                {{ $emails->links() }}
            </div>
        @endif
    </div>
</div>
