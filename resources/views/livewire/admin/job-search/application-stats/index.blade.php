<div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-400">Job Search</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">Application Stats</span>
    </div>

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Application Stats</h1>
            <p class="text-gray-500 mt-1">Track your job search progress and conversion rates.</p>
        </div>

        {{-- Period Selector --}}
        <div class="flex gap-2">
            @foreach(['7d' => '7D', '30d' => '30D', '90d' => '90D'] as $value => $label)
                <button wire:click="$set('period', '{{ $value }}')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $period === $value ? 'bg-primary text-white' : 'bg-dark-700 text-gray-400 hover:text-white' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        {{-- Total Applications --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-500">Total Applications</span>
                <span class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </span>
            </div>
            <p class="text-3xl font-bold text-white">{{ number_format($stats['total_applications']) }}</p>
        </div>

        {{-- Applied This Month --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-500">Applied This Month</span>
                <span class="w-9 h-9 rounded-lg bg-blue-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </span>
            </div>
            <p class="text-3xl font-bold text-white">{{ number_format($stats['applied_this_month']) }}</p>
        </div>

        {{-- Response Rate --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-500">Response Rate</span>
                <span class="w-9 h-9 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </span>
            </div>
            <p class="text-3xl font-bold text-white">{{ $stats['response_rate'] }}%</p>
        </div>

        {{-- Interview Rate --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-500">Interview Rate</span>
                <span class="w-9 h-9 rounded-lg bg-fuchsia-500/10 flex items-center justify-center">
                    <svg class="w-5 h-5 text-fuchsia-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </span>
            </div>
            <p class="text-3xl font-bold text-white">{{ $stats['interview_rate'] }}%</p>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        {{-- Applications Over Time --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
            <h3 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-4">Applications Over Time</h3>
            @if(collect($chartData)->sum('count') > 0)
                <div x-data="{
                        chart: null,
                        labels: {{ Js::from(collect($chartData)->pluck('date')) }},
                        values: {{ Js::from(collect($chartData)->pluck('count')) }},
                        buildChart() {
                            if (this.chart) {
                                this.chart.destroy();
                            }
                            const ctx = this.$refs.areaCanvas.getContext('2d');
                            const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                            gradient.addColorStop(0, 'rgba(124, 58, 237, 0.3)');
                            gradient.addColorStop(1, 'rgba(124, 58, 237, 0)');
                            this.chart = new Chart(ctx, {
                                type: 'line',
                                data: {
                                    labels: this.labels,
                                    datasets: [{
                                        label: 'Applications',
                                        data: this.values,
                                        borderColor: '#7c3aed',
                                        backgroundColor: gradient,
                                        fill: true,
                                        tension: 0.4,
                                        pointRadius: 2,
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    plugins: { legend: { display: false } },
                                    scales: {
                                        x: { grid: { color: '#1a1a24' }, ticks: { color: '#9ca3af', maxTicksLimit: 8 } },
                                        y: { grid: { color: '#1a1a24' }, ticks: { color: '#9ca3af', beginAtZero: true } }
                                    }
                                }
                            });
                        },
                        init() { this.buildChart(); }
                     }"
                     x-effect="buildChart()">
                    <canvas x-ref="areaCanvas" height="120"></canvas>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <svg class="w-12 h-12 text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <p class="text-gray-500 text-sm">Start tracking applications to see stats here.</p>
                </div>
            @endif
        </div>

        {{-- Status Breakdown --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
            <h3 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-4">Status Breakdown</h3>
            @php
                $totalStatus = array_sum($statusBreakdown);
                $statusColors = [
                    'saved' => ['bg' => 'bg-blue-400', 'text' => 'text-blue-400'],
                    'applied' => ['bg' => 'bg-primary-light', 'text' => 'text-primary-light'],
                    'interview' => ['bg' => 'bg-amber-400', 'text' => 'text-amber-400'],
                    'offer' => ['bg' => 'bg-emerald-400', 'text' => 'text-emerald-400'],
                    'rejected' => ['bg' => 'bg-red-400', 'text' => 'text-red-400'],
                ];
            @endphp
            @if($totalStatus > 0)
                <div x-data="{
                        chart: null,
                        buildDonut() {
                            if (this.chart) {
                                this.chart.destroy();
                            }
                            const ctx = this.$refs.donutCanvas.getContext('2d');
                            this.chart = new Chart(ctx, {
                                type: 'doughnut',
                                data: {
                                    labels: ['Saved', 'Applied', 'Interview', 'Offer', 'Rejected'],
                                    datasets: [{
                                        data: [{{ $statusBreakdown['saved'] }}, {{ $statusBreakdown['applied'] }}, {{ $statusBreakdown['interview'] }}, {{ $statusBreakdown['offer'] }}, {{ $statusBreakdown['rejected'] }}],
                                        backgroundColor: ['#60a5fa', '#a78bfa', '#fbbf24', '#34d399', '#f87171'],
                                        borderColor: '#111118',
                                        borderWidth: 2,
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    cutout: '65%',
                                    plugins: {
                                        legend: { display: false }
                                    }
                                }
                            });
                        },
                        init() { this.buildDonut(); }
                     }"
                     x-effect="buildDonut()">
                    <div class="flex justify-center mb-4">
                        <canvas x-ref="donutCanvas" width="220" height="220" style="max-width: 220px; max-height: 220px;"></canvas>
                    </div>
                </div>

                {{-- Legend --}}
                <div class="grid grid-cols-2 gap-2 mt-4">
                    @foreach($statusBreakdown as $status => $count)
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full {{ $statusColors[$status]['bg'] }}"></span>
                            <span class="text-sm text-gray-400">{{ ucfirst($status) }}</span>
                            <span class="text-sm {{ $statusColors[$status]['text'] }} font-medium ml-auto">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <svg class="w-12 h-12 text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                    </svg>
                    <p class="text-gray-500 text-sm">Start tracking applications to see stats here.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Pipeline Funnel --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 mb-8">
        <h3 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-6">Application Pipeline</h3>
        @php
            $pipelineStages = [
                'saved' => ['label' => 'Saved', 'icon' => 'M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z'],
                'applied' => ['label' => 'Applied', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                'interview' => ['label' => 'Interview', 'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
                'offer' => ['label' => 'Offer', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
            ];
            $maxCount = max($pipeline['saved']['count'], 1);
        @endphp
        <div class="space-y-4">
            @foreach($pipelineStages as $key => $stage)
                @php
                    $stageData = $pipeline[$key];
                    $widthPercent = $maxCount > 0 ? max(($stageData['count'] / $maxCount) * 100, 4) : 4;
                @endphp
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stage['icon'] }}"/>
                            </svg>
                            <span class="text-sm font-medium text-white">{{ $stage['label'] }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-bold text-white">{{ $stageData['count'] }}</span>
                            <span class="text-xs text-gray-500">
                                @if($key === 'saved')
                                    100%
                                @else
                                    {{ $stageData['percentage'] }}% conversion
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="w-full bg-dark-700 rounded-full h-3">
                        <div class="bg-gradient-to-r from-primary to-fuchsia-500 h-3 rounded-full transition-all duration-500" style="width: {{ $widthPercent }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Bottom Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Top Rejected Companies --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
            <h3 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-4">Top Rejection Sources</h3>
            @if($topRejected->isNotEmpty())
                @php
                    $maxRejections = $topRejected->max('rejections');
                @endphp
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-dark-700/50">
                                <th class="text-left text-xs font-mono font-medium text-gray-400 uppercase tracking-wider px-4 py-3">Company</th>
                                <th class="text-right text-xs font-mono font-medium text-gray-400 uppercase tracking-wider px-4 py-3">Rejections</th>
                                <th class="text-right text-xs font-mono font-medium text-gray-400 uppercase tracking-wider px-4 py-3 w-32"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-dark-700">
                            @foreach($topRejected as $company)
                                <tr class="hover:bg-dark-700/30 transition-colors">
                                    <td class="px-4 py-3 text-sm text-white">{{ $company->company }}</td>
                                    <td class="px-4 py-3 text-sm text-red-400 text-right font-medium">{{ $company->rejections }}</td>
                                    <td class="px-4 py-3">
                                        <div class="w-full bg-dark-700 rounded-full h-1.5">
                                            <div class="bg-red-400 h-1.5 rounded-full" style="width: {{ $maxRejections > 0 ? round(($company->rejections / $maxRejections) * 100) : 0 }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <svg class="w-12 h-12 text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-gray-500 text-sm">No rejections recorded yet.</p>
                </div>
            @endif
        </div>

        {{-- Recent Activity --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
            <h3 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-4">Recent Activity</h3>
            @if($recentActivity->isNotEmpty())
                <div class="max-h-96 overflow-y-auto space-y-3 pr-1">
                    @php
                        $activityStatusColors = [
                            'saved' => 'bg-blue-500/10 text-blue-400',
                            'applied' => 'bg-primary/10 text-primary-light',
                            'interview' => 'bg-amber-500/10 text-amber-400',
                            'offer' => 'bg-emerald-500/10 text-emerald-400',
                            'rejected' => 'bg-red-500/10 text-red-400',
                        ];
                    @endphp
                    @foreach($recentActivity as $activity)
                        <div class="flex items-start gap-3 py-2 border-b border-dark-700/50 last:border-0">
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium shrink-0 {{ $activityStatusColors[$activity->status] ?? 'bg-dark-700 text-gray-400' }}">
                                {{ ucfirst($activity->status) }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-white truncate">{{ $activity->company }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ $activity->position }}</p>
                            </div>
                            <span class="text-xs text-gray-500 shrink-0">{{ $activity->updated_at->diffForHumans() }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <svg class="w-12 h-12 text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-gray-500 text-sm">No applications yet. Start tracking your job search.</p>
                </div>
            @endif
        </div>
    </div>
</div>
