<div>
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span>Settings</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">API Keys</span>
    </div>

    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">API Keys</h1>
        <p class="text-gray-500 mt-1">Manage API credentials for integrations and automations.</p>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mb-6 flex items-center gap-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl px-4 py-3">
            <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="text-sm text-emerald-400">{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 flex items-center gap-3 bg-red-500/10 border border-red-500/20 rounded-xl px-4 py-3">
            <svg class="w-5 h-5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="text-sm text-red-400">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Provider Card Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach ($providers as $providerKey => $provider)
            @php
                $keyData = $keys[$providerKey] ?? ['exists' => false];
                $exists = $keyData['exists'] ?? false;
                $testStatus = $keyData['test_status'] ?? null;
                $isConnected = $keyData['is_connected'] ?? false;
                $lastTested = $keyData['last_tested_at'] ?? null;
                $keyPreview = $keyData['key_preview'] ?? '';
            @endphp

            <div
                x-data="{ showForm: false, showKey: false }"
                class="bg-dark-800 border border-dark-700 rounded-xl p-6 flex flex-col"
            >
                {{-- Card Header --}}
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-center gap-3">
                        {{-- Provider Icon --}}
                        <div @class([
                            'w-10 h-10 rounded-lg flex items-center justify-center text-sm font-bold',
                            'bg-red-500/10 text-red-400' => $provider['color'] === 'red',
                            'bg-orange-500/10 text-orange-400' => $provider['color'] === 'orange',
                            'bg-green-500/10 text-green-400' => $provider['color'] === 'green',
                            'bg-blue-500/10 text-blue-400' => $provider['color'] === 'blue',
                            'bg-teal-500/10 text-teal-400' => $provider['color'] === 'teal',
                            'bg-indigo-500/10 text-indigo-400' => $provider['color'] === 'indigo',
                        ])>
                            @switch($providerKey)
                                @case('gmail')
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M20 18h-2V9.25L12 13 6 9.25V18H4V6h1.2l6.8 4.25L18.8 6H20m0-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2z"/></svg>
                                    @break
                                @case('claude')
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>
                                    @break
                                @case('openai')
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M22.2 12c0-1.7-.8-3.2-2.2-4.2.2-.7.2-1.4.1-2.1-.2-1.5-1-2.8-2.2-3.7-1.7-1.2-3.9-1.3-5.7-.4C11 .6 9.6 0 8.1 0 6.5 0 5 .7 3.9 1.9 2.2 3.6 1.8 6.2 2.8 8.3 1.4 9.3.6 10.8.6 12.5c0 1.7.8 3.3 2.2 4.3-.2.7-.2 1.4-.1 2.1.2 1.5 1 2.8 2.2 3.7 1.2.9 2.7 1.3 4.1 1.3.6 0 1.1-.1 1.6-.2 1.2 1 2.6 1.5 4.1 1.5 1.6 0 3.1-.7 4.2-1.9 1.7-1.7 2.1-4.3 1.1-6.4 1.4-1 2.2-2.5 2.2-4.2l0 .3z"/></svg>
                                    @break
                                @case('jsearch')
                                    <span class="text-xs font-mono">JS</span>
                                    @break
                                @case('adzuna')
                                    <span class="text-xs font-mono">AZ</span>
                                    @break
                                @case('serpapi')
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                                    @break
                                @case('youtube')
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M10 15l5.19-3L10 9v6m11.56-7.83c.13.47.22 1.1.28 1.9.07.8.1 1.49.1 2.09L22 12c0 2.19-.16 3.8-.44 4.83-.25.9-.83 1.48-1.73 1.73-.47.13-1.33.22-2.65.28-1.3.07-2.49.1-3.59.1L12 19c-4.19 0-6.8-.16-7.83-.44-.9-.25-1.48-.83-1.73-1.73-.13-.47-.22-1.1-.28-1.9-.07-.8-.1-1.49-.1-2.09L2 12c0-2.19.16-3.8.44-4.83.25-.9.83-1.48 1.73-1.73.47-.13 1.33-.22 2.65-.28 1.3-.07 2.49-.1 3.59-.1L12 5c4.19 0 6.8.16 7.83.44.9.25 1.48.83 1.73 1.73z"/></svg>
                                    @break
                            @endswitch
                        </div>
                        <div>
                            <h3 class="font-mono font-bold text-white uppercase tracking-wider text-sm">{{ $provider['name'] }}</h3>
                            <p class="text-xs text-gray-500">{{ $provider['description'] }}</p>
                        </div>
                    </div>

                    {{-- Status Badge --}}
                    @if ($exists && $isConnected)
                        @if ($testStatus === 'passed')
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400">Connected</span>
                        @elseif ($testStatus === 'failed')
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-500/10 text-red-400">Failed</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400">Untested</span>
                        @endif
                    @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-500/10 text-gray-500">Not Connected</span>
                    @endif
                </div>

                {{-- Card Body --}}
                <div class="flex-1 mb-4">
                    @if ($exists)
                        <div class="space-y-1">
                            <p class="text-xs text-gray-500">
                                Last tested:
                                @if ($lastTested)
                                    {{ \Carbon\Carbon::parse($lastTested)->diffForHumans() }}
                                @else
                                    Never tested
                                @endif
                            </p>
                            @if (! in_array($providerKey, ['gmail', 'youtube']) && $keyPreview)
                                <p class="text-xs text-gray-400 font-mono">
                                    {{ $keyPreview }}
                                </p>
                            @elseif (in_array($providerKey, ['gmail', 'youtube']))
                                <p class="text-xs text-gray-400 font-mono">OAuth2 credentials configured</p>
                            @endif
                        </div>
                    @else
                        <p class="text-xs text-gray-500">No API key configured</p>
                    @endif
                </div>

                {{-- Card Actions --}}
                <div class="flex items-center gap-2 mb-4">
                    <button
                        @click="showForm = !showForm"
                        class="bg-dark-700 hover:bg-dark-600 text-gray-300 rounded-lg px-4 py-2 text-sm transition-colors"
                    >
                        Configure
                    </button>

                    @if ($exists)
                        <button
                            wire:click="testKey('{{ $providerKey }}')"
                            wire:loading.attr="disabled"
                            wire:target="testKey('{{ $providerKey }}')"
                            class="bg-primary/10 text-primary-light hover:bg-primary/20 rounded-lg px-4 py-2 text-sm transition-colors inline-flex items-center gap-2"
                        >
                            <span wire:loading wire:target="testKey('{{ $providerKey }}')">
                                <svg class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </span>
                            <span wire:loading.remove wire:target="testKey('{{ $providerKey }}')">Test</span>
                            <span wire:loading wire:target="testKey('{{ $providerKey }}')">Testing...</span>
                        </button>

                        <button
                            wire:click="deleteKey('{{ $providerKey }}')"
                            wire:confirm="Are you sure you want to delete this API key?"
                            class="bg-red-500/10 text-red-400 hover:bg-red-500/20 rounded-lg px-3 py-2 text-sm transition-colors"
                        >
                            Delete
                        </button>
                    @endif
                </div>

                {{-- Inline Form --}}
                <div
                    x-show="showForm"
                    x-cloak
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-2"
                    class="border-t border-dark-700 pt-4"
                >
                    <div class="space-y-3">
                        @if (in_array($providerKey, ['gmail', 'youtube']))
                            {{-- OAuth: 3 fields --}}
                            <div>
                                <label class="text-sm font-medium text-gray-300 mb-1 block">Client ID</label>
                                <input
                                    type="text"
                                    wire:model="formData.{{ $providerKey }}.client_id"
                                    placeholder="Enter {{ ucfirst($providerKey) }} Client ID"
                                    class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                >
                                @error("formData.{$providerKey}.client_id") <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-300 mb-1 block">Client Secret</label>
                                <input
                                    type="password"
                                    wire:model="formData.{{ $providerKey }}.client_secret"
                                    placeholder="Enter {{ ucfirst($providerKey) }} Client Secret"
                                    class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                >
                                @error("formData.{$providerKey}.client_secret") <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-300 mb-1 block">Refresh Token</label>
                                <input
                                    type="password"
                                    wire:model="formData.{{ $providerKey }}.refresh_token"
                                    placeholder="Enter {{ ucfirst($providerKey) }} Refresh Token"
                                    class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                >
                                @error("formData.{$providerKey}.refresh_token") <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        @else
                            {{-- Other providers: 1 field --}}
                            <div>
                                <label class="text-sm font-medium text-gray-300 mb-1 block">API Key</label>
                                <div class="relative" x-data="{ visible: false }">
                                    <input
                                        :type="visible ? 'text' : 'password'"
                                        wire:model="formData.{{ $providerKey }}.key_value"
                                        placeholder="Enter your API key"
                                        class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 pr-10 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                                    >
                                    <button
                                        type="button"
                                        @click="visible = !visible"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition-colors"
                                    >
                                        <svg x-show="!visible" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <svg x-show="visible" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                    </button>
                                </div>
                                @error("formData.{$providerKey}.key_value") <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        @endif

                        {{-- Form Buttons --}}
                        <div class="flex items-center gap-2 pt-1">
                            <button
                                wire:click="saveKey('{{ $providerKey }}')"
                                wire:loading.attr="disabled"
                                wire:target="saveKey('{{ $providerKey }}')"
                                class="bg-primary hover:bg-primary-hover text-white font-medium rounded-lg px-4 py-2.5 text-sm transition-colors inline-flex items-center gap-2"
                            >
                                <span wire:loading wire:target="saveKey('{{ $providerKey }}')">
                                    <svg class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                </span>
                                Save
                            </button>
                            <button
                                @click="showForm = false"
                                class="bg-dark-700 hover:bg-dark-600 text-gray-300 rounded-lg px-4 py-2.5 text-sm transition-colors"
                            >
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
