<?php

use App\Livewire\Admin\Email\Digest\EmailDigestIndex;
use Illuminate\Support\Facades\Route;

Route::get('/email/digest', EmailDigestIndex::class)->name('admin.email.digest.index');
