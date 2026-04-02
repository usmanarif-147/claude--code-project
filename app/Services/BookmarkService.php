<?php

namespace App\Services;

use App\Models\Bookmark\Bookmark;
use App\Models\Bookmark\BookmarkCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BookmarkService
{
    public function getBookmarks(?string $search, ?string $categoryId, int $perPage = 15): LengthAwarePaginator
    {
        $query = Bookmark::with('category')->orderByDesc('created_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%'.$search.'%')
                    ->orWhere('url', 'like', '%'.$search.'%');
            });
        }

        if ($categoryId) {
            $query->where('bookmark_category_id', $categoryId);
        }

        return $query->paginate($perPage);
    }

    public function createBookmark(array $data): Bookmark
    {
        return Bookmark::create($data);
    }

    public function deleteBookmark(int $bookmarkId): void
    {
        Bookmark::findOrFail($bookmarkId)->delete();
    }

    public function getCategories(): Collection
    {
        return BookmarkCategory::orderBy('sort_order')->orderBy('name')->get();
    }

    public function createCategory(string $name): BookmarkCategory
    {
        return BookmarkCategory::create([
            'name' => $name,
            'slug' => Str::slug($name),
            'is_default' => false,
            'sort_order' => 0,
        ]);
    }

    public function deleteCategory(int $categoryId): void
    {
        $category = BookmarkCategory::findOrFail($categoryId);

        if ($category->is_default) {
            throw new \RuntimeException('Cannot delete default category.');
        }

        if ($category->bookmarks()->count() > 0) {
            throw new \RuntimeException('Cannot delete a category that has bookmarks. Move or delete the bookmarks first.');
        }

        $category->delete();
    }

    public function getCategoryCounts(): Collection
    {
        return BookmarkCategory::withCount('bookmarks')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}
