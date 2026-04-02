<?php

namespace App\Livewire\Admin\Personal\GoalsTracker;

use App\Models\Goal;
use App\Services\GoalService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class GoalForm extends Component
{
    public ?int $goalId = null;

    public string $title = '';

    public ?string $description = '';

    public string $category = 'career';

    public string $target_date = '';

    public int $progress = 0;

    public string $status = 'active';

    public function mount(?Goal $goal = null): void
    {
        if ($goal && $goal->exists) {
            $this->goalId = $goal->id;
            $this->title = $goal->title;
            $this->description = $goal->description ?? '';
            $this->category = $goal->category;
            $this->target_date = $goal->target_date->format('Y-m-d');
            $this->progress = $goal->progress;
            $this->status = $goal->status;
        }
    }

    public function save(GoalService $service): void
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'category' => 'required|string|in:career,financial,learning,health,personal',
            'target_date' => 'required|date',
            'progress' => 'required|integer|min:0|max:100',
        ]);

        if ($this->goalId) {
            $goal = Goal::findOrFail($this->goalId);
            $service->update($goal, $validated);
            $message = 'Goal updated successfully.';
        } else {
            $service->create($validated);
            $message = 'Goal created successfully.';
        }

        session()->flash('success', $message);
        $this->redirect(route('admin.personal.goals-tracker.index'), navigate: true);
    }

    public function markCompleted(GoalService $service): void
    {
        $goal = Goal::findOrFail($this->goalId);
        $service->markCompleted($goal);

        session()->flash('success', 'Goal marked as completed.');
        $this->redirect(route('admin.personal.goals-tracker.index'), navigate: true);
    }

    public function markAbandoned(GoalService $service): void
    {
        $goal = Goal::findOrFail($this->goalId);
        $service->markAbandoned($goal);

        session()->flash('success', 'Goal marked as abandoned.');
        $this->redirect(route('admin.personal.goals-tracker.index'), navigate: true);
    }

    public function reopen(GoalService $service): void
    {
        $goal = Goal::findOrFail($this->goalId);
        $service->reopen($goal);

        session()->flash('success', 'Goal reopened.');
        $this->redirect(route('admin.personal.goals-tracker.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.personal.goals-tracker.form');
    }
}
