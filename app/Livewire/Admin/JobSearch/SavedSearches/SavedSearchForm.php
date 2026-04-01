<?php

namespace App\Livewire\Admin\JobSearch\SavedSearches;

use App\Models\JobSearch\SavedSearch;
use App\Models\JobSearchFilter;
use App\Services\SavedSearchService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class SavedSearchForm extends Component
{
    public ?int $savedSearchId = null;

    public string $name = '';

    public array $preferred_titles = [];

    public array $preferred_tech = [];

    public string $location_type = '';

    public string $location_value = '';

    public ?int $min_salary = null;

    public string $salary_currency = 'USD';

    public string $experience_level = '';

    public array $enabled_platforms = [];

    public bool $is_active = true;

    public string $titleInput = '';

    public string $techInput = '';

    public function mount(?int $savedSearch = null): void
    {
        if ($savedSearch) {
            $model = SavedSearch::query()
                ->forUser(auth()->id())
                ->findOrFail($savedSearch);

            $this->savedSearchId = $model->id;
            $this->name = $model->name;
            $this->preferred_titles = $model->preferred_titles ?? [];
            $this->preferred_tech = $model->preferred_tech ?? [];
            $this->location_type = $model->location_type ?? '';
            $this->location_value = $model->location_value ?? '';
            $this->min_salary = $model->min_salary;
            $this->salary_currency = $model->salary_currency ?? 'USD';
            $this->experience_level = $model->experience_level ?? '';
            $this->enabled_platforms = $model->enabled_platforms ?? [];
            $this->is_active = $model->is_active;
        }
    }

    public function addTitle(): void
    {
        $value = trim($this->titleInput);

        if ($value !== '' && ! in_array($value, $this->preferred_titles)) {
            $this->preferred_titles[] = $value;
        }

        $this->titleInput = '';
    }

    public function removeTitle(int $index): void
    {
        unset($this->preferred_titles[$index]);
        $this->preferred_titles = array_values($this->preferred_titles);
    }

    public function addTech(): void
    {
        $value = trim($this->techInput);

        if ($value !== '' && ! in_array($value, $this->preferred_tech)) {
            $this->preferred_tech[] = $value;
        }

        $this->techInput = '';
    }

    public function removeTech(int $index): void
    {
        unset($this->preferred_tech[$index]);
        $this->preferred_tech = array_values($this->preferred_tech);
    }

    public function save(SavedSearchService $service): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'preferred_titles' => 'nullable|array|max:20',
            'preferred_titles.*' => 'string|max:100',
            'preferred_tech' => 'nullable|array|max:20',
            'preferred_tech.*' => 'string|max:100',
            'location_type' => 'nullable|string|in:,remote,onsite,hybrid',
            'location_value' => 'nullable|string|max:255',
            'min_salary' => 'nullable|integer|min:0|max:999999',
            'salary_currency' => 'required|string|size:3',
            'experience_level' => 'nullable|string|in:,junior,mid,senior,lead',
            'enabled_platforms' => 'nullable|array',
            'enabled_platforms.*' => 'string|in:jsearch,remoteok,remotive,adzuna,rozee,mustakbil',
            'is_active' => 'boolean',
        ]);

        // Convert empty strings to null for database storage
        $validated['location_type'] = $validated['location_type'] ?: null;
        $validated['location_value'] = $validated['location_value'] ?: null;
        $validated['experience_level'] = $validated['experience_level'] ?: null;

        if ($this->savedSearchId) {
            $savedSearch = SavedSearch::query()
                ->forUser(auth()->id())
                ->findOrFail($this->savedSearchId);

            $service->update($savedSearch, $validated);
            $message = 'Saved search updated successfully.';
        } else {
            $service->create(auth()->id(), $validated);
            $message = 'Saved search created successfully.';
        }

        session()->flash('success', $message);
        $this->redirect(route('admin.job-search.saved-searches.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.job-search.saved-searches.form', [
            'platforms' => JobSearchFilter::ALL_PLATFORMS,
        ]);
    }
}
