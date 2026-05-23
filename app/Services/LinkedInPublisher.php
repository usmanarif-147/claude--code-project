<?php

namespace App\Services;

use App\Models\Social\PlatformAccount;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Performs the 3-step LinkedIn document-upload + UGC post flow used to publish
 * multi-page PDF carousels to a member's LinkedIn feed.
 *
 * All HTTP traffic to LinkedIn is contained in this class. Other services may
 * orchestrate this publisher, but they must never call the LinkedIn API directly.
 */
class LinkedInPublisher
{
    private const REGISTER_UPLOAD_URL = 'https://api.linkedin.com/v2/assets?action=registerUpload';

    private const UGC_POSTS_URL = 'https://api.linkedin.com/v2/ugcPosts';

    private const RECIPE_DOCUMENT = 'urn:li:digitalmediaRecipe:feedshare-document';

    private const SERVICE_RELATIONSHIP_IDENTIFIER = 'urn:li:userGeneratedContent';

    /**
     * Publish a multi-page PDF as a LinkedIn document carousel.
     *
     * @return array{remote_post_id: string, remote_url: string}
     *
     * @throws RuntimeException
     */
    public function publishCarousel(PlatformAccount $account, string $pdfAbsolutePath, string $captionText): array
    {
        if (! is_file($pdfAbsolutePath) || ! is_readable($pdfAbsolutePath)) {
            throw new RuntimeException("LinkedIn publish failed: PDF not readable at '{$pdfAbsolutePath}'.");
        }

        if (empty($account->remote_account_id)) {
            throw new RuntimeException('LinkedIn publish failed: connected account has no remote_account_id (member URN).');
        }

        $accessToken = $account->access_token;
        if (empty($accessToken)) {
            throw new RuntimeException('LinkedIn publish failed: connected account has no access token.');
        }

        $ownerUrn = $this->ownerUrn($account->remote_account_id);

        // ── Step 1: register the upload, retrieve an uploadUrl + asset URN ──
        [$uploadUrl, $assetUrn] = $this->registerUpload($accessToken, $ownerUrn);

        // ── Step 2: PUT the PDF bytes to the returned uploadUrl ─────────────
        $this->uploadPdfBytes($accessToken, $uploadUrl, $pdfAbsolutePath);

        // ── Step 3: create the UGC post that references the uploaded asset ──
        $postUrn = $this->createUgcPost($accessToken, $ownerUrn, $assetUrn, $captionText);

        return [
            'remote_post_id' => $postUrn,
            'remote_url' => "https://www.linkedin.com/feed/update/{$postUrn}",
        ];
    }

    /**
     * @return array{0: string, 1: string} [uploadUrl, assetUrn]
     */
    private function registerUpload(string $accessToken, string $ownerUrn): array
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
                        'recipes' => [self::RECIPE_DOCUMENT],
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
            throw new RuntimeException('LinkedIn step 1 (registerUpload) failed: '.$e->getMessage(), 0, $e);
        }

        $body = $response->json();

        $uploadUrl = $body['value']['uploadMechanism']['com.linkedin.digitalmedia.uploading.MediaUploadHttpRequest']['uploadUrl'] ?? null;
        $assetUrn = $body['value']['asset'] ?? null;

        if (! $uploadUrl || ! $assetUrn) {
            throw new RuntimeException('LinkedIn step 1 (registerUpload) returned an unexpected payload: missing uploadUrl or asset URN.');
        }

        return [$uploadUrl, $assetUrn];
    }

    private function uploadPdfBytes(string $accessToken, string $uploadUrl, string $pdfAbsolutePath): void
    {
        $bytes = @file_get_contents($pdfAbsolutePath);
        if ($bytes === false) {
            throw new RuntimeException("LinkedIn step 2 (upload bytes) failed: could not read '{$pdfAbsolutePath}'.");
        }

        try {
            Http::timeout(60)
                ->withToken($accessToken)
                ->withBody($bytes, 'application/pdf')
                ->put($uploadUrl)
                ->throw();
        } catch (\Throwable $e) {
            throw new RuntimeException('LinkedIn step 2 (upload PDF bytes) failed: '.$e->getMessage(), 0, $e);
        }
    }

    private function createUgcPost(string $accessToken, string $ownerUrn, string $assetUrn, string $captionText): string
    {
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
                        'com.linkedin.ugc.ShareContent' => [
                            'shareCommentary' => ['text' => $captionText],
                            'shareMediaCategory' => 'DOCUMENT',
                            'media' => [
                                [
                                    'status' => 'READY',
                                    'media' => $assetUrn,
                                ],
                            ],
                        ],
                    ],
                    'visibility' => [
                        'com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC',
                    ],
                ])
                ->throw();
        } catch (\Throwable $e) {
            throw new RuntimeException('LinkedIn step 3 (ugcPosts create) failed: '.$e->getMessage(), 0, $e);
        }

        // LinkedIn returns the new post's URN in the `x-restli-id` response header,
        // and (usually) the same value in the JSON body's `id` field.
        $headerUrn = $response->header('x-restli-id');
        $bodyUrn = $response->json('id');

        $urn = $headerUrn ?: $bodyUrn;
        if (! $urn) {
            throw new RuntimeException('LinkedIn step 3 (ugcPosts create) succeeded but no post URN was returned.');
        }

        return $urn;
    }

    /**
     * Build the member URN from a stored remote_account_id. The DB column may
     * contain either the bare id ("abc123") or an already-qualified URN.
     */
    private function ownerUrn(string $remoteAccountId): string
    {
        if (str_starts_with($remoteAccountId, 'urn:li:person:')) {
            return $remoteAccountId;
        }

        return "urn:li:person:{$remoteAccountId}";
    }
}
