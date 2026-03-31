<?php

namespace App\Livewire\Admin\Portfolio\Testimonials;

use App\Models\Testimonial;
use App\Services\TestimonialService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
class TestimonialForm extends Component
{
    use WithFileUploads;

    public ?Testimonial $testimonial = null;

    public string $client_name = '';

    public string $company = '';

    public string $review = '';

    public int $rating = 5;

    public string $project_url = '';

    public bool $is_visible = true;

    public int $sort_order = 0;

    public ?string $received_at = null;

    // Image handling
    public $clientPhoto = null;

    public ?string $existingPhoto = null;

    public bool $photoRemoved = false;

    public function mount(?Testimonial $testimonial = null): void
    {
        if ($testimonial && $testimonial->exists) {
            $this->testimonial = $testimonial;
            $this->client_name = $testimonial->client_name;
            $this->company = $testimonial->company ?? '';
            $this->review = $testimonial->review;
            $this->rating = $testimonial->rating;
            $this->project_url = $testimonial->project_url ?? '';
            $this->is_visible = $testimonial->is_visible;
            $this->sort_order = $testimonial->sort_order ?? 0;
            $this->received_at = $testimonial->received_at?->format('Y-m-d');
            $this->existingPhoto = $testimonial->client_photo;
        }
    }

    public function removePhoto(): void
    {
        $this->clientPhoto = null;
        $this->existingPhoto = null;
        $this->photoRemoved = true;
    }

    public function save(TestimonialService $service): void
    {
        $validated = $this->validate([
            'client_name' => 'required|string|max:150',
            'company' => 'nullable|string|max:150',
            'review' => 'required|string|max:5000',
            'rating' => 'required|integer|min:1|max:5',
            'project_url' => 'nullable|url|max:255',
            'is_visible' => 'boolean',
            'sort_order' => 'integer|min:0',
            'received_at' => 'nullable|date',
            'clientPhoto' => 'nullable|image|max:2048|mimes:jpg,jpeg,png,webp',
        ]);

        $data = collect($validated)->except(['clientPhoto'])->toArray();

        // Handle photo removal (user clicked remove without uploading new one)
        if ($this->photoRemoved && ! $this->clientPhoto) {
            $data['client_photo'] = null;
        }

        if ($this->testimonial) {
            $service->update($this->testimonial, $data, $this->clientPhoto);
            $message = 'Testimonial updated successfully.';
        } else {
            $service->create($data, $this->clientPhoto);
            $message = 'Testimonial created successfully.';
        }

        session()->flash('success', $message);
        $this->redirect(route('admin.testimonials.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.portfolio.testimonials.form');
    }
}
