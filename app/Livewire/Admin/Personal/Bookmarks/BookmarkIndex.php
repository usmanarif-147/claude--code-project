<?php

namespace App\Livewire\Admin\Personal\Bookmarks;

use App\Services\BookmarkService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class BookmarkIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $filterCategory = '';

    public string $title = '';

    public string $url = '';

    public string $description = '';

    public string $bookmark_category_id = '';

    public string $newCategoryName = '';

    public bool $showAddForm = false;

    public bool $showCategoryModal = false;

    public function save(BookmarkService $service): void
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|url|max:2048',
            'description' => 'nullable|string|max:500',
            'bookmark_category_id' => 'required|exists:bookmark_categories,id',
        ]);

        $service->createBookmark([
            'title' => $this->title,
            'url' => $this->url,
            'description' => $this->description ?: null,
            'bookmark_category_id' => $this->bookmark_category_id,
        ]);

        $this->reset(['title', 'url', 'description', 'bookmark_category_id']);
        $this->showAddForm = false;
        session()->flash('success', 'Bookmark saved successfully.');
    }

    public function delete(BookmarkService $service, int $bookmarkId): void
    {
        $service->deleteBookmark($bookmarkId);
        session()->flash('success', 'Bookmark deleted.');
    }

    public function addCategory(BookmarkService $service): void
    {
        $this->validate([
            'newCategoryName' => 'required|string|max:100|unique:bookmark_categories,name',
        ]);

        $service->createCategory($this->newCategoryName);

        $this->reset('newCategoryName');
        $this->showCategoryModal = false;
        session()->flash('success', 'Category added.');
    }

    public function deleteCategory(BookmarkService $service, int $categoryId): void
    {
        try {
            $service->deleteCategory($categoryId);
            session()->flash('success', 'Category deleted.');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterCategory(): void
    {
        $this->resetPage();
    }

    public function render(BookmarkService $service)
    {
        return view('livewire.admin.personal.bookmarks.index', [
            'bookmarks' => $service->getBookmarks($this->search, $this->filterCategory, 15),
            'categories' => $service->getCategories(),
            'categoryCounts' => $service->getCategoryCounts(),
        ]);
    }
}
