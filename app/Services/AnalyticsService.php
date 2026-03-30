<?php

namespace App\Services;

use App\Models\PortfolioVisitor;
use App\Models\ResumeDownload;
use Illuminate\Support\Collection;

class AnalyticsService
{
    public function getVisitorStats(string $period = '30d'): array
    {
        $query = PortfolioVisitor::query()->inPeriod($period);

        return [
            'total_visits' => (clone $query)->count(),
            'unique_visitors' => (clone $query)->distinct('ip_address')->count('ip_address'),
            'page_views' => (clone $query)->count(),
            'resume_downloads' => ResumeDownload::query()->inPeriod($period)->count(),
        ];
    }

    public function getVisitorsByDay(string $period = '30d'): Collection
    {
        $days = match ($period) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            default => 30,
        };

        return PortfolioVisitor::query()
            ->inPeriod($period)
            ->selectRaw('DATE(visited_at) as date, COUNT(*) as count')
            ->groupByRaw('DATE(visited_at)')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->pipe(function ($data) use ($days) {
                $result = collect();
                for ($i = $days - 1; $i >= 0; $i--) {
                    $date = now()->subDays($i)->format('Y-m-d');
                    $result[$date] = $data[$date] ?? 0;
                }

                return $result;
            });
    }

    public function getTopPages(string $period = '30d', int $limit = 10): Collection
    {
        return PortfolioVisitor::query()
            ->inPeriod($period)
            ->selectRaw('page_visited, COUNT(*) as visits')
            ->groupBy('page_visited')
            ->orderByDesc('visits')
            ->limit($limit)
            ->get();
    }

    public function getDeviceBreakdown(string $period = '30d'): Collection
    {
        return PortfolioVisitor::query()
            ->inPeriod($period)
            ->selectRaw('device_type, COUNT(*) as count')
            ->groupBy('device_type')
            ->orderByDesc('count')
            ->pluck('count', 'device_type');
    }

    public function getReferrerBreakdown(string $period = '30d', int $limit = 10): Collection
    {
        return PortfolioVisitor::query()
            ->inPeriod($period)
            ->whereNotNull('referrer')
            ->where('referrer', '!=', '')
            ->selectRaw('referrer, COUNT(*) as count')
            ->groupBy('referrer')
            ->orderByDesc('count')
            ->limit($limit)
            ->get();
    }
}
