<?php

namespace App\Livewire\Admin\Email\Categories;

use App\Models\Email\EmailCategory;
use App\Services\EmailCategorizationService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class EmailCategoryIndex extends Component
{
    #[Url]
    public string $search = '';

    public function delete(EmailCategorizationService $service, int $id): void
    {
        $service->deleteCategory($id);
        session()->flash('success', 'Category deleted successfully.');
    }

    public function updateOrder(EmailCategorizationService $service, array $orderedIds): void
    {
        $service->reorderCategories($orderedIds);
        session()->flash('success', 'Category order updated successfully.');
    }

    public function render()
    {
        $query = EmailCategory::orderBy('sort_order');

        if ($this->search) {
            $query->where('name', 'like', '%'.$this->search.'%');
        }

        $categories = $query->withCount('emails')->get();

        return view('livewire.admin.email.categories.index', [
            'categories' => $categories,
        ]);
    }
}
