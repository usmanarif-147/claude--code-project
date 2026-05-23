<?php

namespace App\Livewire\Admin\Social\Connections;

use App\Models\Social\PlatformAccount;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class ConnectionsIndex extends Component
{
    public ?PlatformAccount $linkedinAccount = null;

    /** Token paste form state. */
    public string $token_input = '';

    public string $member_urn_input = '';

    public string $account_label_input = '';

    public ?int $expires_in_days = 60;

    public function mount(): void
    {
        $this->loadAccount();
    }

    public function saveLinkedInToken(): void
    {
        $validated = $this->validate([
            'token_input' => 'required|string|min:10',
            'member_urn_input' => 'required|string|max:255',
            'account_label_input' => 'required|string|max:255',
            'expires_in_days' => 'nullable|integer|min:1|max:365',
        ]);

        $days = (int) ($validated['expires_in_days'] ?: 60);

        PlatformAccount::query()->updateOrCreate(
            ['platform' => 'linkedin'],
            [
                'account_label' => $validated['account_label_input'],
                'remote_account_id' => $validated['member_urn_input'],
                'access_token' => $validated['token_input'],
                'token_expires_at' => now()->addDays($days),
                'scope' => 'openid profile w_member_social',
            ],
        );

        $this->reset(['token_input', 'member_urn_input', 'account_label_input']);
        $this->expires_in_days = 60;

        $this->loadAccount();

        session()->flash('success', 'LinkedIn token saved.');
    }

    public function disconnect(): void
    {
        PlatformAccount::query()->where('platform', 'linkedin')->delete();
        $this->linkedinAccount = null;

        session()->flash('success', 'LinkedIn account disconnected.');
    }

    /**
     * 'healthy' | 'expiring' | 'expired' | 'disconnected'
     */
    public function tokenStatus(): string
    {
        if (! $this->linkedinAccount) {
            return 'disconnected';
        }

        $expiresAt = $this->linkedinAccount->token_expires_at;
        if (! $expiresAt) {
            return 'expired';
        }

        if ($expiresAt->isPast()) {
            return 'expired';
        }

        if ($expiresAt->diffInDays(now()) <= 7) {
            return 'expiring';
        }

        return 'healthy';
    }

    public function daysUntilExpiry(): ?int
    {
        if (! $this->linkedinAccount || ! $this->linkedinAccount->token_expires_at) {
            return null;
        }

        if ($this->linkedinAccount->token_expires_at->isPast()) {
            return 0;
        }

        return (int) floor(now()->diffInDays($this->linkedinAccount->token_expires_at, false));
    }

    public function render()
    {
        return view('livewire.admin.social.connections.index');
    }

    private function loadAccount(): void
    {
        $this->linkedinAccount = PlatformAccount::query()
            ->where('platform', 'linkedin')
            ->first();
    }
}
