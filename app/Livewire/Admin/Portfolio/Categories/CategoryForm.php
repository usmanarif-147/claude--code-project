<?php

namespace App\Livewire\Admin\Portfolio\Categories;

use App\Models\Category;
use App\Services\CategoryService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class CategoryForm extends Component
{
    public ?Category $category = null;

    public string $name = '';

    public int $sort_order = 0;

    public bool $is_active = true;

    public function mount(?Category $category = null): void
    {
        if ($category && $category->exists) {
            $this->category = $category;
            $this->name = $category->name;
            $this->sort_order = $category->sort_order ?? 0;
            $this->is_active = $category->is_active;
        }
    }

    public function save(CategoryService $service): void
    {
        $rules = [
            'name' => [
                'required',
                'string',
                'max:100',
                'unique:categories,name'.($this->category ? ','.$this->category->id : ''),
            ],
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ];

        $validated = $this->validate($rules);

        if ($this->category) {
            $service->update($this->category, $validated);
            $message = 'Category updated successfully.';
        } else {
            $service->create($validated);
            $message = 'Category created successfully.';
        }

        session()->flash('success', $message);
        $this->redirect(route('admin.categories.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.portfolio.categories.form');
    }
}
