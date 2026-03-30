<div>
    <div class="mb-8">
        <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">{{ $testimonial ? 'Edit Testimonial' : 'Create Testimonial' }}</h1>
        <p class="text-gray-500 mt-1">{{ $testimonial ? 'Update testimonial details.' : 'Add a new client testimonial.' }}</p>
    </div>

    <form wire:submit="save" class="max-w-3xl space-y-6">
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 space-y-5">

            <div>
                <label for="client_name" class="block text-sm font-medium text-gray-400 mb-1.5">Client Name <span class="text-red-400">*</span></label>
                <input type="text" id="client_name" wire:model="client_name"
                       class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent"
                       placeholder="e.g. John Doe">
                @error('client_name') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="company" class="block text-sm font-medium text-gray-400 mb-1.5">Company</label>
                <input type="text" id="company" wire:model="company"
                       class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent"
                       placeholder="e.g. Acme Inc.">
                @error('company') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            {{-- Client Photo --}}
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-1.5">Client Photo</label>

                @if ($existingPhoto)
                    <div class="mb-3 relative inline-block">
                        <img src="{{ Storage::url($existingPhoto) }}" alt="Client photo" class="w-16 h-16 rounded-full object-cover">
                        <button type="button" wire:click="removePhoto"
                                class="absolute -top-2 -right-2 bg-dark-900 border border-dark-600 rounded-full p-1 text-gray-400 hover:text-red-400 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                @endif

                @if ($clientPhoto)
                    <div class="mb-3 relative inline-block">
                        <img src="{{ $clientPhoto->temporaryUrl() }}" alt="Photo preview" class="w-16 h-16 rounded-full object-cover">
                        <button type="button" wire:click="$set('clientPhoto', null)"
                                class="absolute -top-2 -right-2 bg-dark-900 border border-dark-600 rounded-full p-1 text-gray-400 hover:text-red-400 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                @endif

                @if (!$clientPhoto && !$existingPhoto)
                    <input type="file" wire:model="clientPhoto" accept="image/*"
                           class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:bg-primary/10 file:text-primary-light hover:file:bg-primary/20">
                @endif

                @error('clientPhoto') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="review" class="block text-sm font-medium text-gray-400 mb-1.5">Review <span class="text-red-400">*</span></label>
                <textarea id="review" wire:model="review" rows="4"
                          class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent resize-none"
                          placeholder="What the client said about your work..."></textarea>
                @error('review') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            {{-- Star Rating --}}
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Rating <span class="text-red-400">*</span></label>
                <div class="flex gap-1" x-data="{ hovered: 0 }">
                    @for($i = 1; $i <= 5; $i++)
                        <button type="button"
                                wire:click="$set('rating', {{ $i }})"
                                @mouseenter="hovered = {{ $i }}"
                                @mouseleave="hovered = 0"
                                class="focus:outline-none">
                            <svg class="w-8 h-8 transition-colors {{ $i <= $rating ? 'text-amber-400' : 'text-gray-600' }}"
                                 :class="hovered >= {{ $i }} ? 'text-amber-300' : ''"
                                 fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </button>
                    @endfor
                </div>
                @error('rating') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="project_url" class="block text-sm font-medium text-gray-400 mb-1.5">Project URL</label>
                <input type="url" id="project_url" wire:model="project_url"
                       class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent"
                       placeholder="https://example.com">
                @error('project_url') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="flex items-center pt-2">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="is_visible" class="sr-only peer">
                        <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        <span class="ml-3 text-sm font-medium text-gray-400">Visible</span>
                    </label>
                </div>

                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-400 mb-1.5">Sort Order</label>
                    <input type="number" id="sort_order" wire:model="sort_order" min="0"
                           class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent">
                    @error('sort_order') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="received_at" class="block text-sm font-medium text-gray-400 mb-1.5">Received At</label>
                    <input type="date" id="received_at" wire:model="received_at"
                           class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                    @error('received_at') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center gap-3">
            <button type="submit"
                    class="bg-primary hover:bg-primary-hover text-white font-medium rounded-lg px-6 py-2.5 transition-colors flex items-center gap-2">
                <span wire:loading.remove wire:target="save">{{ $testimonial ? 'Update Testimonial' : 'Save Testimonial' }}</span>
                <span wire:loading wire:target="save" class="flex items-center gap-2">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    Saving...
                </span>
            </button>
            <a href="{{ route('admin.testimonials.index') }}" wire:navigate
               class="text-gray-400 hover:text-white font-medium rounded-lg px-6 py-2.5 transition-colors">
                Cancel
            </a>
        </div>
    </form>
</div>
