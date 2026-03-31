<div>
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-400">Settings</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">Profile Settings</span>
    </div>

    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Profile Settings</h1>
        <p class="text-sm text-gray-500 mt-1">Manage your personal information, social links, and account preferences.</p>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="mb-6 flex items-center gap-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl px-5 py-4">
            <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-sm text-emerald-400">{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="mb-6 flex items-center gap-3 bg-red-500/10 border border-red-500/20 rounded-xl px-5 py-4">
            <svg class="w-5 h-5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-sm text-red-400">{{ session('error') }}</p>
        </div>
    @endif

    {{-- Main Layout: 2/3 + 1/3 Grid --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- Left Column --}}
        <div class="xl:col-span-2 space-y-6">

            {{-- Card 1: Basic Information --}}
            <form wire:submit="save">
                <div class="bg-dark-800 border border-dark-700 rounded-xl">
                    <div class="px-6 py-4 border-b border-dark-700">
                        <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Basic Information</h2>
                    </div>
                    <div class="p-6 space-y-5">
                        {{-- Name --}}
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-300 mb-2">
                                Name <span class="text-red-400">*</span>
                            </label>
                            <input type="text" id="name" wire:model="name"
                                   placeholder="Your full name"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                            @error('name')
                                <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Tagline --}}
                        <div>
                            <label for="tagline" class="block text-sm font-medium text-gray-300 mb-2">Tagline</label>
                            <input type="text" id="tagline" wire:model="tagline"
                                   placeholder="e.g. Full-Stack Developer"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                            @error('tagline')
                                <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Bio --}}
                        <div>
                            <label for="bio" class="block text-sm font-medium text-gray-300 mb-2">Bio</label>
                            <textarea id="bio" wire:model="bio" rows="5"
                                      placeholder="Tell visitors about yourself..."
                                      class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent resize-none transition-all duration-200"></textarea>
                            @error('bio')
                                <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Availability Status --}}
                        <div>
                            <label for="availability_status" class="block text-sm font-medium text-gray-300 mb-2">Availability Status</label>
                            <input type="text" id="availability_status" wire:model="availability_status"
                                   placeholder="e.g. Available for freelance"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                            @error('availability_status')
                                <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Card 2: Contact Information --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl mt-6">
                    <div class="px-6 py-4 border-b border-dark-700">
                        <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Contact Information</h2>
                    </div>
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                        {{-- Secondary Email --}}
                        <div>
                            <label for="secondary_email" class="block text-sm font-medium text-gray-300 mb-2">Secondary Email</label>
                            <input type="email" id="secondary_email" wire:model="secondary_email"
                                   placeholder="contact@example.com"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                            @error('secondary_email')
                                <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Phone --}}
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-300 mb-2">Phone</label>
                            <input type="text" id="phone" wire:model="phone"
                                   placeholder="+1 234 567 890"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                            @error('phone')
                                <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Location --}}
                        <div class="sm:col-span-2">
                            <label for="location" class="block text-sm font-medium text-gray-300 mb-2">Location</label>
                            <input type="text" id="location" wire:model="location"
                                   placeholder="City, Country"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                            @error('location')
                                <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Card 3: Social Links --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl mt-6">
                    <div class="px-6 py-4 border-b border-dark-700">
                        <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Social Links</h2>
                    </div>
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                        {{-- LinkedIn URL --}}
                        <div>
                            <label for="linkedin_url" class="block text-sm font-medium text-gray-300 mb-2">LinkedIn URL</label>
                            <input type="url" id="linkedin_url" wire:model="linkedin_url"
                                   placeholder="https://linkedin.com/in/username"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                            @error('linkedin_url')
                                <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- GitHub URL --}}
                        <div>
                            <label for="github_url" class="block text-sm font-medium text-gray-300 mb-2">GitHub URL</label>
                            <input type="url" id="github_url" wire:model="github_url"
                                   placeholder="https://github.com/username"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                            @error('github_url')
                                <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Fiverr URL --}}
                        <div>
                            <label for="fiverr_url" class="block text-sm font-medium text-gray-300 mb-2">Fiverr URL</label>
                            <input type="url" id="fiverr_url" wire:model="fiverr_url"
                                   placeholder="https://fiverr.com/username"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                            @error('fiverr_url')
                                <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- YouTube URL --}}
                        <div>
                            <label for="youtube_url" class="block text-sm font-medium text-gray-300 mb-2">YouTube URL</label>
                            <input type="url" id="youtube_url" wire:model="youtube_url"
                                   placeholder="https://youtube.com/@channel"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                            @error('youtube_url')
                                <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Card 4: Preferences --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl mt-6">
                    <div class="px-6 py-4 border-b border-dark-700">
                        <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Preferences</h2>
                    </div>
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                        {{-- Timezone --}}
                        <div>
                            <label for="timezone" class="block text-sm font-medium text-gray-300 mb-2">Timezone</label>
                            <select id="timezone" wire:model="timezone"
                                    class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                                <option value="UTC">UTC</option>
                                <option value="Asia/Karachi">Asia/Karachi (PKT)</option>
                                <option value="Asia/Kolkata">Asia/Kolkata (IST)</option>
                                <option value="Asia/Dubai">Asia/Dubai (GST)</option>
                                <option value="Asia/Shanghai">Asia/Shanghai (CST)</option>
                                <option value="Asia/Tokyo">Asia/Tokyo (JST)</option>
                                <option value="Asia/Singapore">Asia/Singapore (SGT)</option>
                                <option value="Europe/London">Europe/London (GMT/BST)</option>
                                <option value="Europe/Berlin">Europe/Berlin (CET)</option>
                                <option value="Europe/Paris">Europe/Paris (CET)</option>
                                <option value="America/New_York">America/New_York (EST)</option>
                                <option value="America/Chicago">America/Chicago (CST)</option>
                                <option value="America/Denver">America/Denver (MST)</option>
                                <option value="America/Los_Angeles">America/Los_Angeles (PST)</option>
                                <option value="America/Toronto">America/Toronto (EST)</option>
                                <option value="America/Sao_Paulo">America/Sao_Paulo (BRT)</option>
                                <option value="Australia/Sydney">Australia/Sydney (AEST)</option>
                                <option value="Pacific/Auckland">Pacific/Auckland (NZST)</option>
                            </select>
                            @error('timezone')
                                <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Language --}}
                        <div>
                            <label for="language" class="block text-sm font-medium text-gray-300 mb-2">Language</label>
                            <select id="language" wire:model="language"
                                    class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                                <option value="en">English</option>
                            </select>
                            @error('language')
                                <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Save Profile Button --}}
                <div class="mt-6 flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20 disabled:opacity-50 disabled:cursor-not-allowed"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        <span wire:loading wire:target="save">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        </span>
                        <span wire:loading.remove wire:target="save">Save Profile</span>
                        <span wire:loading wire:target="save">Saving...</span>
                    </button>
                </div>
            </form>

            {{-- Card 5: Change Password (separate form) --}}
            <div class="bg-dark-800 border border-dark-700 rounded-xl">
                <div class="px-6 py-4 border-b border-dark-700">
                    <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Change Password</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Leave blank if you don't want to change your password.</p>
                </div>
                <div class="p-6 space-y-5">
                    {{-- Current Password --}}
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-300 mb-2">Current Password</label>
                        <input type="password" id="current_password" wire:model="current_password"
                               placeholder="Enter current password"
                               class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                        @error('current_password')
                            <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- New Password --}}
                    <div>
                        <label for="new_password" class="block text-sm font-medium text-gray-300 mb-2">New Password</label>
                        <input type="password" id="new_password" wire:model="new_password"
                               placeholder="Enter new password"
                               class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                        @error('new_password')
                            <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Confirm New Password --}}
                    <div>
                        <label for="new_password_confirmation" class="block text-sm font-medium text-gray-300 mb-2">Confirm New Password</label>
                        <input type="password" id="new_password_confirmation" wire:model="new_password_confirmation"
                               placeholder="Confirm new password"
                               class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                        @error('new_password_confirmation')
                            <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Update Password Button --}}
                    <div class="flex justify-end">
                        <button type="button" wire:click="changePassword"
                                class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20 disabled:opacity-50 disabled:cursor-not-allowed"
                                wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="changePassword">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </span>
                            <span wire:loading wire:target="changePassword">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            </span>
                            <span wire:loading.remove wire:target="changePassword">Update Password</span>
                            <span wire:loading wire:target="changePassword">Updating...</span>
                        </button>
                    </div>
                </div>
            </div>

        </div>

        {{-- Right Column: Profile Image --}}
        <div class="space-y-6">
            <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
                <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider mb-4">Profile Image</h2>

                <div class="space-y-4">
                    @if ($profile_image)
                        <div class="relative">
                            <img src="{{ $profile_image->temporaryUrl() }}" alt="Preview"
                                 class="w-full aspect-square object-cover rounded-lg">
                            <button type="button" wire:click="$set('profile_image', null)"
                                    class="absolute top-2 right-2 bg-dark-900/80 text-gray-400 hover:text-red-400 rounded-lg p-1.5 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    @elseif ($existing_image)
                        <div class="relative">
                            <img src="{{ Storage::url($existing_image) }}" alt="Profile"
                                 class="w-full aspect-square object-cover rounded-lg">
                            <button type="button" wire:click="removeImage" wire:confirm="Remove profile image?"
                                    class="absolute top-2 right-2 bg-dark-900/80 text-gray-400 hover:text-red-400 rounded-lg p-1.5 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    @else
                        <div class="w-full aspect-square bg-dark-700 rounded-lg flex items-center justify-center">
                            <svg class="w-16 h-16 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    @endif

                    <div>
                        <label for="profile_image"
                               class="block w-full text-center cursor-pointer bg-dark-700 border border-dark-600 border-dashed rounded-lg px-4 py-3 text-sm text-gray-400 hover:text-white hover:border-primary transition-colors">
                            <span wire:loading.remove wire:target="profile_image">Choose Image</span>
                            <span wire:loading wire:target="profile_image">Uploading...</span>
                        </label>
                        <input type="file" id="profile_image" wire:model="profile_image" class="hidden" accept="image/jpg,image/jpeg,image/png,image/webp">
                    </div>
                    @error('profile_image')
                        <p class="text-xs text-red-400 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                    <p class="text-xs text-gray-500">JPG, PNG or WebP. Max 2MB.</p>
                </div>
            </div>
        </div>

    </div>
</div>
