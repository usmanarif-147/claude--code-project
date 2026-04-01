<?php

namespace App\Livewire\Admin\JobSearch\AiCoverLetter;

use App\Models\JobSearch\CoverLetter;
use App\Models\JobSearch\JobListing;
use App\Services\AiCoverLetterService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('components.layouts.admin')]
class AiCoverLetterForm extends Component
{
    public ?int $coverLetterId = null;

    public ?int $jobListingId = null;

    public string $content = '';

    public string $aiProvider = 'claude';

    public bool $isGenerating = false;

    public string $selectedJobTitle = '';

    public function mount(?int $coverLetter = null, ?int $jobListingId = null): void
    {
        // Handle edit mode — route model binding passes the ID as $coverLetter
        if ($coverLetter) {
            $existing = CoverLetter::query()
                ->forUser(auth()->id())
                ->findOrFail($coverLetter);

            $this->coverLetterId = $existing->id;
            $this->content = $existing->content;
            $this->jobListingId = $existing->job_listing_id;
            $this->selectedJobTitle = $existing->job_title;
            $this->aiProvider = $existing->ai_provider;
        }

        // Handle pre-selected job from query string
        if (! $coverLetter && $jobListingId) {
            $job = JobListing::where('user_id', auth()->id())->findOrFail($jobListingId);
            $this->jobListingId = $job->id;
            $this->selectedJobTitle = $job->title.($job->company_name ? " at {$job->company_name}" : '');
        }

        // Auto-select provider if only one is available
        $providers = $this->availableProviders;
        if (count($providers) === 1) {
            $this->aiProvider = $providers[0];
        }
    }

    public function generate(): void
    {
        $this->validate([
            'jobListingId' => 'required|integer|exists:job_listings,id',
            'aiProvider' => 'required|string|in:claude,openai',
        ]);

        $this->isGenerating = true;

        try {
            $job = JobListing::where('user_id', auth()->id())->findOrFail($this->jobListingId);

            $service = app(AiCoverLetterService::class);
            $coverLetter = $service->generate(auth()->user(), $job, $this->aiProvider);

            $this->content = $coverLetter->content;
            $this->coverLetterId = $coverLetter->id;
            $this->selectedJobTitle = $coverLetter->job_title.($coverLetter->company_name ? " at {$coverLetter->company_name}" : '');

            session()->flash('success', 'Cover letter generated successfully.');
        } catch (\Exception $e) {
            session()->flash('error', 'AI generation failed. '.$e->getMessage());
        } finally {
            $this->isGenerating = false;
        }
    }

    public function save(): void
    {
        $this->validate([
            'content' => 'required|string|min:50',
        ]);

        if ($this->coverLetterId) {
            $coverLetter = CoverLetter::query()
                ->forUser(auth()->id())
                ->findOrFail($this->coverLetterId);

            $service = app(AiCoverLetterService::class);
            $service->update($coverLetter, $this->content);

            session()->flash('success', 'Cover letter saved successfully.');
        }
    }

    public function downloadPdf(): StreamedResponse
    {
        $coverLetter = CoverLetter::query()
            ->forUser(auth()->id())
            ->findOrFail($this->coverLetterId);

        $service = app(AiCoverLetterService::class);
        $pdf = $service->generatePdf($coverLetter);

        $filename = str_replace(' ', '_', $coverLetter->job_title).'_Cover_Letter.pdf';

        return $pdf->download($filename);
    }

    #[Computed]
    public function availableProviders(): array
    {
        $service = app(AiCoverLetterService::class);

        return $service->getAvailableProviders(auth()->user());
    }

    #[Computed]
    public function jobListings(): Collection
    {
        return JobListing::query()
            ->forUser(auth()->id())
            ->visible()
            ->latest('posted_at')
            ->limit(100)
            ->get(['id', 'title', 'company_name']);
    }

    public function render()
    {
        return view('livewire.admin.job-search.ai-cover-letter.form');
    }
}
