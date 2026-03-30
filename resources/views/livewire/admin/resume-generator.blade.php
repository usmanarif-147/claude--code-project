<div>
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Resume Generator</h1>
        <p class="text-gray-500 mt-1">Generate a professional PDF resume from your portfolio data.</p>
    </div>

    {{-- Data Summary --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 mb-8">
        <h3 class="text-lg font-mono font-semibold text-white uppercase tracking-wider mb-3">Data Summary</h3>
        <div class="flex flex-wrap gap-4">
            <span class="text-sm text-gray-400">{{ $skillCount }} Skills</span>
            <span class="text-gray-600">•</span>
            <span class="text-sm text-gray-400">{{ $experienceCount }} Experiences</span>
            <span class="text-gray-600">•</span>
            <span class="text-sm text-gray-400">{{ $projectCount }} Projects</span>
        </div>
    </div>

    {{-- Template Selector --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 mb-8">
        <h3 class="text-lg font-mono font-semibold text-white uppercase tracking-wider mb-4">Choose Template</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            @foreach($templates as $key => $description)
                <button wire:click="$set('selectedTemplate', '{{ $key }}')"
                        class="p-4 rounded-xl border-2 text-left transition-all {{ $selectedTemplate === $key ? 'border-primary bg-primary/10' : 'border-dark-700 bg-dark-700 hover:border-dark-600' }}">
                    <h4 class="text-white font-medium mb-1">{{ ucfirst($key) }}</h4>
                    <p class="text-sm text-gray-400">{{ $description }}</p>
                </button>
            @endforeach
        </div>
    </div>

    {{-- Preview Section --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 mb-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-mono font-semibold text-white uppercase tracking-wider">Preview</h3>
            <a href="{{ route('admin.resume.download', $selectedTemplate) }}"
               class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white font-medium rounded-lg px-6 py-2.5 transition-colors text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Download PDF
            </a>
        </div>
        <div class="bg-white rounded-lg overflow-hidden" style="height: 600px;">
            <iframe srcdoc="{{ $previewHtml }}" class="w-full h-full border-0" sandbox="allow-same-origin"></iframe>
        </div>
    </div>
</div>
