<?php

namespace App\Services;

use App\Models\BlogAndPost\Post;
use App\Models\Social\PlatformAccount;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Publishes Blog & Post `Post` rows to LinkedIn.
 *
 * Supports all four post types: text, image, video, article. Image and video
 * use LinkedIn's 3-step asset upload flow (registerUpload → PUT bytes → ugcPosts).
 * Text and article are single-call ugcPosts.
 *
 * All HTTP traffic to LinkedIn lives in this class. Form components / index
 * actions call `publish(Post)` and never touch the LinkedIn API directly.
 *
 * Standalone — does NOT extend or reuse `LinkedInPublisher`. Mirrors the shape
 * intentionally so each module stays decoupled.
 */
class BlogAndPostPublisher
{
    private const REGISTER_UPLOAD_URL = 'https://api.linkedin.com/v2/assets?action=registerUpload';

    private const UGC_POSTS_URL = 'https://api.linkedin.com/v2/ugcPosts';

    private const RECIPE_IMAGE = 'urn:li:digitalmediaRecipe:feedshare-image';

    private const RECIPE_VIDEO = 'urn:li:digitalmediaRecipe:feedshare-video';

    private const SERVICE_RELATIONSHIP_IDENTIFIER = 'urn:li:userGeneratedContent';

    /**
     * Publish a single Post to LinkedIn.
     *
     * Catches RuntimeException to mark the post as failed without crashing the
     * caller (form action / cron). Other exceptions intentionally bubble up so
     * they surface in Laravel's exception log.
     */
    public function publish(Post $post): void
    {
        $account = PlatformAccount::query()->where('platform', 'linkedin')->first();

        if (! $account || ! $account->access_token || ($account->token_expires_at && $account->token_expires_at->isPast())) {
            Log::warning('Blog & post publish gated — LinkedIn not connected or token expired', [
                'post_id' => $post->id,
                'type' => $post->type,
            ]);

            $post->forceFill([
                'status' => 'failed',
                'linkedin_error' => 'LinkedIn account not connected or token expired',
                'linkedin_last_attempted_at' => now(),
            ])->save();

            return;
        }

        if (empty($account->remote_account_id)) {
            $post->forceFill([
                'status' => 'failed',
                'linkedin_error' => 'LinkedIn account has no remote_account_id (member URN).',
                'linkedin_last_attempted_at' => now(),
            ])->save();

            return;
        }

        // Mark publishing + bump attempts BEFORE the network call so a crash
        // leaves a visible row in the admin.
        $post->forceFill([
            'status' => 'publishing',
            'linkedin_attempts' => (int) $post->linkedin_attempts + 1,
            'linkedin_last_attempted_at' => now(),
        ])->save();

        try {
            $caption = $this->buildCaption($post->caption ?? '', $post->hashtags ?? '');

            $urn = match ($post->type) {
                Post::TYPE_TEXT => $this->publishText($account, $caption),
                Post::TYPE_IMAGE => $this->publishImage($account, $this->resolveMediaPath($post, 'image_path'), $caption),
                Post::TYPE_VIDEO => $this->publishVideo($account, $this->resolveMediaPath($post, 'video_path'), $caption),
                Post::TYPE_ARTICLE => $this->publishArticle($account, $this->resolveArticleUrl($post), $caption),
                default => throw new RuntimeException("Unsupported post type: {$post->type}"),
            };

            $post->forceFill([
                'status' => 'posted',
                'linkedin_post_id' => $urn,
                'linkedin_post_url' => "https://www.linkedin.com/feed/update/{$urn}",
                'linkedin_error' => null,
            ])->save();
        } catch (RuntimeException $e) {
            $post->forceFill([
                'status' => 'failed',
                'linkedin_error' => $e->getMessage(),
            ])->save();

            Log::warning('Blog & Post publish failed', [
                'post_id' => $post->id,
                'type' => $post->type,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Find scheduled posts that are due now and publish each one.
     *
     * Never throws — individual `publish()` failures are caught inside `publish()`
     * (which marks the post 'failed'), so the loop continues across the batch.
     *
     * @return array{attempted: int, posted: int, failed: int}
     */
    public function publishDue(): array
    {
        $due = Post::query()
            ->where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->get();

        $summary = ['attempted' => 0, 'posted' => 0, 'failed' => 0];

        foreach ($due as $post) {
            $summary['attempted']++;
            $this->publish($post);
            $finalStatus = $post->fresh()->status;
            if ($finalStatus === 'posted') {
                $summary['posted']++;
            } else {
                $summary['failed']++;
            }
        }

        return $summary;
    }

    // ── Type-specific publish flows ──────────────────────────────────────────

    private function publishText(PlatformAccount $account, string $caption): string
    {
        return $this->createUgcPost(
            accessToken: $account->access_token,
            ownerUrn: $this->ownerUrn($account->remote_account_id),
            caption: $caption,
            shareMediaCategory: 'NONE',
            media: null,
        );
    }

    private function publishImage(PlatformAccount $account, string $absolutePath, string $caption): string
    {
        return $this->publishMedia(
            account: $account,
            absolutePath: $absolutePath,
            caption: $caption,
            recipe: self::RECIPE_IMAGE,
            shareMediaCategory: 'IMAGE',
            uploadTimeoutSeconds: 60,
            stepLabel: 'image',
        );
    }

    private function publishVideo(PlatformAccount $account, string $absolutePath, string $caption): string
    {
        return $this->publishMedia(
            account: $account,
            absolutePath: $absolutePath,
            caption: $caption,
            recipe: self::RECIPE_VIDEO,
            shareMediaCategory: 'VIDEO',
            uploadTimeoutSeconds: 120,
            stepLabel: 'video',
        );
    }

    private function publishArticle(PlatformAccount $account, string $url, string $caption): string
    {
        return $this->createUgcPost(
            accessToken: $account->access_token,
            ownerUrn: $this->ownerUrn($account->remote_account_id),
            caption: $caption,
            shareMediaCategory: 'ARTICLE',
            media: [['status' => 'READY', 'originalUrl' => $url]],
        );
    }

    // ── Shared 3-step flow for image + video ─────────────────────────────────

    private function publishMedia(
        PlatformAccount $account,
        string $absolutePath,
        string $caption,
        string $recipe,
        string $shareMediaCategory,
        int $uploadTimeoutSeconds,
        string $stepLabel,
    ): string {
        if (! is_file($absolutePath) || ! is_readable($absolutePath)) {
            throw new RuntimeException("LinkedIn publish failed: {$stepLabel} not readable at '{$absolutePath}'.");
        }

        $accessToken = $account->access_token;
        $ownerUrn = $this->ownerUrn($account->remote_account_id);

        [$uploadUrl, $assetUrn] = $this->registerUpload($accessToken, $ownerUrn, $recipe, $stepLabel);

        $this->uploadAssetBytes($accessToken, $uploadUrl, $absolutePath, $uploadTimeoutSeconds, $stepLabel);

        return $this->createUgcPost(
            accessToken: $accessToken,
            ownerUrn: $ownerUrn,
            caption: $caption,
            shareMediaCategory: $shareMediaCategory,
            media: [['status' => 'READY', 'media' => $assetUrn]],
        );
    }

    /**
     * @return array{0: string, 1: string} [uploadUrl, assetUrn]
     */
    private function registerUpload(string $accessToken, string $ownerUrn, string $recipe, string $stepLabel): array
    {
        try {
            $response = Http::timeout(30)
                ->withToken($accessToken)
                ->withHeaders([
                    'X-Restli-Protocol-Version' => '2.0.0',
                    'Content-Type' => 'application/json',
                ])
                ->post(self::REGISTER_UPLOAD_URL, [
                    'registerUploadRequest' => [
                        'owner' => $ownerUrn,
                        'recipes' => [$recipe],
                        'serviceRelationships' => [
                            [
                                'identifier' => self::SERVICE_RELATIONSHIP_IDENTIFIER,
                                'relationshipType' => 'OWNER',
                            ],
                        ],
                    ],
                ])
                ->throw();
        } catch (\Throwable $e) {
            throw new RuntimeException("LinkedIn step 1 (registerUpload for {$stepLabel}) failed: ".$e->getMessage(), 0, $e);
        }

        $body = $response->json();

        $uploadUrl = $body['value']['uploadMechanism']['com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest']['uploadUrl'] ?? null;
        $assetUrn = $body['value']['asset'] ?? null;

        if (! $uploadUrl || ! $assetUrn) {
            throw new RuntimeException("LinkedIn step 1 (registerUpload for {$stepLabel}) returned an unexpected payload: missing uploadUrl or asset URN.");
        }

        return [$uploadUrl, $assetUrn];
    }

    private function uploadAssetBytes(string $accessToken, string $uploadUrl, string $absolutePath, int $timeoutSeconds, string $stepLabel): void
    {
        $bytes = @file_get_contents($absolutePath);
        if ($bytes === false) {
            throw new RuntimeException("LinkedIn step 2 (upload {$stepLabel} bytes) failed: could not read '{$absolutePath}'.");
        }

        try {
            Http::timeout($timeoutSeconds)
                ->withToken($accessToken)
                ->withBody($bytes, 'application/octet-stream')
                ->put($uploadUrl)
                ->throw();
        } catch (\Throwable $e) {
            throw new RuntimeException("LinkedIn step 2 (upload {$stepLabel} bytes) failed: ".$e->getMessage(), 0, $e);
        }
    }

    /**
     * Create the UGC post. Branches on shareMediaCategory for the payload shape.
     *
     * @param  array<int, array<string, string>>|null  $media
     */
    private function createUgcPost(
        string $accessToken,
        string $ownerUrn,
        string $caption,
        string $shareMediaCategory,
        ?array $media,
    ): string {
        $shareContent = [
            'shareCommentary' => ['text' => $caption],
            'shareMediaCategory' => $shareMediaCategory,
        ];

        if ($media !== null) {
            $shareContent['media'] = $media;
        }

        try {
            $response = Http::timeout(30)
                ->withToken($accessToken)
                ->withHeaders([
                    'X-Restli-Protocol-Version' => '2.0.0',
                    'Content-Type' => 'application/json',
                ])
                ->post(self::UGC_POSTS_URL, [
                    'author' => $ownerUrn,
                    'lifecycleState' => 'PUBLISHED',
                    'specificContent' => [
                        'com.linkedin.ugc.ShareContent' => $shareContent,
                    ],
                    'visibility' => [
                        'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
                    ],
                ])
                ->throw();
        } catch (\Throwable $e) {
            throw new RuntimeException('LinkedIn step 3 (ugcPosts create) failed: '.$e->getMessage(), 0, $e);
        }

        return $this->extractPostUrn($response);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function extractPostUrn(Response $response): string
    {
        $headerUrn = $response->header('x-restli-id');
        $bodyUrn = $response->json('id');

        $urn = $headerUrn ?: $bodyUrn;

        if (! $urn) {
            throw new RuntimeException('LinkedIn step 3 (ugcPosts create) succeeded but no post URN was returned.');
        }

        return $urn;
    }

    /**
     * Build the LinkedIn caption: post caption + (if any) hashtags on a new line.
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

            $bare = ltrim($part, '#');
            if ($bare === '') {
                continue;
            }

            $formatted[] = '#'.$bare;
        }

        return implode(' ', $formatted);
    }

    private function ownerUrn(string $remoteAccountId): string
    {
        if (str_starts_with($remoteAccountId, 'urn:li:person:')) {
            return $remoteAccountId;
        }

        return "urn:li:person:{$remoteAccountId}";
    }

    private function resolveMediaPath(Post $post, string $metaKey): string
    {
        $relative = $post->meta[$metaKey] ?? null;

        if (! $relative) {
            $label = $metaKey === 'image_path' ? 'image' : 'video';
            throw new RuntimeException("LinkedIn publish failed: post has no {$label} attached (meta.{$metaKey} missing).");
        }

        $absolute = Storage::disk('public')->path($relative);

        if (! is_file($absolute) || ! is_readable($absolute)) {
            throw new RuntimeException("LinkedIn publish failed: media file not found on disk at '{$absolute}'.");
        }

        return $absolute;
    }

    private function resolveArticleUrl(Post $post): string
    {
        $url = $post->meta['url'] ?? null;

        if (! $url || ! filter_var($url, FILTER_VALIDATE_URL)) {
            throw new RuntimeException('LinkedIn publish failed: article post has no valid URL (meta.url missing or invalid).');
        }

        return $url;
    }
}
