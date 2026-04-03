<div>
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">Resume Builder</span>
    </div>

    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Resume Builder</h1>
            <p class="text-sm text-gray-500 mt-1">Build, edit, preview and download your professional resume.</p>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success') || session('error'))
        <div x-data="{ show: true }"
             x-show="show"
             x-init="setTimeout(() => show = false, 4000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="mb-6">
            @if(session('success'))
                <div class="flex items-center gap-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-lg px-4 py-3 text-sm">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p>{{ session('success') }}</p>
                    <button @click="show = false" class="ml-auto text-emerald-400/60 hover:text-emerald-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-center gap-3 bg-red-500/10 border border-red-500/20 text-red-400 rounded-lg px-4 py-3 text-sm">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p>{{ session('error') }}</p>
                    <button @click="show = false" class="ml-auto text-red-400/60 hover:text-red-400"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                </div>
            @endif
        </div>
    @endif

    {{-- Processing Banner --}}
    @if($isProcessing)
        <div class="mb-6 flex items-center gap-3 bg-primary/10 border border-primary/20 text-primary-light rounded-lg px-4 py-3 text-sm">
            <svg class="animate-spin w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <p>{{ $processingMessage }}</p>
        </div>
    @endif

    {{-- Data Summary Bar --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-4 hover:border-dark-600 transition-colors">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center">
                    <svg class="w-4 h-4 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-white">{{ $skillCount }}</p>
                    <p class="text-xs text-gray-500">Skills</p>
                </div>
            </div>
        </div>
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-4 hover:border-dark-600 transition-colors">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-blue-500/10 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-white">{{ $technologyCount }}</p>
                    <p class="text-xs text-gray-500">Technologies</p>
                </div>
            </div>
        </div>
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-4 hover:border-dark-600 transition-colors">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-white">{{ $experienceCount }}</p>
                    <p class="text-xs text-gray-500">Experiences</p>
                </div>
            </div>
        </div>
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-4 hover:border-dark-600 transition-colors">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-amber-500/10 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-white">{{ $educationCount }}</p>
                    <p class="text-xs text-gray-500">Education</p>
                </div>
            </div>
        </div>
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-4 hover:border-dark-600 transition-colors">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-fuchsia-500/10 flex items-center justify-center">
                    <svg class="w-4 h-4 text-fuchsia-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-white">{{ $projectCount }}</p>
                    <p class="text-xs text-gray-500">Projects</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center gap-3 mb-6">
        <button wire:click="$set('activeModal', 'upload-template')"
                class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 border border-dark-600 text-gray-300 hover:text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-all duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Upload Template
        </button>
        <button wire:click="$set('activeModal', 'upload-details')"
                class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 border border-dark-600 text-gray-300 hover:text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-all duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
            Upload Details
        </button>
        <a href="{{ route('admin.resume.download', $currentTemplateName) }}"
           class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Download PDF
        </a>
        @if($isCustomTemplate)
            <button wire:click="deleteCustomTemplate('{{ $currentTemplateName }}')" wire:confirm="Delete this custom template?"
                    class="inline-flex items-center gap-1.5 bg-red-500/10 hover:bg-red-500/20 text-red-400 hover:text-red-300 text-sm font-medium rounded-lg px-4 py-2.5 transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Delete Template
            </button>
        @endif
    </div>

    {{-- Preview + Edit Strip --}}
    <div class="flex gap-6">
        {{-- Edit Strip --}}
        <div class="hidden lg:flex flex-col gap-2 w-48 shrink-0">
            <p class="text-xs font-mono font-medium text-gray-500 uppercase tracking-widest mb-2 px-3">Edit Sections</p>
            @foreach([
                'personal' => ['Personal Info', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                'about' => ['About / Summary', 'M4 6h16M4 12h16M4 18h7'],
                'experience' => ['Work Experience', 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                'education' => ['Education', 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z'],
                'skills' => ['Skills', 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z'],
                'technologies' => ['Technologies', 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4'],
                'projects' => ['Projects', 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
            ] as $key => [$label, $icon])
                <button wire:click="openModal('{{ $key }}')"
                        class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm text-left text-gray-400 hover:text-white hover:bg-dark-700 transition-all duration-200 group">
                    <svg class="w-4 h-4 text-gray-500 group-hover:text-primary-light transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/></svg>
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Carousel + Preview --}}
        <div class="flex-1 min-w-0">
            <div class="bg-dark-800 border border-dark-700 rounded-xl overflow-hidden">
                {{-- Carousel Controls --}}
                <div class="flex items-center justify-between px-5 py-3 border-b border-dark-700">
                    <button wire:click="prevTemplate"
                            class="p-2 text-gray-400 hover:text-white hover:bg-dark-700 rounded-lg transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>

                    <div class="flex items-center gap-3">
                        {{-- Dot indicators --}}
                        <div class="flex items-center gap-1.5">
                            @foreach($templateKeys as $idx => $key)
                                <button wire:click="selectTemplate({{ $idx }})"
                                        class="w-2.5 h-2.5 rounded-full transition-all duration-200 {{ $idx === $currentTemplateIndex ? 'bg-primary scale-110' : 'bg-dark-600 hover:bg-dark-500' }}"></button>
                            @endforeach
                        </div>
                        <span class="text-xs text-gray-500 font-mono">Template {{ $currentTemplateIndex + 1 }} of {{ $templateCount }}</span>
                        <span class="text-xs text-gray-400 font-medium px-2 py-0.5 rounded bg-dark-700">{{ ucfirst($currentTemplateName) }}</span>
                    </div>

                    <button wire:click="nextTemplate"
                            class="p-2 text-gray-400 hover:text-white hover:bg-dark-700 rounded-lg transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>

                {{-- Mobile Edit Buttons --}}
                <div class="lg:hidden flex flex-wrap gap-2 px-5 py-3 border-b border-dark-700">
                    @foreach(['personal' => 'Personal', 'about' => 'About', 'experience' => 'Experience', 'education' => 'Education', 'skills' => 'Skills', 'technologies' => 'Tech', 'projects' => 'Projects'] as $key => $label)
                        <button wire:click="openModal('{{ $key }}')"
                                class="text-xs text-gray-400 hover:text-primary-light bg-dark-700 hover:bg-primary/10 px-2.5 py-1.5 rounded-lg transition-all">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                {{-- Preview --}}
                <div class="bg-gray-100" style="height: 800px;">
                    <iframe srcdoc="{{ $previewHtml }}" class="w-full h-full border-0" sandbox="allow-same-origin"></iframe>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- MODALS                                                  --}}
    {{-- ═══════════════════════════════════════════════════════ --}}

    {{-- Modal Backdrop --}}
    @if($activeModal)
        <div class="fixed inset-0 z-50 flex items-start justify-center pt-16 px-4" x-data x-transition>
            <div class="fixed inset-0 bg-dark-950/80" wire:click="closeModal"></div>
            <div class="relative bg-dark-800 border border-dark-700 rounded-xl w-full max-w-2xl max-h-[80vh] overflow-y-auto shadow-2xl">

                {{-- ── Personal Info Modal ── --}}
                @if($activeModal === 'personal')
                    <div class="px-6 py-4 border-b border-dark-700 flex items-center justify-between">
                        <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider">Edit Personal Info</h2>
                        <button wire:click="closeModal" class="p-1 text-gray-400 hover:text-white"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Name <span class="text-red-400">*</span></label>
                            <input type="text" wire:model="editName" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            @error('editName') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Email <span class="text-red-400">*</span></label>
                            <input type="email" wire:model="editEmail" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            @error('editEmail') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-300 mb-2">Tagline</label>
                            <input type="text" wire:model="editTagline" placeholder="e.g. Full Stack Developer" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Phone</label>
                            <input type="text" wire:model="editPhone" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Location</label>
                            <input type="text" wire:model="editLocation" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">LinkedIn URL</label>
                            <input type="url" wire:model="editLinkedin" placeholder="https://linkedin.com/in/..." class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            @error('editLinkedin') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">GitHub URL</label>
                            <input type="url" wire:model="editGithub" placeholder="https://github.com/..." class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            @error('editGithub') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-dark-700 flex items-center justify-end gap-3">
                        <button wire:click="closeModal" class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 border border-dark-600 text-gray-300 text-sm font-medium rounded-lg px-5 py-2.5 transition-colors">Cancel</button>
                        <button wire:click="savePersonalInfo" class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all shadow-lg shadow-primary/20">
                            <span wire:loading.remove wire:target="savePersonalInfo">Save Changes</span>
                            <span wire:loading wire:target="savePersonalInfo">Saving...</span>
                        </button>
                    </div>
                @endif

                {{-- ── About Modal ── --}}
                @if($activeModal === 'about')
                    <div class="px-6 py-4 border-b border-dark-700 flex items-center justify-between">
                        <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider">Edit About / Summary</h2>
                        <button wire:click="closeModal" class="p-1 text-gray-400 hover:text-white"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                    <div class="p-6">
                        <label class="block text-sm font-medium text-gray-300 mb-2">Professional Summary</label>
                        <textarea wire:model="editBio" rows="6" placeholder="Write a brief professional summary..." class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent resize-none"></textarea>
                        @error('editBio') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div class="px-6 py-4 border-t border-dark-700 flex items-center justify-end gap-3">
                        <button wire:click="closeModal" class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 border border-dark-600 text-gray-300 text-sm font-medium rounded-lg px-5 py-2.5 transition-colors">Cancel</button>
                        <button wire:click="saveAbout" class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all shadow-lg shadow-primary/20">
                            <span wire:loading.remove wire:target="saveAbout">Save Changes</span>
                            <span wire:loading wire:target="saveAbout">Saving...</span>
                        </button>
                    </div>
                @endif

                {{-- ── Experience Modal ── --}}
                @if($activeModal === 'experience')
                    <div class="px-6 py-4 border-b border-dark-700 flex items-center justify-between">
                        <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider">Work Experience</h2>
                        <button wire:click="closeModal" class="p-1 text-gray-400 hover:text-white"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                    <div class="p-6 space-y-5">
                        {{-- Existing entries --}}
                        @if($experiences->count())
                            <div class="space-y-2">
                                @foreach($experiences as $exp)
                                    <div class="flex items-center justify-between bg-dark-700 rounded-lg px-4 py-3">
                                        <div>
                                            <p class="text-sm font-medium text-white">{{ $exp->role }}</p>
                                            <p class="text-xs text-gray-400">{{ $exp->company }} &middot; {{ $exp->start_date->format('M Y') }} — {{ $exp->is_current ? 'Present' : ($exp->end_date?->format('M Y') ?? '') }}</p>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <button wire:click="editExperienceItem({{ $exp->id }})" class="p-2 text-gray-400 hover:text-primary-light hover:bg-primary/10 rounded-lg transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                                            <button wire:click="deleteExperience({{ $exp->id }})" wire:confirm="Delete this experience?" class="p-2 text-gray-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Add/Edit form --}}
                        <div class="border border-dark-600 rounded-lg p-5 space-y-4">
                            <h3 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">{{ $editExperienceId ? 'Edit Entry' : 'Add New Entry' }}</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Role <span class="text-red-400">*</span></label>
                                    <input type="text" wire:model="editExpRole" placeholder="e.g. Senior Developer" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    @error('editExpRole') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Company <span class="text-red-400">*</span></label>
                                    <input type="text" wire:model="editExpCompany" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    @error('editExpCompany') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Start Date <span class="text-red-400">*</span></label>
                                    <input type="date" wire:model="editExpStartDate" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    @error('editExpStartDate') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">End Date</label>
                                    <input type="date" wire:model="editExpEndDate" @if($editExpIsCurrent) disabled @endif class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent disabled:opacity-50">
                                </div>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-dark-700 rounded-lg">
                                <span class="text-sm text-gray-300">Currently working here</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model.live="editExpIsCurrent" class="sr-only peer">
                                    <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                </label>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                                <textarea wire:model="editExpDescription" rows="3" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent resize-none"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Responsibilities</label>
                                @foreach($editExpResponsibilities as $index => $resp)
                                    <div class="flex items-center gap-2 mb-2">
                                        <input type="text" wire:model="editExpResponsibilities.{{ $index }}" placeholder="Responsibility..." class="flex-1 bg-dark-700 border border-dark-600 rounded-lg px-4 py-2 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                        <button wire:click="removeResponsibility({{ $index }})" class="p-2 text-gray-400 hover:text-red-400 transition-colors"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                    </div>
                                @endforeach
                                <button wire:click="addResponsibility" class="text-xs text-primary-light hover:text-white transition-colors">+ Add Responsibility</button>
                            </div>
                            <div class="flex items-center gap-3">
                                <button wire:click="saveExperience" class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all shadow-lg shadow-primary/20">
                                    <span wire:loading.remove wire:target="saveExperience">{{ $editExperienceId ? 'Update' : 'Add' }} Experience</span>
                                    <span wire:loading wire:target="saveExperience">Saving...</span>
                                </button>
                                @if($editExperienceId)
                                    <button wire:click="newExperienceItem" class="text-sm text-gray-400 hover:text-white transition-colors">Cancel Edit</button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ── Education Modal ── --}}
                @if($activeModal === 'education')
                    <div class="px-6 py-4 border-b border-dark-700 flex items-center justify-between">
                        <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider">Education</h2>
                        <button wire:click="closeModal" class="p-1 text-gray-400 hover:text-white"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                    <div class="p-6 space-y-5">
                        @if($educationList->count())
                            <div class="space-y-2">
                                @foreach($educationList as $edu)
                                    <div class="flex items-center justify-between bg-dark-700 rounded-lg px-4 py-3">
                                        <div>
                                            <p class="text-sm font-medium text-white">{{ $edu->degree ?? $edu->role }}</p>
                                            <p class="text-xs text-gray-400">{{ $edu->company }} &middot; {{ $edu->start_date->format('Y') }} — {{ $edu->is_current ? 'Present' : ($edu->end_date?->format('Y') ?? '') }}</p>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <button wire:click="editEducationItem({{ $edu->id }})" class="p-2 text-gray-400 hover:text-primary-light hover:bg-primary/10 rounded-lg transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                                            <button wire:click="deleteEducation({{ $edu->id }})" wire:confirm="Delete this entry?" class="p-2 text-gray-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="border border-dark-600 rounded-lg p-5 space-y-4">
                            <h3 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">{{ $editEducationId ? 'Edit Entry' : 'Add New Entry' }}</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Degree <span class="text-red-400">*</span></label>
                                    <input type="text" wire:model="editEduDegree" placeholder="e.g. B.Sc. Computer Science" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    @error('editEduDegree') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Field of Study</label>
                                    <input type="text" wire:model="editEduField" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Institution <span class="text-red-400">*</span></label>
                                    <input type="text" wire:model="editEduCompany" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    @error('editEduCompany') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">Start Date <span class="text-red-400">*</span></label>
                                    <input type="date" wire:model="editEduStartDate" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    @error('editEduStartDate') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-300 mb-2">End Date</label>
                                    <input type="date" wire:model="editEduEndDate" @if($editEduIsCurrent) disabled @endif class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent disabled:opacity-50">
                                </div>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-dark-700 rounded-lg">
                                <span class="text-sm text-gray-300">Currently studying here</span>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model.live="editEduIsCurrent" class="sr-only peer">
                                    <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                                </label>
                            </div>
                            <div class="flex items-center gap-3">
                                <button wire:click="saveEducation" class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all shadow-lg shadow-primary/20">
                                    <span wire:loading.remove wire:target="saveEducation">{{ $editEducationId ? 'Update' : 'Add' }} Education</span>
                                    <span wire:loading wire:target="saveEducation">Saving...</span>
                                </button>
                                @if($editEducationId)
                                    <button wire:click="newEducationItem" class="text-sm text-gray-400 hover:text-white transition-colors">Cancel Edit</button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ── Skills Modal ── --}}
                @if($activeModal === 'skills')
                    <div class="px-6 py-4 border-b border-dark-700 flex items-center justify-between">
                        <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider">Skills</h2>
                        <button wire:click="closeModal" class="p-1 text-gray-400 hover:text-white"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                    <div class="p-6 space-y-5">
                        {{-- Existing skills --}}
                        <div class="flex flex-wrap gap-2">
                            @foreach($editSkills as $index => $skill)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm bg-dark-700 text-gray-300 border border-dark-600">
                                    {{ $skill['title'] }}
                                    @if(!empty($skill['category']))
                                        <span class="text-xs text-gray-500">({{ $skill['category'] }})</span>
                                    @endif
                                    <button wire:click="removeSkill({{ $index }})" class="ml-1 text-gray-500 hover:text-red-400 transition-colors">&times;</button>
                                </span>
                            @endforeach
                        </div>

                        {{-- Add new skill --}}
                        <div class="flex items-end gap-3">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-300 mb-2">Skill Name <span class="text-red-400">*</span></label>
                                <input type="text" wire:model="newSkillTitle" placeholder="e.g. Laravel" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                @error('newSkillTitle') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                            </div>
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-300 mb-2">Category</label>
                                <input type="text" wire:model="newSkillCategory" placeholder="e.g. Backend" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <button wire:click="addSkill" class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 border border-dark-600 text-gray-300 text-sm font-medium rounded-lg px-4 py-2.5 transition-colors shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Add
                            </button>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-dark-700 flex items-center justify-end gap-3">
                        <button wire:click="closeModal" class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 border border-dark-600 text-gray-300 text-sm font-medium rounded-lg px-5 py-2.5 transition-colors">Cancel</button>
                        <button wire:click="saveSkills" class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all shadow-lg shadow-primary/20">
                            <span wire:loading.remove wire:target="saveSkills">Save All Skills</span>
                            <span wire:loading wire:target="saveSkills">Saving...</span>
                        </button>
                    </div>
                @endif

                {{-- ── Technologies Modal ── --}}
                @if($activeModal === 'technologies')
                    <div class="px-6 py-4 border-b border-dark-700 flex items-center justify-between">
                        <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider">Technologies</h2>
                        <button wire:click="closeModal" class="p-1 text-gray-400 hover:text-white"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                    <div class="p-6 space-y-5">
                        {{-- Grouped by category --}}
                        @php
                            $grouped = collect($editTechnologies)->groupBy('category');
                        @endphp
                        @foreach($grouped as $category => $techs)
                            <div>
                                <p class="text-xs font-mono font-medium text-gray-500 uppercase tracking-widest mb-2">{{ $category }}</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($techs as $tech)
                                        @php $origIndex = array_search($tech, $editTechnologies); @endphp
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm bg-dark-700 text-gray-300 border border-dark-600">
                                            {{ $tech['name'] }}
                                            <button wire:click="removeTechnology({{ $origIndex !== false ? $origIndex : 0 }})" class="ml-1 text-gray-500 hover:text-red-400 transition-colors">&times;</button>
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        {{-- Add new technology --}}
                        <div class="flex items-end gap-3">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-300 mb-2">Technology Name <span class="text-red-400">*</span></label>
                                <input type="text" wire:model="newTechName" placeholder="e.g. React" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                @error('newTechName') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                            </div>
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-300 mb-2">Category <span class="text-red-400">*</span></label>
                                <input type="text" wire:model="newTechCategory" placeholder="e.g. Frontend" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                @error('newTechCategory') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                            </div>
                            <button wire:click="addTechnology" class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 border border-dark-600 text-gray-300 text-sm font-medium rounded-lg px-4 py-2.5 transition-colors shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Add
                            </button>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-dark-700 flex items-center justify-end gap-3">
                        <button wire:click="closeModal" class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 border border-dark-600 text-gray-300 text-sm font-medium rounded-lg px-5 py-2.5 transition-colors">Cancel</button>
                        <button wire:click="saveTechnologies" class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all shadow-lg shadow-primary/20">
                            <span wire:loading.remove wire:target="saveTechnologies">Save All Technologies</span>
                            <span wire:loading wire:target="saveTechnologies">Saving...</span>
                        </button>
                    </div>
                @endif

                {{-- ── Projects Modal ── --}}
                @if($activeModal === 'projects')
                    <div class="px-6 py-4 border-b border-dark-700 flex items-center justify-between">
                        <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider">Projects</h2>
                        <button wire:click="closeModal" class="p-1 text-gray-400 hover:text-white"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                    <div class="p-6 space-y-5">
                        @if($projects->count())
                            <div class="space-y-2">
                                @foreach($projects as $proj)
                                    <div class="flex items-center justify-between bg-dark-700 rounded-lg px-4 py-3">
                                        <div>
                                            <p class="text-sm font-medium text-white">{{ $proj->title }}</p>
                                            <p class="text-xs text-gray-400">{{ \Illuminate\Support\Str::limit($proj->short_description, 60) }}</p>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <button wire:click="editProjectItem({{ $proj->id }})" class="p-2 text-gray-400 hover:text-primary-light hover:bg-primary/10 rounded-lg transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg></button>
                                            <button wire:click="deleteProject({{ $proj->id }})" wire:confirm="Delete this project?" class="p-2 text-gray-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-all"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="border border-dark-600 rounded-lg p-5 space-y-4">
                            <h3 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">{{ $editProjectId ? 'Edit Project' : 'Add New Project' }}</h3>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Title <span class="text-red-400">*</span></label>
                                <input type="text" wire:model="editProjTitle" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                @error('editProjTitle') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Short Description</label>
                                <input type="text" wire:model="editProjShortDescription" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                                <textarea wire:model="editProjDescription" rows="3" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent resize-none"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Tech Stack</label>
                                <div class="flex flex-wrap gap-2 mb-2">
                                    @foreach($editProjTechStack as $index => $tech)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary-light">
                                            {{ $tech }}
                                            <button wire:click="removeTechStackItem({{ $index }})" class="text-primary-light/60 hover:text-red-400">&times;</button>
                                        </span>
                                    @endforeach
                                </div>
                                <div class="flex items-center gap-2">
                                    <input type="text" wire:model="newTechStackItem" placeholder="e.g. Laravel" wire:keydown.enter="addTechStackItem" class="flex-1 bg-dark-700 border border-dark-600 rounded-lg px-4 py-2 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <button wire:click="addTechStackItem" class="text-xs text-primary-light hover:text-white px-3 py-2 bg-dark-700 rounded-lg transition-colors">Add</button>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <button wire:click="saveProject" class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all shadow-lg shadow-primary/20">
                                    <span wire:loading.remove wire:target="saveProject">{{ $editProjectId ? 'Update' : 'Add' }} Project</span>
                                    <span wire:loading wire:target="saveProject">Saving...</span>
                                </button>
                                @if($editProjectId)
                                    <button wire:click="newProjectItem" class="text-sm text-gray-400 hover:text-white transition-colors">Cancel Edit</button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ── Upload Template Modal ── --}}
                @if($activeModal === 'upload-template')
                    <div class="px-6 py-4 border-b border-dark-700 flex items-center justify-between">
                        <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider">Upload Template Screenshot</h2>
                        <button wire:click="closeModal" class="p-1 text-gray-400 hover:text-white"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                    <div class="p-6 space-y-5">
                        <div class="bg-dark-700/50 border border-dark-600 rounded-lg p-4">
                            <p class="text-sm text-gray-300 mb-2">Upload a screenshot of a resume design you like. AI will analyze it and generate a matching PDF template.</p>
                            <p class="text-xs text-gray-500">Supports: PNG, JPG, JPEG, WEBP (max 5MB)</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Screenshot</label>
                            <input type="file" wire:model="templateScreenshot" accept="image/png,image/jpeg,image/webp"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary/20 file:text-primary-light hover:file:bg-primary/30 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            @error('templateScreenshot') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror

                            @if($templateScreenshot)
                                <div class="mt-3 rounded-lg overflow-hidden border border-dark-600" style="max-height: 200px;">
                                    <img src="{{ $templateScreenshot->temporaryUrl() }}" alt="Preview" class="w-full object-contain">
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-dark-700 flex items-center justify-end gap-3">
                        <button wire:click="closeModal" class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 border border-dark-600 text-gray-300 text-sm font-medium rounded-lg px-5 py-2.5 transition-colors">Cancel</button>
                        <button wire:click="uploadTemplateScreenshot" wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all shadow-lg shadow-primary/20 disabled:opacity-50">
                            <span wire:loading.remove wire:target="uploadTemplateScreenshot">Generate Template</span>
                            <span wire:loading wire:target="uploadTemplateScreenshot" class="flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                Generating...
                            </span>
                        </button>
                    </div>
                @endif

                {{-- ── Upload Details Modal ── --}}
                @if($activeModal === 'upload-details')
                    <div class="px-6 py-4 border-b border-dark-700 flex items-center justify-between">
                        <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider">Upload Resume Details</h2>
                        <button wire:click="closeModal" class="p-1 text-gray-400 hover:text-white"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                    <div class="p-6 space-y-5">
                        <div class="bg-dark-700/50 border border-dark-600 rounded-lg p-4">
                            <p class="text-sm text-gray-300 mb-2">Upload a PDF, TXT, or JSON file with your resume details. AI will parse and show you a preview before importing.</p>
                            <p class="text-xs text-gray-500 mb-3">Supports: PDF, TXT, JSON (max 5MB)</p>
                            <details class="text-xs text-gray-500">
                                <summary class="cursor-pointer text-primary-light hover:text-white transition-colors">Example TXT format</summary>
                                <pre class="mt-2 bg-dark-800 rounded p-3 text-gray-400 overflow-x-auto">Name: John Doe
Tagline: Full Stack Developer
Location: New York, NY

EXPERIENCE:
Senior Developer at Acme Corp (2020 - Present)
- Led team of 5 developers
- Built microservices architecture

EDUCATION:
B.Sc. Computer Science, MIT (2016 - 2020)

SKILLS:
Laravel, React, PostgreSQL, Docker

PROJECTS:
E-Commerce Platform - Built with Laravel + Vue.js
Portfolio Website - React + Tailwind CSS</pre>
                            </details>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Resume File</label>
                            <input type="file" wire:model="resumeFile" accept=".txt,.json,.pdf"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary/20 file:text-primary-light hover:file:bg-primary/30 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                            @error('resumeFile') <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-dark-700 flex items-center justify-end gap-3">
                        <button wire:click="closeModal" class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 border border-dark-600 text-gray-300 text-sm font-medium rounded-lg px-5 py-2.5 transition-colors">Cancel</button>
                        <button wire:click="uploadResumeDetails" wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all shadow-lg shadow-primary/20 disabled:opacity-50">
                            <span wire:loading.remove wire:target="uploadResumeDetails">Parse File</span>
                            <span wire:loading wire:target="uploadResumeDetails" class="flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                Parsing...
                            </span>
                        </button>
                    </div>
                @endif

                {{-- ── Preview Parsed Details Modal ── --}}
                @if($activeModal === 'preview-details')
                    <div class="px-6 py-4 border-b border-dark-700 flex items-center justify-between">
                        <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider">Review Parsed Details</h2>
                        <button wire:click="discardParsedData" class="p-1 text-gray-400 hover:text-white"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                    <div class="p-6 space-y-5 max-h-[60vh] overflow-y-auto">
                        <div class="bg-primary/5 border border-primary/20 rounded-lg px-4 py-3">
                            <p class="text-sm text-primary-light">Review the details below. You can edit any field before confirming. Only sections with data will be imported.</p>
                        </div>

                        {{-- Profile Section --}}
                        @if(!empty($parsedResumeData['profile']))
                            <div class="border border-dark-600 rounded-lg overflow-hidden">
                                <div class="bg-dark-700 px-4 py-2.5 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <h3 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Profile</h3>
                                </div>
                                <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach(['name' => 'Name', 'tagline' => 'Tagline', 'bio' => 'Bio', 'phone' => 'Phone', 'location' => 'Location', 'linkedin_url' => 'LinkedIn', 'github_url' => 'GitHub'] as $field => $label)
                                        @if(isset($parsedResumeData['profile'][$field]))
                                            <div class="{{ $field === 'bio' ? 'sm:col-span-2' : '' }}">
                                                <label class="block text-xs font-medium text-gray-500 mb-1">{{ $label }}</label>
                                                @if($field === 'bio')
                                                    <textarea wire:model="parsedResumeData.profile.{{ $field }}" rows="3" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent resize-none"></textarea>
                                                @else
                                                    <input type="text" wire:model="parsedResumeData.profile.{{ $field }}" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                                @endif
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Skills Section --}}
                        @if(!empty($parsedResumeData['skills']))
                            <div class="border border-dark-600 rounded-lg overflow-hidden">
                                <div class="bg-dark-700 px-4 py-2.5 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                    <h3 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Skills</h3>
                                    <span class="text-xs text-gray-500">({{ count($parsedResumeData['skills']) }} found)</span>
                                </div>
                                <div class="p-4 space-y-2">
                                    @foreach($parsedResumeData['skills'] as $idx => $skill)
                                        <div class="flex items-center gap-2">
                                            <input type="text" wire:model="parsedResumeData.skills.{{ $idx }}.title" placeholder="Skill name" class="flex-1 bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                            <input type="text" wire:model="parsedResumeData.skills.{{ $idx }}.category" placeholder="Category" class="w-32 bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                            <button wire:click="$set('parsedResumeData.skills.{{ $idx }}', null)" class="p-1.5 text-gray-500 hover:text-red-400 transition-colors">&times;</button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Technologies Section --}}
                        @if(!empty($parsedResumeData['technologies']))
                            <div class="border border-dark-600 rounded-lg overflow-hidden">
                                <div class="bg-dark-700 px-4 py-2.5 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                                    <h3 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Technologies</h3>
                                    <span class="text-xs text-gray-500">({{ count($parsedResumeData['technologies']) }} found)</span>
                                </div>
                                <div class="p-4 space-y-2">
                                    @foreach($parsedResumeData['technologies'] as $idx => $tech)
                                        <div class="flex items-center gap-2">
                                            <input type="text" wire:model="parsedResumeData.technologies.{{ $idx }}.name" placeholder="Technology" class="flex-1 bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                            <input type="text" wire:model="parsedResumeData.technologies.{{ $idx }}.category" placeholder="Category" class="w-36 bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                            <button wire:click="$set('parsedResumeData.technologies.{{ $idx }}', null)" class="p-1.5 text-gray-500 hover:text-red-400 transition-colors">&times;</button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Experiences Section --}}
                        @if(!empty($parsedResumeData['experiences']))
                            <div class="border border-dark-600 rounded-lg overflow-hidden">
                                <div class="bg-dark-700 px-4 py-2.5 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    <h3 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Work Experience</h3>
                                    <span class="text-xs text-gray-500">({{ count($parsedResumeData['experiences']) }} found)</span>
                                </div>
                                <div class="p-4 space-y-4">
                                    @foreach($parsedResumeData['experiences'] as $idx => $exp)
                                        <div class="bg-dark-700/50 rounded-lg p-4 space-y-3">
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                <div>
                                                    <label class="block text-xs text-gray-500 mb-1">Role</label>
                                                    <input type="text" wire:model="parsedResumeData.experiences.{{ $idx }}.role" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                                </div>
                                                <div>
                                                    <label class="block text-xs text-gray-500 mb-1">Company</label>
                                                    <input type="text" wire:model="parsedResumeData.experiences.{{ $idx }}.company" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                                </div>
                                                <div>
                                                    <label class="block text-xs text-gray-500 mb-1">Start Date</label>
                                                    <input type="date" wire:model="parsedResumeData.experiences.{{ $idx }}.start_date" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                                </div>
                                                <div>
                                                    <label class="block text-xs text-gray-500 mb-1">End Date</label>
                                                    <input type="date" wire:model="parsedResumeData.experiences.{{ $idx }}.end_date" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                                </div>
                                            </div>
                                            @if(!empty($exp['description']))
                                                <div>
                                                    <label class="block text-xs text-gray-500 mb-1">Description</label>
                                                    <textarea wire:model="parsedResumeData.experiences.{{ $idx }}.description" rows="2" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent resize-none"></textarea>
                                                </div>
                                            @endif
                                            @if(!empty($exp['responsibilities']))
                                                <div>
                                                    <label class="block text-xs text-gray-500 mb-1">Responsibilities</label>
                                                    @foreach($exp['responsibilities'] as $rIdx => $resp)
                                                        <input type="text" wire:model="parsedResumeData.experiences.{{ $idx }}.responsibilities.{{ $rIdx }}" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm mb-1.5 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Education Section --}}
                        @if(!empty($parsedResumeData['education']))
                            <div class="border border-dark-600 rounded-lg overflow-hidden">
                                <div class="bg-dark-700 px-4 py-2.5 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                                    <h3 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Education</h3>
                                    <span class="text-xs text-gray-500">({{ count($parsedResumeData['education']) }} found)</span>
                                </div>
                                <div class="p-4 space-y-4">
                                    @foreach($parsedResumeData['education'] as $idx => $edu)
                                        <div class="bg-dark-700/50 rounded-lg p-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-xs text-gray-500 mb-1">Degree</label>
                                                <input type="text" wire:model="parsedResumeData.education.{{ $idx }}.degree" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                            </div>
                                            <div>
                                                <label class="block text-xs text-gray-500 mb-1">Field of Study</label>
                                                <input type="text" wire:model="parsedResumeData.education.{{ $idx }}.field_of_study" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                            </div>
                                            <div>
                                                <label class="block text-xs text-gray-500 mb-1">Institution</label>
                                                <input type="text" wire:model="parsedResumeData.education.{{ $idx }}.institution" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                            </div>
                                            <div class="grid grid-cols-2 gap-2">
                                                <div>
                                                    <label class="block text-xs text-gray-500 mb-1">Start</label>
                                                    <input type="date" wire:model="parsedResumeData.education.{{ $idx }}.start_date" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                                </div>
                                                <div>
                                                    <label class="block text-xs text-gray-500 mb-1">End</label>
                                                    <input type="date" wire:model="parsedResumeData.education.{{ $idx }}.end_date" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Projects Section --}}
                        @if(!empty($parsedResumeData['projects']))
                            <div class="border border-dark-600 rounded-lg overflow-hidden">
                                <div class="bg-dark-700 px-4 py-2.5 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-fuchsia-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                    <h3 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Projects</h3>
                                    <span class="text-xs text-gray-500">({{ count($parsedResumeData['projects']) }} found)</span>
                                </div>
                                <div class="p-4 space-y-4">
                                    @foreach($parsedResumeData['projects'] as $idx => $proj)
                                        <div class="bg-dark-700/50 rounded-lg p-4 space-y-3">
                                            <div>
                                                <label class="block text-xs text-gray-500 mb-1">Title</label>
                                                <input type="text" wire:model="parsedResumeData.projects.{{ $idx }}.title" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                                            </div>
                                            <div>
                                                <label class="block text-xs text-gray-500 mb-1">Description</label>
                                                <textarea wire:model="parsedResumeData.projects.{{ $idx }}.description" rows="2" class="w-full bg-dark-700 border border-dark-600 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent resize-none"></textarea>
                                            </div>
                                            @if(!empty($proj['tech_stack']))
                                                <div>
                                                    <label class="block text-xs text-gray-500 mb-1">Tech Stack</label>
                                                    <div class="flex flex-wrap gap-1.5">
                                                        @foreach($proj['tech_stack'] as $tIdx => $t)
                                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-xs bg-primary/10 text-primary-light">{{ $t }}</span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="px-6 py-4 border-t border-dark-700 flex items-center justify-between">
                        <button wire:click="discardParsedData" class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 border border-dark-600 text-gray-300 text-sm font-medium rounded-lg px-5 py-2.5 transition-colors">
                            Discard
                        </button>
                        <button wire:click="confirmImportDetails"
                                class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all shadow-lg shadow-emerald-600/20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span wire:loading.remove wire:target="confirmImportDetails">Confirm & Import</span>
                            <span wire:loading wire:target="confirmImportDetails">Importing...</span>
                        </button>
                    </div>
                @endif

            </div>
        </div>
    @endif
</div>
