<?php

namespace App\Livewire\Admin;

use App\Models\Technology;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class TechnologyForm extends Component
{
    public ?Technology $technology = null;

    public string $name = '';
    public string $category = 'frontend';
    public int $sort_order = 0;
    public bool $is_active = true;

    public function mount(?Technology $technology = null): void
    {
        if ($technology && $technology->exists) {
            $this->technology = $technology;
            $this->name = $technology->name;
            $this->category = $technology->category;
            $this->sort_order = $technology->sort_order ?? 0;
            $this->is_active = $technology->is_active;
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:frontend,backend,database_tools',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($this->technology) {
            $this->technology->update($validated);
            $message = 'Technology updated successfully.';
        } else {
            Technology::create($validated);
            $message = 'Technology created successfully.';
        }

        session()->flash('success', $message);
        $this->redirect(route('admin.technologies.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.technology-form');
    }
}
