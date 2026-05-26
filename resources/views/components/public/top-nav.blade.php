@props(['active' => null])

@php
    $siteName = \App\Models\User::query()->value('name') ?? 'Portfolio';
@endphp

<nav class="fixed top-0 left-0 right-0 z-50 border-b border-white/[0.04] bg-dark-950/80 backdrop-blur-md">
    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <div class="flex h-16 items-center justify-between">
            <a href="{{ url('/') }}" class="text-xl font-extrabold tracking-tight text-white">
                {{ $siteName }}
            </a>

            <div class="flex items-center gap-6">
                <a href="{{ url('/') }}"
                   class="text-sm text-gray-500 transition-colors duration-200 hover:text-white">
                    Portfolio
                </a>
                <a href="{{ route('resume.download') }}"
                   class="rounded-lg bg-accent px-5 py-2 text-xs font-extrabold uppercase tracking-widest text-black transition-colors duration-200 hover:bg-accent-light">
                    Resume
                </a>
            </div>
        </div>
    </div>
</nav>
