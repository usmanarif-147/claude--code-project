<?php

namespace App\Livewire\Admin\Personal\ExpenseTracker;

use App\Models\Expense\ExpenseCategory;
use App\Services\ExpenseCategoryService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class ExpenseCategoryIndex extends Component
{
    public Collection $categories;

    public ?int $editingCategoryId = null;

    public string $name = '';

    public string $color = '#7c3aed';

    public bool $showForm = false;

    public function mount(ExpenseCategoryService $service): void
    {
        $service->seedDefaultsIfEmpty();
        $this->loadCategories();
    }

    public function addCategory(ExpenseCategoryService $service): void
    {
        $this->validate([
            'name' => 'required|string|max:100|unique:expense_categories,name',
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $service->createCategory([
            'name' => $this->name,
            'color' => $this->color,
            'sort_order' => ExpenseCategory::max('sort_order') + 1,
        ]);

        $this->reset('name', 'showForm');
        $this->color = '#7c3aed';
        $this->loadCategories();

        session()->flash('success', 'Category added successfully.');
    }

    public function startEdit(int $id): void
    {
        $category = ExpenseCategory::findOrFail($id);

        $this->editingCategoryId = $category->id;
        $this->name = $category->name;
        $this->color = $category->color;
    }

    public function updateCategory(ExpenseCategoryService $service): void
    {
        $this->validate([
            'name' => 'required|string|max:100|unique:expense_categories,name,'.$this->editingCategoryId,
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $category = ExpenseCategory::findOrFail($this->editingCategoryId);
        $service->updateCategory($category, [
            'name' => $this->name,
            'color' => $this->color,
        ]);

        $this->cancelEdit();
        $this->loadCategories();

        session()->flash('success', 'Category updated successfully.');
    }

    public function cancelEdit(): void
    {
        $this->editingCategoryId = null;
        $this->name = '';
        $this->color = '#7c3aed';
    }

    public function deleteCategory(ExpenseCategoryService $service, int $id): void
    {
        $category = ExpenseCategory::findOrFail($id);

        try {
            $service->deleteCategory($category);
            $this->loadCategories();
            session()->flash('success', 'Category deleted successfully.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.personal.expense-tracker.categories');
    }

    private function loadCategories(): void
    {
        $this->categories = ExpenseCategory::query()
            ->ordered()
            ->withCount('expenses')
            ->get();
    }
}
