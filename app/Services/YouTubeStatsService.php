<?php

namespace App\Services;

use App\Models\ApiKey;
use App\Models\YouTube\YouTubeChannelStat;
use App\Models\YouTube\YouTubeVideo;
use App\Models\YouTube\YouTubeWeeklySnapshot;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YouTubeStatsService
{
    private const STALE_HOURS = 6;

    private const REFRESH_COOLDOWN_MINUTES = 5;

    public function getChannelStats(int $userId): ?YouTubeChannelStat
    {
        return YouTubeChannelStat::forUser($userId)->first();
    }

    public function getRecentVideos(int $userId, int $limit = 10): Collection
    {
        return YouTubeVideo::forUser($userId)
            ->recent()
            ->limit($limit)
            ->get();
    }

    public function getWeeklyComparison(int $userId): array
    {
        $currentWeekStart = Carbon::now()->startOfWeek(Carbon::MONDAY);
        $previousWeekStart = $currentWeekStart->copy()->subWeek();

        $current = YouTubeWeeklySnapshot::forUser($userId)
            ->where('week_start', $currentWeekStart->toDateString())
            ->first();

        $previous = YouTubeWeeklySnapshot::forUser($userId)
            ->where('week_start', $previousWeekStart->toDateString())
            ->first();

        $deltas = [];

        if ($current && $previous) {
            $deltas = [
                'subscribers' => $this->calculateDelta($current->subscriber_count, $previous->subscriber_count),
                'views' => $this->calculateDelta($current->view_count, $previous->view_count),
                'watch_hours' => $this->calculateDelta((float) $current->estimated_watch_hours, (float) $previous->estimated_watch_hours),
            ];
        }

        return [
            'current' => $current,
            'previous' => $previous,
            'deltas' => $deltas,
        ];
    }

    public function refreshFromApi(int $userId): bool
    {
        $apiKey = ApiKey::forUser($userId)
            ->forProvider(ApiKey::PROVIDER_YOUTUBE)
            ->first();

        if (! $apiKey || ! $apiKey->key_value) {
            return false;
        }

        $extra = $apiKey->extra_data;
        $channelId = $extra['channel_id'] ?? null;

        if (! $channelId) {
            return false;
        }

        try {
            $channelData = $this->fetchChannelData($apiKey->key_value, $channelId);

            if (empty($channelData)) {
                return false;
            }

            YouTubeChannelStat::updateOrCreate(
                ['user_id' => $userId],
                [
                    'channel_id' => $channelId,
                    'channel_title' => $channelData['title'] ?? 'Unknown',
                    'channel_thumbnail_url' => $channelData['thumbnail_url'] ?? null,
                    'subscriber_count' => $channelData['subscriber_count'] ?? 0,
                    'total_view_count' => $channelData['view_count'] ?? 0,
                    'video_count' => $channelData['video_count'] ?? 0,
                    'estimated_watch_hours' => $channelData['estimated_watch_hours'] ?? 0,
                    'fetched_at' => now(),
                ],
            );

            $videosData = $this->fetchRecentVideos($apiKey->key_value, $channelId);
            $this->syncVideos($userId, $videosData);

            $this->saveWeeklySnapshot($userId);

            return true;
        } catch (\Throwable $e) {
            Log::error('YouTube API refresh failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function updateMonthlyRevenue(int $userId, float $amount): YouTubeChannelStat
    {
        $stat = YouTubeChannelStat::forUser($userId)->first();

        if (! $stat) {
            $stat = YouTubeChannelStat::create([
                'user_id' => $userId,
                'channel_id' => '',
                'channel_title' => 'Unknown',
                'monthly_revenue' => $amount,
                'fetched_at' => now(),
            ]);
        } else {
            $stat->update(['monthly_revenue' => $amount]);
        }

        return $stat;
    }

    public function isStale(int $userId): bool
    {
        $stat = YouTubeChannelStat::forUser($userId)->first();

        if (! $stat || ! $stat->fetched_at) {
            return true;
        }

        return $stat->fetched_at->diffInHours(now()) >= self::STALE_HOURS;
    }

    public function canRefresh(int $userId): bool
    {
        $stat = YouTubeChannelStat::forUser($userId)->first();

        if (! $stat || ! $stat->fetched_at) {
            return true;
        }

        return $stat->fetched_at->diffInMinutes(now()) >= self::REFRESH_COOLDOWN_MINUTES;
    }

    public function fetchChannelData(string $apiKey, string $channelId): array
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
            'subscriber_count' => (int) ($statistics['subscriberCount'] ?? 0),
            'view_count' => (int) ($statistics['viewCount'] ?? 0),
            'video_count' => (int) ($statistics['videoCount'] ?? 0),
            'estimated_watch_hours' => 0,
        ];
    }

    public function fetchRecentVideos(string $apiKey, string $channelId, int $maxResults = 10): array
    {
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
                'thumbnail_url' => $snippet['thumbnails']['medium']['url'] ?? null,
                'published_at' => $snippet['publishedAt'] ?? now()->toIso8601String(),
                'view_count' => (int) ($statistics['viewCount'] ?? 0),
                'like_count' => (int) ($statistics['likeCount'] ?? 0),
                'comment_count' => (int) ($statistics['commentCount'] ?? 0),
                'duration' => $contentDetails['duration'] ?? null,
            ];
        })->toArray();
    }

    public function saveWeeklySnapshot(int $userId): void
    {
        $stat = YouTubeChannelStat::forUser($userId)->first();

        if (! $stat) {
            return;
        }

        $weekStart = Carbon::now()->startOfWeek(Carbon::MONDAY)->toDateString();

        YouTubeWeeklySnapshot::updateOrCreate(
            ['user_id' => $userId, 'week_start' => $weekStart],
            [
                'subscriber_count' => $stat->subscriber_count,
                'view_count' => $stat->total_view_count,
                'video_count' => $stat->video_count,
                'estimated_watch_hours' => $stat->estimated_watch_hours,
            ],
        );
    }

    private function syncVideos(int $userId, array $videosData): void
    {
        $fetchedVideoIds = collect($videosData)->pluck('video_id')->filter()->toArray();

        if (! empty($fetchedVideoIds)) {
            YouTubeVideo::forUser($userId)
                ->whereNotIn('video_id', $fetchedVideoIds)
                ->delete();
        }

        foreach ($videosData as $videoData) {
            YouTubeVideo::updateOrCreate(
                ['user_id' => $userId, 'video_id' => $videoData['video_id']],
                [
                    'title' => $videoData['title'],
                    'thumbnail_url' => $videoData['thumbnail_url'],
                    'published_at' => Carbon::parse($videoData['published_at']),
                    'view_count' => $videoData['view_count'],
                    'like_count' => $videoData['like_count'],
                    'comment_count' => $videoData['comment_count'],
                    'duration' => $videoData['duration'],
                ],
            );
        }
    }

    private function calculateDelta(float $current, float $previous): array
    {
        $change = $current - $previous;
        $percentage = $previous > 0 ? round(($change / $previous) * 100, 1) : 0;

        return [
            'change' => $change,
            'percentage' => $percentage,
            'direction' => $change >= 0 ? 'up' : 'down',
        ];
    }
}
