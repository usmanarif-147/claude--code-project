<div>
    {{-- 1. BREADCRUMB --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-400">Email</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('admin.email.recruiter-alerts.index') }}" wire:navigate class="hover:text-gray-300 transition-colors">Recruiter Alerts</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">Settings</span>
    </div>

    {{-- 2. PAGE HEADER --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Alert Settings</h1>
            <p class="text-sm text-gray-500 mt-1">Configure recruiter alert detection preferences.</p>
        </div>
        <a href="{{ route('admin.email.recruiter-alerts.index') }}" wire:navigate
           class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 text-gray-300 text-sm font-medium rounded-lg px-4 py-2.5 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back
        </a>
    </div>

    <form wire:submit="save" class="space-y-6">
        {{-- Section 1: General --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl">
            <div class="px-6 py-4 border-b border-dark-700">
                <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">General</h2>
                <p class="text-xs text-gray-500 mt-0.5">Master controls for the recruiter alert feature.</p>
            </div>
            <div class="p-6 space-y-5">
                {{-- Master toggle --}}
                <div class="flex items-center justify-between p-4 bg-dark-700 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-300">Enable Recruiter Alerts</p>
                        <p class="text-xs text-gray-500 mt-0.5">When disabled, no new alerts will be created during email scans.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="is_enabled" class="sr-only peer">
                        <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>

                {{-- Min confidence score --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-medium text-gray-300">Minimum Confidence Score</label>
                        <span class="text-sm font-semibold text-primary-light">{{ $min_confidence_score }}%</span>
                    </div>
                    <input type="range" wire:model.live="min_confidence_score" min="0" max="100" step="5"
                           class="w-full h-2 bg-dark-700 rounded-full appearance-none cursor-pointer accent-primary">
                    <div class="w-full bg-dark-700 rounded-full h-1.5 mt-2">
                        <div class="bg-gradient-to-r from-primary to-fuchsia-500 h-1.5 rounded-full transition-all duration-300"
                             style="width: {{ $min_confidence_score }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Only emails with AI confidence above this threshold will create alerts.</p>
                    @error('min_confidence_score')
                        <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Section 2: Alert Types --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl">
            <div class="px-6 py-4 border-b border-dark-700">
                <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Alert Types</h2>
                <p class="text-xs text-gray-500 mt-0.5">Choose which types of emails trigger alerts.</p>
            </div>
            <div class="p-6 space-y-4">
                {{-- Recruiter toggle --}}
                <div class="flex items-center justify-between p-4 bg-dark-700 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-300">Alert on Recruiter Emails</p>
                        <p class="text-xs text-gray-500 mt-0.5">Emails from recruiting agencies and LinkedIn recruiters.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="alert_on_recruiter" class="sr-only peer">
                        <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>

                {{-- Hiring Manager toggle --}}
                <div class="flex items-center justify-between p-4 bg-dark-700 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-300">Alert on Hiring Manager Emails</p>
                        <p class="text-xs text-gray-500 mt-0.5">Direct emails from hiring managers at companies.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="alert_on_hiring_manager" class="sr-only peer">
                        <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>

                {{-- Freelance Client toggle --}}
                <div class="flex items-center justify-between p-4 bg-dark-700 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-300">Alert on Freelance Client Emails</p>
                        <p class="text-xs text-gray-500 mt-0.5">Project inquiries from Fiverr, Upwork, or direct clients.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="alert_on_freelance_client" class="sr-only peer">
                        <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>
            </div>
        </div>

        {{-- Section 3: Notifications --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl">
            <div class="px-6 py-4 border-b border-dark-700">
                <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Notifications</h2>
                <p class="text-xs text-gray-500 mt-0.5">Configure how you get notified about new alerts.</p>
            </div>
            <div class="p-6 space-y-4">
                {{-- Browser Notifications toggle --}}
                <div class="flex items-center justify-between p-4 bg-dark-700 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-300">Browser Notifications</p>
                        <p class="text-xs text-gray-500 mt-0.5">Get push notifications for new alerts.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="browser_notification" class="sr-only peer">
                        <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>

                {{-- Email Forwarding toggle --}}
                <div class="flex items-center justify-between p-4 bg-dark-700 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-300">Email Forwarding</p>
                        <p class="text-xs text-gray-500 mt-0.5">Forward urgent alerts to another email address.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model.live="email_forward" class="sr-only peer">
                        <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                    </label>
                </div>

                {{-- Forward email input (conditional) --}}
                @if($email_forward)
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Forward Email Address <span class="text-red-400">*</span>
                        </label>
                        <input type="email" wire:model="forward_email"
                               placeholder="alerts@example.com"
                               class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                        @error('forward_email')
                            <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                @endif
            </div>
        </div>

        {{-- Submit section --}}
        <div class="flex items-center gap-3">
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20 disabled:opacity-50 disabled:cursor-not-allowed"
                    wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </span>
                <span wire:loading wire:target="save">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                </span>
                <span wire:loading.remove wire:target="save">Save Changes</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
            <a href="{{ route('admin.email.recruiter-alerts.index') }}" wire:navigate
               class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 border border-dark-600 text-gray-300 hover:text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200">
                Cancel
            </a>
        </div>
    </form>
</div>
