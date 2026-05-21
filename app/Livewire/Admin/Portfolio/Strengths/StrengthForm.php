<?php

namespace App\Livewire\Admin\Portfolio\Strengths;

use App\Models\Strength;
use App\Services\StrengthService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class StrengthForm extends Component
{
    public ?Strength $strength = null;

    public string $title = '';

    public string $icon = '';

    public int $sort_order = 0;

    public bool $is_active = true;

    public function mount(?Strength $strength = null): void
    {
        if ($strength && $strength->exists) {
            $this->strength = $strength;
            $this->title = $strength->title;
            $this->icon = $strength->icon ?? '';
            $this->sort_order = $strength->sort_order ?? 0;
            $this->is_active = $strength->is_active;
        }
    }

    public function save(StrengthService $service): void
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'icon' => 'nullable|string|max:5000',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($this->strength) {
            $service->update($this->strength, $validated);
            $message = 'Strength updated successfully.';
        } else {
            $service->create($validated);
            $message = 'Strength created successfully.';
        }

        session()->flash('success', $message);
        $this->redirect(route('admin.strengths.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.portfolio.strengths.form');
    }
}
