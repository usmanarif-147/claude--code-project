<x-layouts.app>
    <x-public.top-nav active="projects" />

    <main class="min-h-screen px-4 pb-24 pt-28 sm:px-6">
        <div class="mx-auto max-w-6xl">

            {{-- Header --}}
            <div class="mb-16 text-center">
                <h1 class="mb-4 text-4xl font-extrabold text-white md:text-5xl">Projects</h1>
                <p class="mx-auto max-w-xl text-gray-500">
                    Things I've designed, built, and shipped.
                </p>
            </div>

            @if($projects->isEmpty())
                <p class="py-20 text-center text-gray-500">No projects yet. Check back soon.</p>
            @else
                {{-- Grid: 1 → 4 per row. Wrapper marked for future infinite-scroll swap. --}}
                <div id="project-grid" class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                    @foreach($projects as $project)
                        <x-project-card :project="$project" />
                    @endforeach
                </div>

                {{-- Themed pagination (swap for infinite scroll later) --}}
                @if($projects->hasPages())
                    <nav class="mt-12 flex items-center justify-center gap-2" aria-label="Pagination">
                        @if($projects->onFirstPage())
                            <span class="cursor-not-allowed rounded-lg border border-white/[0.04] px-4 py-2 text-sm text-gray-600">&lsaquo; Prev</span>
                        @else
                            <a href="{{ $projects->previousPageUrl() }}"
                               class="rounded-lg border border-white/[0.04] px-4 py-2 text-sm text-gray-400 transition-colors hover:border-accent/30 hover:text-white">&lsaquo; Prev</a>
                        @endif

                        @foreach($projects->getUrlRange(1, $projects->lastPage()) as $page => $url)
                            @if($page == $projects->currentPage())
                                <span class="rounded-lg bg-accent px-4 py-2 text-sm font-bold text-black">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}"
                                   class="rounded-lg border border-white/[0.04] px-4 py-2 text-sm text-gray-400 transition-colors hover:border-accent/30 hover:text-white">{{ $page }}</a>
                            @endif
                        @endforeach

                        @if($projects->hasMorePages())
                            <a href="{{ $projects->nextPageUrl() }}"
                               class="rounded-lg border border-white/[0.04] px-4 py-2 text-sm text-gray-400 transition-colors hover:border-accent/30 hover:text-white">Next &rsaquo;</a>
                        @else
                            <span class="cursor-not-allowed rounded-lg border border-white/[0.04] px-4 py-2 text-sm text-gray-600">Next &rsaquo;</span>
                        @endif
                    </nav>
                @endif
            @endif

        </div>
    </main>

    <x-public.footer />
</x-layouts.app>
