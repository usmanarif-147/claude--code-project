<?php

namespace App\Livewire\Admin\Social\Scheduler;

use App\Models\Social\ScheduledPost;
use App\Services\SocialPdfRenderService;
use App\Services\SocialPostService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class PostForm extends Component
{
    public ?ScheduledPost $scheduledPost = null;

    public string $title = '';

    public string $caption = '';

    public string $hashtags = '';

    public string $cover_page = '';

    public array $content_pages = [''];

    public string $final_page = '';

    public string $template_slug = 'default';

    public ?string $scheduled_date = null;

    public ?string $scheduled_time = null;

    /**
     * Index of the page currently shown in the preview pane.
     * 0 = cover, 1..N = content_pages[N-1], (N+1) = final.
     */
    public int $focusedPageIndex = 0;

    public function mount(?ScheduledPost $scheduledPost = null): void
    {
        if ($scheduledPost && $scheduledPost->exists) {
            $this->scheduledPost = $scheduledPost;
            $this->title = $scheduledPost->title;
            $this->caption = $scheduledPost->caption ?? '';
            $this->hashtags = $scheduledPost->hashtags ?? '';
            $this->cover_page = $scheduledPost->cover_page ?? '';
            $this->content_pages = ! empty($scheduledPost->content_pages)
                ? $scheduledPost->content_pages
                : [''];
            $this->final_page = $scheduledPost->final_page ?? '';
            $this->template_slug = $scheduledPost->template_slug ?? 'default';

            if ($scheduledPost->scheduled_at) {
                $this->scheduled_date = $scheduledPost->scheduled_at->format('Y-m-d');
                $this->scheduled_time = $scheduledPost->scheduled_at->format('H:i');
            }
        }
    }

    public function addContentPage(): void
    {
        $this->content_pages[] = '';
    }

    /**
     * Livewire lifecycle hook — clamp focus when the content_pages array changes
     * (add/remove/reorder) so the preview never points at a now-invalid index.
     */
    public function updatedContentPages(): void
    {
        $this->clampFocusedPageIndex();
    }

    public function focusPage(int $index): void
    {
        $total = $this->totalPages();
        if ($index < 0 || $index >= $total) {
            return;
        }

        $this->focusedPageIndex = $index;
    }

    public function totalPages(): int
    {
        $nonEmptyContent = count(array_filter(
            $this->content_pages,
            fn ($page) => is_string($page) && trim($page) !== ''
        ));

        // cover + (non-empty content pages) + final
        return 1 + $nonEmptyContent + 1;
    }

    public function previewHtml(SocialPdfRenderService $renderer): string
    {
        try {
            $this->clampFocusedPageIndex();

            return $renderer->renderPageHtml(
                [
                    'title' => $this->title,
                    'cover_page' => $this->cover_page,
                    'content_pages' => $this->content_pages,
                    'final_page' => $this->final_page,
                ],
                $this->focusedPageIndex,
                $this->template_slug ?: 'default'
            );
        } catch (\Throwable $e) {
            $message = htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            return '<html><body style="font-family:sans-serif;color:#888;padding:20px">'
                ."Preview unavailable: {$message}"
                .'</body></html>';
        }
    }

    public function downloadPdf(SocialPdfRenderService $renderer)
    {
        if (! $this->scheduledPost?->exists) {
            session()->flash('error', 'Save the post first before downloading the PDF.');

            return null;
        }

        // Persist current form state via the service so its cache-invalidation
        // logic clears any stale PDF before re-rendering.
        $service = app(SocialPostService::class);
        $service->update($this->scheduledPost, [
            'title' => $this->title,
            'caption' => $this->caption,
            'hashtags' => $this->hashtags,
            'cover_page' => $this->cover_page,
            'content_pages' => $this->content_pages,
            'final_page' => $this->final_page,
            'template_slug' => $this->template_slug ?: 'default',
        ]);

        $this->scheduledPost->refresh();

        $relativePath = $renderer->renderPdfFromPost($this->scheduledPost);
        $absolutePath = Storage::disk('public')->path($relativePath);

        return response()->download($absolutePath, "post-{$this->scheduledPost->id}.pdf");
    }

    private function clampFocusedPageIndex(): void
    {
        $total = $this->totalPages();
        $max = max(0, $total - 1);

        if ($this->focusedPageIndex < 0) {
            $this->focusedPageIndex = 0;
        } elseif ($this->focusedPageIndex > $max) {
            $this->focusedPageIndex = $max;
        }
    }

    public function removeContentPage(int $index): void
    {
        if (count($this->content_pages) <= 1) {
            return;
        }

        if (! array_key_exists($index, $this->content_pages)) {
            return;
        }

        array_splice($this->content_pages, $index, 1);
    }

    public function moveContentPageUp(int $index): void
    {
        if ($index <= 0 || ! array_key_exists($index, $this->content_pages)) {
            return;
        }

        [$this->content_pages[$index - 1], $this->content_pages[$index]] =
            [$this->content_pages[$index], $this->content_pages[$index - 1]];
    }

    public function moveContentPageDown(int $index): void
    {
        if (! array_key_exists($index, $this->content_pages) || $index >= count($this->content_pages) - 1) {
            return;
        }

        [$this->content_pages[$index], $this->content_pages[$index + 1]] =
            [$this->content_pages[$index + 1], $this->content_pages[$index]];
    }

    public function saveDraft(SocialPostService $service): void
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'caption' => 'required|string',
            'hashtags' => 'nullable|string',
            'cover_page' => 'required|string',
            'content_pages' => 'array|min:1',
            'content_pages.*' => 'nullable|string',
            'final_page' => 'required|string',
            'template_slug' => 'required|string|max:50',
        ]);

        $data = $validated;
        $data['hashtags'] = $data['hashtags'] ?? '';
        $data['status'] = 'draft';
        $data['scheduled_at'] = null;

        $this->persist($service, $data, 'Scheduled post saved as draft.');
    }

    public function schedule(SocialPostService $service): void
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'caption' => 'required|string',
            'hashtags' => 'nullable|string',
            'cover_page' => 'required|string',
            'content_pages' => 'array|min:1',
            'content_pages.*' => 'nullable|string',
            'final_page' => 'required|string',
            'template_slug' => 'required|string|max:50',
            'scheduled_date' => 'required|date',
            'scheduled_time' => ['required', 'string', 'regex:/^\d{2}:\d{2}$/'],
        ]);

        $when = $this->roundToFifteenMinutes(
            Carbon::parse($validated['scheduled_date'].' '.$validated['scheduled_time'])
        );

        $data = collect($validated)
            ->except(['scheduled_date', 'scheduled_time'])
            ->toArray();
        $data['hashtags'] = $data['hashtags'] ?? '';
        $data['status'] = 'scheduled';
        $data['scheduled_at'] = $when;

        $this->persist($service, $data, 'Post scheduled successfully.');
    }

    private function persist(SocialPostService $service, array $data, string $successMessage): void
    {
        if ($this->scheduledPost) {
            $service->update($this->scheduledPost, $data);
        } else {
            $service->create($data);
        }

        session()->flash('success', $successMessage);
        $this->redirect(route('admin.social.scheduler.index'), navigate: true);
    }

    private function roundToFifteenMinutes(Carbon $when): Carbon
    {
        $minutes = (int) $when->minute;
        $remainder = $minutes % 15;

        // Round to nearest 15-minute slot (down if <8, up otherwise).
        if ($remainder === 0) {
            return $when->copy()->second(0);
        }

        $adjustment = $remainder < 8 ? -$remainder : (15 - $remainder);

        return $when->copy()->second(0)->addMinutes($adjustment);
    }

    public function render()
    {
        return view('livewire.admin.social.scheduler.form');
    }
}
