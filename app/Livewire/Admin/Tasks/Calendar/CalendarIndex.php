<?php

namespace App\Livewire\Admin\Tasks\Calendar;

use App\Services\CalendarService;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class CalendarIndex extends Component
{
    #[Url]
    public string $viewMode = 'month';

    #[Url]
    public int $currentYear;

    #[Url]
    public int $currentMonth;

    #[Url]
    public string $currentWeekStart = '';

    public function mount(): void
    {
        $today = Carbon::today();

        if (! isset($this->currentYear) || $this->currentYear === 0) {
            $this->currentYear = $today->year;
        }

        if (! isset($this->currentMonth) || $this->currentMonth === 0) {
            $this->currentMonth = $today->month;
        }

        if (! $this->currentWeekStart) {
            $this->currentWeekStart = $today->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        }
    }

    public function setViewMode(string $mode): void
    {
        if (in_array($mode, ['month', 'week'])) {
            $this->viewMode = $mode;
        }
    }

    public function previousPeriod(): void
    {
        if ($this->viewMode === 'month') {
            $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->subMonth();
            $this->currentYear = $date->year;
            $this->currentMonth = $date->month;
        } else {
            $weekStart = Carbon::parse($this->currentWeekStart)->subWeek();
            $this->currentWeekStart = $weekStart->format('Y-m-d');
        }
    }

    public function nextPeriod(): void
    {
        if ($this->viewMode === 'month') {
            $date = Carbon::create($this->currentYear, $this->currentMonth, 1)->addMonth();
            $this->currentYear = $date->year;
            $this->currentMonth = $date->month;
        } else {
            $weekStart = Carbon::parse($this->currentWeekStart)->addWeek();
            $this->currentWeekStart = $weekStart->format('Y-m-d');
        }
    }

    public function goToToday(): void
    {
        $today = Carbon::today();
        $this->currentYear = $today->year;
        $this->currentMonth = $today->month;
        $this->currentWeekStart = $today->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
    }

    public function navigateToDay(string $date): void
    {
        $this->redirect(route('admin.tasks.planner.index', ['selectedDate' => $date]), navigate: true);
    }

    public function render(CalendarService $service)
    {
        $userId = auth()->id();

        if ($this->viewMode === 'month') {
            $tasksByDate = $service->getTasksForMonth($userId, $this->currentYear, $this->currentMonth);
            $calendarDays = $service->buildMonthGrid($this->currentYear, $this->currentMonth, $tasksByDate);

            $periodStart = Carbon::create($this->currentYear, $this->currentMonth, 1);
            $periodEnd = $periodStart->copy()->endOfMonth();
            $periodLabel = $periodStart->format('F Y');
        } else {
            $weekStart = Carbon::parse($this->currentWeekStart);
            $tasksByDate = $service->getTasksForWeek($userId, $weekStart);
            $calendarDays = $service->buildWeekGrid($weekStart, $tasksByDate);

            $periodStart = $weekStart->copy();
            $periodEnd = $weekStart->copy()->addDays(6);
            $periodLabel = $periodStart->format('M j').' – '.$periodEnd->format('M j, Y');
        }

        $stats = $service->getCalendarStats($userId, $periodStart, $periodEnd);
        $categories = $service->getCategories();

        return view('livewire.admin.tasks.calendar.index', [
            'calendarDays' => $calendarDays,
            'stats' => $stats,
            'categories' => $categories,
            'periodLabel' => $periodLabel,
        ]);
    }
}
