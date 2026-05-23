<div>
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-500">Social</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">Templates</span>
    </div>

    {{-- Page header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Templates</h1>
            <p class="text-sm text-gray-500 mt-1">
                Visual layouts available for your carousel posts. Drop a new Blade file into
                <code class="text-primary-light bg-dark-700/60 px-1.5 py-0.5 rounded">resources/views/social-posts/templates/</code>
                and register it in <code class="text-primary-light bg-dark-700/60 px-1.5 py-0.5 rounded">config/social-templates.php</code> to add more.
            </p>
        </div>
        <a href="{{ route('admin.social.scheduler.index') }}" wire:navigate
           class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 text-gray-300 text-sm font-medium rounded-lg px-4 py-2.5 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Scheduler
        </a>
    </div>

    @if (empty($templates))
        {{-- Empty state --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-12 text-center">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-xl bg-dark-700 mb-4">
                <svg class="w-7 h-7 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-300">No templates configured yet</p>
            <p class="text-xs text-gray-500 mt-1">Add an entry to <code class="text-primary-light">config/social-templates.php</code> to get started.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach ($templates as $template)
                <div wire:key="template-card-{{ $template['slug'] }}"
                     class="bg-dark-800 border border-dark-700 rounded-xl overflow-hidden transition-all duration-200 hover:border-primary/60 hover:shadow-lg hover:shadow-primary/20 group">
                    {{-- Card header --}}
                    <div class="px-5 py-4 border-b border-dark-700 flex items-center justify-between">
                        <h2 class="text-sm font-mono font-semibold text-primary-light uppercase tracking-wider">
                            {{ $template['label'] }}
                        </h2>
                        <span class="text-[10px] font-mono text-gray-500 uppercase tracking-widest">{{ $template['slug'] }}</span>
                    </div>

                    {{-- Preview iframe (square thumbnail) --}}
                    <div class="relative bg-dark-900 border-b border-dark-700">
                        <div class="w-full aspect-square overflow-hidden">
                            <iframe
                                srcdoc="{{ $template['previewHtml'] }}"
                                class="block border-0 origin-top-left pointer-events-none"
                                style="width: 1200px; height: 1200px; transform: scale(0.25); transform-origin: top left;"
                                sandbox="allow-same-origin"
                                scrolling="no"
                                tabindex="-1"
                                aria-hidden="true"
                                title="{{ $template['label'] }} preview"></iframe>
                        </div>
                    </div>

                    {{-- Card footer --}}
                    <div class="px-5 py-3 flex items-center justify-between">
                        <p class="text-xs text-gray-500">Used for: <span class="text-gray-300">All carousel posts</span></p>
                        <span class="inline-flex items-center gap-1 text-[10px] font-medium text-primary-light bg-primary/10 px-2 py-0.5 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-primary-light"></span>
                            Active
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
