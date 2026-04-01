<?php

namespace App\Livewire\Admin\Email\Categorize;

use App\Models\Email\Email;
use App\Models\Email\EmailCategoryCorrection;
use App\Services\EmailCategorizationService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class CategorizeDashboard extends Component
{
    use WithPagination;

    public Collection $categories;

    public array $categoryStats = [];

    #[Url]
    public ?int $selectedCategoryId = null;

    #[Url]
    public string $search = '';

    public float $accuracyRate = 100.0;

    public int $uncategorizedCount = 0;

    public function mount(EmailCategorizationService $service): void
    {
        $this->categories = $service->getCategories();
        $this->loadStats($service);
    }

    public function filterByCategory(?int $categoryId): void
    {
        $this->selectedCategoryId = $categoryId;
        $this->resetPage();
    }

    public function reassignCategory(EmailCategorizationService $service, int $emailId, int $newCategoryId): void
    {
        $email = Email::findOrFail($emailId);
        $service->reassignCategory($email, $newCategoryId);

        $this->categories = $service->getCategories();
        $this->loadStats($service);

        session()->flash('success', 'Email category updated successfully.');
    }

    public function categorizeAll(EmailCategorizationService $service): void
    {
        $count = $service->categorizeUncategorized();

        $this->categories = $service->getCategories();
        $this->loadStats($service);

        session()->flash('success', "Successfully categorized {$count} emails.");
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    protected function loadStats(EmailCategorizationService $service): void
    {
        $this->categoryStats = $service->getCategoryStats();
        $this->accuracyRate = $service->getAccuracyRate();
        $this->uncategorizedCount = Email::whereNull('category_id')->count();
    }

    public function render()
    {
        $query = Email::query()->orderByDesc('received_at');

        if ($this->selectedCategoryId === 0) {
            $query->whereNull('category_id');
        } elseif ($this->selectedCategoryId) {
            $query->where('category_id', $this->selectedCategoryId);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('subject', 'like', '%'.$this->search.'%')
                    ->orWhere('from_email', 'like', '%'.$this->search.'%')
                    ->orWhere('from_name', 'like', '%'.$this->search.'%');
            });
        }

        return view('livewire.admin.email.categorize.dashboard', [
            'emails' => $query->paginate(15),
            'totalCategorized' => Email::whereNotNull('category_id')->count(),
            'totalCorrections' => EmailCategoryCorrection::count(),
        ]);
    }
}
