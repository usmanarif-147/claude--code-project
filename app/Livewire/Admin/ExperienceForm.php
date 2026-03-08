<?php

namespace App\Livewire\Admin;

use App\Models\Experience;
use App\Models\ExperienceResponsibility;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class ExperienceForm extends Component
{
    public ?Experience $experience = null;

    public string $role = '';
    public string $company = '';
    public ?string $start_date = null;
    public ?string $end_date = null;
    public bool $is_current = false;
    public int $sort_order = 0;
    public bool $is_active = true;

    public array $responsibilities = [];

    public function mount(?Experience $experience = null): void
    {
        if ($experience && $experience->exists) {
            $this->experience = $experience;
            $this->role = $experience->role;
            $this->company = $experience->company;
            $this->start_date = $experience->start_date?->format('Y-m-d');
            $this->end_date = $experience->end_date?->format('Y-m-d');
            $this->is_current = $experience->is_current;
            $this->sort_order = $experience->sort_order ?? 0;
            $this->is_active = $experience->is_active;

            $this->responsibilities = $experience->responsibilities()
                ->orderBy('sort_order')
                ->get()
                ->map(fn ($r) => [
                    'id' => $r->id,
                    'description' => $r->description,
                    'sort_order' => $r->sort_order,
                ])
                ->toArray();
        }
    }

    public function addResponsibility(): void
    {
        $this->responsibilities[] = [
            'id' => null,
            'description' => '',
            'sort_order' => count($this->responsibilities),
        ];
    }

    public function removeResponsibility(int $index): void
    {
        array_splice($this->responsibilities, $index, 1);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'role' => 'required|string|max:255',
            'company' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_current' => 'boolean',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
            'responsibilities' => 'array',
            'responsibilities.*.description' => 'required|string|max:1000',
            'responsibilities.*.sort_order' => 'integer|min:0',
        ]);

        if ($this->is_current) {
            $validated['end_date'] = null;
        }

        DB::transaction(function () use ($validated) {
            $experienceData = collect($validated)->except('responsibilities')->toArray();

            if ($this->experience) {
                $this->experience->update($experienceData);
                $experience = $this->experience;
            } else {
                $experience = Experience::create($experienceData);
            }

            // Sync responsibilities
            $keepIds = [];

            foreach ($validated['responsibilities'] ?? [] as $resp) {
                if (!empty($resp['id'] ?? null)) {
                    // Update existing
                    $responsibility = ExperienceResponsibility::find($resp['id']);
                    if ($responsibility) {
                        $responsibility->update([
                            'description' => $resp['description'],
                            'sort_order' => $resp['sort_order'],
                        ]);
                        $keepIds[] = $responsibility->id;
                    }
                } else {
                    // Create new
                    $new = $experience->responsibilities()->create([
                        'description' => $resp['description'],
                        'sort_order' => $resp['sort_order'],
                    ]);
                    $keepIds[] = $new->id;
                }
            }

            // Delete removed responsibilities
            $experience->responsibilities()->whereNotIn('id', $keepIds)->delete();
        });

        $message = $this->experience ? 'Experience updated successfully.' : 'Experience created successfully.';
        session()->flash('success', $message);
        $this->redirect(route('admin.experiences.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.experience-form');
    }
}
