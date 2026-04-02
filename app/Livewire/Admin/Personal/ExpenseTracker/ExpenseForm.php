<?php

namespace App\Livewire\Admin\Personal\ExpenseTracker;

use App\Models\Expense\Expense;
use App\Services\ExpenseCategoryService;
use App\Services\ExpenseService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class ExpenseForm extends Component
{
    public ?int $expenseId = null;

    public string $expense_category_id = '';

    public string $amount = '';

    public string $note = '';

    public string $spent_at = '';

    public Collection $categories;

    public function mount(?Expense $expense, ExpenseCategoryService $categoryService): void
    {
        $categoryService->seedDefaultsIfEmpty();
        $this->categories = $categoryService->getAllOrdered();

        if ($expense && $expense->exists) {
            $this->expenseId = $expense->id;
            $this->expense_category_id = (string) $expense->expense_category_id;
            $this->amount = (string) $expense->amount;
            $this->note = $expense->note ?? '';
            $this->spent_at = $expense->spent_at->format('Y-m-d');
        } else {
            $this->spent_at = now()->format('Y-m-d');
        }
    }

    public function save(ExpenseService $service): void
    {
        $validated = $this->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0.01|max:99999999.99',
            'note' => 'nullable|string|max:500',
            'spent_at' => 'required|date|before_or_equal:today',
        ]);

        if ($this->expenseId) {
            $expense = Expense::findOrFail($this->expenseId);
            $service->updateExpense($expense, $validated);
            session()->flash('success', 'Expense updated successfully.');
        } else {
            $service->createExpense($validated);
            session()->flash('success', 'Expense created successfully.');
        }

        $this->redirect(route('admin.personal.expense-tracker.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.personal.expense-tracker.form');
    }
}
