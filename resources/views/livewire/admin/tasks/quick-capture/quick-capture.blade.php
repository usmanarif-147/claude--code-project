<div
    x-data="{
        open: @entangle('showModal'),
        success: false,
        toggle() {
            this.open = !this.open;
        },
        close() {
            this.open = false;
        },
        showSuccess() {
            this.success = true;
            setTimeout(() => { this.success = false; }, 1500);
        }
    }"
    x-on:task-created.window="showSuccess(); $nextTick(() => $refs.titleInput.focus())"
    x-on:keydown.window="if ((e = $event) && e.ctrlKey && e.shiftKey && e.key === 'K') { e.preventDefault(); toggle(); }"
    class="fixed z-50"
>
    {{-- Floating Action Button --}}
    <button
        x-on:click="toggle()"
        class="fixed bottom-20 right-4 lg:bottom-6 lg:right-6 w-14 h-14 rounded-full shadow-lg flex items-center justify-center transition-all duration-300 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:ring-offset-dark-900"
        :class="success ? 'bg-emerald-500 shadow-emerald-500/30' : 'bg-primary hover:bg-primary-hover shadow-primary/30'"
    >
        {{-- Plus icon (default) --}}
        <svg x-show="!success" class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        {{-- Checkmark icon (success) --}}
        <svg x-show="success" x-cloak class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
    </button>

    {{-- Popup Panel --}}
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-2"
        x-on:click.outside="close()"
        x-on:keydown.escape.window="close()"
        class="fixed bottom-36 right-4 lg:bottom-24 lg:right-6 w-72 sm:w-80 bg-dark-800 border border-dark-700 rounded-xl shadow-2xl shadow-black/50"
    >
        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 border-b border-dark-700">
            <h3 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Quick Add Task</h3>
            <button x-on:click="close()" class="text-gray-500 hover:text-gray-300 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Form --}}
        <div class="p-4 space-y-3">
            {{-- Title Input --}}
            <div>
                <input
                    type="text"
                    wire:model="title"
                    wire:keydown.enter="save"
                    x-ref="titleInput"
                    x-effect="if (open) $nextTick(() => $refs.titleInput.focus())"
                    placeholder="What needs to be done?"
                    class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200"
                >
                @error('title')
                    <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Optional Fields Row --}}
            <div class="grid grid-cols-2 gap-2">
                {{-- Category Select --}}
                @if($categories->isNotEmpty())
                    <div>
                        <select
                            wire:model="taskCategoryId"
                            class="w-full bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200"
                        >
                            <option value="">Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                {{-- Due Date --}}
                <div class="{{ $categories->isEmpty() ? 'col-span-2' : '' }}">
                    <input
                        type="date"
                        wire:model="dueDate"
                        class="w-full bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200"
                    >
                    @error('dueDate')
                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Footer / Actions --}}
            <div class="flex items-center justify-between pt-1">
                <span class="text-xs text-gray-500">Press Enter to save</span>
                <button
                    wire:click="save"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-4 py-2 transition-colors disabled:opacity-50"
                >
                    <svg wire:loading wire:target="save" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="save">Add Task</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>
            </div>
        </div>
    </div>
</div>
