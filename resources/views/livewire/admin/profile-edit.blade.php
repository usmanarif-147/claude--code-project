<div>
    <div class="mb-8">
        <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Edit Profile</h1>
        <p class="text-gray-500 mt-1">Update your portfolio profile information.</p>
    </div>

    <form wire:submit="save">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Info --}}
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 space-y-5">
                    <h2 class="text-lg font-mono font-semibold text-white uppercase tracking-wider">Basic Information</h2>

                    <div>
                        <label for="tagline" class="block text-sm font-medium text-gray-300 mb-1.5">Tagline</label>
                        <input type="text" id="tagline" wire:model="tagline"
                               class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="e.g. Full-Stack Developer">
                        @error('tagline') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="bio" class="block text-sm font-medium text-gray-300 mb-1.5">Bio</label>
                        <textarea id="bio" wire:model="bio" rows="5"
                                  class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent"
                                  placeholder="Tell visitors about yourself..."></textarea>
                        @error('bio') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="availability_status" class="block text-sm font-medium text-gray-300 mb-1.5">Availability Status</label>
                        <input type="text" id="availability_status" wire:model="availability_status"
                               class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="e.g. Available for freelance">
                        @error('availability_status') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 space-y-5">
                    <h2 class="text-lg font-mono font-semibold text-white uppercase tracking-wider">Contact & Links</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="secondary_email" class="block text-sm font-medium text-gray-300 mb-1.5">Secondary Email</label>
                            <input type="email" id="secondary_email" wire:model="secondary_email"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="contact@example.com">
                            @error('secondary_email') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-300 mb-1.5">Phone</label>
                            <input type="text" id="phone" wire:model="phone"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="+1 234 567 890">
                            @error('phone') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-300 mb-1.5">Location</label>
                            <input type="text" id="location" wire:model="location"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="City, Country">
                            @error('location') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="linkedin_url" class="block text-sm font-medium text-gray-300 mb-1.5">LinkedIn URL</label>
                            <input type="url" id="linkedin_url" wire:model="linkedin_url"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="https://linkedin.com/in/username">
                            @error('linkedin_url') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="github_url" class="block text-sm font-medium text-gray-300 mb-1.5">GitHub URL</label>
                            <input type="url" id="github_url" wire:model="github_url"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="https://github.com/username">
                            @error('github_url') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar: Image Upload --}}
            <div class="space-y-6">
                <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
                    <h2 class="text-lg font-mono font-semibold text-white uppercase tracking-wider mb-4">Profile Image</h2>

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
                        @error('profile_image') <p class="text-sm text-red-400">{{ $message }}</p> @enderror
                        <p class="text-xs text-gray-500">JPG, PNG or WebP. Max 2MB.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Save Button --}}
        <div class="mt-6 flex justify-end">
            <button type="submit"
                    class="bg-primary hover:bg-primary-hover text-white font-medium rounded-lg px-6 py-2.5 transition-colors flex items-center gap-2">
                <span wire:loading.remove wire:target="save">Save Profile</span>
                <span wire:loading wire:target="save" class="flex items-center gap-2">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    Saving...
                </span>
            </button>
        </div>
    </form>
</div>
