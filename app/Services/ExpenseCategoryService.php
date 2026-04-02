<?php

namespace App\Services;

use App\Models\Expense\ExpenseCategory;
use Illuminate\Support\Collection;

class ExpenseCategoryService
{
    public function getAllOrdered(): Collection
    {
        return ExpenseCategory::query()->ordered()->get();
    }

    public function createCategory(array $data): ExpenseCategory
    {
        return ExpenseCategory::create($data);
    }

    public function updateCategory(ExpenseCategory $category, array $data): ExpenseCategory
    {
        $category->update($data);

        return $category;
    }

    public function deleteCategory(ExpenseCategory $category): void
    {
        if ($category->is_default) {
            throw new \Exception('Cannot delete a default category.');
        }

        $category->delete();
    }

    public function seedDefaultsIfEmpty(): void
    {
        if (ExpenseCategory::count() === 0) {
            $categories = [
                ['name' => 'Food', 'color' => '#22c55e', 'is_default' => true, 'sort_order' => 1],
                ['name' => 'Transport', 'color' => '#3b82f6', 'is_default' => true, 'sort_order' => 2],
                ['name' => 'Bills', 'color' => '#f59e0b', 'is_default' => true, 'sort_order' => 3],
                ['name' => 'Shopping', 'color' => '#a78bfa', 'is_default' => true, 'sort_order' => 4],
                ['name' => 'Other', 'color' => '#6b7280', 'is_default' => true, 'sort_order' => 5],
            ];

            foreach ($categories as $category) {
                ExpenseCategory::create($category);
            }
        }
    }
}
