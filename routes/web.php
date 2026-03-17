<?php

use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\ExperienceForm;
use App\Livewire\Admin\ExperienceIndex;
use App\Livewire\Admin\FileManager;
use App\Livewire\Admin\Login;
use App\Livewire\Admin\ProfileEdit;
use App\Livewire\Admin\SkillForm;
use App\Livewire\Admin\SkillIndex;
use App\Livewire\Admin\TechnologyForm;
use App\Livewire\Admin\TechnologyIndex;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// routes
Route::prefix('admin')->group(function () {
    Route::get('/', fn () => redirect()->route('admin.dashboard'));

    Route::get('/login', Login::class)
        ->middleware('guest')
        ->name('admin.login');

    Route::get('/dashboard', Dashboard::class)
        ->middleware('auth')
        ->name('admin.dashboard');

    Route::middleware('auth')->group(function () {
        Route::get('/profile', ProfileEdit::class)->name('admin.profile.edit');

        Route::get('/skills', SkillIndex::class)->name('admin.skills.index');
        Route::get('/skills/create', SkillForm::class)->name('admin.skills.create');
        Route::get('/skills/{skill}/edit', SkillForm::class)->name('admin.skills.edit');

        Route::get('/technologies', TechnologyIndex::class)->name('admin.technologies.index');
        Route::get('/technologies/create', TechnologyForm::class)->name('admin.technologies.create');
        Route::get('/technologies/{technology}/edit', TechnologyForm::class)->name('admin.technologies.edit');

        Route::get('/experiences', ExperienceIndex::class)->name('admin.experiences.index');
        Route::get('/experiences/create', ExperienceForm::class)->name('admin.experiences.create');
        Route::get('/experiences/{experience}/edit', ExperienceForm::class)->name('admin.experiences.edit');

        Route::get('/files', FileManager::class)->name('admin.files.index');
    });

    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('admin.login');
    })->middleware('auth')->name('admin.logout');
});
