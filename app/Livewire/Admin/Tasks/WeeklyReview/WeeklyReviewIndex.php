<?php

namespace App\Livewire\Admin\Tasks\WeeklyReview;

use App\Services\WeeklyReviewService;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class WeeklyReviewIndex extends Component
{
    #[Url]
    public string $weekStart = '';

    public bool $isGenerating = false;

    public function mount(): void
    {
        if (! $this->weekStart) {
            $this->weekStart = now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        }

        $parsed = Carbon::parse($this->weekStart);
        if ($parsed->dayOfWeekIso !== 1) {
            $this->weekStart = $parsed->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        }
    }

    public function previousWeek(): void
    {
        $this->weekStart = Carbon::parse($this->weekStart)->subWeek()->format('Y-m-d');
    }

    public function nextWeek(): void
    {
        $next = Carbon::parse($this->weekStart)->addWeek();
        $currentMonday = now()->startOfWeek(Carbon::MONDAY);

        if ($next->lte($currentMonday)) {
            $this->weekStart = $next->format('Y-m-d');
        }
    }

    public function goToCurrentWeek(): void
    {
        $this->weekStart = now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
    }

    public function regenerateSummary(WeeklyReviewService $service): void
    {
        $this->isGenerating = true;

        try {
            $service->refreshReview(auth()->id(), Carbon::parse($this->weekStart));
            session()->flash('success', 'Weekly review regenerated.');
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to regenerate weekly review. Please try again.');
        }

        $this->isGenerating = false;
    }

    public function render(WeeklyReviewService $service)
    {
        $weekStart = Carbon::parse($this->weekStart);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        $review = $service->getOrCreateReview(auth()->id(), $weekStart);
        $incompleteTasks = $service->getIncompleteTasks(auth()->id(), $weekStart, $weekEnd);
        $previousReview = $service->getPreviousWeekReview(auth()->id(), $weekStart);
        $comparison = $service->computeWeekComparison($review, $previousReview);
        $hasApiKey = $service->getAiApiKey(auth()->id()) !== null;

        return view('livewire.admin.tasks.weekly-review.index', [
            'review' => $review,
            'incompleteTasks' => $incompleteTasks,
            'comparison' => $comparison,
            'hasApiKey' => $hasApiKey,
            'previousReview' => $previousReview,
            'weekEnd' => $weekEnd,
        ]);
    }
}
