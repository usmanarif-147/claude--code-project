<?php

namespace App\Services;

use App\Models\JobSearch\JobApplication;
use Illuminate\Support\Collection;

class ApplicationStatsService
{
    public function getSummaryStats(string $period): array
    {
        $days = $this->periodToDays($period);

        $totalApplications = JobApplication::count();

        $appliedThisMonth = JobApplication::where('status', '!=', JobApplication::STATUS_SAVED)
            ->whereNotNull('applied_date')
            ->whereMonth('applied_date', now()->month)
            ->whereYear('applied_date', now()->year)
            ->count();

        $appliedThisWeek = JobApplication::where('status', '!=', JobApplication::STATUS_SAVED)
            ->whereNotNull('applied_date')
            ->where('applied_date', '>=', now()->startOfWeek())
            ->count();

        $totalApplied = JobApplication::where('status', '!=', JobApplication::STATUS_SAVED)->count();

        $responded = JobApplication::whereIn('status', [
            JobApplication::STATUS_INTERVIEW,
            JobApplication::STATUS_OFFER,
            JobApplication::STATUS_REJECTED,
        ])->count();

        $interviewCount = JobApplication::whereIn('status', [
            JobApplication::STATUS_INTERVIEW,
            JobApplication::STATUS_OFFER,
        ])->count();

        $offerCount = JobApplication::where('status', JobApplication::STATUS_OFFER)->count();
        $rejectedCount = JobApplication::where('status', JobApplication::STATUS_REJECTED)->count();

        $activeCount = JobApplication::whereIn('status', [
            JobApplication::STATUS_APPLIED,
            JobApplication::STATUS_INTERVIEW,
        ])->count();

        $responseRate = $totalApplied > 0 ? round(($responded / $totalApplied) * 100, 1) : 0;
        $interviewRate = $totalApplied > 0 ? round(($interviewCount / $totalApplied) * 100, 1) : 0;

        return [
            'total_applications' => $totalApplications,
            'applied_this_week' => $appliedThisWeek,
            'applied_this_month' => $appliedThisMonth,
            'response_rate' => $responseRate,
            'interview_rate' => $interviewRate,
            'offer_count' => $offerCount,
            'rejected_count' => $rejectedCount,
            'active_count' => $activeCount,
        ];
    }

    public function getApplicationsByDay(string $period): array
    {
        $days = $this->periodToDays($period);
        $startDate = now()->subDays($days)->startOfDay();

        $results = JobApplication::where(function ($query) use ($startDate) {
            $query->where(function ($q) use ($startDate) {
                $q->whereNotNull('applied_date')->where('applied_date', '>=', $startDate->toDateString());
            })->orWhere(function ($q) use ($startDate) {
                $q->whereNull('applied_date')->where('created_at', '>=', $startDate);
            });
        })
            ->selectRaw('COALESCE(applied_date, DATE(created_at)) as date, COUNT(*) as count')
            ->groupByRaw('COALESCE(applied_date, DATE(created_at))')
            ->orderBy('date')
            ->pluck('count', 'date');

        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $data[] = [
                'date' => $date,
                'count' => $results[$date] ?? 0,
            ];
        }

        return $data;
    }

    public function getStatusBreakdown(): array
    {
        $counts = JobApplication::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            'saved' => $counts[JobApplication::STATUS_SAVED] ?? 0,
            'applied' => $counts[JobApplication::STATUS_APPLIED] ?? 0,
            'interview' => $counts[JobApplication::STATUS_INTERVIEW] ?? 0,
            'offer' => $counts[JobApplication::STATUS_OFFER] ?? 0,
            'rejected' => $counts[JobApplication::STATUS_REJECTED] ?? 0,
        ];
    }

    public function getTopRejectedCompanies(string $period, int $limit = 10): Collection
    {
        $days = $this->periodToDays($period);

        return JobApplication::where('status', JobApplication::STATUS_REJECTED)
            ->where('updated_at', '>=', now()->subDays($days))
            ->selectRaw('LOWER(company) as company_name, company, COUNT(*) as rejections')
            ->groupByRaw('LOWER(company), company')
            ->orderByDesc('rejections')
            ->limit($limit)
            ->get();
    }

    public function getRecentActivity(int $limit = 15): Collection
    {
        return JobApplication::orderByDesc('updated_at')
            ->limit($limit)
            ->get();
    }

    public function getPipelineConversion(): array
    {
        $total = JobApplication::count();

        $applied = JobApplication::whereIn('status', [
            JobApplication::STATUS_APPLIED,
            JobApplication::STATUS_INTERVIEW,
            JobApplication::STATUS_OFFER,
            JobApplication::STATUS_REJECTED,
        ])->count();

        $interview = JobApplication::whereIn('status', [
            JobApplication::STATUS_INTERVIEW,
            JobApplication::STATUS_OFFER,
        ])->count();

        $offer = JobApplication::where('status', JobApplication::STATUS_OFFER)->count();

        return [
            'saved' => [
                'count' => $total,
                'percentage' => 100,
            ],
            'applied' => [
                'count' => $applied,
                'percentage' => $total > 0 ? round(($applied / $total) * 100, 1) : 0,
            ],
            'interview' => [
                'count' => $interview,
                'percentage' => $applied > 0 ? round(($interview / $applied) * 100, 1) : 0,
            ],
            'offer' => [
                'count' => $offer,
                'percentage' => $interview > 0 ? round(($offer / $interview) * 100, 1) : 0,
            ],
        ];
    }

    public function periodToDays(string $period): int
    {
        return match ($period) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            default => 30,
        };
    }
}
