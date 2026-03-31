<div>
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-400">Settings</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">Job Search Filters</span>
    </div>

    {{-- Page Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Job Search Filters</h1>
        <p class="text-sm text-gray-500 mt-1">Configure your default job search preferences.</p>
    </div>

    <form wire:submit="save">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            {{-- Left Column --}}
            <div class="xl:col-span-2 space-y-6">

                {{-- Card 1: Preferred Job Titles --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
                    <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-1">Preferred Job Titles</h2>
                    <p class="text-xs text-gray-500 mb-4">Add titles you're looking for (e.g., Laravel Developer)</p>

                    <div class="flex items-center gap-2 mb-4">
                        <input type="text" wire:model="newTitle" wire:keydown.enter.prevent="addTitle"
                               placeholder="Type a job title..."
                               class="flex-1 bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                        <button type="button" wire:click="addTitle"
                                class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-all duration-200">
                            Add
                        </button>
                    </div>

                    @error('preferred_titles') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror

                    <div class="flex flex-wrap gap-2">
                        @foreach($preferred_titles as $index => $title)
                            <span class="inline-flex items-center gap-1.5 bg-primary/10 text-primary-light px-3 py-1 rounded-full text-sm">
                                {{ $title }}
                                <button type="button" wire:click="removeTitle({{ $index }})"
                                        class="text-primary-light/60 hover:text-white transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </span>
                        @endforeach
                    </div>
                </div>

                {{-- Card 2: Tech Stack --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
                    <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-1">Preferred Tech Stack</h2>
                    <p class="text-xs text-gray-500 mb-4">Add technologies you specialize in</p>

                    <div class="flex items-center gap-2 mb-4">
                        <input type="text" wire:model="newTech" wire:keydown.enter.prevent="addTech"
                               placeholder="Type a technology..."
                               class="flex-1 bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                        <button type="button" wire:click="addTech"
                                class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-all duration-200">
                            Add
                        </button>
                    </div>

                    @error('preferred_tech') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror

                    <div class="flex flex-wrap gap-2">
                        @foreach($preferred_tech as $index => $tech)
                            <span class="inline-flex items-center gap-1.5 bg-primary/10 text-primary-light px-3 py-1 rounded-full text-sm">
                                {{ $tech }}
                                <button type="button" wire:click="removeTech({{ $index }})"
                                        class="text-primary-light/60 hover:text-white transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </span>
                        @endforeach
                    </div>
                </div>

                {{-- Card 3: Location Preferences --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl p-6" x-data>
                    <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-4">Location Preference</h2>

                    <div class="space-y-3 mb-4">
                        <label class="flex items-center gap-3 p-3 bg-dark-700 rounded-lg cursor-pointer hover:bg-dark-600 transition-colors">
                            <input type="radio" wire:model.live="location_type" value="remote"
                                   class="w-4 h-4 text-primary bg-dark-600 border-dark-500 focus:ring-primary focus:ring-2">
                            <span class="text-sm text-gray-300">Remote (worldwide)</span>
                        </label>

                        <label class="flex items-center gap-3 p-3 bg-dark-700 rounded-lg cursor-pointer hover:bg-dark-600 transition-colors">
                            <input type="radio" wire:model.live="location_type" value="pakistan"
                                   class="w-4 h-4 text-primary bg-dark-600 border-dark-500 focus:ring-primary focus:ring-2">
                            <span class="text-sm text-gray-300">Pakistan</span>
                        </label>

                        <label class="flex items-center gap-3 p-3 bg-dark-700 rounded-lg cursor-pointer hover:bg-dark-600 transition-colors">
                            <input type="radio" wire:model.live="location_type" value="country"
                                   class="w-4 h-4 text-primary bg-dark-600 border-dark-500 focus:ring-primary focus:ring-2">
                            <span class="text-sm text-gray-300">Specific Country</span>
                        </label>

                        <label class="flex items-center gap-3 p-3 bg-dark-700 rounded-lg cursor-pointer hover:bg-dark-600 transition-colors">
                            <input type="radio" wire:model.live="location_type" value="hybrid"
                                   class="w-4 h-4 text-primary bg-dark-600 border-dark-500 focus:ring-primary focus:ring-2">
                            <span class="text-sm text-gray-300">Hybrid</span>
                        </label>

                        <label class="flex items-center gap-3 p-3 bg-dark-700 rounded-lg cursor-pointer hover:bg-dark-600 transition-colors">
                            <input type="radio" wire:model.live="location_type" value="onsite"
                                   class="w-4 h-4 text-primary bg-dark-600 border-dark-500 focus:ring-primary focus:ring-2">
                            <span class="text-sm text-gray-300">Onsite</span>
                        </label>
                    </div>

                    @error('location_type') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror

                    @if($location_type === 'pakistan')
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-300 mb-2">City</label>
                            <input type="text" wire:model="location_value"
                                   placeholder="e.g., Lahore, Karachi, Islamabad"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                            @error('location_value') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    @if($location_type === 'country')
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-300 mb-2">Country</label>
                            <input type="text" wire:model="location_value"
                                   placeholder="e.g., United States, Germany"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                            @error('location_value') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </div>

                {{-- Card 4: Compensation --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
                    <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-4">Compensation</h2>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Minimum Salary</label>
                            <input type="number" wire:model="min_salary" min="0"
                                   placeholder="e.g., 5000"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                            @error('min_salary') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Currency</label>
                            <select wire:model="salary_currency"
                                    class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                                <option value="USD">USD</option>
                                <option value="PKR">PKR</option>
                            </select>
                            @error('salary_currency') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Card 5: Experience Level --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
                    <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-4">Experience Level</h2>

                    <div class="space-y-3">
                        <label class="flex items-center gap-3 p-3 bg-dark-700 rounded-lg cursor-pointer hover:bg-dark-600 transition-colors">
                            <input type="radio" wire:model="experience_level" value="mid"
                                   class="w-4 h-4 text-primary bg-dark-600 border-dark-500 focus:ring-primary focus:ring-2">
                            <span class="text-sm text-gray-300">Mid-Level</span>
                        </label>

                        <label class="flex items-center gap-3 p-3 bg-dark-700 rounded-lg cursor-pointer hover:bg-dark-600 transition-colors">
                            <input type="radio" wire:model="experience_level" value="senior"
                                   class="w-4 h-4 text-primary bg-dark-600 border-dark-500 focus:ring-primary focus:ring-2">
                            <span class="text-sm text-gray-300">Senior</span>
                        </label>
                    </div>

                    @error('experience_level') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Right Column --}}
            <div class="space-y-6">
                {{-- Card 6: Job Platforms --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
                    <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-1">Job Platforms</h2>
                    <p class="text-xs text-gray-500 mb-5">Enable the platforms to search for jobs.</p>

                    {{-- International --}}
                    <p class="text-xs font-mono text-gray-500 uppercase tracking-widest mb-3">International</p>
                    <div class="space-y-3 mb-6">
                        <div class="flex items-center justify-between p-4 bg-dark-700 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-gray-300">JSearch</p>
                                <p class="text-xs text-gray-500 mt-0.5">Indeed / Glassdoor / LinkedIn</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model.live="platform_jsearch" class="sr-only peer">
                                <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-dark-700 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-gray-300">RemoteOK</p>
                                <p class="text-xs text-gray-500 mt-0.5">Remote jobs worldwide</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model.live="platform_remoteok" class="sr-only peer">
                                <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-dark-700 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-gray-300">Remotive</p>
                                <p class="text-xs text-gray-500 mt-0.5">Remote tech jobs</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model.live="platform_remotive" class="sr-only peer">
                                <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-dark-700 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-gray-300">Adzuna</p>
                                <p class="text-xs text-gray-500 mt-0.5">Global job search engine</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model.live="platform_adzuna" class="sr-only peer">
                                <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                            </label>
                        </div>
                    </div>

                    {{-- Pakistani --}}
                    <p class="text-xs font-mono text-gray-500 uppercase tracking-widest mb-3">Pakistani</p>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-4 bg-dark-700 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-gray-300">Rozee.pk</p>
                                <p class="text-xs text-gray-500 mt-0.5">Pakistan's leading job portal</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model.live="platform_rozee" class="sr-only peer">
                                <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-dark-700 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-gray-300">Mustakbil.com</p>
                                <p class="text-xs text-gray-500 mt-0.5">Pakistani career platform</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model.live="platform_mustakbil" class="sr-only peer">
                                <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Save Button --}}
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
                <span wire:loading.remove wire:target="save">Save Filters</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
        </div>
    </form>
</div>
