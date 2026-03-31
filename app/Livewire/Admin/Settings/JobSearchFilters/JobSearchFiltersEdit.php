<?php

namespace App\Livewire\Admin\Settings\JobSearchFilters;

use App\Models\JobSearchFilter;
use App\Services\JobSearchFilterService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class JobSearchFiltersEdit extends Component
{
    public ?int $filterId = null;

    public array $preferred_titles = [];

    public array $preferred_tech = [];

    public ?string $location_type = null;

    public ?string $location_value = null;

    public ?int $min_salary = null;

    public string $salary_currency = 'USD';

    public ?string $experience_level = null;

    public string $newTitle = '';

    public string $newTech = '';

    public bool $platform_jsearch = false;

    public bool $platform_remoteok = false;

    public bool $platform_remotive = false;

    public bool $platform_adzuna = false;

    public bool $platform_rozee = false;

    public bool $platform_mustakbil = false;

    public function mount(): void
    {
        $service = app(JobSearchFilterService::class);
        $filter = $service->getOrCreateForUser(auth()->user());

        $this->filterId = $filter->id;
        $this->preferred_titles = $filter->preferred_titles ?? [];
        $this->preferred_tech = $filter->preferred_tech ?? [];
        $this->location_type = $filter->location_type;
        $this->location_value = $filter->location_value;
        $this->min_salary = $filter->min_salary;
        $this->salary_currency = $filter->salary_currency ?? 'USD';
        $this->experience_level = $filter->experience_level;

        $platforms = $filter->enabled_platforms ?? [];
        $this->platform_jsearch = $platforms[JobSearchFilter::JSEARCH] ?? false;
        $this->platform_remoteok = $platforms[JobSearchFilter::REMOTEOK] ?? false;
        $this->platform_remotive = $platforms[JobSearchFilter::REMOTIVE] ?? false;
        $this->platform_adzuna = $platforms[JobSearchFilter::ADZUNA] ?? false;
        $this->platform_rozee = $platforms[JobSearchFilter::ROZEE] ?? false;
        $this->platform_mustakbil = $platforms[JobSearchFilter::MUSTAKBIL] ?? false;
    }

    public function addTitle(): void
    {
        $title = trim($this->newTitle);

        if ($title !== '' && ! in_array(strtolower($title), array_map('strtolower', $this->preferred_titles))) {
            $this->preferred_titles[] = $title;
        }

        $this->newTitle = '';
    }

    public function removeTitle(int $index): void
    {
        unset($this->preferred_titles[$index]);
        $this->preferred_titles = array_values($this->preferred_titles);
    }

    public function addTech(): void
    {
        $tech = trim($this->newTech);

        if ($tech !== '' && ! in_array(strtolower($tech), array_map('strtolower', $this->preferred_tech))) {
            $this->preferred_tech[] = $tech;
        }

        $this->newTech = '';
    }

    public function removeTech(int $index): void
    {
        unset($this->preferred_tech[$index]);
        $this->preferred_tech = array_values($this->preferred_tech);
    }

    public function save(JobSearchFilterService $service): void
    {
        $this->validate([
            'preferred_titles' => 'nullable|array',
            'preferred_titles.*' => 'string|max:100',
            'preferred_tech' => 'nullable|array',
            'preferred_tech.*' => 'string|max:100',
            'location_type' => 'nullable|string|in:remote,pakistan,country,hybrid,onsite',
            'location_value' => 'nullable|string|max:255|required_if:location_type,pakistan|required_if:location_type,country',
            'min_salary' => 'nullable|integer|min:0|max:9999999',
            'salary_currency' => 'required|string|in:USD,PKR',
            'experience_level' => 'nullable|string|in:mid,senior',
            'platform_jsearch' => 'boolean',
            'platform_remoteok' => 'boolean',
            'platform_remotive' => 'boolean',
            'platform_adzuna' => 'boolean',
            'platform_rozee' => 'boolean',
            'platform_mustakbil' => 'boolean',
        ]);

        // Clear location_value for types that don't need it
        if (! in_array($this->location_type, ['pakistan', 'country'])) {
            $this->location_value = null;
        }

        $enabledPlatforms = [
            JobSearchFilter::JSEARCH => $this->platform_jsearch,
            JobSearchFilter::REMOTEOK => $this->platform_remoteok,
            JobSearchFilter::REMOTIVE => $this->platform_remotive,
            JobSearchFilter::ADZUNA => $this->platform_adzuna,
            JobSearchFilter::ROZEE => $this->platform_rozee,
            JobSearchFilter::MUSTAKBIL => $this->platform_mustakbil,
        ];

        $filter = JobSearchFilter::findOrFail($this->filterId);

        $service->update($filter, [
            'preferred_titles' => $this->preferred_titles,
            'preferred_tech' => $this->preferred_tech,
            'location_type' => $this->location_type,
            'location_value' => $this->location_value,
            'min_salary' => $this->min_salary,
            'salary_currency' => $this->salary_currency,
            'experience_level' => $this->experience_level,
            'enabled_platforms' => $enabledPlatforms,
        ]);

        session()->flash('success', 'Job search filters updated successfully.');
        $this->redirect(route('admin.settings.job-search-filters'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.settings.job-search-filters.edit');
    }
}
