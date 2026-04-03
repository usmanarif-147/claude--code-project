<?php

namespace App\Livewire\Admin\Youtube\Subscriptions;

use App\Services\YouTubeSubscriptionService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class SubscriptionIndex extends Component
{
    public Collection $subscriptions;

    public string $newChannelInput = '';

    #[Url]
    public string $search = '';

    public function mount(YouTubeSubscriptionService $service): void
    {
        $this->loadSubscriptions($service);
    }

    public function subscribe(): void
    {
        $this->validate(['newChannelInput' => 'required|string|max:500']);

        $service = app(YouTubeSubscriptionService::class);

        try {
            $service->subscribe(auth()->id(), $this->newChannelInput);
            $this->newChannelInput = '';
            $this->loadSubscriptions($service);
            session()->flash('success', 'Channel subscribed successfully.');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function unsubscribe(int $id): void
    {
        $service = app(YouTubeSubscriptionService::class);
        $service->unsubscribe(auth()->id(), $id);
        $this->loadSubscriptions($service);
        session()->flash('success', 'Channel unsubscribed.');
    }

    public function refreshChannel(int $id): void
    {
        $service = app(YouTubeSubscriptionService::class);

        try {
            $service->refreshChannelData(auth()->id(), $id);
            $this->loadSubscriptions($service);
            session()->flash('success', 'Channel data refreshed.');
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function refreshAll(): void
    {
        $service = app(YouTubeSubscriptionService::class);
        $result = $service->syncAllSubscriptions(auth()->id());
        $this->loadSubscriptions($service);
        session()->flash('success', "Synced {$result['synced']} channels, found {$result['new_videos']} new videos.");
    }

    public function render()
    {
        $subscriptions = $this->subscriptions;

        if ($this->search) {
            $search = strtolower($this->search);
            $subscriptions = $subscriptions->filter(fn ($sub) => str_contains(strtolower($sub->channel_title), $search));
        }

        return view('livewire.admin.youtube.subscriptions.index', [
            'filteredSubscriptions' => $subscriptions,
        ]);
    }

    private function loadSubscriptions(YouTubeSubscriptionService $service): void
    {
        $this->subscriptions = $service->getSubscriptions(auth()->id());
    }
}
