<?php

namespace Database\Seeders;

use App\Models\Expense\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Food', 'color' => '#22c55e', 'is_default' => true, 'sort_order' => 1],
            ['name' => 'Transport', 'color' => '#3b82f6', 'is_default' => true, 'sort_order' => 2],
            ['name' => 'Bills', 'color' => '#f59e0b', 'is_default' => true, 'sort_order' => 3],
            ['name' => 'Shopping', 'color' => '#a78bfa', 'is_default' => true, 'sort_order' => 4],
            ['name' => 'Other', 'color' => '#6b7280', 'is_default' => true, 'sort_order' => 5],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::firstOrCreate(
                ['name' => $category['name']],
                $category,
            );
        }
    }
}
