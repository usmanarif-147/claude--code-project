<?php

namespace App\Livewire\Admin\Portfolio\Educations;

use App\Models\Education;
use App\Services\EducationService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class EducationForm extends Component
{
    public ?Education $education = null;

    public string $degree_title = '';

    public string $institution = '';

    public ?string $start_date = null;

    public ?string $end_date = null;

    public int $sort_order = 0;

    public function mount(?Education $education = null): void
    {
        if ($education && $education->exists) {
            $this->education = $education;
            $this->degree_title = $education->degree_title;
            $this->institution = $education->institution;
            $this->start_date = $education->start_date?->format('Y-m-d');
            $this->end_date = $education->end_date?->format('Y-m-d');
            $this->sort_order = $education->sort_order ?? 0;
        }
    }

    public function save(EducationService $service): void
    {
        $validated = $this->validate([
            'degree_title' => 'required|string|max:255',
            'institution' => 'required|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'sort_order' => 'integer|min:0',
        ]);

        if ($this->education) {
            $service->update($this->education, $validated);
            $message = 'Education updated successfully.';
        } else {
            $service->create($validated);
            $message = 'Education created successfully.';
        }

        session()->flash('success', $message);
        $this->redirect(route('admin.educations.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.portfolio.educations.form');
    }
}
