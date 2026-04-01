<div>
    {{-- Flash Messages --}}
    @if(session('success') || session('error'))
        <div x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 4000)"
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

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-400">Job Search</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('admin.job-search.saved-searches.index') }}" wire:navigate class="hover:text-gray-300 transition-colors">Saved Searches</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">{{ $savedSearchId ? 'Edit' : 'Create' }}</span>
    </div>

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">
                {{ $savedSearchId ? 'Edit Saved Search' : 'Create Saved Search' }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ $savedSearchId ? 'Update the details of your saved search.' : 'Fill in the details to create a new saved search.' }}
            </p>
        </div>
        <a href="{{ route('admin.job-search.saved-searches.index') }}" wire:navigate
           class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 text-gray-300 text-sm font-medium rounded-lg px-4 py-2.5 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back
        </a>
    </div>

    {{-- Form --}}
    <form wire:submit="save">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            {{-- Main Content --}}
            <div class="xl:col-span-2 space-y-6">

                {{-- Basic Information --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl">
                    <div class="px-6 py-4 border-b border-dark-700">
                        <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Basic Information</h2>
                    </div>
                    <div class="p-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Name <span class="text-red-400">*</span>
                            </label>
                            <input type="text" wire:model="name"
                                   placeholder="e.g., Remote Laravel International"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                            @error('name')
                                <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Job Title Keywords --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl">
                    <div class="px-6 py-4 border-b border-dark-700">
                        <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Job Title Keywords</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Add job titles you're looking for (e.g., Laravel Developer, PHP Engineer)</p>
                    </div>
                    <div class="p-6">
                        <div class="flex gap-2 mb-3">
                            <input type="text" wire:model="titleInput"
                                   wire:keydown.enter.prevent="addTitle"
                                   placeholder="Type a job title and press Enter..."
                                   class="flex-1 bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                            <button type="button" wire:click="addTitle"
                                    class="inline-flex items-center gap-1.5 bg-dark-700 hover:bg-dark-600 border border-dark-600 text-gray-300 hover:text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-all duration-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Add
                            </button>
                        </div>
                        @if(count($preferred_titles) > 0)
                            <div class="flex flex-wrap gap-2">
                                @foreach($preferred_titles as $index => $title)
                                    <span class="inline-flex items-center gap-1.5 bg-primary/10 text-primary-light rounded-full px-3 py-1 text-sm">
                                        {{ $title }}
                                        <button type="button" wire:click="removeTitle({{ $index }})" class="text-primary-light/60 hover:text-primary-light">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </span>
                                @endforeach
                            </div>
                        @endif
                        @error('preferred_titles')
                            <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                {{-- Tech Stack --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl">
                    <div class="px-6 py-4 border-b border-dark-700">
                        <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Tech Stack</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Add technologies to match (e.g., Laravel, Vue.js, React)</p>
                    </div>
                    <div class="p-6">
                        <div class="flex gap-2 mb-3">
                            <input type="text" wire:model="techInput"
                                   wire:keydown.enter.prevent="addTech"
                                   placeholder="Type a technology and press Enter..."
                                   class="flex-1 bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                            <button type="button" wire:click="addTech"
                                    class="inline-flex items-center gap-1.5 bg-dark-700 hover:bg-dark-600 border border-dark-600 text-gray-300 hover:text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-all duration-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Add
                            </button>
                        </div>
                        @if(count($preferred_tech) > 0)
                            <div class="flex flex-wrap gap-2">
                                @foreach($preferred_tech as $index => $tech)
                                    <span class="inline-flex items-center gap-1.5 bg-primary/10 text-primary-light rounded-full px-3 py-1 text-sm">
                                        {{ $tech }}
                                        <button type="button" wire:click="removeTech({{ $index }})" class="text-primary-light/60 hover:text-primary-light">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </span>
                                @endforeach
                            </div>
                        @endif
                        @error('preferred_tech')
                            <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                {{-- Location & Salary --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl">
                    <div class="px-6 py-4 border-b border-dark-700">
                        <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Location & Salary</h2>
                    </div>
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                        {{-- Location Type --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Location Type</label>
                            <select wire:model="location_type"
                                    class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                                <option value="">Any</option>
                                <option value="remote">Remote</option>
                                <option value="onsite">On-site</option>
                                <option value="hybrid">Hybrid</option>
                            </select>
                            @error('location_type')
                                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Location Value --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Location</label>
                            <input type="text" wire:model="location_value"
                                   placeholder="City or country..."
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                            @error('location_value')
                                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Min Salary --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Minimum Salary</label>
                            <input type="number" wire:model="min_salary" min="0" max="999999"
                                   placeholder="e.g., 50000"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                            @error('min_salary')
                                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Salary Currency --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Currency</label>
                            <select wire:model="salary_currency"
                                    class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                                <option value="USD">USD</option>
                                <option value="PKR">PKR</option>
                                <option value="EUR">EUR</option>
                                <option value="GBP">GBP</option>
                            </select>
                            @error('salary_currency')
                                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Experience Level --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl">
                    <div class="px-6 py-4 border-b border-dark-700">
                        <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Experience Level</h2>
                    </div>
                    <div class="p-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Level</label>
                            <select wire:model="experience_level"
                                    class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                                <option value="">Any</option>
                                <option value="junior">Junior</option>
                                <option value="mid">Mid</option>
                                <option value="senior">Senior</option>
                                <option value="lead">Lead</option>
                            </select>
                            @error('experience_level')
                                <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Platforms --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl">
                    <div class="px-6 py-4 border-b border-dark-700">
                        <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Platforms</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Select which job platforms to include in this search.</p>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($platforms as $key => $label)
                                <label class="flex items-center gap-3 p-3 bg-dark-700 rounded-lg cursor-pointer hover:bg-dark-600 transition-colors">
                                    <input type="checkbox" wire:model="enabled_platforms" value="{{ $key }}"
                                           class="w-4 h-4 rounded border-dark-600 bg-dark-700 text-primary focus:ring-primary focus:ring-offset-0">
                                    <span class="text-sm text-gray-300">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('enabled_platforms')
                            <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Status Card --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl">
                    <div class="px-6 py-4 border-b border-dark-700">
                        <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Status</h2>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center justify-between p-4 bg-dark-700 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-gray-300">{{ $is_active ? 'Active' : 'Inactive' }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">Active searches run during daily job fetching.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model.live="is_active" class="sr-only peer">
                                <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Submit Card --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl">
                    <div class="p-6 space-y-3">
                        <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20 disabled:opacity-50 disabled:cursor-not-allowed"
                                wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="save">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <span wire:loading wire:target="save">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            </span>
                            <span wire:loading.remove wire:target="save">{{ $savedSearchId ? 'Update Saved Search' : 'Create Saved Search' }}</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </button>
                        <a href="{{ route('admin.job-search.saved-searches.index') }}" wire:navigate
                           class="w-full inline-flex items-center justify-center gap-2 bg-dark-700 hover:bg-dark-600 border border-dark-600 text-gray-300 hover:text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
