<?php

namespace App\Livewire\Admin\Email\Categories;

use App\Models\Email\EmailCategory;
use App\Services\EmailCategorizationService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class EmailCategoryForm extends Component
{
    public ?int $emailCategoryId = null;

    public string $name = '';

    public string $color = '';

    public string $icon = '';

    public int $sort_order = 0;

    public function mount(?EmailCategory $emailCategory = null): void
    {
        if ($emailCategory && $emailCategory->exists) {
            $this->emailCategoryId = $emailCategory->id;
            $this->name = $emailCategory->name;
            $this->color = $emailCategory->color;
            $this->icon = $emailCategory->icon;
            $this->sort_order = $emailCategory->sort_order;
        }
    }

    public function save(EmailCategorizationService $service): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:100',
            'color' => 'required|string|in:emerald,blue,amber,primary,gray,red,fuchsia,cyan',
            'icon' => 'required|string|in:briefcase,code,star,newspaper,trash,envelope,bell,flag',
            'sort_order' => 'required|integer|min:0',
        ]);

        if ($this->emailCategoryId) {
            $service->updateCategory($this->emailCategoryId, $validated);
            $message = 'Category updated successfully.';
        } else {
            $service->createCategory($validated);
            $message = 'Category created successfully.';
        }

        session()->flash('success', $message);
        $this->redirect(route('admin.email.categories.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.email.categories.form');
    }
}
