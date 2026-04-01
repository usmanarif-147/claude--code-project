<div>
    {{-- 1. BREADCRUMB --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-400">Job Search</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('admin.job-search.alerts.index') }}" wire:navigate class="hover:text-gray-300 transition-colors">Job Alerts</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">Settings</span>
    </div>

    {{-- 2. PAGE HEADER --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Alert Settings</h1>
            <p class="text-sm text-gray-500 mt-1">Configure when and how you want to be notified about high-match jobs.</p>
        </div>
        <a href="{{ route('admin.job-search.alerts.index') }}" wire:navigate
           class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 border border-dark-600 text-gray-300 hover:text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-all duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Alerts
        </a>
    </div>

    {{-- FLASH MESSAGES --}}
    @if(session('success') || session('error'))
        <div x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 5000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="mb-6">
            @if(session('success'))
                <div class="flex items-center gap-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-lg px-4 py-3 text-sm">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p>{{ session('success') }}</p>
                    <button @click="show = false" class="ml-auto text-emerald-400/60 hover:text-emerald-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            @endif
            @if(session('error'))
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

    {{-- 3. SETTINGS FORM --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl">

        {{-- Section: General --}}
        <div class="px-6 py-4 border-b border-dark-700">
            <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">General</h2>
            <p class="text-xs text-gray-500 mt-0.5">Master control for job alert notifications.</p>
        </div>
        <div class="p-6 border-b border-dark-700">
            <div class="flex items-center justify-between p-4 bg-dark-700 rounded-lg">
                <div>
                    <p class="text-sm font-medium text-gray-300">Enable Job Alerts</p>
                    <p class="text-xs text-gray-500 mt-0.5">Receive notifications when jobs match above your threshold.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" wire:model.live="isEnabled" class="sr-only peer">
                    <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                </label>
            </div>
            @error('isEnabled')
                <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Section: Threshold --}}
        <div class="px-6 py-4 border-b border-dark-700">
            <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Threshold</h2>
            <p class="text-xs text-gray-500 mt-0.5">Set the minimum match score to trigger an alert.</p>
        </div>
        <div class="p-6 border-b border-dark-700">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-sm font-medium text-gray-300">Minimum Match Score</label>
                    <span class="text-sm font-semibold text-primary-light">{{ $minScoreThreshold }}%</span>
                </div>
                <input type="range" wire:model.live="minScoreThreshold" min="0" max="100" step="5"
                       class="w-full h-2 bg-dark-700 rounded-full appearance-none cursor-pointer accent-primary">
                <div class="w-full bg-dark-700 rounded-full h-1.5 mt-2">
                    <div class="bg-gradient-to-r from-primary to-fuchsia-500 h-1.5 rounded-full transition-all duration-300"
                         style="width: {{ $minScoreThreshold }}%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-2">You will only be alerted for jobs scoring at or above this percentage.</p>
            </div>
            @error('minScoreThreshold')
                <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Section: Frequency --}}
        <div class="px-6 py-4 border-b border-dark-700">
            <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Frequency</h2>
            <p class="text-xs text-gray-500 mt-0.5">Choose how often you want to receive alert notifications.</p>
        </div>
        <div class="p-6 border-b border-dark-700">
            <div class="space-y-3">
                <label class="flex items-start gap-3 p-4 bg-dark-700 rounded-lg cursor-pointer hover:bg-dark-600 transition-colors {{ $frequency === 'instant' ? 'ring-2 ring-primary' : '' }}">
                    <input type="radio" wire:model.live="frequency" value="instant" class="mt-0.5 text-primary focus:ring-primary bg-dark-600 border-dark-600">
                    <div>
                        <p class="text-sm font-medium text-gray-300">Instant</p>
                        <p class="text-xs text-gray-500 mt-0.5">Get notified immediately when a matching job is found.</p>
                    </div>
                </label>
                <label class="flex items-start gap-3 p-4 bg-dark-700 rounded-lg cursor-pointer hover:bg-dark-600 transition-colors {{ $frequency === 'daily' ? 'ring-2 ring-primary' : '' }}">
                    <input type="radio" wire:model.live="frequency" value="daily" class="mt-0.5 text-primary focus:ring-primary bg-dark-600 border-dark-600">
                    <div>
                        <p class="text-sm font-medium text-gray-300">Daily Digest</p>
                        <p class="text-xs text-gray-500 mt-0.5">Receive a summary of matching jobs once per day.</p>
                    </div>
                </label>
                <label class="flex items-start gap-3 p-4 bg-dark-700 rounded-lg cursor-pointer hover:bg-dark-600 transition-colors {{ $frequency === 'weekly' ? 'ring-2 ring-primary' : '' }}">
                    <input type="radio" wire:model.live="frequency" value="weekly" class="mt-0.5 text-primary focus:ring-primary bg-dark-600 border-dark-600">
                    <div>
                        <p class="text-sm font-medium text-gray-300">Weekly Digest</p>
                        <p class="text-xs text-gray-500 mt-0.5">Receive a summary of matching jobs once per week.</p>
                    </div>
                </label>
            </div>
            @error('frequency')
                <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Section: Notification Channels --}}
        <div class="px-6 py-4 border-b border-dark-700">
            <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Notification Channels</h2>
            <p class="text-xs text-gray-500 mt-0.5">Choose where you want to receive alert notifications.</p>
        </div>
        <div class="p-6 border-b border-dark-700 space-y-4">
            {{-- Dashboard Toggle --}}
            <div class="flex items-center justify-between p-4 bg-dark-700 rounded-lg">
                <div>
                    <p class="text-sm font-medium text-gray-300">Dashboard Notifications</p>
                    <p class="text-xs text-gray-500 mt-0.5">Show alerts in the admin panel notification area.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" wire:model.live="notifyDashboard" class="sr-only peer">
                    <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                </label>
            </div>
            @error('notifyDashboard')
                <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ $message }}
                </p>
            @enderror

            {{-- Email Toggle --}}
            <div class="flex items-center justify-between p-4 bg-dark-700 rounded-lg">
                <div>
                    <p class="text-sm font-medium text-gray-300">Email Notifications</p>
                    <p class="text-xs text-gray-500 mt-0.5">Send alerts to your registered email address.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" wire:model.live="notifyEmail" class="sr-only peer">
                    <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                </label>
            </div>
            @error('notifyEmail')
                <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ $message }}
                </p>
            @enderror

            {{-- Email Warning --}}
            @if($notifyEmail)
                <div class="bg-amber-500/10 border border-amber-500/20 rounded-lg p-4">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <div class="flex-1">
                            <p class="text-sm text-amber-400 font-medium">Email API key required</p>
                            <p class="text-xs text-gray-500 mt-0.5">Make sure a Gmail API key is configured in Settings for email delivery to work.</p>
                        </div>
                        <a href="{{ route('admin.settings.api-keys') }}" wire:navigate
                           class="inline-flex items-center gap-1.5 text-sm font-medium text-amber-400 hover:text-amber-300 transition-colors whitespace-nowrap">
                            Go to Settings
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            @endif
        </div>

        {{-- Save Button --}}
        <div class="px-6 py-4 flex justify-end">
            <button wire:click="save"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20 disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="save">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </span>
                <span wire:loading wire:target="save">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                </span>
                <span wire:loading.remove wire:target="save">Save Settings</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
        </div>
    </div>
</div>
