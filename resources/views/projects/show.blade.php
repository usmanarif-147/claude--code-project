<x-layouts.app>
    <x-public.top-nav active="projects" />

    @php
        $src = \Illuminate\Support\Str::startsWith($project->cover_image, 'http')
            ? $project->cover_image
            : asset('storage/'.$project->cover_image);
    @endphp

    <article class="min-h-screen px-4 pb-24 pt-28 sm:px-6">
        <div class="mx-auto max-w-3xl">

            <a href="{{ route('projects.index') }}"
               class="mb-8 inline-flex items-center gap-2 text-sm text-gray-500 transition-colors hover:text-accent">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Projects
            </a>

            {{-- Cover --}}
            <div class="mb-8 overflow-hidden rounded-2xl border border-white/[0.04]">
                <img src="{{ $src }}" alt="{{ $project->title }}" class="h-64 w-full object-cover md:h-80">
            </div>

            {{-- Title + featured --}}
            <div class="mb-3 flex flex-wrap items-center gap-3">
                <h1 class="text-3xl font-extrabold text-white md:text-4xl">{{ $project->title }}</h1>
                @if($project->is_featured)
                    <span class="rounded-full border border-accent/20 bg-accent/20 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-accent">Featured</span>
                @endif
            </div>

            @if($project->completed_at)
                <p class="mb-4 text-sm text-gray-500">Completed {{ $project->completed_at->format('M Y') }}</p>
            @endif

            {{-- Tech stack --}}
            @if(!empty($project->tech_stack))
                <div class="mb-6 flex flex-wrap gap-2">
                    @foreach($project->tech_stack as $tech)
                        <span class="rounded-full border border-accent/10 bg-accent/10 px-3 py-1 text-xs text-accent-light">{{ $tech }}</span>
                    @endforeach
                </div>
            @endif

            {{-- Action buttons --}}
            @if($project->demo_url || $project->github_url)
                <div class="mb-8 flex flex-wrap gap-4 border-b border-white/[0.04] pb-8">
                    @if($project->demo_url)
                        <a href="{{ $project->demo_url }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-2 rounded-xl bg-accent px-6 py-3 text-sm font-extrabold uppercase tracking-widest text-black transition-colors hover:bg-accent-light">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            Live Demo
                        </a>
                    @endif
                    @if($project->github_url)
                        <a href="{{ $project->github_url }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-2 rounded-xl border border-accent/30 px-6 py-3 text-sm font-semibold text-accent transition-colors hover:bg-accent/10">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                            View Code
                        </a>
                    @endif
                </div>
            @endif

            {{-- Description --}}
            <div class="article-prose">
                {!! $project->description !!}
            </div>

            {{-- Gallery --}}
            @if(!empty($project->gallery))
                <div class="mt-12">
                    <h2 class="mb-5 text-xl font-bold text-white">Gallery</h2>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        @foreach($project->gallery as $image)
                            <div class="overflow-hidden rounded-2xl border border-white/[0.04]">
                                <img src="{{ $image }}" alt="{{ $project->title }} screenshot" loading="lazy"
                                     class="h-full w-full object-cover">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mt-12 border-t border-white/[0.04] pt-8">
                <a href="{{ route('projects.index') }}"
                   class="inline-flex items-center gap-2 text-sm font-medium text-accent transition-colors hover:text-accent-light">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to all projects
                </a>
            </div>

        </div>
    </article>

    <x-public.footer />

    <x-public.prose-styles />
</x-layouts.app>
