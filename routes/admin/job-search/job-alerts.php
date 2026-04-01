<?php

use App\Livewire\Admin\JobSearch\JobAlerts\JobAlertIndex;
use App\Livewire\Admin\JobSearch\JobAlerts\JobAlertSettings;
use Illuminate\Support\Facades\Route;

Route::get('/job-search/alerts', JobAlertIndex::class)->name('admin.job-search.alerts.index');
Route::get('/job-search/alerts/settings', JobAlertSettings::class)->name('admin.job-search.alerts.settings');
