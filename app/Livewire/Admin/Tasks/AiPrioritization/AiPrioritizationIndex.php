<?php

namespace App\Livewire\Admin\Tasks\AiPrioritization;

use App\Services\AiTaskPrioritizationService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class AiPrioritizationIndex extends Component
{
    public bool $hasApiKey = false;

    public ?string $provider = null;

    public ?array $result = null;

    public bool $isLoading = false;

    public ?string $error = null;

    public ?string $lastAnalyzedAt = null;

    public function mount(AiTaskPrioritizationService $service): void
    {
        $provider = $service->getConfiguredProvider(auth()->id());

        $this->hasApiKey = $provider !== null;
        $this->provider = $provider;
    }

    public function analyze(AiTaskPrioritizationService $service): void
    {
        $this->isLoading = true;
        $this->error = null;

        try {
            $this->result = $service->prioritize(auth()->id());
            $this->lastAnalyzedAt = now()->format('g:i A');
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
            $this->result = null;
        } finally {
            $this->isLoading = false;
        }
    }

    public function applyOrder(AiTaskPrioritizationService $service): void
    {
        if (! $this->result || empty($this->result['prioritized_tasks'])) {
            return;
        }

        $taskIds = collect($this->result['prioritized_tasks'])
            ->sortBy('rank')
            ->pluck('task_id')
            ->toArray();

        $service->applyOrder(auth()->id(), $taskIds);

        session()->flash('success', 'Task order updated successfully.');
    }

    public function render(AiTaskPrioritizationService $service)
    {
        $userId = auth()->id();
        $todaysTasks = $service->getTodaysTasks($userId);
        $overdueTasks = $service->getOverdueTasks($userId);
        $completedToday = $service->getCompletedTodayCount($userId);

        return view('livewire.admin.tasks.ai-prioritization.index', [
            'todaysTasks' => $todaysTasks,
            'overdueTasks' => $overdueTasks,
            'completedToday' => $completedToday,
        ]);
    }
}
