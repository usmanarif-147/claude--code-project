<div>
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span>Settings</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">Database Management</span>
    </div>

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Database Management</h1>
            <p class="text-sm text-gray-500 mt-1">Monitor tables, manage backups, and perform maintenance operations.</p>
        </div>
        <button wire:click="confirmEmptyAll"
                class="inline-flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-all duration-200 shadow-lg shadow-red-500/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Empty All Tables
        </button>
        <button wire:click="createFullBackup"
                wire:loading.attr="disabled"
                wire:target="createFullBackup"
                class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20 disabled:opacity-50 disabled:cursor-not-allowed">
            <svg wire:loading.remove wire:target="createFullBackup" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            <svg wire:loading wire:target="createFullBackup" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Create Full Backup
        </button>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mb-6 flex items-center gap-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl px-4 py-3">
            <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="text-sm text-emerald-400">{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 flex items-center gap-3 bg-red-500/10 border border-red-500/20 rounded-xl px-4 py-3">
            <svg class="w-5 h-5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="text-sm text-red-400">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Section 2: Analytics Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        {{-- Total Tables --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-white mb-1">{{ $analytics['total_tables'] }}</p>
            <p class="text-sm text-gray-500">Total Tables</p>
        </div>

        {{-- Total Rows --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-lg bg-blue-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-white mb-1">{{ $analytics['formatted_total_rows'] }}</p>
            <p class="text-sm text-gray-500">Total Rows</p>
        </div>

        {{-- Database Size --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-white mb-1">{{ $analytics['formatted_data_size'] }}</p>
            <p class="text-sm text-gray-500">Database Size</p>
        </div>

        {{-- Index Size --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-lg bg-amber-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-white mb-1">{{ $analytics['formatted_index_size'] }}</p>
            <p class="text-sm text-gray-500">Index Size</p>
        </div>
    </div>

    {{-- Section 3: Filter Bar --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl p-4 mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
            {{-- Search --}}
            <div class="flex-1 w-full sm:w-auto">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text"
                           wire:model.live.debounce.300ms="search"
                           placeholder="Search tables..."
                           class="w-full bg-dark-700 border border-dark-600 rounded-lg pl-10 pr-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                </div>
            </div>

            {{-- Engine Filter --}}
            <div class="w-full sm:w-44">
                <select wire:model.live="filterEngine"
                        class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                    <option value="">All Engines</option>
                    @foreach ($engines as $engine)
                        <option value="{{ $engine }}">{{ $engine }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Min Rows --}}
            <div class="w-full sm:w-40">
                <input type="number"
                       wire:model.live.debounce.500ms="filterMinRows"
                       placeholder="Min rows..."
                       min="0"
                       class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
            </div>

            {{-- Refresh --}}
            <button wire:click="refreshData"
                    class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 text-gray-300 text-sm font-medium rounded-lg px-4 py-2.5 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Refresh
            </button>
        </div>
    </div>

    {{-- Section 4: Tables List --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl mb-8">
        <div class="flex items-center justify-between px-6 py-4 border-b border-dark-700">
            <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider">Database Tables</h2>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary-light">
                {{ $tables->count() }} {{ $tables->count() === 1 ? 'table' : 'tables' }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-dark-700">
                        @php
                            $sortableColumns = [
                                'Name' => 'Table Name',
                                'Engine' => 'Engine',
                                'Rows' => 'Rows',
                                'Data_length' => 'Data Size',
                                'Index_length' => 'Index Size',
                                'Auto_increment' => 'Auto Inc',
                            ];
                        @endphp
                        @foreach ($sortableColumns as $column => $label)
                            <th wire:click="sortByColumn('{{ $column }}')"
                                class="px-6 py-3 text-left text-xs font-mono font-medium text-gray-400 uppercase tracking-wider cursor-pointer hover:text-white transition-colors">
                                <div class="flex items-center gap-1">
                                    {{ $label }}
                                    @if ($sortBy === $column)
                                        <svg class="w-3 h-3 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @if ($sortDirection === 'asc')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            @endif
                                        </svg>
                                    @else
                                        <svg class="w-3 h-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                        </svg>
                                    @endif
                                </div>
                            </th>
                        @endforeach
                        <th class="px-6 py-3 text-right text-xs font-mono font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700/50">
                    @forelse ($tables as $table)
                        <tr class="hover:bg-dark-700/50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="text-sm font-medium text-white font-mono">{{ $table->Name }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-dark-700 text-gray-300">
                                    {{ $table->Engine ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-400">
                                {{ number_format($table->Rows ?? 0) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-400">
                                {{ app(\App\Services\DatabaseManagementService::class)->formatBytes($table->Data_length ?? 0) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-400">
                                {{ app(\App\Services\DatabaseManagementService::class)->formatBytes($table->Index_length ?? 0) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-400">
                                {{ $table->Auto_increment ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="confirmEmpty('{{ $table->Name }}')"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-red-500/10 text-red-400 hover:bg-red-500/20 transition-colors"
                                            title="Empty table">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Empty
                                    </button>
                                    <button wire:click="createTableBackup('{{ $table->Name }}')"
                                            wire:loading.attr="disabled"
                                            wire:target="createTableBackup('{{ $table->Name }}')"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-primary/10 text-primary-light hover:bg-primary/20 transition-colors disabled:opacity-50"
                                            title="Backup table">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                        </svg>
                                        Backup
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <p class="text-sm text-gray-500">No tables found matching your filters.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Section 5: Empty Table Modal --}}
    @if ($showEmptyModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-data="{ countdown: @js($lockoutInfo['remaining_seconds']) }"
             x-init="if (countdown > 0) { let timer = setInterval(() => { countdown--; if (countdown <= 0) { clearInterval(timer); $wire.refreshData(); } }, 1000); }"
        >
            {{-- Overlay --}}
            <div class="absolute inset-0 bg-dark-950/80" wire:click="cancelEmpty"></div>

            {{-- Modal --}}
            <div class="relative bg-dark-800 border border-dark-700 rounded-xl w-full max-w-lg p-6 shadow-2xl">
                {{-- Title --}}
                <h2 class="text-lg font-mono font-bold text-red-400 uppercase tracking-wider mb-4">
                    @if ($emptyAllMode)
                        Empty All Tables
                    @else
                        Empty Table: {{ $emptyingTable }}
                    @endif
                </h2>

                {{-- Empty All Warning --}}
                @if ($emptyAllMode)
                    <div class="mb-4 bg-red-500/10 border border-red-500/20 rounded-lg p-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-red-400 mb-1">This will empty ALL database tables</p>
                                <p class="text-xs text-gray-400 mb-2">All data will be permanently deleted. Only the <span class="text-white font-mono">users</span> table is preserved so you can still log in.</p>
                                <p class="text-xs text-gray-400 mb-2">All uploaded files (images, documents) in storage will also be deleted.</p>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-500/10 text-red-400">
                                    This action cannot be undone
                                </span>
                            </div>
                        </div>
                    </div>

                {{-- Single Table Dependencies Warning --}}
                @elseif (!empty($tableDependencies['referenced_by']))
                    <div class="mb-4 bg-red-500/10 border border-red-500/20 rounded-lg p-4">
                        <p class="text-sm font-medium text-red-400 mb-2">This table is referenced by:</p>
                        <ul class="space-y-1">
                            @foreach ($tableDependencies['referenced_by'] as $dep)
                                <li class="text-xs text-gray-400">
                                    <span class="text-white font-mono">{{ $dep['table'] }}</span>
                                    <span class="text-gray-500">via</span>
                                    <span class="text-amber-400 font-mono">{{ $dep['column'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <div class="mt-3">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-500/10 text-red-400">
                                Emptying this table may break foreign key relationships
                            </span>
                        </div>
                    </div>
                @elseif (!empty($tableDependencies['references']))
                    <div class="mb-4 bg-amber-500/10 border border-amber-500/20 rounded-lg p-4">
                        <p class="text-sm font-medium text-amber-400 mb-2">This table references:</p>
                        <ul class="space-y-1">
                            @foreach ($tableDependencies['references'] as $dep)
                                <li class="text-xs text-gray-400">
                                    <span class="text-white font-mono">{{ $dep['table'] }}</span>
                                    <span class="text-gray-500">via</span>
                                    <span class="text-amber-400 font-mono">{{ $dep['column'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <div class="mb-4 bg-emerald-500/10 border border-emerald-500/20 rounded-lg p-3">
                        <p class="text-sm text-emerald-400">No dependencies found for this table.</p>
                    </div>
                @endif

                {{-- Password Input --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Confirm your password to proceed
                    </label>
                    <input type="password"
                           wire:model="password"
                           wire:keydown.enter="executeEmpty"
                           placeholder="Enter your password"
                           @if($lockoutInfo['locked']) disabled @endif
                           class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">

                    @if ($passwordError)
                        <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $passwordError }}
                        </p>
                    @endif
                </div>

                {{-- Lockout / Attempts Info --}}
                <div class="mb-4">
                    @if ($lockoutInfo['locked'])
                        <div class="flex items-center gap-2 text-sm text-red-400 bg-red-500/10 rounded-lg px-3 py-2">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <span>Locked out. Try again in <strong x-text="Math.ceil(countdown / 60)">{{ ceil($lockoutInfo['remaining_seconds'] / 60) }}</strong> minute(s).</span>
                        </div>
                    @else
                        <p class="text-xs text-gray-500">
                            {{ $lockoutInfo['max_attempts'] - $lockoutInfo['attempts_used'] }} of {{ $lockoutInfo['max_attempts'] }} attempts remaining
                        </p>
                    @endif
                </div>

                {{-- Buttons --}}
                <div class="flex items-center justify-end gap-3">
                    <button wire:click="cancelEmpty"
                            class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 text-gray-300 text-sm font-medium rounded-lg px-4 py-2.5 transition-colors">
                        Cancel
                    </button>
                    <button wire:click="executeEmpty"
                            wire:loading.attr="disabled"
                            wire:target="executeEmpty"
                            @if($lockoutInfo['locked']) disabled @endif
                            class="inline-flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg wire:loading wire:target="executeEmpty" class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ $emptyAllMode ? 'Confirm Empty All' : 'Confirm Empty' }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Section 6: Backups Card --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-dark-700">
            <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider">Database Backups</h2>
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary-light">
                {{ count($backups) }} {{ count($backups) === 1 ? 'backup' : 'backups' }}
            </span>
        </div>

        @if (count($backups) > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-dark-700">
                            <th class="px-6 py-3 text-left text-xs font-mono font-medium text-gray-400 uppercase tracking-wider">Filename</th>
                            <th class="px-6 py-3 text-left text-xs font-mono font-medium text-gray-400 uppercase tracking-wider">Size</th>
                            <th class="px-6 py-3 text-left text-xs font-mono font-medium text-gray-400 uppercase tracking-wider">Created</th>
                            <th class="px-6 py-3 text-right text-xs font-mono font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-dark-700/50">
                        @foreach ($backups as $backup)
                            <tr class="hover:bg-dark-700/50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="text-sm text-white font-mono">{{ $backup['name'] }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-400">
                                    {{ $backup['formatted_size'] }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-400">
                                    {{ $backup['created_at'] }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <button wire:click="downloadBackup('{{ $backup['name'] }}')"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-primary/10 text-primary-light hover:bg-primary/20 transition-colors"
                                                title="Download backup">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                            Download
                                        </button>
                                        <button wire:click="deleteBackup('{{ $backup['name'] }}')"
                                                wire:confirm="Are you sure you want to delete this backup?"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-red-500/10 text-red-400 hover:bg-red-500/20 transition-colors"
                                                title="Delete backup">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <svg class="w-12 h-12 mx-auto text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                <p class="text-sm text-gray-500">No backups found. Create your first backup using the button above.</p>
            </div>
        @endif
    </div>
</div>
