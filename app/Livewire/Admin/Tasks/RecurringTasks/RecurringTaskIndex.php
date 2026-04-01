<?php

namespace App\Livewire\Admin\Tasks\RecurringTasks;

use App\Models\Task\RecurringTask;
use App\Services\RecurringTaskService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class RecurringTaskIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $frequencyFilter = 'all';

    #[Url]
    public string $statusFilter = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFrequencyFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function toggleActive(RecurringTaskService $service, int $id): void
    {
        $recurringTask = RecurringTask::query()->forUser(auth()->id())->findOrFail($id);
        $service->toggleActive($recurringTask);

        $message = $recurringTask->is_active ? 'Recurring task resumed.' : 'Recurring task paused.';
        session()->flash('success', $message);
    }

    public function delete(RecurringTaskService $service, int $id): void
    {
        $recurringTask = RecurringTask::query()->forUser(auth()->id())->findOrFail($id);

        try {
            $service->delete($recurringTask);
            session()->flash('success', 'Recurring task deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render(RecurringTaskService $service)
    {
        $recurringTasks = $service->getFilteredRecurringTasks(
            auth()->id(),
            $this->search ?: null,
            $this->frequencyFilter,
            $this->statusFilter,
        )->paginate(10);

        return view('livewire.admin.tasks.recurring-tasks.index', [
            'recurringTasks' => $recurringTasks,
        ]);
    }
}
