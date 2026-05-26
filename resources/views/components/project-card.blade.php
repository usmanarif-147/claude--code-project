@props(['project'])

@php
    $src = \Illuminate\Support\Str::startsWith($project->cover_image, 'http')
        ? $project->cover_image
        : asset('storage/'.$project->cover_image);
@endphp

<div class="group relative flex h-full flex-col overflow-hidden rounded-2xl border border-white/[0.04] bg-dark-800 transition-all duration-300 hover:border-accent/20">
    <div class="relative overflow-hidden">
        <img src="{{ $src }}" alt="{{ $project->title }}" loading="lazy"
             class="h-48 w-full object-cover transition-transform duration-500 group-hover:scale-105">
        <div class="absolute inset-0 bg-gradient-to-t from-dark-800 to-transparent"></div>
        @if($project->is_featured)
            <span class="absolute right-3 top-3 rounded-full border border-accent/20 bg-accent/20 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-accent">Featured</span>
        @endif
    </div>

    <div class="flex flex-1 flex-col p-6">
        {{-- Stretched link makes the whole card open the detail page --}}
        <h3 class="mb-2 text-xl font-bold text-white transition-colors group-hover:text-accent">
            <a href="{{ route('projects.show', $project->slug) }}" class="before:absolute before:inset-0">
                {{ $project->title }}
            </a>
        </h3>
        <p class="mb-4 line-clamp-2 flex-1 text-sm text-gray-400">{{ $project->short_description }}</p>

        @if(!empty($project->tech_stack))
            <div class="mb-4 flex flex-wrap gap-2">
                @foreach($project->tech_stack as $tech)
                    <span class="rounded-full bg-accent/10 px-2 py-1 text-xs text-accent-light">{{ $tech }}</span>
                @endforeach
            </div>
        @endif

        @if($project->demo_url || $project->github_url)
            {{-- relative z-10 keeps these clickable ABOVE the stretched card link --}}
            <div class="relative z-10 flex gap-4 border-t border-white/[0.04] pt-3">
                @if($project->demo_url)
                    <a href="{{ $project->demo_url }}" target="_blank" rel="noopener"
                       class="text-sm text-accent transition-colors hover:text-accent-light">Live Demo &rarr;</a>
                @endif
                @if($project->github_url)
                    <a href="{{ $project->github_url }}" target="_blank" rel="noopener"
                       class="text-sm text-gray-400 transition-colors hover:text-white">GitHub &rarr;</a>
                @endif
            </div>
        @endif
    </div>
</div>
