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
            <div class="blog-prose">
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

    {{-- Plain-CSS prose styling: build-free, themed for the dark public side. --}}
    <style>
        .blog-prose { color: #d1d5db; line-height: 1.8; font-size: 1.05rem; }
        .blog-prose > * + * { margin-top: 1.25rem; }
        .blog-prose h2 { color: #fff; font-size: 1.6rem; font-weight: 800; margin-top: 2.5rem; margin-bottom: 0.5rem; }
        .blog-prose h3 { color: #fff; font-size: 1.25rem; font-weight: 700; margin-top: 2rem; margin-bottom: 0.5rem; }
        .blog-prose p { color: #9ca3af; }
        .blog-prose a { color: #a78bfa; text-decoration: underline; }
        .blog-prose a:hover { color: #c4b5fd; }
        .blog-prose strong { color: #fff; }
        .blog-prose ul, .blog-prose ol { padding-left: 1.5rem; color: #9ca3af; }
        .blog-prose ul { list-style: disc; }
        .blog-prose ol { list-style: decimal; }
        .blog-prose li { margin-top: 0.4rem; }
        .blog-prose blockquote {
            border-left: 3px solid #7c3aed; padding: 0.25rem 0 0.25rem 1.25rem;
            color: #d1d5db; font-style: italic; margin-left: 0;
        }
        .blog-prose pre {
            background: #111118; border: 1px solid #1a1a24; border-radius: 0.75rem;
            padding: 1.25rem; overflow-x: auto; font-size: 0.9rem; line-height: 1.6;
        }
        .blog-prose code {
            font-family: 'Fira Code', monospace; color: #c4b5fd;
        }
        .blog-prose pre code { color: #e5e7eb; }
        .blog-prose :not(pre) > code {
            background: #1a1a24; padding: 0.15rem 0.4rem; border-radius: 0.35rem; font-size: 0.85em;
        }
    </style>
</x-layouts.app>
