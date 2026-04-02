<?php

namespace App\Services;

use App\Models\Expense\Expense;
use App\Models\Expense\MonthlyBudget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ExpenseService
{
    public function getExpenses(?string $search, ?int $categoryId, ?string $dateFrom, ?string $dateTo): Builder
    {
        $query = Expense::query()->with('category');

        if ($search) {
            $query->where('note', 'like', '%'.$search.'%');
        }

        if ($categoryId) {
            $query->forCategory($categoryId);
        }

        if ($dateFrom && $dateTo) {
            $query->forDateRange($dateFrom, $dateTo);
        } elseif ($dateFrom) {
            $query->where('spent_at', '>=', $dateFrom);
        } elseif ($dateTo) {
            $query->where('spent_at', '<=', $dateTo);
        }

        return $query->orderByDesc('spent_at')->orderByDesc('id');
    }

    public function createExpense(array $data): Expense
    {
        return Expense::create($data);
    }

    public function updateExpense(Expense $expense, array $data): Expense
    {
        $expense->update($data);

        return $expense;
    }

    public function deleteExpense(Expense $expense): void
    {
        $expense->delete();
    }

    public function getTodayTotal(): float
    {
        return (float) Expense::query()->forDate(Carbon::today())->sum('amount');
    }

    public function getWeekTotal(?Carbon $date = null): float
    {
        $date = $date ?? Carbon::today();

        return (float) Expense::query()->forWeek($date)->sum('amount');
    }

    public function getMonthTotal(int $year, int $month): float
    {
        return (float) Expense::query()->forMonth($year, $month)->sum('amount');
    }

    public function getCategoryBreakdown(int $year, int $month): Collection
    {
        return Expense::query()
            ->forMonth($year, $month)
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->selectRaw('expense_categories.name, expense_categories.color, SUM(expenses.amount) as total')
            ->groupBy('expense_categories.id', 'expense_categories.name', 'expense_categories.color')
            ->having('total', '>', 0)
            ->orderByDesc('total')
            ->get();
    }

    public function getMonthlyBudget(int $year, int $month): ?MonthlyBudget
    {
        return MonthlyBudget::where('year', $year)->where('month', $month)->first();
    }

    public function setMonthlyBudget(int $year, int $month, float $amount): MonthlyBudget
    {
        return MonthlyBudget::updateOrCreate(
            ['year' => $year, 'month' => $month],
            ['amount' => $amount],
        );
    }

    public function getBudgetRemaining(int $year, int $month): ?float
    {
        $budget = $this->getMonthlyBudget($year, $month);

        if (! $budget) {
            return null;
        }

        $spent = $this->getMonthTotal($year, $month);

        return (float) $budget->amount - $spent;
    }

    public function getDailyTotals(int $year, int $month): Collection
    {
        return Expense::query()
            ->forMonth($year, $month)
            ->selectRaw('DATE(spent_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }
}
