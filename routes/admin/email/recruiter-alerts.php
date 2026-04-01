<?php

use App\Livewire\Admin\Email\RecruiterAlerts\RecruiterAlertIndex;
use App\Livewire\Admin\Email\RecruiterAlerts\RecruiterAlertSettings;
use Illuminate\Support\Facades\Route;

Route::get('/email/recruiter-alerts', RecruiterAlertIndex::class)->name('admin.email.recruiter-alerts.index');
Route::get('/email/recruiter-alerts/settings', RecruiterAlertSettings::class)->name('admin.email.recruiter-alerts.settings');
