<?php

namespace App\Services;

use App\Models\Task\TaskCategory;
use Illuminate\Support\Collection;

class TaskCategoryService
{
    public function getAll(): Collection
    {
        return TaskCategory::query()->ordered()->get();
    }

    public function create(array $data): TaskCategory
    {
        return TaskCategory::create($data);
    }

    public function update(TaskCategory $category, array $data): TaskCategory
    {
        $category->update($data);

        return $category;
    }

    public function delete(TaskCategory $category): void
    {
        if ($category->tasks()->count() > 0) {
            throw new \Exception('Cannot delete category with assigned tasks.');
        }

        $category->delete();
    }
}
