<div>
    @php
        $colorOptions = [
            'emerald' => ['bg' => 'bg-emerald-500', 'text' => 'text-emerald-400', 'bgLight' => 'bg-emerald-500/10', 'label' => 'Emerald'],
            'blue' => ['bg' => 'bg-blue-500', 'text' => 'text-blue-400', 'bgLight' => 'bg-blue-500/10', 'label' => 'Blue'],
            'amber' => ['bg' => 'bg-amber-500', 'text' => 'text-amber-400', 'bgLight' => 'bg-amber-500/10', 'label' => 'Amber'],
            'primary' => ['bg' => 'bg-primary', 'text' => 'text-primary-light', 'bgLight' => 'bg-primary/10', 'label' => 'Purple'],
            'gray' => ['bg' => 'bg-gray-500', 'text' => 'text-gray-400', 'bgLight' => 'bg-gray-500/10', 'label' => 'Gray'],
            'red' => ['bg' => 'bg-red-500', 'text' => 'text-red-400', 'bgLight' => 'bg-red-500/10', 'label' => 'Red'],
            'fuchsia' => ['bg' => 'bg-fuchsia-500', 'text' => 'text-fuchsia-400', 'bgLight' => 'bg-fuchsia-500/10', 'label' => 'Fuchsia'],
            'cyan' => ['bg' => 'bg-cyan-500', 'text' => 'text-cyan-400', 'bgLight' => 'bg-cyan-500/10', 'label' => 'Cyan'],
        ];

        $iconOptions = [
            'briefcase' => ['label' => 'Briefcase', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>'],
            'code' => ['label' => 'Code', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>'],
            'star' => ['label' => 'Star', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>'],
            'newspaper' => ['label' => 'Newspaper', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>'],
            'trash' => ['label' => 'Trash', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>'],
            'envelope' => ['label' => 'Envelope', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>'],
            'bell' => ['label' => 'Bell', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>'],
            'flag' => ['label' => 'Flag', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>'],
        ];

        $currentColor = $colorOptions[$color] ?? $colorOptions['gray'];
        $currentIcon = $iconOptions[$icon] ?? null;
    @endphp

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-400">Email</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('admin.email.categories.index') }}" wire:navigate class="hover:text-gray-300 transition-colors">Categories</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">{{ $emailCategoryId ? 'Edit' : 'Create' }}</span>
    </div>

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">
                {{ $emailCategoryId ? 'Edit Category' : 'Create Category' }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ $emailCategoryId ? 'Update category details.' : 'Add a new email category.' }}
            </p>
        </div>
        <a href="{{ route('admin.email.categories.index') }}" wire:navigate
           class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 text-gray-300 text-sm font-medium rounded-lg px-4 py-2.5 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back
        </a>
    </div>

    <form wire:submit="save">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            {{-- Main Content: 2/3 --}}
            <div class="xl:col-span-2 space-y-6">
                <div class="bg-dark-800 border border-dark-700 rounded-xl">
                    <div class="px-6 py-4 border-b border-dark-700">
                        <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Category Details</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Define the category name, color, and icon.</p>
                    </div>
                    <div class="p-6 space-y-5">
                        {{-- Name --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Name <span class="text-red-400">*</span>
                            </label>
                            <input type="text" wire:model="name"
                                   placeholder="e.g. Job Response"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                            @error('name')
                                <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Color --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Color <span class="text-red-400">*</span>
                            </label>
                            <div class="grid grid-cols-4 sm:grid-cols-8 gap-2">
                                @foreach($colorOptions as $colorKey => $colorVal)
                                    <button type="button" wire:click="$set('color', '{{ $colorKey }}')"
                                            class="flex flex-col items-center gap-1.5 p-2.5 rounded-lg border transition-all duration-200 {{ $color === $colorKey ? 'border-primary bg-dark-700' : 'border-dark-600 hover:border-dark-500 bg-dark-700/50' }}">
                                        <span class="w-6 h-6 rounded-full {{ $colorVal['bg'] }}"></span>
                                        <span class="text-xs {{ $color === $colorKey ? 'text-white' : 'text-gray-500' }}">{{ $colorVal['label'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                            @error('color')
                                <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        {{-- Icon --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Icon <span class="text-red-400">*</span>
                            </label>
                            <div class="grid grid-cols-4 sm:grid-cols-8 gap-2">
                                @foreach($iconOptions as $iconKey => $iconVal)
                                    <button type="button" wire:click="$set('icon', '{{ $iconKey }}')"
                                            class="flex flex-col items-center gap-1.5 p-2.5 rounded-lg border transition-all duration-200 {{ $icon === $iconKey ? 'border-primary bg-dark-700' : 'border-dark-600 hover:border-dark-500 bg-dark-700/50' }}">
                                        <svg class="w-5 h-5 {{ $icon === $iconKey ? 'text-primary-light' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $iconVal['svg'] !!}</svg>
                                        <span class="text-xs {{ $icon === $iconKey ? 'text-white' : 'text-gray-500' }}">{{ $iconVal['label'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                            @error('icon')
                                <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar: 1/3 --}}
            <div class="space-y-6">
                {{-- Preview Card --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl">
                    <div class="px-6 py-4 border-b border-dark-700">
                        <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Preview</h2>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center gap-3 p-3 bg-dark-700 rounded-lg">
                            @if($color && $icon && $currentIcon)
                                <div class="w-8 h-8 rounded-lg {{ $currentColor['bgLight'] }} flex items-center justify-center">
                                    <svg class="w-4 h-4 {{ $currentColor['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $currentIcon['svg'] !!}</svg>
                                </div>
                                <span class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-medium {{ $currentColor['bgLight'] }} {{ $currentColor['text'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $currentColor['bg'] }}"></span>
                                    {{ $name ?: 'Category Name' }}
                                </span>
                            @else
                                <span class="text-sm text-gray-500">Select a color and icon to preview</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Settings Card --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl">
                    <div class="px-6 py-4 border-b border-dark-700">
                        <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Settings</h2>
                    </div>
                    <div class="p-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Sort Order</label>
                            <input type="number" wire:model="sort_order" min="0"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                            @error('sort_order')
                                <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Actions Card --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl">
                    <div class="px-6 py-4 border-b border-dark-700">
                        <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Actions</h2>
                    </div>
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
                            <span wire:loading.remove wire:target="save">{{ $emailCategoryId ? 'Update Category' : 'Create Category' }}</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </button>
                        <a href="{{ route('admin.email.categories.index') }}" wire:navigate
                           class="w-full inline-flex items-center justify-center gap-2 bg-dark-700 hover:bg-dark-600 border border-dark-600 text-gray-300 hover:text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
