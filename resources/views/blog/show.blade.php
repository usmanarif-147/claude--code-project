<x-layouts.app>
    <x-public.top-nav active="blog" />

    @php
        $src = \Illuminate\Support\Str::startsWith($post->cover_image, 'http')
            ? $post->cover_image
            : asset('storage/'.$post->cover_image);
    @endphp

    <article class="min-h-screen px-4 pb-24 pt-28 sm:px-6">
        <div class="mx-auto max-w-3xl">

            <a href="{{ route('blogs.index') }}"
               class="mb-8 inline-flex items-center gap-2 text-sm text-gray-500 transition-colors hover:text-accent">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Blog
            </a>

            {{-- Cover --}}
            <div class="mb-8 overflow-hidden rounded-2xl border border-white/[0.04]">
                <img src="{{ $src }}" alt="{{ $post->title }}" class="h-64 w-full object-cover md:h-80">
            </div>

            {{-- Tags --}}
            @if(!empty($post->tags))
                <div class="mb-4 flex flex-wrap gap-2">
                    @foreach($post->tags as $tag)
                        <span class="rounded-full border border-accent/10 bg-accent/10 px-3 py-1 text-xs text-accent-light">{{ $tag }}</span>
                    @endforeach
                </div>
            @endif

            <h1 class="mb-4 text-3xl font-extrabold text-white md:text-4xl">{{ $post->title }}</h1>

            <div class="mb-8 flex flex-wrap items-center gap-3 border-b border-white/[0.04] pb-8 text-sm text-gray-500">
                <span>{{ $post->published_at->format('M d, Y') }}</span>
                @if($post->reading_time_minutes)
                    <span>&middot;</span>
                    <span>{{ $post->reading_time_minutes }} min read</span>
                @endif
                @if($post->view_count)
                    <span>&middot;</span>
                    <span>{{ number_format($post->view_count) }} views</span>
                @endif
            </div>

            {{-- Body (HTML content) --}}
            <div class="article-prose">
                {!! $post->content !!}
            </div>

            <div class="mt-12 border-t border-white/[0.04] pt-8">
                <a href="{{ route('blogs.index') }}"
                   class="inline-flex items-center gap-2 text-sm font-medium text-accent transition-colors hover:text-accent-light">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to all articles
                </a>
            </div>

        </div>
    </article>

    <x-public.footer />

    <x-public.prose-styles />
</x-layouts.app>
