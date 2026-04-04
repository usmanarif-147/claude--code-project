<div>
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span>Settings</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">Logs</span>
    </div>

    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Error Logs</h1>
        <p class="text-gray-500 mt-1">View and manage application log files.</p>
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

    {{-- Toolbar --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl p-4 mb-6">
        <div class="flex flex-col lg:flex-row items-start lg:items-center gap-4">
            {{-- File Selector --}}
            <div class="w-full lg:w-auto lg:min-w-[220px]">
                <select
                    wire:model.live="selectedFile"
                    class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-primary focus:border-transparent"
                >
                    @if (count($logFiles) === 0)
                        <option value="">No log files found</option>
                    @else
                        @foreach ($logFiles as $file)
                            <option value="{{ $file['name'] }}">{{ $file['name'] }}</option>
                        @endforeach
                    @endif
                </select>
            </div>

            {{-- Level Filter --}}
            <div class="w-full lg:w-auto lg:min-w-[160px]">
                <select
                    wire:model.live="levelFilter"
                    class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-primary focus:border-transparent"
                >
                    <option value="">All Levels</option>
                    <option value="emergency">Emergency</option>
                    <option value="alert">Alert</option>
                    <option value="critical">Critical</option>
                    <option value="error">Error</option>
                    <option value="warning">Warning</option>
                    <option value="notice">Notice</option>
                    <option value="info">Info</option>
                    <option value="debug">Debug</option>
                </select>
            </div>

            {{-- Search --}}
            <div class="w-full lg:flex-1">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search log entries..."
                    class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 text-sm focus:ring-2 focus:ring-primary focus:border-transparent"
                >
            </div>

            {{-- Action Buttons --}}
            @if ($selectedFile)
                <div class="flex items-center gap-2 shrink-0">
                    <button
                        wire:click="clearFile"
                        wire:confirm="Are you sure you want to clear this log file? This cannot be undone."
                        class="bg-amber-500/10 text-amber-400 hover:bg-amber-500/20 rounded-lg px-4 py-2.5 text-sm font-medium transition-colors"
                    >
                        Clear
                    </button>
                    <button
                        wire:click="deleteFile"
                        wire:confirm="Are you sure you want to delete this log file? This cannot be undone."
                        class="bg-red-500/10 text-red-400 hover:bg-red-500/20 rounded-lg px-4 py-2.5 text-sm font-medium transition-colors"
                    >
                        Delete
                    </button>
                    <button
                        wire:click="downloadFile"
                        class="bg-primary hover:bg-primary-hover text-white rounded-lg px-4 py-2.5 text-sm font-medium transition-colors"
                    >
                        Download
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Stats Bar --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-white">{{ count($logFiles) }}</p>
                <p class="text-xs text-gray-500">Log Files</p>
            </div>
        </div>

        <div class="bg-dark-800 border border-dark-700 rounded-xl p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-blue-500/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                </svg>
            </div>
            <div>
                @php
                    $currentFileSize = 0;
                    if ($selectedFile) {
                        foreach ($logFiles as $f) {
                            if ($f['name'] === $selectedFile) {
                                $currentFileSize = $f['size'];
                                break;
                            }
                        }
                    }
                @endphp
                <p class="text-2xl font-bold text-white">
                    @if ($currentFileSize >= 1048576)
                        {{ number_format($currentFileSize / 1048576, 1) }} MB
                    @elseif ($currentFileSize >= 1024)
                        {{ number_format($currentFileSize / 1024, 1) }} KB
                    @else
                        {{ $currentFileSize }} B
                    @endif
                </p>
                <p class="text-xs text-gray-500">Current File Size</p>
            </div>
        </div>

        <div class="bg-dark-800 border border-dark-700 rounded-xl p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-amber-500/10 flex items-center justify-center">
                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-white">{{ number_format($total) }}</p>
                <p class="text-xs text-gray-500">Matching Entries</p>
            </div>
        </div>
    </div>

    {{-- Log Entries --}}
    @if (count($logFiles) === 0)
        {{-- Empty State: No log files --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-2">No Log Files</h3>
            <p class="text-sm text-gray-500">No log files were found in the storage/logs directory.</p>
        </div>
    @elseif (count($entries) === 0)
        {{-- Empty State: No matching entries --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <h3 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-2">No Entries Found</h3>
            <p class="text-sm text-gray-500">No log entries match your current filters. Try adjusting the level or search term.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($entries as $entry)
                <div
                    x-data="{ expanded: false }"
                    class="bg-dark-800 border border-dark-700 rounded-xl p-4 transition-colors hover:border-dark-600"
                >
                    <div class="flex items-start gap-3">
                        {{-- Level Badge --}}
                        <div class="shrink-0 mt-0.5">
                            @php
                                $level = strtolower($entry['level']);
                                $badgeClass = match(true) {
                                    in_array($level, ['emergency', 'alert', 'critical', 'error']) => 'bg-red-500/10 text-red-400',
                                    $level === 'warning' => 'bg-amber-500/10 text-amber-400',
                                    in_array($level, ['notice', 'info']) => 'bg-blue-500/10 text-blue-400',
                                    default => 'bg-gray-500/10 text-gray-400',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $badgeClass }}">
                                {{ strtoupper($entry['level']) }}
                            </span>
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            {{-- Timestamp & Environment --}}
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-mono text-xs text-gray-500">{{ $entry['timestamp'] }}</span>
                                <span class="text-xs text-gray-600">{{ $entry['environment'] }}</span>
                            </div>

                            {{-- Message --}}
                            <p class="text-sm text-gray-300 line-clamp-2 break-all">{{ $entry['message'] }}</p>

                            {{-- Stack Trace Toggle --}}
                            @if (! empty($entry['stack_trace']))
                                <button
                                    @click="expanded = !expanded"
                                    class="mt-2 text-xs text-primary-light hover:text-primary transition-colors inline-flex items-center gap-1"
                                >
                                    <svg
                                        class="w-3 h-3 transition-transform duration-200"
                                        :class="expanded ? 'rotate-90' : ''"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                    <span x-text="expanded ? 'Hide Stack Trace' : 'Show Stack Trace'"></span>
                                </button>

                                <div
                                    x-show="expanded"
                                    x-cloak
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 -translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 -translate-y-1"
                                    class="font-mono text-xs text-gray-500 bg-dark-900 rounded-lg p-4 mt-2 overflow-x-auto whitespace-pre-wrap"
                                >{{ $entry['stack_trace'] }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Load More / End --}}
        <div class="mt-6 text-center">
            @if ($hasMore)
                <button
                    wire:click="loadMore"
                    wire:loading.attr="disabled"
                    wire:target="loadMore"
                    class="bg-dark-700 hover:bg-dark-600 text-gray-300 rounded-lg px-6 py-2.5 text-sm font-medium transition-colors inline-flex items-center gap-2"
                >
                    <span wire:loading wire:target="loadMore">
                        <svg class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </span>
                    <span wire:loading.remove wire:target="loadMore">Load More</span>
                    <span wire:loading wire:target="loadMore">Loading...</span>
                </button>
            @else
                <p class="text-sm text-gray-500">No more entries.</p>
            @endif
        </div>
    @endif
</div>
