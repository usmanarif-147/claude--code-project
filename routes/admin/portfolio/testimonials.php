<?php

use App\Livewire\Admin\TestimonialForm;
use App\Livewire\Admin\TestimonialIndex;
use Illuminate\Support\Facades\Route;

Route::get('/testimonials', TestimonialIndex::class)->name('admin.testimonials.index');
Route::get('/testimonials/create', TestimonialForm::class)->name('admin.testimonials.create');
Route::get('/testimonials/{testimonial}/edit', TestimonialForm::class)->name('admin.testimonials.edit');
