<?php

namespace App\Livewire\Admin\Tasks\RecurringTasks;

use App\Models\Task\RecurringTask;
use App\Models\Task\TaskCategory;
use App\Services\RecurringTaskService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class RecurringTaskForm extends Component
{
    public ?RecurringTask $recurringTask = null;

    public string $title = '';

    public string $description = '';

    public string $category_id = '';

    public string $frequency = 'daily';

    public ?int $day_of_week = null;

    public ?int $day_of_month = null;

    public string $priority = 'medium';

    public bool $is_active = true;

    public function mount(?RecurringTask $recurringTask = null): void
    {
        if ($recurringTask && $recurringTask->exists) {
            abort_unless($recurringTask->user_id === auth()->id(), 403);
            $this->recurringTask = $recurringTask;
            $this->title = $recurringTask->title;
            $this->description = $recurringTask->description ?? '';
            $this->category_id = $recurringTask->category_id ? (string) $recurringTask->category_id : '';
            $this->frequency = $recurringTask->frequency;
            $this->day_of_week = $recurringTask->day_of_week;
            $this->day_of_month = $recurringTask->day_of_month;
            $this->priority = $recurringTask->priority;
            $this->is_active = $recurringTask->is_active;
        }
    }

    public function save(RecurringTaskService $service): void
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'category_id' => 'nullable|exists:task_categories,id',
            'frequency' => 'required|in:daily,weekly,monthly',
            'day_of_week' => 'required_if:frequency,weekly|nullable|integer|min:0|max:6',
            'day_of_month' => 'required_if:frequency,monthly|nullable|integer|min:1|max:31',
            'priority' => 'required|in:low,medium,high,urgent',
            'is_active' => 'boolean',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['category_id'] = $validated['category_id'] ?: null;

        // Nullify irrelevant day fields based on frequency
        if ($validated['frequency'] !== 'weekly') {
            $validated['day_of_week'] = null;
        }
        if ($validated['frequency'] !== 'monthly') {
            $validated['day_of_month'] = null;
        }

        if ($this->recurringTask) {
            $service->update($this->recurringTask, $validated);
            $message = 'Recurring task updated successfully.';
        } else {
            $service->create($validated);
            $message = 'Recurring task created successfully.';
        }

        session()->flash('success', $message);
        $this->redirect(route('admin.tasks.recurring.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.tasks.recurring-tasks.form', [
            'categories' => TaskCategory::query()->ordered()->get(),
        ]);
    }
}
