<?php

namespace App\Livewire\Admin\Youtube\Stats;

use App\Models\ApiKey;
use App\Models\YouTube\YouTubeChannelStat;
use App\Services\YouTubeStatsService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class YouTubeStatsIndex extends Component
{
    public ?YouTubeChannelStat $channelStats = null;

    public Collection $recentVideos;

    public array $weeklyComparison = [];

    public bool $isConfigured = false;

    public bool $isLoading = false;

    public string $monthlyRevenue = '';

    public function mount(YouTubeStatsService $service): void
    {
        $this->recentVideos = collect();

        $apiKey = ApiKey::where('provider', ApiKey::PROVIDER_YOUTUBE)
            ->where('user_id', auth()->id())
            ->first();

        if (! $apiKey || ! $apiKey->key_value) {
            $this->isConfigured = false;

            return;
        }

        $extra = $apiKey->extra_data;

        if (empty($extra['channel_id'])) {
            $this->isConfigured = false;

            return;
        }

        $this->isConfigured = true;
        $this->loadData($service);

        if ($service->isStale(auth()->id())) {
            $this->refreshStats();
        }
    }

    public function refreshStats(): void
    {
        $service = app(YouTubeStatsService::class);

        if (! $service->canRefresh(auth()->id())) {
            $stat = $service->getChannelStats(auth()->id());
            $minutes = $stat && $stat->fetched_at
                ? 5 - $stat->fetched_at->diffInMinutes(now())
                : 0;
            session()->flash('error', "Please wait {$minutes} minute(s) before refreshing again.");

            return;
        }

        $this->isLoading = true;

        $success = $service->refreshFromApi(auth()->id());

        if ($success) {
            $this->loadData($service);
            session()->flash('success', 'YouTube stats refreshed successfully.');
        } else {
            session()->flash('error', 'Failed to refresh YouTube stats. Please check your API key and channel ID.');
        }

        $this->isLoading = false;
    }

    public function updateRevenue(): void
    {
        $this->validate([
            'monthlyRevenue' => 'nullable|numeric|min:0|max:9999999.99',
        ]);

        $service = app(YouTubeStatsService::class);

        $amount = $this->monthlyRevenue !== '' ? (float) $this->monthlyRevenue : 0;
        $service->updateMonthlyRevenue(auth()->id(), $amount);

        session()->flash('success', 'Monthly revenue updated.');
    }

    public function render()
    {
        return view('livewire.admin.youtube.stats.index');
    }

    public function formatCompactNumber(int $number): string
    {
        if ($number >= 1000000) {
            return round($number / 1000000, 1).'M';
        }

        if ($number >= 1000) {
            return round($number / 1000, 1).'K';
        }

        return (string) $number;
    }

    public function formatDuration(?string $duration): string
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

    private function loadData(YouTubeStatsService $service): void
    {
        $userId = auth()->id();

        $this->channelStats = $service->getChannelStats($userId);
        $this->recentVideos = $service->getRecentVideos($userId);
        $this->weeklyComparison = $service->getWeeklyComparison($userId);
        $this->monthlyRevenue = $this->channelStats?->monthly_revenue ?? '';
    }
}
