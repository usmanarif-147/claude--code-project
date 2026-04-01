<div>
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('admin.email.templates.index') }}" wire:navigate class="hover:text-gray-300 transition-colors">Email Templates</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">{{ $emailTemplateId ? 'Edit' : 'Create' }} Template</span>
    </div>

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">
                {{ $emailTemplateId ? 'Edit Template' : 'Create Template' }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ $emailTemplateId ? 'Update the template details below.' : 'Fill in the details to create a new email template.' }}
            </p>
        </div>
        <a href="{{ route('admin.email.templates.index') }}" wire:navigate
           class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 text-gray-300 text-sm font-medium rounded-lg px-4 py-2.5 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back
        </a>
    </div>

    <form wire:submit="save">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            {{-- Main Content: 2/3 width --}}
            <div class="xl:col-span-2 space-y-6">
                {{-- Template Details --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl">
                    <div class="px-6 py-4 border-b border-dark-700">
                        <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Template Details</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Define the template name, subject, and body content.</p>
                    </div>
                    <div class="p-6 space-y-5">
                        {{-- Name --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Name <span class="text-red-400">*</span>
                            </label>
                            <input type="text" wire:model="name"
                                   placeholder="e.g. Interview Follow-Up"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                            @error('name')
                                <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Subject --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Subject</label>
                            <input type="text" wire:model="subject"
                                   placeholder="Email subject line..."
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                            @error('subject')
                                <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Body --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Body <span class="text-red-400">*</span>
                            </label>
                            <textarea wire:model="body" rows="10"
                                      placeholder="Write your email template... Use {name}, {company}, {position} as placeholders."
                                      class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent resize-none transition-all duration-200"></textarea>
                            @error('body')
                                <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar: 1/3 width --}}
            <div class="space-y-6">
                {{-- Settings --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl">
                    <div class="px-6 py-4 border-b border-dark-700">
                        <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Settings</h2>
                    </div>
                    <div class="p-6 space-y-5">
                        {{-- Category --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Category <span class="text-red-400">*</span>
                            </label>
                            <select wire:model="category"
                                    class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                                @foreach($categories as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('category')
                                <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Sort Order --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Sort Order</label>
                            <input type="number" wire:model="sort_order" min="0" max="999"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                            @error('sort_order')
                                <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Favorite Toggle --}}
                        <div class="flex items-center justify-between p-4 bg-dark-700 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-gray-300">Favorite</p>
                                <p class="text-xs text-gray-500 mt-0.5">Pin to top of template list</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" wire:model="is_favorite" class="sr-only peer">
                                <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl">
                    <div class="px-6 py-4 border-b border-dark-700">
                        <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Actions</h2>
                    </div>
                    <div class="p-6 space-y-3">
                        {{-- Save Button --}}
                        <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20 disabled:opacity-50 disabled:cursor-not-allowed"
                                wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="save">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <span wire:loading wire:target="save">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            </span>
                            <span wire:loading.remove wire:target="save">{{ $emailTemplateId ? 'Update Template' : 'Create Template' }}</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </button>

                        {{-- Cancel Button --}}
                        <a href="{{ route('admin.email.templates.index') }}" wire:navigate
                           class="w-full inline-flex items-center justify-center gap-2 bg-dark-700 hover:bg-dark-600 border border-dark-600 text-gray-300 hover:text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
