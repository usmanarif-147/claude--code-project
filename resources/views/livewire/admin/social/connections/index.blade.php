<div>
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-500">Social</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">Connections</span>
    </div>

    {{-- Page header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Connections</h1>
            <p class="text-gray-500 mt-1">Manage which platforms the scheduler can publish to.</p>
        </div>
    </div>

    {{-- Flash messages --}}
    @if (session('success') || session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="mb-6">
            @if (session('success'))
                <div class="flex items-center gap-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-lg px-4 py-3 text-sm">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p>{{ session('success') }}</p>
                    <button @click="show = false" class="ml-auto text-emerald-400/60 hover:text-emerald-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            @endif
            @if (session('error'))
                <div class="flex items-center gap-3 bg-red-500/10 border border-red-500/20 text-red-400 rounded-lg px-4 py-3 text-sm">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p>{{ session('error') }}</p>
                    <button @click="show = false" class="ml-auto text-red-400/60 hover:text-red-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            @endif
        </div>
    @endif

    @php
        $status = $this->tokenStatus();
        $days = $this->daysUntilExpiry();

        $statusMeta = match ($status) {
            'healthy' => [
                'label' => 'Healthy',
                'dot'   => 'bg-emerald-400 animate-pulse',
                'badge' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
            ],
            'expiring' => [
                'label' => 'Expiring soon',
                'dot'   => 'bg-amber-400 animate-pulse',
                'badge' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
            ],
            'expired' => [
                'label' => 'Expired',
                'dot'   => 'bg-red-400',
                'badge' => 'bg-red-500/10 text-red-400 border-red-500/20',
            ],
            default => [
                'label' => 'Not connected',
                'dot'   => 'bg-gray-500',
                'badge' => 'bg-gray-500/10 text-gray-400 border-gray-500/20',
            ],
        };
    @endphp

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        {{-- =====================================
             LEFT — LinkedIn account card (2/3)
             ===================================== --}}
        <div class="xl:col-span-2 space-y-6">
            <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
                {{-- Header row: platform chip + status badge --}}
                <div class="flex items-start justify-between gap-4 flex-wrap mb-5">
                    <div class="flex items-center gap-3">
                        {{-- LinkedIn chip --}}
                        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-[#0a66c2]/15 border border-[#0a66c2]/30 text-[#7cb4ee] text-sm font-semibold">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.852 3.37-1.852 3.601 0 4.267 2.37 4.267 5.455v6.288zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.063 2.063 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                            LinkedIn
                        </span>
                        @if ($linkedinAccount)
                            <span class="text-xs text-gray-500 hidden sm:inline">Personal Profile</span>
                        @endif
                    </div>

                    {{-- Status badge --}}
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border text-xs font-medium {{ $statusMeta['badge'] }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $statusMeta['dot'] }}"></span>
                        {{ $statusMeta['label'] }}
                    </span>
                </div>

                {{-- Account details OR not-connected note --}}
                @if ($linkedinAccount)
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <p class="text-[10px] font-mono uppercase tracking-widest text-gray-500 mb-1">Account label</p>
                                <p class="text-sm text-white font-medium">{{ $linkedinAccount->account_label }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-mono uppercase tracking-widest text-gray-500 mb-1">Member URN</p>
                                <p class="text-xs font-mono text-gray-300 break-all">{{ $linkedinAccount->remote_account_id }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-mono uppercase tracking-widest text-gray-500 mb-1">Access token</p>
                                <p class="text-sm text-emerald-400 inline-flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Token saved (encrypted)
                                </p>
                            </div>
                            <div>
                                <p class="text-[10px] font-mono uppercase tracking-widest text-gray-500 mb-1">Expires</p>
                                @if ($linkedinAccount->token_expires_at)
                                    <p class="text-sm text-white">
                                        {{ $linkedinAccount->token_expires_at->format('M d, Y') }}
                                        @if ($days !== null)
                                            <span class="text-xs text-gray-500 ml-1">({{ $days }} {{ Str::plural('day', $days) }})</span>
                                        @endif
                                    </p>
                                @else
                                    <p class="text-sm text-gray-500">&mdash;</p>
                                @endif
                            </div>
                        </div>

                        <div class="pt-4 border-t border-dark-700 flex items-center gap-3">
                            <x-admin.confirm-button
                                title="Disconnect LinkedIn?"
                                text="The scheduler will stop publishing until you reconnect."
                                action="$wire.disconnect()"
                                confirm-text="Yes, disconnect"
                                class="inline-flex items-center gap-1.5 bg-red-500/10 hover:bg-red-500/20 text-red-400 hover:text-red-300 text-sm font-medium rounded-lg px-4 py-2 transition-all duration-200"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-5 0-9.27-3.11-11-7.5a11.79 11.79 0 012.92-4.19m3.08-2A10.05 10.05 0 0112 5c5 0 9.27 3.11 11 7.5a11.79 11.79 0 01-4.13 5.16M9.88 9.88a3 3 0 104.24 4.24M3 3l18 18"/></svg>
                                Disconnect
                            </x-admin.confirm-button>
                        </div>
                    </div>
                @else
                    <div class="flex flex-col items-start gap-2 py-3">
                        <p class="text-sm text-gray-300 font-medium">Not connected yet</p>
                        <p class="text-xs text-gray-500">Paste a LinkedIn access token below to start publishing carousels from the scheduler.</p>
                    </div>
                @endif
            </div>

            {{-- Manual token paste form --}}
            <div class="bg-dark-800 border border-dark-700 rounded-xl">
                <div class="px-6 py-4 border-b border-dark-700">
                    <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Manual Token Paste</h2>
                    <p class="text-xs text-gray-500 mt-1">
                        Paste a LinkedIn access token from the
                        <a href="https://developer.linkedin.com" target="_blank" rel="noopener" class="text-primary-light hover:text-white transition-colors underline">LinkedIn Developer Console</a>.
                        OAuth UI is coming in a later phase.
                    </p>
                </div>

                <div class="p-6 space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="account_label_input" class="block text-sm font-medium text-gray-300 mb-2">
                                Account label <span class="text-red-400">*</span>
                            </label>
                            <input type="text" id="account_label_input" wire:model="account_label_input"
                                   placeholder="e.g. Usman Iqbal personal"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                            @error('account_label_input') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="member_urn_input" class="block text-sm font-medium text-gray-300 mb-2">
                                Member URN <span class="text-red-400">*</span>
                            </label>
                            <input type="text" id="member_urn_input" wire:model="member_urn_input"
                                   placeholder="abc123 or urn:li:person:abc123"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 font-mono focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                            @error('member_urn_input') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label for="token_input" class="block text-sm font-medium text-gray-300 mb-2">
                            Access token <span class="text-red-400">*</span>
                        </label>
                        <textarea id="token_input" wire:model.defer="token_input" rows="5"
                                  placeholder="Paste your LinkedIn access token here (typically 500+ chars)..."
                                  class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white font-mono text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent resize-y transition-all"></textarea>
                        <p class="mt-1.5 text-xs text-gray-500">Stored encrypted at rest. Never displayed back to the UI.</p>
                        @error('token_input') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <div>
                            <label for="expires_in_days" class="block text-sm font-medium text-gray-300 mb-2">
                                Expires in (days)
                            </label>
                            <input type="number" id="expires_in_days" wire:model="expires_in_days"
                                   min="1" max="365"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                            <p class="mt-1.5 text-xs text-gray-500">LinkedIn tokens last ~60 days.</p>
                            @error('expires_in_days') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-dark-700">
                    <button wire:click="saveLinkedInToken" wire:loading.attr="disabled" wire:target="saveLinkedInToken"
                            class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="saveLinkedInToken" class="inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Save Token
                        </span>
                        <span wire:loading wire:target="saveLinkedInToken" class="inline-flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Saving...
                        </span>
                    </button>
                </div>
            </div>
        </div>

        {{-- =====================================
             RIGHT — Help / sidebar (1/3)
             ===================================== --}}
        <div class="space-y-6">
            <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
                <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider mb-3">How it works</h2>
                <ol class="space-y-3 text-sm text-gray-400">
                    <li class="flex gap-3">
                        <span class="shrink-0 w-5 h-5 rounded-md bg-primary/10 text-primary-light text-xs font-mono font-semibold flex items-center justify-center">1</span>
                        <span>Create a LinkedIn Developer app and generate an access token with the <code class="font-mono text-xs bg-dark-700 px-1.5 py-0.5 rounded text-primary-light">w_member_social</code> scope.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="shrink-0 w-5 h-5 rounded-md bg-primary/10 text-primary-light text-xs font-mono font-semibold flex items-center justify-center">2</span>
                        <span>Paste the token plus your member URN into the form. Token is encrypted before storage.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="shrink-0 w-5 h-5 rounded-md bg-primary/10 text-primary-light text-xs font-mono font-semibold flex items-center justify-center">3</span>
                        <span>The scheduler can now publish carousels to your LinkedIn feed via cron or the Publish Now button.</span>
                    </li>
                </ol>
            </div>

            <div class="bg-gradient-to-br from-primary/10 to-fuchsia-600/10 border border-primary/20 rounded-xl p-6">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-lg bg-primary/15 text-primary-light flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-mono font-semibold text-white uppercase tracking-wider mb-1">OAuth coming soon</h3>
                        <p class="text-xs text-gray-400 leading-relaxed">A future phase will replace manual token paste with a one-click "Connect LinkedIn" OAuth flow plus auto-refresh.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
