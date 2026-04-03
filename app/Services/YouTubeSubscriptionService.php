<?php

namespace App\Services;

use App\Models\ApiKey;
use App\Models\YouTube\YouTubeSavedVideo;
use App\Models\YouTube\YouTubeSubscription;
use App\Models\YouTube\YouTubeSubscriptionVideo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YouTubeSubscriptionService
{
    private const SYNC_COOLDOWN_HOURS = 2;

    // --- Channel Management ---

    public function getSubscriptions(int $userId): Collection
    {
        return YouTubeSubscription::forUser($userId)
            ->orderBy('channel_title')
            ->get();
    }

    public function subscribe(int $userId, string $channelIdentifier): YouTubeSubscription
    {
        $apiKey = $this->getApiKey($userId);
        if (! $apiKey) {
            throw new \RuntimeException('YouTube API key not configured.');
        }

        $channelId = $this->resolveChannelId($apiKey, $channelIdentifier);
        if (! $channelId) {
            throw new \RuntimeException('Could not find YouTube channel.');
        }

        // Check if already subscribed
        $existing = YouTubeSubscription::forUser($userId)
            ->where('channel_id', $channelId)
            ->first();
        if ($existing) {
            throw new \RuntimeException('Already subscribed to this channel.');
        }

        $channelInfo = $this->fetchChannelInfo($apiKey, $channelId);
        if (empty($channelInfo)) {
            throw new \RuntimeException('Could not fetch channel data.');
        }

        return YouTubeSubscription::create([
            'user_id' => $userId,
            'channel_id' => $channelId,
            'channel_title' => $channelInfo['title'],
            'channel_thumbnail_url' => $channelInfo['thumbnail_url'],
            'channel_description' => $channelInfo['description'],
            'subscriber_count' => $channelInfo['subscriber_count'],
            'video_count' => $channelInfo['video_count'],
            'subscribed_at' => now(),
            'synced_at' => now(),
        ]);
    }

    public function unsubscribe(int $userId, int $subscriptionId): void
    {
        YouTubeSubscription::where('id', $subscriptionId)
            ->where('user_id', $userId)
            ->delete(); // cascade deletes videos
    }

    public function refreshChannelData(int $userId, int $subscriptionId): void
    {
        $subscription = YouTubeSubscription::where('id', $subscriptionId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $apiKey = $this->getApiKey($userId);
        if (! $apiKey) {
            throw new \RuntimeException('YouTube API key not configured.');
        }

        $channelInfo = $this->fetchChannelInfo($apiKey, $subscription->channel_id);
        if (! empty($channelInfo)) {
            $subscription->update([
                'channel_title' => $channelInfo['title'],
                'channel_thumbnail_url' => $channelInfo['thumbnail_url'],
                'channel_description' => $channelInfo['description'],
                'subscriber_count' => $channelInfo['subscriber_count'],
                'video_count' => $channelInfo['video_count'],
                'synced_at' => now(),
            ]);
        }
    }

    // --- Video Sync ---

    public function syncVideosForSubscription(int $userId, YouTubeSubscription $subscription, int $maxResults = 10): int
    {
        $apiKey = $this->getApiKey($userId);
        if (! $apiKey) {
            return 0;
        }

        try {
            $videosData = $this->fetchChannelVideos($apiKey, $subscription->channel_id, $maxResults);
        } catch (\Throwable $e) {
            Log::error('YouTube video sync failed', [
                'user_id' => $userId,
                'channel_id' => $subscription->channel_id,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }

        $newCount = 0;

        foreach ($videosData as $video) {
            $existing = YouTubeSubscriptionVideo::where('user_id', $userId)
                ->where('video_id', $video['video_id'])
                ->first();

            if ($existing) {
                // Update stats only
                $existing->update([
                    'view_count' => $video['view_count'],
                    'like_count' => $video['like_count'],
                    'comment_count' => $video['comment_count'],
                ]);
            } else {
                YouTubeSubscriptionVideo::create([
                    'user_id' => $userId,
                    'subscription_id' => $subscription->id,
                    'video_id' => $video['video_id'],
                    'title' => $video['title'],
                    'description' => $video['description'] ?? null,
                    'thumbnail_url' => $video['thumbnail_url'],
                    'channel_title' => $subscription->channel_title,
                    'published_at' => Carbon::parse($video['published_at']),
                    'duration' => $video['duration'],
                    'view_count' => $video['view_count'],
                    'like_count' => $video['like_count'],
                    'comment_count' => $video['comment_count'],
                    'category_id' => $video['category_id'] ?? null,
                    'default_language' => $video['default_language'] ?? null,
                    'tags' => $video['tags'] ?? null,
                    'is_new' => true,
                ]);
                $newCount++;
            }
        }

        // Update last_video_at on subscription
        $latestVideo = YouTubeSubscriptionVideo::where('subscription_id', $subscription->id)
            ->orderByDesc('published_at')
            ->first();

        $subscription->update([
            'last_video_at' => $latestVideo?->published_at,
            'synced_at' => now(),
        ]);

        return $newCount;
    }

    public function syncAllSubscriptions(int $userId): array
    {
        $subscriptions = YouTubeSubscription::forUser($userId)->get();
        $synced = 0;
        $newVideos = 0;

        foreach ($subscriptions as $subscription) {
            // Respect cooldown per channel
            if ($subscription->synced_at && $subscription->synced_at->diffInHours(now()) < self::SYNC_COOLDOWN_HOURS) {
                continue;
            }

            $newVideos += $this->syncVideosForSubscription($userId, $subscription);
            $synced++;
        }

        return ['synced' => $synced, 'new_videos' => $newVideos];
    }

    public function getVideoFeed(int $userId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = YouTubeSubscriptionVideo::forUser($userId)->recent();

        if (! empty($filters['channel_id'])) {
            $query->forChannel((int) $filters['channel_id']);
        }

        if (! empty($filters['date_from'])) {
            $query->where('published_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
        }

        if (! empty($filters['date_to'])) {
            $query->where('published_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', (int) $filters['category_id']);
        }

        if (! empty($filters['language'])) {
            $query->where('default_language', $filters['language']);
        }

        if (! empty($filters['duration'])) {
            $query->where(function ($q) use ($filters) {
                match ($filters['duration']) {
                    'short' => $q->whereRaw("duration REGEXP '^PT([0-3]M[0-9]+S|[0-9]+S)$'"),
                    'medium' => $q->whereRaw("duration NOT REGEXP '^PT([0-3]M[0-9]+S|[0-9]+S)$'")
                        ->whereRaw("duration NOT REGEXP '^PT([2-9][0-9]M|1[0-9]{2,}M|[0-9]+H)'"),
                    'long' => $q->whereRaw("duration REGEXP '^PT([2-9][0-9]M|1[0-9]{2,}M|[0-9]+H)'"),
                    default => null,
                };
            });
        }

        if (! empty($filters['search'])) {
            $search = '%'.$filters['search'].'%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                    ->orWhere('description', 'like', $search)
                    ->orWhere('channel_title', 'like', $search);
            });
        }

        return $query->paginate($perPage);
    }

    public function getVideoDetails(int $userId, string $videoId): ?YouTubeSubscriptionVideo
    {
        return YouTubeSubscriptionVideo::forUser($userId)
            ->where('video_id', $videoId)
            ->first();
    }

    public function getNewVideoCount(int $userId): int
    {
        return YouTubeSubscriptionVideo::forUser($userId)->new()->count();
    }

    public function markVideosAsRead(int $userId, ?int $subscriptionId = null): void
    {
        $query = YouTubeSubscriptionVideo::forUser($userId)->new();

        if ($subscriptionId) {
            $query->forChannel($subscriptionId);
        }

        $query->update(['is_new' => false]);
    }

    // --- Saved Videos ---

    public function saveVideo(int $userId, string $videoId, ?string $notes = null): YouTubeSavedVideo
    {
        $existing = YouTubeSavedVideo::where('user_id', $userId)
            ->where('video_id', $videoId)
            ->first();

        if ($existing) {
            throw new \RuntimeException('Video is already saved.');
        }

        // Try to get data from subscription videos first
        $subVideo = YouTubeSubscriptionVideo::forUser($userId)
            ->where('video_id', $videoId)
            ->first();

        if ($subVideo) {
            return YouTubeSavedVideo::create([
                'user_id' => $userId,
                'video_id' => $videoId,
                'title' => $subVideo->title,
                'thumbnail_url' => $subVideo->thumbnail_url,
                'channel_title' => $subVideo->channel_title,
                'channel_id' => $subVideo->subscription->channel_id,
                'published_at' => $subVideo->published_at,
                'duration' => $subVideo->duration,
                'view_count' => $subVideo->view_count,
                'notes' => $notes,
                'saved_at' => now(),
            ]);
        }

        throw new \RuntimeException('Video not found in subscriptions.');
    }

    public function unsaveVideo(int $userId, int $savedVideoId): void
    {
        YouTubeSavedVideo::where('id', $savedVideoId)
            ->where('user_id', $userId)
            ->delete();
    }

    public function getSavedVideos(int $userId, ?string $search = null, int $perPage = 20): LengthAwarePaginator
    {
        $query = YouTubeSavedVideo::forUser($userId)->recent();

        if ($search) {
            $term = '%'.$search.'%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)
                    ->orWhere('channel_title', 'like', $term)
                    ->orWhere('notes', 'like', $term);
            });
        }

        return $query->paginate($perPage);
    }

    public function updateSavedVideoNotes(int $userId, int $savedVideoId, string $notes): void
    {
        YouTubeSavedVideo::where('id', $savedVideoId)
            ->where('user_id', $userId)
            ->update(['notes' => $notes]);
    }

    public function isVideoSaved(int $userId, string $videoId): bool
    {
        return YouTubeSavedVideo::where('user_id', $userId)
            ->where('video_id', $videoId)
            ->exists();
    }

    // --- Static Helpers ---

    public static function categoryMap(): array
    {
        return [
            1 => 'Film & Animation',
            2 => 'Autos & Vehicles',
            10 => 'Music',
            15 => 'Pets & Animals',
            17 => 'Sports',
            19 => 'Travel & Events',
            20 => 'Gaming',
            22 => 'People & Blogs',
            23 => 'Comedy',
            24 => 'Entertainment',
            25 => 'News & Politics',
            26 => 'Howto & Style',
            27 => 'Education',
            28 => 'Science & Technology',
            29 => 'Nonprofits & Activism',
        ];
    }

    public static function parseDurationToSeconds(?string $isoDuration): int
    {
        if (! $isoDuration) {
            return 0;
        }

        try {
            $interval = new \DateInterval($isoDuration);

            return ($interval->h * 3600) + ($interval->i * 60) + $interval->s;
        } catch (\Throwable) {
            return 0;
        }
    }

    public static function formatDuration(?string $duration): string
    {
        if (! $duration) {
            return '--';
        }

        try {
            $interval = new \DateInterval($duration);
            $hours = $interval->h;
            $minutes = $interval->i;
            $seconds = $interval->s;

            if ($hours > 0) {
                return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
            }

            return sprintf('%d:%02d', $minutes, $seconds);
        } catch (\Throwable) {
            return $duration;
        }
    }

    // --- Private API Helpers ---

    private function getApiKey(int $userId): ?string
    {
        $apiKey = ApiKey::forUser($userId)
            ->forProvider(ApiKey::PROVIDER_YOUTUBE)
            ->first();

        return $apiKey?->key_value;
    }

    private function resolveChannelId(string $apiKey, string $identifier): ?string
    {
        $identifier = trim($identifier);

        // If it looks like a channel ID already (starts with UC)
        if (preg_match('/^UC[\w-]{22}$/', $identifier)) {
            return $identifier;
        }

        // Extract from URL patterns
        if (str_contains($identifier, 'youtube.com')) {
            // Handle /channel/UC... URLs
            if (preg_match('/\/channel\/(UC[\w-]{22})/', $identifier, $matches)) {
                return $matches[1];
            }

            // Handle /@handle URLs
            if (preg_match('/\/@([\w.-]+)/', $identifier, $matches)) {
                $identifier = '@'.$matches[1];
            }

            // Handle /c/customname or /user/username URLs
            if (preg_match('/\/(c|user)\/([\w.-]+)/', $identifier, $matches)) {
                $identifier = $matches[2];
            }
        }

        // Prepend @ if not present (assume handle)
        if (! str_starts_with($identifier, '@')) {
            $identifier = '@'.$identifier;
        }

        // Try to resolve handle via search
        try {
            $response = Http::timeout(10)
                ->get('https://www.googleapis.com/youtube/v3/channels', [
                    'part' => 'id',
                    'forHandle' => $identifier,
                    'key' => $apiKey,
                ]);

            if ($response->successful()) {
                $items = $response->json('items', []);
                if (! empty($items)) {
                    return $items[0]['id'] ?? null;
                }
            }
        } catch (\Throwable $e) {
            Log::error('YouTube channel resolve failed', ['identifier' => $identifier, 'error' => $e->getMessage()]);
        }

        // Fallback: try search endpoint
        try {
            $response = Http::timeout(10)
                ->get('https://www.googleapis.com/youtube/v3/search', [
                    'part' => 'id',
                    'type' => 'channel',
                    'q' => $identifier,
                    'maxResults' => 1,
                    'key' => $apiKey,
                ]);

            if ($response->successful()) {
                $items = $response->json('items', []);
                if (! empty($items)) {
                    return $items[0]['id']['channelId'] ?? null;
                }
            }
        } catch (\Throwable $e) {
            Log::error('YouTube channel search failed', ['identifier' => $identifier, 'error' => $e->getMessage()]);
        }

        return null;
    }

    private function fetchChannelInfo(string $apiKey, string $channelId): array
    {
        $response = Http::timeout(10)
            ->get('https://www.googleapis.com/youtube/v3/channels', [
                'part' => 'snippet,statistics',
                'id' => $channelId,
                'key' => $apiKey,
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('YouTube API error: '.$response->status());
        }

        $items = $response->json('items', []);

        if (empty($items)) {
            return [];
        }

        $channel = $items[0];
        $snippet = $channel['snippet'] ?? [];
        $statistics = $channel['statistics'] ?? [];

        return [
            'title' => $snippet['title'] ?? 'Unknown',
            'thumbnail_url' => $snippet['thumbnails']['default']['url'] ?? null,
            'description' => $snippet['description'] ?? null,
            'subscriber_count' => (int) ($statistics['subscriberCount'] ?? 0),
            'video_count' => (int) ($statistics['videoCount'] ?? 0),
        ];
    }

    private function fetchChannelVideos(string $apiKey, string $channelId, int $maxResults = 10): array
    {
        // Step 1: Get video IDs via search
        $searchResponse = Http::timeout(10)
            ->get('https://www.googleapis.com/youtube/v3/search', [
                'part' => 'id',
                'channelId' => $channelId,
                'type' => 'video',
                'order' => 'date',
                'maxResults' => $maxResults,
                'key' => $apiKey,
            ]);

        if (! $searchResponse->successful()) {
            throw new \RuntimeException('YouTube search API error: '.$searchResponse->status());
        }

        $videoIds = collect($searchResponse->json('items', []))
            ->pluck('id.videoId')
            ->filter()
            ->implode(',');

        if (empty($videoIds)) {
            return [];
        }

        // Step 2: Get video details
        $videosResponse = Http::timeout(10)
            ->get('https://www.googleapis.com/youtube/v3/videos', [
                'part' => 'snippet,statistics,contentDetails',
                'id' => $videoIds,
                'key' => $apiKey,
            ]);

        if (! $videosResponse->successful()) {
            throw new \RuntimeException('YouTube videos API error: '.$videosResponse->status());
        }

        return collect($videosResponse->json('items', []))->map(function ($video) {
            $snippet = $video['snippet'] ?? [];
            $statistics = $video['statistics'] ?? [];
            $contentDetails = $video['contentDetails'] ?? [];

            return [
                'video_id' => $video['id'] ?? '',
                'title' => $snippet['title'] ?? 'Untitled',
                'description' => $snippet['description'] ?? null,
                'thumbnail_url' => $snippet['thumbnails']['medium']['url'] ?? null,
                'published_at' => $snippet['publishedAt'] ?? now()->toIso8601String(),
                'view_count' => (int) ($statistics['viewCount'] ?? 0),
                'like_count' => (int) ($statistics['likeCount'] ?? 0),
                'comment_count' => (int) ($statistics['commentCount'] ?? 0),
                'duration' => $contentDetails['duration'] ?? null,
                'category_id' => isset($snippet['categoryId']) ? (int) $snippet['categoryId'] : null,
                'default_language' => $snippet['defaultLanguage'] ?? $snippet['defaultAudioLanguage'] ?? null,
                'tags' => $snippet['tags'] ?? null,
            ];
        })->toArray();
    }
}
