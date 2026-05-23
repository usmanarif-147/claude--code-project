<?php

namespace App\Services;

use App\Models\Social\PlatformAccount;
use App\Models\Social\ScheduledPost;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Orchestrates publishing a ScheduledPost to LinkedIn.
 *
 *  - Renders the PDF (via SocialPdfRenderService).
 *  - Builds the caption (post caption + formatted hashtags).
 *  - Hands off to LinkedInPublisher for the actual API calls.
 *  - Persists the result (post id / url / error / status) back to the ScheduledPost.
 *
 * Livewire components call this service. They never touch LinkedInPublisher directly.
 */
class SocialPublishingService
{
    public function __construct(
        private readonly SocialPdfRenderService $renderer,
        private readonly LinkedInPublisher $linkedIn,
    ) {}

    /**
     * Publish a single ScheduledPost to LinkedIn.
     *
     * Catches all RuntimeExceptions to keep batch callers (cron command) from
     * crashing on a single bad post. Unexpected errors (\Throwable) intentionally
     * bubble up so they appear in Laravel's exception log.
     */
    public function publish(ScheduledPost $post): void
    {
        $account = PlatformAccount::query()->where('platform', 'linkedin')->first();

        if (! $account || ! $account->access_token || ($account->token_expires_at && $account->token_expires_at->isPast())) {
            $post->forceFill([
                'status' => 'failed',
                'linkedin_error' => 'LinkedIn account not connected or token expired',
                'linkedin_last_attempted_at' => now(),
            ])->save();

            return;
        }

        // Mark publishing + bump attempt counter BEFORE we leave the local boundary.
        $post->forceFill([
            'status' => 'publishing',
            'linkedin_attempts' => (int) $post->linkedin_attempts + 1,
            'linkedin_last_attempted_at' => now(),
        ])->save();

        try {
            $relativePdfPath = $this->renderer->renderPdfFromPost($post);
            $absolutePdfPath = Storage::disk('public')->path($relativePdfPath);

            $captionText = $this->buildCaption($post->caption ?? '', $post->hashtags ?? '');

            $result = $this->linkedIn->publishCarousel($account, $absolutePdfPath, $captionText);

            $post->forceFill([
                'status' => 'posted',
                'linkedin_post_id' => $result['remote_post_id'],
                'linkedin_post_url' => $result['remote_url'],
                'linkedin_error' => null,
            ])->save();
        } catch (RuntimeException $e) {
            $post->forceFill([
                'status' => 'failed',
                'linkedin_error' => $e->getMessage(),
            ])->save();

            Log::warning('Social post publish failed', [
                'post_id' => $post->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Find all due posts (status=scheduled AND scheduled_at <= now) and publish each.
     *
     * @return array{attempted: int, posted: int, failed: int}
     */
    public function publishDue(): array
    {
        $due = ScheduledPost::query()
            ->where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->get();

        $summary = ['attempted' => 0, 'posted' => 0, 'failed' => 0];

        foreach ($due as $post) {
            $summary['attempted']++;
            $this->publish($post);

            $post->refresh();
            if ($post->status === 'posted') {
                $summary['posted']++;
            } else {
                $summary['failed']++;
            }
        }

        return $summary;
    }

    /**
     * Build the LinkedIn caption: post caption + (if any) hashtags on a new line.
     *
     * `hashtags` is stored as a space-separated string. Each token is normalized
     * to `#word` (existing leading `#` is preserved). Empty input -> caption only.
     */
    private function buildCaption(string $caption, string $hashtags): string
    {
        $caption = trim($caption);
        $tags = $this->formatHashtags($hashtags);

        if ($tags === '') {
            return $caption;
        }

        if ($caption === '') {
            return $tags;
        }

        return $caption."\n\n".$tags;
    }

    private function formatHashtags(string $raw): string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return '';
        }

        $parts = preg_split('/\s+/', $raw) ?: [];
        $formatted = [];

        foreach ($parts as $part) {
            $part = ltrim($part);
            if ($part === '') {
                continue;
            }

            // Strip any leading #s then re-prefix exactly one.
            $bare = ltrim($part, '#');
            if ($bare === '') {
                continue;
            }

            $formatted[] = '#'.$bare;
        }

        return implode(' ', $formatted);
    }
}
