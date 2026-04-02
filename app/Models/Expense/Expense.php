<?php

namespace App\Models\Expense;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Expense extends Model
{
    protected $fillable = [
        'expense_category_id',
        'amount',
        'note',
        'spent_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'spent_at' => 'date',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function scopeForDate(Builder $query, Carbon $date): Builder
    {
        return $query->whereDate('spent_at', $date);
    }

    public function scopeForDateRange(Builder $query, string $start, string $end): Builder
    {
        return $query->whereBetween('spent_at', [$start, $end]);
    }

    public function scopeForMonth(Builder $query, int $year, int $month): Builder
    {
        return $query->whereYear('spent_at', $year)->whereMonth('spent_at', $month);
    }

    public function scopeForWeek(Builder $query, Carbon $date): Builder
    {
        $start = $date->copy()->startOfWeek(Carbon::MONDAY);
        $end = $date->copy()->endOfWeek(Carbon::SUNDAY);

        return $query->whereBetween('spent_at', [$start, $end]);
    }

    public function scopeForCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where('expense_category_id', $categoryId);
    }
}
