<div>
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-white">Welcome back, {{ auth()->user()->name }}!</h1>
        <p class="text-gray-500 mt-1">Here's an overview of your portfolio.</p>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        @foreach ($stats as $stat)
            <div class="bg-dark-800 border border-dark-700 rounded-xl p-5">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm text-gray-500">{{ $stat['label'] }}</span>
                    <span class="w-9 h-9 rounded-lg bg-accent-500/10 flex items-center justify-center">
                        @if ($stat['icon'] === 'lightbulb')
                            <svg class="w-5 h-5 text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                        @elseif ($stat['icon'] === 'code')
                            <svg class="w-5 h-5 text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                        @elseif ($stat['icon'] === 'briefcase')
                            <svg class="w-5 h-5 text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m8 0H8m8 0h2a2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V8a2 2 0 012-2h2"/></svg>
                        @elseif ($stat['icon'] === 'user')
                            <svg class="w-5 h-5 text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        @endif
                    </span>
                </div>
                <p class="text-3xl font-bold text-white">{{ $stat['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Skills --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-semibold text-white">Skills Overview</h2>
                <a href="{{ route('admin.skills.index') }}" wire:navigate class="text-xs text-accent-400 hover:underline">View all</a>
            </div>
            @if ($skills->isNotEmpty())
                <div class="space-y-3">
                    @foreach ($skills->take(8) as $skill)
                        <div class="flex items-center justify-between py-1.5">
                            <div class="flex items-center gap-3">
                                @if ($skill->icon)
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="w-5 h-5 text-accent-400 shrink-0">{!! $skill->icon !!}</svg>
                                @endif
                                <span class="text-sm text-gray-300">{{ $skill->title }}</span>
                            </div>
                            <span class="text-xs text-gray-500">#{{ $skill->sort_order }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-sm text-center py-4">No skills added yet. <a href="{{ route('admin.skills.create') }}" wire:navigate class="text-accent-400 hover:underline">Add one</a>.</p>
            @endif
        </div>

        {{-- Experience --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-lg font-semibold text-white">Experience</h2>
                <a href="{{ route('admin.experiences.index') }}" wire:navigate class="text-xs text-accent-400 hover:underline">View all</a>
            </div>
            @if ($experience->isNotEmpty())
                <div class="space-y-5">
                    @foreach ($experience->take(5) as $exp)
                        <div class="relative pl-6 border-l-2 border-dark-600">
                            <div class="absolute -left-[5px] top-1.5 w-2 h-2 rounded-full bg-accent-500"></div>
                            <h3 class="text-sm font-semibold text-white">{{ $exp->role }}</h3>
                            <p class="text-xs text-accent-400 mt-0.5">{{ $exp->company }} &middot; {{ $exp->start_date->format('M Y') }} — {{ $exp->is_current ? 'Present' : $exp->end_date?->format('M Y') }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-sm text-center py-4">No experience added yet. <a href="{{ route('admin.experiences.create') }}" wire:navigate class="text-accent-400 hover:underline">Add one</a>.</p>
            @endif
        </div>
    </div>
</div>
