<?php

namespace App\Services;

use App\Models\BlogAndPost\Post;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Blog & Post service — handles CRUD for the new module's posts.
 *
 * File-size limits at the Livewire layer:
 *   - Images: 5 MB (matches LinkedIn's own hard cap for feed images).
 *   - Videos: 100 MB (sane default for PHP/nginx upload handling).
 *
 * LinkedIn's hard limits are 5 MB for images and 5 GB for videos; we cap
 * video uploads much lower than that so the default PHP/nginx
 * upload_max_filesize / client_max_body_size settings work without
 * extra ops configuration. Bump these in Phase 5 if needed.
 */
class BlogAndPostService
{
    public function create(array $data, ?UploadedFile $media = null): Post
    {
        return DB::transaction(function () use ($data, $media) {
            $data['status'] = $data['status'] ?? 'draft';
            $data['type'] = $data['type'] ?? Post::TYPE_TEXT;

            $data = $this->shapeMeta($data, $media);
            $data = $this->stripExtraneous($data);

            return Post::create($data);
        });
    }

    public function update(
        Post $post,
        array $data,
        ?UploadedFile $media = null,
        bool $removeMedia = false,
    ): Post {
        return DB::transaction(function () use ($post, $data, $media, $removeMedia) {
            $type = $data['type'] ?? $post->type;
            $existingMeta = $post->meta ?? [];

            // Removal of existing media (when no new file is provided).
            if ($removeMedia && ! $media) {
                $existingMeta = $this->deleteStoredMedia($type, $existingMeta);
            }

            // New media replaces old.
            if ($media) {
                $existingMeta = $this->deleteStoredMedia($type, $existingMeta);
                $existingMeta = $this->storeMedia($type, $media, $existingMeta);
            }

            // Article URL shaping (override meta entirely for article type).
            if ($type === Post::TYPE_ARTICLE && array_key_exists('article_url', $data)) {
                $existingMeta = ['url' => $data['article_url']];
            }

            // Text type has no meta.
            if ($type === Post::TYPE_TEXT) {
                $existingMeta = null;
            }

            $data['meta'] = $existingMeta;
            $data = $this->stripExtraneous($data);

            $post->update($data);

            return $post->fresh();
        });
    }

    public function delete(Post $post): void
    {
        DB::transaction(function () use ($post) {
            $meta = $post->meta ?? [];

            foreach (['image_path', 'video_path'] as $key) {
                $path = $meta[$key] ?? null;
                if ($path && Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            $post->delete();
        });
    }

    public function markAsDraft(Post $post): Post
    {
        return DB::transaction(function () use ($post) {
            $post->update([
                'status' => 'draft',
                'scheduled_at' => null,
            ]);

            return $post->refresh();
        });
    }

    public function schedule(Post $post, Carbon $when): Post
    {
        return DB::transaction(function () use ($post, $when) {
            $post->update([
                'status' => 'scheduled',
                'scheduled_at' => $when,
            ]);

            return $post->refresh();
        });
    }

    /**
     * Shape meta on create based on type + uploaded file.
     */
    private function shapeMeta(array $data, ?UploadedFile $media): array
    {
        $type = $data['type'] ?? Post::TYPE_TEXT;

        if ($type === Post::TYPE_IMAGE && $media) {
            $path = $media->store('blog-and-post/images', 'public');
            $data['meta'] = ['image_path' => $path];
        } elseif ($type === Post::TYPE_VIDEO && $media) {
            $path = $media->store('blog-and-post/videos', 'public');
            $data['meta'] = ['video_path' => $path];
        } elseif ($type === Post::TYPE_ARTICLE && isset($data['article_url'])) {
            $data['meta'] = ['url' => $data['article_url']];
        } elseif ($type === Post::TYPE_TEXT) {
            $data['meta'] = null;
        }

        return $data;
    }

    /**
     * Store a fresh media file and return updated meta.
     */
    private function storeMedia(string $type, UploadedFile $media, array $meta): array
    {
        if ($type === Post::TYPE_IMAGE) {
            $path = $media->store('blog-and-post/images', 'public');
            $meta['image_path'] = $path;
        } elseif ($type === Post::TYPE_VIDEO) {
            $path = $media->store('blog-and-post/videos', 'public');
            $meta['video_path'] = $path;
        }

        return $meta;
    }

    /**
     * Delete the on-disk media for the given type and clear the meta key.
     */
    private function deleteStoredMedia(string $type, array $meta): array
    {
        $key = match ($type) {
            Post::TYPE_IMAGE => 'image_path',
            Post::TYPE_VIDEO => 'video_path',
            default => null,
        };

        if (! $key) {
            return $meta;
        }

        $path = $meta[$key] ?? null;
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }

        $meta[$key] = null;

        return $meta;
    }

    /**
     * Strip any keys that aren't part of the model's $fillable.
     */
    private function stripExtraneous(array $data): array
    {
        $fillable = (new Post)->getFillable();

        return array_intersect_key($data, array_flip($fillable));
    }
}
