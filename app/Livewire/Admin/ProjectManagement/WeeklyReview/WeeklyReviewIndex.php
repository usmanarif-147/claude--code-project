<?php

namespace App\Livewire\Admin\ProjectManagement\WeeklyReview;

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

    #[Url]
    public string $boardFilter = 'all';

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
        $userId = auth()->id();
        $boardId = $this->boardFilter !== 'all' ? (int) $this->boardFilter : null;

        $review = $service->getOrCreateReview($userId, $weekStart);
        $incompleteTasks = $service->getIncompleteTasks($userId, $weekStart, $weekEnd, $boardId);
        $previousReview = $service->getPreviousWeekReview($userId, $weekStart);
        $comparison = $service->computeWeekComparison($review, $previousReview);
        $hasApiKey = $service->getAiApiKey($userId) !== null;
        $boards = $service->getBoards($userId);
        $boardColumnBreakdown = $service->computeBoardColumnBreakdown($userId, $weekStart, $weekEnd, $boardId);
        $perBoardAnalytics = $service->computePerBoardAnalytics($userId, $weekStart, $weekEnd);

        return view('livewire.admin.project-management.weekly-review.index', [
            'review' => $review,
            'incompleteTasks' => $incompleteTasks,
            'comparison' => $comparison,
            'hasApiKey' => $hasApiKey,
            'previousReview' => $previousReview,
            'weekEnd' => $weekEnd,
            'boards' => $boards,
            'boardColumnBreakdown' => $boardColumnBreakdown,
            'perBoardAnalytics' => $perBoardAnalytics,
        ]);
    }
}
