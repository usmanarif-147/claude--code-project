<?php

namespace App\Services;

use App\Models\BlogPost;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Stevebauman\Purify\Facades\Purify;

class BlogPostService
{
    public function create(array $data, ?UploadedFile $coverImage = null, array $tags = []): BlogPost
    {
        return DB::transaction(function () use ($data, $coverImage, $tags) {
            $data['slug'] = $this->generateUniqueSlug($data['title']);
            $data['content'] = Purify::clean($data['content']);
            $data['reading_time_minutes'] = $this->calculateReadingTime($data['content']);

            if ($coverImage) {
                $data['cover_image'] = $coverImage->store('blog/covers', 'public');
            }

            $post = BlogPost::create($data);
            $this->syncTags($post, $tags);

            return $post;
        });
    }

    public function update(BlogPost $post, array $data, ?UploadedFile $coverImage = null, array $tags = []): BlogPost
    {
        return DB::transaction(function () use ($post, $data, $coverImage, $tags) {
            if ($data['title'] !== $post->title) {
                $data['slug'] = $this->generateUniqueSlug($data['title'], $post->id);
            }

            $data['content'] = Purify::clean($data['content']);
            $data['reading_time_minutes'] = $this->calculateReadingTime($data['content']);

            if ($coverImage) {
                if ($post->cover_image) {
                    Storage::disk('public')->delete($post->cover_image);
                }
                $data['cover_image'] = $coverImage->store('blog/covers', 'public');
            } elseif (array_key_exists('cover_image', $data) && $data['cover_image'] === null && $post->cover_image) {
                Storage::disk('public')->delete($post->cover_image);
            }

            $post->update($data);
            $this->syncTags($post, $tags);

            return $post;
        });
    }

    public function delete(BlogPost $post): void
    {
        if ($post->cover_image) {
            Storage::disk('public')->delete($post->cover_image);
        }

        $post->delete();
    }

    public function publish(BlogPost $post): BlogPost
    {
        $post->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return $post;
    }

    public function unpublish(BlogPost $post): BlogPost
    {
        $post->update([
            'status' => 'draft',
            'published_at' => null,
        ]);

        return $post;
    }

    private function syncTags(BlogPost $post, array $tags): void
    {
        $post->tags()->delete();

        $uniqueTags = array_unique(array_filter(array_map('trim', $tags)));

        foreach ($uniqueTags as $tag) {
            $post->tags()->create(['tag' => $tag]);
        }
    }

    private function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $slug = Str::slug($title);
        $original = $slug;
        $count = 1;

        $query = BlogPost::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        while ($query->exists()) {
            $slug = $original.'-'.$count++;
            $query = BlogPost::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return $slug;
    }

    private function calculateReadingTime(string $content): int
    {
        $wordCount = str_word_count(strip_tags($content));

        return max(1, (int) ceil($wordCount / 200));
    }
}
