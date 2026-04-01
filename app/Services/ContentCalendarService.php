<?php

namespace App\Services;

use App\Models\ContentCalendarItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ContentCalendarService
{
    public function getItemsForMonth(int $year, int $month): Collection
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        return $this->getItemsForDateRange($start, $end);
    }

    public function getItemsForDateRange(Carbon $start, Carbon $end): Collection
    {
        return ContentCalendarItem::query()
            ->inDateRange($start, $end)
            ->orderBy('planned_date')
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->get();
    }

    public function createItem(array $data): ContentCalendarItem
    {
        return ContentCalendarItem::create($data);
    }

    public function updateItem(ContentCalendarItem $item, array $data): ContentCalendarItem
    {
        $item->update($data);

        return $item;
    }

    public function deleteItem(ContentCalendarItem $item): void
    {
        $item->delete();
    }

    public function rescheduleItem(ContentCalendarItem $item, string $newDate): ContentCalendarItem
    {
        $item->update(['planned_date' => $newDate]);

        return $item;
    }

    public function markAsPublished(ContentCalendarItem $item): ContentCalendarItem
    {
        $item->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return $item;
    }

    public function markAsPlanned(ContentCalendarItem $item): ContentCalendarItem
    {
        $item->update([
            'status' => 'planned',
            'published_at' => null,
        ]);

        return $item;
    }

    public function getGapWeeks(int $year, int $month): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        // Get all ISO weeks that overlap with this month
        $weeks = [];
        $current = $start->copy()->startOfWeek(Carbon::MONDAY);

        while ($current->lte($end)) {
            $weekStart = $current->copy();
            $weekEnd = $current->copy()->endOfWeek(Carbon::SUNDAY);

            // Only include weeks that have at least one day in the month
            if ($weekStart->lte($end) && $weekEnd->gte($start)) {
                $weeks[] = $weekStart;
            }

            $current->addWeek();
        }

        // Check each week for content
        $gapWeeks = [];
        foreach ($weeks as $weekStart) {
            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

            $count = ContentCalendarItem::query()
                ->inDateRange($weekStart, $weekEnd)
                ->count();

            if ($count === 0) {
                $gapWeeks[] = $weekStart->format('Y-m-d');
            }
        }

        return $gapWeeks;
    }

    public function getMonthStats(int $year, int $month): array
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $items = ContentCalendarItem::query()
            ->inDateRange($start, $end)
            ->get();

        return [
            'total' => $items->count(),
            'planned' => $items->where('status', 'planned')->count(),
            'published' => $items->where('status', 'published')->count(),
            'videos' => $items->where('type', 'video')->count(),
            'blogs' => $items->where('type', 'blog')->count(),
        ];
    }
}
