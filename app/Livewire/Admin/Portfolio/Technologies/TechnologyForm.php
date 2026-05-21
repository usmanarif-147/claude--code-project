<?php

namespace App\Livewire\Admin\Portfolio\Technologies;

use App\Models\Category;
use App\Models\Technology;
use App\Services\TechnologyService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class TechnologyForm extends Component
{
    public ?Technology $technology = null;

    public string $name = '';

    public ?int $category_id = null;

    public int $sort_order = 0;

    public bool $is_active = true;

    public function mount(?Technology $technology = null): void
    {
        if ($technology && $technology->exists) {
            $this->technology = $technology;
            $this->name = $technology->name;
            $this->category_id = $technology->category_id;
            $this->sort_order = $technology->sort_order ?? 0;
            $this->is_active = $technology->is_active;
        }
    }

    public function save(TechnologyService $service): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:categories,id',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($this->technology) {
            $service->update($this->technology, $validated);
            $message = 'Technology updated successfully.';
        } else {
            $service->create($validated);
            $message = 'Technology created successfully.';
        }

        session()->flash('success', $message);
        $this->redirect(route('admin.technologies.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.portfolio.technologies.form', [
            'categories' => Category::active()->ordered()->get(),
        ]);
    }
}
