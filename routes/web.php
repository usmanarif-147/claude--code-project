<?php

use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\PortfolioController;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\FileManager;
use App\Livewire\Admin\Login;
use App\Livewire\Admin\ProfileEdit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [PortfolioController::class, 'index']);
Route::get('/resume/download/{template?}', [PortfolioController::class, 'downloadResume'])->name('resume.download');

Route::post('/chatbot/message', [ChatbotController::class, 'sendMessage'])
    ->middleware('throttle:20,1')
    ->name('chatbot.message');

// routes
Route::prefix('admin')->group(function () {
    Route::get('/', fn () => redirect()->route('admin.home.daily-briefing'));

    Route::get('/login', Login::class)
        ->middleware('guest')
        ->name('admin.login');

    Route::get('/dashboard', Dashboard::class)
        ->middleware('auth')
        ->name('admin.dashboard');

    Route::middleware('auth')->group(function () {
        Route::get('/profile', ProfileEdit::class)->name('admin.profile.edit');

        Route::get('/files', FileManager::class)->name('admin.files.index');
    });

    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('admin.login');
    })->middleware('auth')->name('admin.logout');
});
