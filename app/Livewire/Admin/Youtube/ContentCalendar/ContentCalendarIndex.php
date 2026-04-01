<?php

namespace App\Livewire\Admin\Youtube\ContentCalendar;

use App\Models\ContentCalendarItem;
use App\Services\ContentCalendarService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class ContentCalendarIndex extends Component
{
    public int $year;

    public int $month;

    public Collection $items;

    public array $gapWeeks = [];

    public array $monthStats = [];

    #[Url]
    public string $filterType = '';

    public function mount(): void
    {
        $this->year = now()->year;
        $this->month = now()->month;
        $this->items = collect();
        $this->loadCalendarData();
    }

    public function loadCalendarData(): void
    {
        $service = app(ContentCalendarService::class);

        $items = $service->getItemsForMonth($this->year, $this->month);

        if ($this->filterType) {
            $items = $items->where('type', $this->filterType);
        }

        $this->items = $items;
        $this->gapWeeks = $service->getGapWeeks($this->year, $this->month);
        $this->monthStats = $service->getMonthStats($this->year, $this->month);
    }

    public function previousMonth(): void
    {
        $this->month--;
        if ($this->month < 1) {
            $this->month = 12;
            $this->year--;
        }
        $this->loadCalendarData();
    }

    public function nextMonth(): void
    {
        $this->month++;
        if ($this->month > 12) {
            $this->month = 1;
            $this->year++;
        }
        $this->loadCalendarData();
    }

    public function goToToday(): void
    {
        $this->year = now()->year;
        $this->month = now()->month;
        $this->loadCalendarData();
    }

    public function reschedule(int $itemId, string $newDate): void
    {
        $validator = \Illuminate\Support\Facades\Validator::make(
            ['newDate' => $newDate],
            ['newDate' => 'required|date|date_format:Y-m-d']
        );

        if ($validator->fails()) {
            session()->flash('error', 'Invalid date provided.');

            return;
        }

        $item = ContentCalendarItem::findOrFail($itemId);

        app(ContentCalendarService::class)->rescheduleItem($item, $newDate);

        $this->loadCalendarData();
        session()->flash('success', 'Content rescheduled successfully.');
    }

    public function togglePublished(int $itemId): void
    {
        $item = ContentCalendarItem::findOrFail($itemId);
        $service = app(ContentCalendarService::class);

        if ($item->is_published) {
            $service->markAsPlanned($item);
            session()->flash('success', 'Content marked as planned.');
        } else {
            $service->markAsPublished($item);
            session()->flash('success', 'Content marked as published.');
        }

        $this->loadCalendarData();
    }

    public function delete(int $itemId): void
    {
        $item = ContentCalendarItem::findOrFail($itemId);

        app(ContentCalendarService::class)->deleteItem($item);

        $this->loadCalendarData();
        session()->flash('success', 'Content deleted successfully.');
    }

    public function updatedFilterType(): void
    {
        $this->loadCalendarData();
    }

    public function render()
    {
        return view('livewire.admin.youtube.content-calendar.index');
    }
}
