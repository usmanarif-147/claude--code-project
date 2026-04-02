<?php

use App\Livewire\Admin\Personal\ExpenseTracker\ExpenseCategoryIndex;
use App\Livewire\Admin\Personal\ExpenseTracker\ExpenseForm;
use App\Livewire\Admin\Personal\ExpenseTracker\ExpenseIndex;
use Illuminate\Support\Facades\Route;

Route::get('/personal/expense-tracker', ExpenseIndex::class)->name('admin.personal.expense-tracker.index');
Route::get('/personal/expense-tracker/create', ExpenseForm::class)->name('admin.personal.expense-tracker.create');
Route::get('/personal/expense-tracker/{expense}/edit', ExpenseForm::class)->name('admin.personal.expense-tracker.edit');
Route::get('/personal/expense-tracker/categories', ExpenseCategoryIndex::class)->name('admin.personal.expense-tracker.categories');
