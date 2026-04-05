<div>
    {{-- 1. BREADCRUMB --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-400">Personal</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('admin.personal.expense-tracker.index') }}" wire:navigate class="hover:text-gray-300 transition-colors">Expense Tracker</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">Categories</span>
    </div>

    {{-- 2. PAGE HEADER --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Expense Categories</h1>
            <p class="text-sm text-gray-500 mt-1">Manage your expense categories.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.personal.expense-tracker.index') }}" wire:navigate
               class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 text-gray-300 text-sm font-medium rounded-lg px-4 py-2.5 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back to Expenses
            </a>
            <button wire:click="$toggle('showForm')"
                    class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Category
            </button>
        </div>
    </div>

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

    {{-- 3. ADD CATEGORY INLINE FORM --}}
    @if($showForm)
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-4 mb-6"
             x-data x-init="
                $el.style.opacity = '0';
                $el.style.transform = 'translateY(12px)';
                setTimeout(() => {
                    $el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                    $el.style.opacity = '1';
                    $el.style.transform = 'translateY(0)';
                }, 50)">
            <form wire:submit="addCategory" class="flex flex-col sm:flex-row items-start sm:items-end gap-3">
                <div class="flex-1 w-full">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Name <span class="text-red-400">*</span></label>
                    <input type="text" wire:model="name" placeholder="Category name"
                           class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                    @error('name')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Color</label>
                    <input type="color" wire:model="color"
                           class="w-12 h-[42px] bg-dark-700 border border-dark-600 rounded-lg cursor-pointer">
                </div>
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add
                </button>
                <button type="button" wire:click="$set('showForm', false)"
                        class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 border border-dark-600 text-gray-300 hover:text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200">
                    Cancel
                </button>
            </form>
        </div>
    @endif

    {{-- 4. CATEGORIES LIST --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl overflow-hidden">
        @forelse($categories as $category)
            <div class="px-6 py-4 border-b border-dark-700/50 hover:bg-dark-700/30 transition-colors">
                @if($editingCategoryId === $category->id)
                    {{-- Inline Edit Mode --}}
                    <form wire:submit="updateCategory" class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                        <div class="flex-1 w-full">
                            <input type="text" wire:model="name" placeholder="Category name"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                            @error('name')
                                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <input type="color" wire:model="color"
                                   class="w-10 h-10 bg-dark-700 border border-dark-600 rounded-lg cursor-pointer">
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="submit"
                                    class="bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-colors">
                                Save
                            </button>
                            <button type="button" wire:click="cancelEdit"
                                    class="bg-dark-700 hover:bg-dark-600 text-gray-300 text-sm font-medium rounded-lg px-4 py-2.5 transition-colors">
                                Cancel
                            </button>
                        </div>
                    </form>
                @else
                    {{-- Display Mode --}}
                    <div class="flex items-center gap-4">
                        {{-- Color Dot --}}
                        <span class="w-4 h-4 rounded-full shrink-0 border border-dark-600" style="background-color: {{ $category->color }};"></span>

                        {{-- Name --}}
                        <span class="flex-1 text-sm font-medium text-white">{{ $category->name }}</span>

                        {{-- Expense Count Badge --}}
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-dark-700 text-gray-400">
                            {{ $category->expenses_count }} {{ Str::plural('expense', $category->expenses_count) }}
                        </span>

                        {{-- Default Badge --}}
                        @if($category->is_default)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary-light">
                                <span class="w-1.5 h-1.5 rounded-full bg-primary-light"></span>
                                Default
                            </span>
                        @endif

                        {{-- Action Buttons --}}
                        <div class="flex items-center gap-1">
                            <button wire:click="startEdit({{ $category->id }})"
                                    class="p-2 text-gray-400 hover:text-primary-light hover:bg-primary/10 rounded-lg transition-all duration-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            @if(!$category->is_default)
                                <x-admin.confirm-button
                                    title="Delete Category?"
                                    text="This category will be permanently removed. All expenses in this category will also be deleted."
                                    action="$wire.deleteCategory({{ $category->id }})"
                                    confirm-text="Yes, delete it"
                                    class="p-2 text-gray-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-all duration-200"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </x-admin.confirm-button>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="px-6 py-16 text-center">
                <div class="flex flex-col items-center gap-3">
                    <div class="w-14 h-14 rounded-xl bg-dark-700 flex items-center justify-center">
                        <svg class="w-7 h-7 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-300">No categories found</p>
                        <p class="text-xs text-gray-500 mt-1">Default categories will be created automatically.</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
</div>
