<div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Analytics</h1>
            <p class="text-gray-500 mt-1">Track your portfolio visitors and engagement.</p>
        </div>

        {{-- Period Selector --}}
        <div class="flex gap-2">
            @foreach(['7d' => '7 Days', '30d' => '30 Days', '90d' => '90 Days'] as $value => $label)
                <button wire:click="$set('period', '{{ $value }}')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $period === $value ? 'bg-primary text-white' : 'bg-dark-700 text-gray-400 hover:text-white' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        {{-- Total Visits --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-500">Total Visits</span>
                <span class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </span>
            </div>
            <p class="text-3xl font-bold text-white">{{ number_format($stats['total_visits']) }}</p>
        </div>

        {{-- Unique Visitors --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-500">Unique Visitors</span>
                <span class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </span>
            </div>
            <p class="text-3xl font-bold text-white">{{ number_format($stats['unique_visitors']) }}</p>
        </div>

        {{-- Page Views --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-500">Page Views</span>
                <span class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </span>
            </div>
            <p class="text-3xl font-bold text-white">{{ number_format($stats['page_views']) }}</p>
        </div>

        {{-- Resume Downloads --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-500">Resume Downloads</span>
                <span class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                </span>
            </div>
            <p class="text-3xl font-bold text-white">{{ number_format($stats['resume_downloads']) }}</p>
        </div>
    </div>

    {{-- Chart Section --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 mb-8">
        <h3 class="text-lg font-mono font-semibold text-white uppercase tracking-wider mb-4">Visitors Over Time</h3>
        <div x-data="{
                chart: null,
                labels: {{ Js::from($chartData->keys()) }},
                values: {{ Js::from($chartData->values()) }},
                buildChart() {
                    if (this.chart) {
                        this.chart.destroy();
                    }
                    const ctx = this.$refs.canvas.getContext('2d');
                    this.chart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: this.labels,
                            datasets: [{
                                label: 'Visitors',
                                data: this.values,
                                borderColor: '#6366f1',
                                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                                fill: true,
                                tension: 0.4,
                                pointRadius: 2,
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: { legend: { display: false } },
                            scales: {
                                x: { grid: { color: '#1a1a2e' }, ticks: { color: '#9ca3af', maxTicksLimit: 10 } },
                                y: { grid: { color: '#1a1a2e' }, ticks: { color: '#9ca3af', beginAtZero: true } }
                            }
                        }
                    });
                },
                init() { this.buildChart(); }
             }"
             x-effect="buildChart()">
            <canvas x-ref="canvas" height="100"></canvas>
        </div>
    </div>

    {{-- Two-Column Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        {{-- Top Pages --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
            <h3 class="text-lg font-mono font-semibold text-white uppercase tracking-wider mb-4">Top Pages</h3>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-dark-700/50">
                            <th class="text-left text-xs font-mono font-medium text-gray-400 uppercase tracking-wider px-4 py-3">Page</th>
                            <th class="text-right text-xs font-mono font-medium text-gray-400 uppercase tracking-wider px-4 py-3">Visits</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-dark-700">
                        @forelse ($topPages as $page)
                            <tr class="hover:bg-dark-700/30 transition-colors">
                                <td class="px-4 py-3 text-sm text-gray-300 truncate max-w-[250px]">{{ $page->page_visited }}</td>
                                <td class="px-4 py-3 text-sm text-gray-400 text-right">{{ number_format($page->visits) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-4 py-8 text-center text-gray-500 text-sm">No page data available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Device Breakdown --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
            <h3 class="text-lg font-mono font-semibold text-white uppercase tracking-wider mb-4">Device Breakdown</h3>
            @forelse ($devices as $device => $count)
                <div class="flex items-center gap-3 mb-3">
                    <span class="text-sm text-gray-400 w-20">{{ ucfirst($device) }}</span>
                    <div class="flex-1 bg-dark-700 rounded-full h-2.5">
                        <div class="bg-primary h-2.5 rounded-full" style="width: {{ $devices->sum() > 0 ? round($count / $devices->sum() * 100) : 0 }}%"></div>
                    </div>
                    <span class="text-sm text-gray-400 w-12 text-right">{{ $count }}</span>
                </div>
            @empty
                <p class="text-gray-500 text-sm text-center py-4">No device data available.</p>
            @endforelse
        </div>
    </div>

    {{-- Referrer Sources --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
        <h3 class="text-lg font-mono font-semibold text-white uppercase tracking-wider mb-4">Referrer Sources</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-dark-700/50">
                        <th class="text-left text-xs font-mono font-medium text-gray-400 uppercase tracking-wider px-4 py-3">Referrer</th>
                        <th class="text-right text-xs font-mono font-medium text-gray-400 uppercase tracking-wider px-4 py-3">Visits</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700">
                    @forelse ($referrers as $referrer)
                        <tr class="hover:bg-dark-700/30 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-300 truncate max-w-[400px]">{{ $referrer->referrer ?: 'Direct' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-400 text-right">{{ number_format($referrer->count) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-4 py-8 text-center text-gray-500 text-sm">No referrer data available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
