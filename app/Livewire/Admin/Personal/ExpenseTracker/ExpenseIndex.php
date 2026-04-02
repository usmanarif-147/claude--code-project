<?php

namespace App\Livewire\Admin\Personal\ExpenseTracker;

use App\Services\ExpenseCategoryService;
use App\Services\ExpenseService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class ExpenseIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $filterCategory = '';

    #[Url]
    public string $filterMonth = '';

    public float $todayTotal = 0;

    public float $weekTotal = 0;

    public float $monthTotal = 0;

    public ?float $budgetAmount = null;

    public ?float $budgetRemaining = null;

    public string $newBudgetAmount = '';

    public array $categoryBreakdown = [];

    public Collection $categories;

    public function mount(ExpenseCategoryService $categoryService): void
    {
        $categoryService->seedDefaultsIfEmpty();
        $this->categories = $categoryService->getAllOrdered();

        if (! $this->filterMonth) {
            $this->filterMonth = now()->format('Y-m');
        }

        $this->computeStats();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCategory(): void
    {
        $this->resetPage();
    }

    public function updatedFilterMonth(): void
    {
        $this->resetPage();
        $this->computeStats();
    }

    public function setBudget(ExpenseService $service): void
    {
        $this->validate([
            'newBudgetAmount' => 'required|numeric|min:0.01|max:99999999.99',
        ]);

        [$year, $month] = explode('-', $this->filterMonth);
        $service->setMonthlyBudget((int) $year, (int) $month, (float) $this->newBudgetAmount);

        $this->newBudgetAmount = '';
        $this->computeStats();

        session()->flash('success', 'Budget updated successfully.');
    }

    public function delete(ExpenseService $service, int $id): void
    {
        $expense = \App\Models\Expense\Expense::findOrFail($id);
        $service->deleteExpense($expense);
        $this->computeStats();

        session()->flash('success', 'Expense deleted successfully.');
    }

    public function render(ExpenseService $service)
    {
        [$year, $month] = explode('-', $this->filterMonth);
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString();
        $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString();

        $expenses = $service->getExpenses(
            $this->search ?: null,
            $this->filterCategory ? (int) $this->filterCategory : null,
            $startOfMonth,
            $endOfMonth,
        )->paginate(20);

        return view('livewire.admin.personal.expense-tracker.index', [
            'expenses' => $expenses,
        ]);
    }

    private function computeStats(): void
    {
        $service = app(ExpenseService::class);

        [$year, $month] = explode('-', $this->filterMonth);
        $year = (int) $year;
        $month = (int) $month;

        $this->todayTotal = $service->getTodayTotal();
        $this->weekTotal = $service->getWeekTotal();
        $this->monthTotal = $service->getMonthTotal($year, $month);

        $budget = $service->getMonthlyBudget($year, $month);
        $this->budgetAmount = $budget?->amount ? (float) $budget->amount : null;
        $this->budgetRemaining = $service->getBudgetRemaining($year, $month);

        $this->categoryBreakdown = $service->getCategoryBreakdown($year, $month)->toArray();
    }
}
