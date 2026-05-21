<?php

namespace App\Livewire\Admin\Portfolio\Skills;

use App\Models\Category;
use App\Models\Skill\Skill;
use App\Services\SkillService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class SkillForm extends Component
{
    public ?Skill $skill = null;

    public string $title = '';

    public ?int $category_id = null;

    public int $proficiency = 0;

    public string $icon = '';

    public int $sort_order = 0;

    public bool $is_active = true;

    public function mount(?Skill $skill = null): void
    {
        if ($skill && $skill->exists) {
            $this->skill = $skill;
            $this->title = $skill->title;
            $this->category_id = $skill->category_id;
            $this->proficiency = $skill->proficiency ?? 0;
            $this->icon = $skill->icon ?? '';
            $this->sort_order = $skill->sort_order ?? 0;
            $this->is_active = $skill->is_active;
        }
    }

    public function save(SkillService $service): void
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:categories,id',
            'proficiency' => 'integer|min:0|max:100',
            'icon' => 'nullable|string|max:5000',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($this->skill) {
            $service->update($this->skill, $validated);
            $message = 'Skill updated successfully.';
        } else {
            $service->create($validated);
            $message = 'Skill created successfully.';
        }

        session()->flash('success', $message);
        $this->redirect(route('admin.skills.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.portfolio.skills.form', [
            'categories' => Category::active()->ordered()->get(),
        ]);
    }
}
