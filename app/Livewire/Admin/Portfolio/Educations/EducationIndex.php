<?php

namespace App\Livewire\Admin\Portfolio\Educations;

use App\Models\Education;
use App\Services\EducationService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class EducationIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(EducationService $service, int $id): void
    {
        $service->delete(Education::findOrFail($id));
        session()->flash('success', 'Education deleted successfully.');
    }

    public function render()
    {
        $query = Education::query()->ordered();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('degree_title', 'like', '%'.$this->search.'%')
                    ->orWhere('institution', 'like', '%'.$this->search.'%');
            });
        }

        return view('livewire.admin.portfolio.educations.index', [
            'educations' => $query->paginate(10),
        ]);
    }
}
