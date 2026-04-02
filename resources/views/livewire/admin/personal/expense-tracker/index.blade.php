<div>
    {{-- 1. BREADCRUMB --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-400">Personal</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">Expense Tracker</span>
    </div>

    {{-- 2. PAGE HEADER --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Expense Tracker</h1>
            <p class="text-sm text-gray-500 mt-1">Track and manage your daily expenses.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.personal.expense-tracker.categories') }}" wire:navigate
               class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 border border-dark-600 text-gray-300 hover:text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                Categories
            </a>
            <a href="{{ route('admin.personal.expense-tracker.create') }}" wire:navigate
               class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Expense
            </a>
        </div>
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
        {{-- Today's Spending --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors"
             x-data x-init="
                $el.style.opacity = '0';
                $el.style.transform = 'translateY(12px)';
                setTimeout(() => {
                    $el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                    $el.style.opacity = '1';
                    $el.style.transform = 'translateY(0)';
                }, 50)">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-white mb-1">${{ number_format($todayTotal, 2) }}</p>
            <p class="text-sm text-gray-500">Today's Spending</p>
        </div>

        {{-- This Week --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors"
             x-data x-init="
                $el.style.opacity = '0';
                $el.style.transform = 'translateY(12px)';
                setTimeout(() => {
                    $el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                    $el.style.opacity = '1';
                    $el.style.transform = 'translateY(0)';
                }, 150)">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-lg bg-blue-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-white mb-1">${{ number_format($weekTotal, 2) }}</p>
            <p class="text-sm text-gray-500">This Week</p>
        </div>

        {{-- This Month --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors"
             x-data x-init="
                $el.style.opacity = '0';
                $el.style.transform = 'translateY(12px)';
                setTimeout(() => {
                    $el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                    $el.style.opacity = '1';
                    $el.style.transform = 'translateY(0)';
                }, 250)">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-white mb-1">${{ number_format($monthTotal, 2) }}</p>
            <p class="text-sm text-gray-500">This Month</p>
        </div>

        {{-- Budget Remaining --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors"
             x-data x-init="
                $el.style.opacity = '0';
                $el.style.transform = 'translateY(12px)';
                setTimeout(() => {
                    $el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                    $el.style.opacity = '1';
                    $el.style.transform = 'translateY(0)';
                }, 350)">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-lg {{ $budgetRemaining !== null && $budgetRemaining < 0 ? 'bg-red-500/10' : 'bg-amber-500/10' }} flex items-center justify-center">
                    <svg class="w-5 h-5 {{ $budgetRemaining !== null && $budgetRemaining < 0 ? 'text-red-400' : 'text-amber-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
            </div>
            @if($budgetRemaining !== null)
                <p class="text-3xl font-bold {{ $budgetRemaining < 0 ? 'text-red-400' : 'text-white' }} mb-1">
                    {{ $budgetRemaining < 0 ? '-' : '' }}${{ number_format(abs($budgetRemaining), 2) }}
                </p>
            @else
                <p class="text-3xl font-bold text-gray-500 mb-1">--</p>
            @endif
            <p class="text-sm text-gray-500">Budget Remaining</p>
        </div>
    </div>

    {{-- 4. TWO-COLUMN LAYOUT: Table + Sidebar --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        {{-- Left: Expense Table (2/3) --}}
        <div class="xl:col-span-2">
            {{-- Filter Bar --}}
            <div class="bg-dark-800 border border-dark-700 rounded-xl p-4 mb-5">
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                        <input type="text" wire:model.live.debounce.300ms="search"
                               placeholder="Search notes..."
                               class="w-full bg-dark-700 border border-dark-600 rounded-lg pl-9 pr-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                    </div>
                    <select wire:model.live="filterCategory"
                            class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-primary focus:border-transparent transition-all min-w-[140px]">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <input type="month" wire:model.live="filterMonth"
                           class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-primary focus:border-transparent transition-all min-w-[160px]">
                </div>
            </div>

            {{-- Expense Table --}}
            <div class="bg-dark-800 border border-dark-700 rounded-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-dark-700">
                                <th class="px-6 py-4 text-left text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-4 text-left text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-4 text-right text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-6 py-4 text-left text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">Note</th>
                                <th class="px-6 py-4 text-right text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-dark-700/50">
                            @forelse($expenses as $expense)
                                <tr class="hover:bg-dark-700/30 transition-colors duration-150 group">
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-white">{{ $expense->spent_at->format('M j, Y') }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium"
                                              style="background-color: {{ $expense->category->color }}15; color: {{ $expense->category->color }};">
                                            <span class="w-1.5 h-1.5 rounded-full" style="background-color: {{ $expense->category->color }};"></span>
                                            {{ $expense->category->name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="text-sm font-medium text-white">${{ number_format($expense->amount, 2) }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-gray-400">{{ $expense->note ?? '-' }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('admin.personal.expense-tracker.edit', $expense) }}" wire:navigate
                                               class="p-2 text-gray-400 hover:text-primary-light hover:bg-primary/10 rounded-lg transition-all duration-200">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </a>
                                            <button wire:click="delete({{ $expense->id }})" wire:confirm="Delete this expense?"
                                                    class="p-2 text-gray-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-all duration-200">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-16 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <div class="w-14 h-14 rounded-xl bg-dark-700 flex items-center justify-center">
                                                <svg class="w-7 h-7 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-300">No expenses logged yet</p>
                                                <p class="text-xs text-gray-500 mt-1">Start tracking your spending!</p>
                                            </div>
                                            <a href="{{ route('admin.personal.expense-tracker.create') }}" wire:navigate
                                               class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-4 py-2 transition-all duration-200 shadow-lg shadow-primary/20 mt-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                Add Expense
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($expenses->hasPages())
                    <div class="px-6 py-4 border-t border-dark-700 flex items-center justify-between">
                        <p class="text-sm text-gray-500">Showing {{ $expenses->firstItem() }}-{{ $expenses->lastItem() }} of {{ $expenses->total() }} results</p>
                        {{ $expenses->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Right: Chart + Budget (1/3) --}}
        <div class="space-y-6">
            {{-- Category Breakdown Chart --}}
            <div class="bg-dark-800 border border-dark-700 rounded-xl"
                 x-data x-init="
                    $el.style.opacity = '0';
                    $el.style.transform = 'translateY(12px)';
                    setTimeout(() => {
                        $el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                        $el.style.opacity = '1';
                        $el.style.transform = 'translateY(0)';
                    }, 100)">
                <div class="flex items-center justify-between px-6 py-4 border-b border-dark-700">
                    <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider">By Category</h2>
                </div>
                <div class="p-6">
                    @if(count($categoryBreakdown) > 0)
                        <div class="space-y-3">
                            @php $maxTotal = max(array_column($categoryBreakdown, 'total')); @endphp
                            @foreach($categoryBreakdown as $item)
                                <div>
                                    <div class="flex items-center justify-between mb-1.5">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $item['color'] }};"></span>
                                            <span class="text-sm text-gray-300">{{ $item['name'] }}</span>
                                        </div>
                                        <span class="text-sm font-medium text-white">${{ number_format($item['total'], 2) }}</span>
                                    </div>
                                    <div class="w-full bg-dark-700 rounded-full h-1.5">
                                        <div class="h-1.5 rounded-full transition-all duration-500"
                                             style="width: {{ $maxTotal > 0 ? ($item['total'] / $maxTotal) * 100 : 0 }}%; background-color: {{ $item['color'] }};"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 text-center py-4">No expenses this month.</p>
                    @endif
                </div>
            </div>

            {{-- Budget Card --}}
            <div class="bg-dark-800 border border-dark-700 rounded-xl"
                 x-data x-init="
                    $el.style.opacity = '0';
                    $el.style.transform = 'translateY(12px)';
                    setTimeout(() => {
                        $el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                        $el.style.opacity = '1';
                        $el.style.transform = 'translateY(0)';
                    }, 200)">
                <div class="flex items-center justify-between px-6 py-4 border-b border-dark-700">
                    <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider">Monthly Budget</h2>
                </div>
                <div class="p-6">
                    @if($budgetAmount)
                        {{-- Progress Bar --}}
                        @php
                            $percentage = $budgetAmount > 0 ? min(($monthTotal / $budgetAmount) * 100, 100) : 0;
                            $isOverBudget = $budgetRemaining !== null && $budgetRemaining < 0;
                        @endphp
                        <div class="mb-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-gray-400">${{ number_format($monthTotal, 2) }} spent of ${{ number_format($budgetAmount, 2) }}</span>
                            </div>
                            <div class="w-full bg-dark-700 rounded-full h-2.5">
                                <div class="h-2.5 rounded-full transition-all duration-500 {{ $isOverBudget ? 'bg-red-500' : 'bg-gradient-to-r from-primary to-fuchsia-500' }}"
                                     style="width: {{ $isOverBudget ? 100 : $percentage }}%"></div>
                            </div>
                            <p class="text-sm mt-2 {{ $isOverBudget ? 'text-red-400' : 'text-gray-400' }}">
                                @if($isOverBudget)
                                    ${{ number_format(abs($budgetRemaining), 2) }} over budget
                                @else
                                    ${{ number_format($budgetRemaining, 2) }} remaining
                                @endif
                            </p>
                        </div>
                    @else
                        <p class="text-sm text-gray-500 mb-4">No budget set for this month.</p>
                    @endif

                    {{-- Set Budget Form --}}
                    <form wire:submit="setBudget" class="flex gap-2">
                        <input type="number" wire:model="newBudgetAmount" step="0.01" min="0.01" placeholder="Budget amount"
                               class="flex-1 bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                        <button type="submit"
                                class="bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20"
                                wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="setBudget">Set</span>
                            <span wire:loading wire:target="setBudget">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            </span>
                        </button>
                    </form>
                    @error('newBudgetAmount')
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
