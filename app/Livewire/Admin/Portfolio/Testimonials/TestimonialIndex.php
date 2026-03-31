<?php

namespace App\Livewire\Admin\Portfolio\Testimonials;

use App\Models\Testimonial;
use App\Services\TestimonialService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class TestimonialIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $visibleFilter = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingVisibleFilter(): void
    {
        $this->resetPage();
    }

    public function delete(TestimonialService $service, int $id): void
    {
        $service->delete(Testimonial::findOrFail($id));
        session()->flash('success', 'Testimonial deleted successfully.');
    }

    public function render()
    {
        $query = Testimonial::query()->ordered();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('client_name', 'like', '%'.$this->search.'%')
                    ->orWhere('company', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->visibleFilter === 'visible') {
            $query->where('is_visible', true);
        } elseif ($this->visibleFilter === 'hidden') {
            $query->where('is_visible', false);
        }

        return view('livewire.admin.portfolio.testimonials.index', [
            'testimonials' => $query->paginate(10),
        ]);
    }
}
