@props(['title', 'section', 'hasData' => false])

<div class="flex items-center justify-between border-b-2 border-blue-600 mb-3 pb-1">
    <h3 class="text-base font-extrabold text-blue-700 uppercase tracking-wide">{{ $title }}</h3>
    <button type="button" wire:click="openSection('{{ $section }}')"
        class="flex items-center justify-center w-7 h-7 rounded-full cursor-pointer {{ $hasData ? 'bg-slate-200 hover:bg-slate-300 text-slate-700' : 'bg-blue-600 hover:bg-blue-700 text-white' }} transition-colors"
        title="{{ $hasData ? 'Edit ' . $title : 'Add ' . $title }}">
        @if ($hasData)
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
        @else
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
        @endif
    </button>
</div>
