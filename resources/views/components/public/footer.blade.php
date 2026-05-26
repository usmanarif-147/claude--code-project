@php
    $siteName = \App\Models\User::query()->value('name') ?? 'Portfolio';
@endphp

<footer class="border-t border-white/[0.04] py-8">
    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <div class="flex flex-col items-center justify-between gap-4 md:flex-row">
            <p class="text-sm text-gray-600">
                &copy; {{ date('Y') }} {{ $siteName }}. All rights reserved.
            </p>
            <a href="{{ url('/') }}"
               class="inline-flex items-center gap-2 text-sm text-gray-500 transition-colors duration-200 hover:text-accent">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to portfolio
            </a>
        </div>
    </div>
</footer>
