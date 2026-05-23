<?php

namespace App\Services;

use App\Models\Social\ScheduledPost;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SocialPostService
{
    /**
     * Fields whose changes invalidate any previously rendered PDF.
     */
    private const PDF_CACHE_INVALIDATING_FIELDS = [
        'caption',
        'hashtags',
        'cover_page',
        'content_pages',
        'final_page',
        'template_slug',
    ];

    public function create(array $data): ScheduledPost
    {
        return DB::transaction(function () use ($data) {
            $data['status'] = $data['status'] ?? 'draft';
            $data['template_slug'] = $data['template_slug'] ?? 'default';
            $data['content_pages'] = $this->normalizeContentPages($data['content_pages'] ?? []);

            return ScheduledPost::create($data);
        });
    }

    public function update(ScheduledPost $post, array $data): ScheduledPost
    {
        return DB::transaction(function () use ($post, $data) {
            if (array_key_exists('content_pages', $data)) {
                $data['content_pages'] = $this->normalizeContentPages($data['content_pages']);
            }

            if ($this->shouldInvalidatePdfCache($post, $data)) {
                $this->deleteCachedPdf($post);
                $data['rendered_pdf_path'] = null;
            }

            $post->update($data);

            return $post->refresh();
        });
    }

    public function delete(ScheduledPost $post): void
    {
        DB::transaction(function () use ($post) {
            $this->deleteCachedPdf($post);
            $post->delete();
        });
    }

    public function schedule(ScheduledPost $post, Carbon $when): ScheduledPost
    {
        return DB::transaction(function () use ($post, $when) {
            $post->update([
                'status' => 'scheduled',
                'scheduled_at' => $when,
            ]);

            return $post->refresh();
        });
    }

    public function markAsDraft(ScheduledPost $post): ScheduledPost
    {
        return DB::transaction(function () use ($post) {
            $post->update([
                'status' => 'draft',
                'scheduled_at' => null,
            ]);

            return $post->refresh();
        });
    }

    private function shouldInvalidatePdfCache(ScheduledPost $post, array $data): bool
    {
        if (! $post->rendered_pdf_path) {
            return false;
        }

        foreach (self::PDF_CACHE_INVALIDATING_FIELDS as $field) {
            if (! array_key_exists($field, $data)) {
                continue;
            }

            if ($data[$field] !== $post->{$field}) {
                return true;
            }
        }

        return false;
    }

    private function deleteCachedPdf(ScheduledPost $post): void
    {
        if ($post->rendered_pdf_path && Storage::disk('public')->exists($post->rendered_pdf_path)) {
            Storage::disk('public')->delete($post->rendered_pdf_path);
        }
    }

    private function normalizeContentPages(array $pages): array
    {
        $normalized = array_values(array_filter(
            array_map(fn ($page) => is_string($page) ? trim($page) : '', $pages),
            fn ($page) => $page !== ''
        ));

        // Always store at least one page (even if empty) so downstream code has
        // a stable shape to iterate over.
        return $normalized === [] ? [''] : $normalized;
    }
}
