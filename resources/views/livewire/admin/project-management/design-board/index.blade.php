<div>
    {{-- Mermaid.js CDN --}}
    @assets
    <script src="https://cdn.jsdelivr.net/npm/mermaid@11/dist/mermaid.min.js"></script>
    @endassets

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-400">Project Management</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">Design Board</span>
    </div>

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Design Board</h1>
            <p class="text-sm text-gray-500 mt-1">Create diagrams, requirements, and generate tasks with AI</p>
        </div>
        @if ($selectedBoardId && $diagrams->isNotEmpty())
            <a href="{{ route('admin.project-management.design-board.export-all', ['boardId' => $selectedBoardId]) }}"
               target="_blank"
               class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 text-gray-300 text-sm font-medium rounded-lg px-4 py-2.5 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export All PDF
            </a>
        @endif
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm"
             x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" x-transition>
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-red-500/10 border border-red-500/20 text-red-400 text-sm"
             x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition>
            {{ session('error') }}
        </div>
    @endif

    {{-- Board Selector --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl p-4 mb-6">
        <div class="flex flex-wrap items-center gap-4">
            <select wire:change="selectBoard($event.target.value)"
                    class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
                <option value="">Select Board</option>
                @foreach ($boards as $board)
                    <option value="{{ $board->id }}" @selected($board->id === $selectedBoardId)>{{ $board->name }}</option>
                @endforeach
            </select>

            @if ($selectedBoardId)
                {{-- Tab Navigation --}}
                <div class="flex items-center gap-1 bg-dark-700 rounded-lg p-1">
                    <button wire:click="switchTab('diagrams')"
                            class="px-4 py-2 rounded-md text-sm font-medium transition-colors {{ $activeTab === 'diagrams' ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white' }}">
                        Diagrams
                    </button>
                    <button wire:click="switchTab('requirements')"
                            class="px-4 py-2 rounded-md text-sm font-medium transition-colors {{ $activeTab === 'requirements' ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white' }}">
                        Requirements
                    </button>
                </div>

                {{-- Generate Tasks Button --}}
                <div class="ml-auto">
                    <button wire:click="generateTasks"
                            wire:loading.attr="disabled"
                            wire:target="generateTasks"
                            {{ !$hasAiKey ? 'disabled' : '' }}
                            class="inline-flex items-center gap-2 bg-gradient-to-r from-primary via-fuchsia-500 to-orange-500 hover:opacity-90 text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg wire:loading.remove wire:target="generateTasks" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        <svg wire:loading wire:target="generateTasks" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="generateTasks">Generate Tasks</span>
                        <span wire:loading wire:target="generateTasks">Generating...</span>
                    </button>
                    @if (!$hasAiKey)
                        <p class="text-xs text-gray-500 mt-1">Configure Gemini or Groq API key in Settings</p>
                    @endif
                </div>
            @endif
        </div>
    </div>

    @if (!$selectedBoardId)
        {{-- Empty State --}}
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-12 text-center">
            <svg class="w-16 h-16 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
            </svg>
            <h3 class="text-lg font-mono font-semibold text-white uppercase tracking-wider mb-2">No Board Selected</h3>
            <p class="text-gray-500 mb-4">Select a project board above, or create one in the Project Board page first.</p>
            <a href="{{ route('admin.project-management.project-board.index') }}" wire:navigate
               class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-colors">
                Go to Project Board
            </a>
        </div>
    @else
        {{-- ========== DIAGRAMS TAB ========== --}}
        @if ($activeTab === 'diagrams')
            {{-- Diagram Cards Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                @forelse ($diagrams as $diagram)
                    <div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors {{ $activeDiagramId === $diagram->id ? 'ring-2 ring-primary border-primary/30' : '' }}">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h3 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">{{ $diagram->title }}</h3>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary-light mt-2">
                                    {{ str_replace('-', ' ', ucfirst($diagram->type)) }}
                                </span>
                            </div>
                            <div class="flex items-center gap-1">
                                <button wire:click="openEditor({{ $diagram->id }})"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-primary-light hover:bg-dark-700 transition-colors"
                                        title="Edit in editor">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button wire:click="openDiagramModal({{ $diagram->id }})"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-amber-400 hover:bg-dark-700 transition-colors"
                                        title="Edit details">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </button>
                                <a href="{{ route('admin.project-management.design-board.export-diagram', ['diagram' => $diagram->id]) }}"
                                   target="_blank"
                                   class="p-1.5 rounded-lg text-gray-400 hover:text-red-400 hover:bg-dark-700 transition-colors"
                                   title="Export PDF">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                </a>
                                <button wire:click="deleteDiagram({{ $diagram->id }})"
                                        wire:confirm="Are you sure you want to delete this diagram?"
                                        class="p-1.5 rounded-lg text-gray-400 hover:text-red-400 hover:bg-red-500/10 transition-colors"
                                        title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        @if ($diagram->description)
                            <p class="text-xs text-gray-500 line-clamp-2">{{ $diagram->description }}</p>
                        @endif
                        @if ($diagram->mermaid_syntax)
                            <p class="text-xs text-gray-600 mt-2">Has Mermaid syntax</p>
                        @endif
                    </div>
                @empty
                    <div class="col-span-2 bg-dark-800 border border-dark-700 rounded-xl p-8 text-center">
                        <p class="text-gray-500">No diagrams yet. Add your first diagram below.</p>
                    </div>
                @endforelse
            </div>

            {{-- Add Diagram Button --}}
            <div class="mb-6">
                <button wire:click="openDiagramModal"
                        class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Diagram
                </button>
            </div>

            {{-- Editor Panel --}}
            @if ($activeDiagramId)
                @php $activeDiagram = $diagrams->firstWhere('id', $activeDiagramId); @endphp
                @if ($activeDiagram)
                    <div class="bg-dark-800 border border-dark-700 rounded-xl mb-6">
                        <div class="flex items-center justify-between px-6 py-4 border-b border-dark-700">
                            <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider">
                                Editor: {{ $activeDiagram->title }}
                            </h2>
                            <div class="flex items-center gap-2">
                                @if ($hasAiKey)
                                    <button wire:click="generateDiagram"
                                            wire:loading.attr="disabled"
                                            wire:target="generateDiagram"
                                            class="inline-flex items-center gap-2 bg-gradient-to-r from-primary to-fuchsia-500 hover:opacity-90 text-white text-xs font-medium rounded-lg px-3 py-2 transition-all disabled:opacity-50">
                                        <svg wire:loading.remove wire:target="generateDiagram" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                        <svg wire:loading wire:target="generateDiagram" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                        <span wire:loading.remove wire:target="generateDiagram">AI Generate</span>
                                        <span wire:loading wire:target="generateDiagram">Generating...</span>
                                    </button>
                                @endif
                                <button wire:click="saveEditorContent"
                                        class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-xs font-medium rounded-lg px-3 py-2 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Save
                                </button>
                                <button wire:click="closeEditor"
                                        class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 text-gray-300 text-xs font-medium rounded-lg px-3 py-2 transition-colors">
                                    Close
                                </button>
                            </div>
                        </div>
                        <div class="p-6">
                            {{-- Description Input --}}
                            <div class="mb-4">
                                <label class="text-sm font-medium text-gray-300 mb-2 block">Description (used as AI input)</label>
                                <textarea wire:model="editorDescription"
                                          rows="2"
                                          placeholder="Describe what this diagram should show..."
                                          class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                                <p class="text-xs text-gray-500 mt-1">~{{ $this->getEstimatedTokens() }} tokens</p>
                            </div>

                            {{-- Split View: Editor + Preview --}}
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4"
                                 x-data="{
                                     syntax: @entangle('editorMermaidSyntax'),
                                     renderCount: 0,
                                     ready: false,
                                     async renderDiagram() {
                                         if (!this.ready) return;
                                         const el = this.$refs.preview;
                                         if (!this.syntax || !this.syntax.trim()) {
                                             el.innerHTML = '<p class=\'text-gray-500 text-sm\'>Enter Mermaid syntax to see preview</p>';
                                             return;
                                         }
                                         try {
                                             el.innerHTML = '';
                                             this.renderCount++;
                                             const id = 'diagram-preview-' + this.renderCount;
                                             const { svg } = await mermaid.render(id, this.syntax);
                                             el.innerHTML = svg;
                                         } catch (e) {
                                             el.innerHTML = '<p class=\'text-red-400 text-sm\'>Invalid Mermaid syntax: ' + e.message + '</p>';
                                         }
                                     }
                                 }"
                                 x-effect="renderDiagram()"
                                 x-init="
                                     const waitForMermaid = setInterval(() => {
                                         if (typeof mermaid !== 'undefined') {
                                             clearInterval(waitForMermaid);
                                             mermaid.initialize({ startOnLoad: false, theme: 'dark' });
                                             ready = true;
                                             renderDiagram();
                                         }
                                     }, 100);
                                 ">
                                {{-- Text Editor --}}
                                <div>
                                    <label class="text-sm font-medium text-gray-300 mb-2 block">Mermaid Syntax</label>
                                    <textarea x-model="syntax"
                                              wire:model.live.debounce.500ms="editorMermaidSyntax"
                                              rows="15"
                                              placeholder="Enter Mermaid.js syntax here..."
                                              class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm font-mono placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                                </div>
                                {{-- Mermaid Preview --}}
                                <div>
                                    <label class="text-sm font-medium text-gray-300 mb-2 block">Preview</label>
                                    <div x-ref="preview" class="bg-dark-700 border border-dark-600 rounded-lg p-4 min-h-[300px] overflow-auto">
                                        <p class="text-gray-500 text-sm">Enter Mermaid syntax to see preview</p>
                                    </div>
                                </div>
                            </div>

                            {{-- AI Token Info --}}
                            @if ($lastAiTokenCount)
                                <p class="text-xs text-gray-500 mt-3">
                                    Last AI call: ~{{ number_format($lastAiTokenCount) }} tokens via {{ ucfirst($lastAiProvider) }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endif
            @endif
        @endif

        {{-- ========== REQUIREMENTS TAB ========== --}}
        @if ($activeTab === 'requirements')
            <div class="space-y-6">
                {{-- Functional Requirements --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-dark-700">
                        <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider">Functional Requirements</h2>
                        <span class="text-xs text-gray-500">{{ $requirements->where('type', 'functional')->count() }} items</span>
                    </div>
                    <div class="p-6">
                        @forelse ($requirements->where('type', 'functional')->values() as $index => $req)
                            <div class="flex items-start justify-between py-3 {{ !$loop->last ? 'border-b border-dark-700/50' : '' }}">
                                <div class="flex-1 min-w-0">
                                    <span class="text-xs font-mono text-primary-light">FR-{{ $index + 1 }}</span>
                                    <h4 class="text-sm font-medium text-white mt-0.5">{{ $req->title }}</h4>
                                    @if ($req->description)
                                        <p class="text-xs text-gray-500 mt-1">{{ $req->description }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1 ml-3">
                                    <button wire:click="openRequirementModal({{ $req->id }})"
                                            class="p-1.5 rounded-lg text-gray-400 hover:text-primary-light hover:bg-dark-700 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button wire:click="deleteRequirement({{ $req->id }})"
                                            wire:confirm="Delete this requirement?"
                                            class="p-1.5 rounded-lg text-gray-400 hover:text-red-400 hover:bg-red-500/10 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No functional requirements yet.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Non-Functional Requirements --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-dark-700">
                        <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider">Non-Functional Requirements</h2>
                        <span class="text-xs text-gray-500">{{ $requirements->where('type', 'non-functional')->count() }} items</span>
                    </div>
                    <div class="p-6">
                        @forelse ($requirements->where('type', 'non-functional')->values() as $index => $req)
                            <div class="flex items-start justify-between py-3 {{ !$loop->last ? 'border-b border-dark-700/50' : '' }}">
                                <div class="flex-1 min-w-0">
                                    <span class="text-xs font-mono text-amber-400">NFR-{{ $index + 1 }}</span>
                                    <h4 class="text-sm font-medium text-white mt-0.5">{{ $req->title }}</h4>
                                    @if ($req->description)
                                        <p class="text-xs text-gray-500 mt-1">{{ $req->description }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1 ml-3">
                                    <button wire:click="openRequirementModal({{ $req->id }})"
                                            class="p-1.5 rounded-lg text-gray-400 hover:text-primary-light hover:bg-dark-700 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button wire:click="deleteRequirement({{ $req->id }})"
                                            wire:confirm="Delete this requirement?"
                                            class="p-1.5 rounded-lg text-gray-400 hover:text-red-400 hover:bg-red-500/10 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No non-functional requirements yet.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Add Requirement Button --}}
                <div>
                    <button wire:click="openRequirementModal"
                            class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Requirement
                    </button>
                </div>

                {{-- AI Requirements Generation --}}
                @if ($hasAiKey)
                    <div class="bg-dark-800 border border-dark-700 rounded-xl">
                        <div class="px-6 py-4 border-b border-dark-700">
                            <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">AI Requirements Generation</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Describe your project and AI will generate requirements.</p>
                        </div>
                        <div class="p-6">
                            <textarea wire:model="projectDescriptionForAi"
                                      rows="4"
                                      placeholder="Describe your project in detail..."
                                      class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent mb-3"></textarea>

                            @php
                                $estimatedTokens = $projectDescriptionForAi ? app(\App\Services\DesignBoardService::class)->estimateTokens($projectDescriptionForAi) : 0;
                            @endphp
                            @if ($projectDescriptionForAi)
                                <p class="text-xs text-gray-500 mb-3">~{{ number_format($estimatedTokens) }} tokens</p>
                                @if ($estimatedTokens > 10000)
                                    <p class="text-xs text-amber-400 mb-3">Large prompt — AI may truncate or fail on free tier</p>
                                @endif
                            @endif

                            <button wire:click="generateRequirements"
                                    wire:loading.attr="disabled"
                                    wire:target="generateRequirements"
                                    class="inline-flex items-center gap-2 bg-gradient-to-r from-primary to-fuchsia-500 hover:opacity-90 text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-all disabled:opacity-50">
                                <svg wire:loading.remove wire:target="generateRequirements" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                <svg wire:loading wire:target="generateRequirements" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span wire:loading.remove wire:target="generateRequirements">Generate Requirements with AI</span>
                                <span wire:loading wire:target="generateRequirements">Generating...</span>
                            </button>
                        </div>
                    </div>
                @endif

                {{-- AI Token Info --}}
                @if ($lastAiTokenCount)
                    <p class="text-xs text-gray-500">
                        Last AI call: ~{{ number_format($lastAiTokenCount) }} tokens via {{ ucfirst($lastAiProvider) }}
                    </p>
                @endif
            </div>
        @endif
    @endif

    {{-- ========== DIAGRAM MODAL ========== --}}
    @if ($showDiagramModal)
        <div class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" x-data x-transition>
            <div class="bg-dark-800 border border-dark-700 rounded-xl w-full max-w-lg shadow-2xl" @click.outside="$wire.set('showDiagramModal', false)">
                <div class="flex items-center justify-between px-6 py-4 border-b border-dark-700">
                    <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider">
                        {{ $editingDiagramId ? 'Edit Diagram' : 'New Diagram' }}
                    </h2>
                    <button wire:click="$set('showDiagramModal', false)" class="text-gray-400 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="p-6 space-y-5">
                    <div>
                        <label class="text-sm font-medium text-gray-300 mb-2 block">Title</label>
                        <input type="text" wire:model="diagramTitle"
                               class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="e.g. User Authentication Flow">
                        @error('diagramTitle') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-300 mb-2 block">Type</label>
                        <select wire:model="diagramType"
                                class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
                            @foreach ($diagramTypes as $type)
                                <option value="{{ $type }}">{{ str_replace('-', ' ', ucfirst($type)) }}</option>
                            @endforeach
                        </select>
                        @error('diagramType') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-300 mb-2 block">Description (optional)</label>
                        <textarea wire:model="diagramDescription" rows="3"
                                  class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent"
                                  placeholder="Brief description of the diagram..."></textarea>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-dark-700">
                    <button wire:click="$set('showDiagramModal', false)"
                            class="bg-dark-700 hover:bg-dark-600 text-gray-300 text-sm font-medium rounded-lg px-4 py-2.5 transition-colors">
                        Cancel
                    </button>
                    <button wire:click="saveDiagram"
                            class="bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-colors">
                        {{ $editingDiagramId ? 'Update' : 'Create' }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ========== REQUIREMENT MODAL ========== --}}
    @if ($showRequirementModal)
        <div class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" x-data x-transition>
            <div class="bg-dark-800 border border-dark-700 rounded-xl w-full max-w-lg shadow-2xl" @click.outside="$wire.set('showRequirementModal', false)">
                <div class="flex items-center justify-between px-6 py-4 border-b border-dark-700">
                    <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider">
                        {{ $editingRequirementId ? 'Edit Requirement' : 'New Requirement' }}
                    </h2>
                    <button wire:click="$set('showRequirementModal', false)" class="text-gray-400 hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="p-6 space-y-5">
                    <div>
                        <label class="text-sm font-medium text-gray-300 mb-2 block">Type</label>
                        <select wire:model="requirementType"
                                class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="functional">Functional</option>
                            <option value="non-functional">Non-Functional</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-300 mb-2 block">Title</label>
                        <input type="text" wire:model="requirementTitle"
                               class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent"
                               placeholder="e.g. User can register an account">
                        @error('requirementTitle') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-300 mb-2 block">Description (optional)</label>
                        <textarea wire:model="requirementDescription" rows="3"
                                  class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent"
                                  placeholder="Detailed description..."></textarea>
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-dark-700">
                    <button wire:click="$set('showRequirementModal', false)"
                            class="bg-dark-700 hover:bg-dark-600 text-gray-300 text-sm font-medium rounded-lg px-4 py-2.5 transition-colors">
                        Cancel
                    </button>
                    <button wire:click="saveRequirement"
                            class="bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-colors">
                        {{ $editingRequirementId ? 'Update' : 'Create' }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ========== TASK PREVIEW MODAL ========== --}}
    @if ($showTaskPreview)
        <div class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4" x-data x-transition>
            <div class="bg-dark-800 border border-dark-700 rounded-xl w-full max-w-2xl shadow-2xl max-h-[80vh] flex flex-col">
                <div class="flex items-center justify-between px-6 py-4 border-b border-dark-700">
                    <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider">
                        Generated Tasks ({{ count($generatedTasks) }})
                    </h2>
                    <div class="flex items-center gap-2">
                        <button wire:click="selectAllTasks" class="text-xs text-primary-light hover:text-white transition-colors">Select All</button>
                        <span class="text-gray-600">|</span>
                        <button wire:click="deselectAllTasks" class="text-xs text-gray-400 hover:text-white transition-colors">Deselect All</button>
                    </div>
                </div>
                <div class="flex-1 overflow-y-auto p-6 space-y-2">
                    @foreach ($generatedTasks as $index => $task)
                        <div wire:click="toggleTaskSelection({{ $index }})"
                             class="flex items-start gap-3 p-3 rounded-lg cursor-pointer transition-colors {{ in_array($index, $selectedTaskIndices) ? 'bg-primary/10 border border-primary/20' : 'bg-dark-700/50 border border-dark-700 hover:bg-dark-700' }}">
                            <div class="mt-0.5">
                                @if (in_array($index, $selectedTaskIndices))
                                    <svg class="w-5 h-5 text-primary-light" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10" stroke-width="2"/>
                                    </svg>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-medium text-white">{{ $task['title'] ?? 'Untitled' }}</h4>
                                @if (!empty($task['description']))
                                    <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $task['description'] }}</p>
                                @endif
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium
                                {{ ($task['priority'] ?? 'medium') === 'urgent' ? 'bg-red-500/10 text-red-400' : '' }}
                                {{ ($task['priority'] ?? 'medium') === 'high' ? 'bg-amber-500/10 text-amber-400' : '' }}
                                {{ ($task['priority'] ?? 'medium') === 'medium' ? 'bg-blue-500/10 text-blue-400' : '' }}
                                {{ ($task['priority'] ?? 'medium') === 'low' ? 'bg-emerald-500/10 text-emerald-400' : '' }}">
                                {{ ucfirst($task['priority'] ?? 'medium') }}
                            </span>
                        </div>
                    @endforeach
                </div>
                <div class="flex items-center justify-between px-6 py-4 border-t border-dark-700">
                    <p class="text-xs text-gray-500">{{ count($selectedTaskIndices) }} of {{ count($generatedTasks) }} selected</p>
                    <div class="flex items-center gap-3">
                        <button wire:click="cancelTaskPreview"
                                class="bg-dark-700 hover:bg-dark-600 text-gray-300 text-sm font-medium rounded-lg px-4 py-2.5 transition-colors">
                            Cancel
                        </button>
                        <button wire:click="approveSelectedTasks"
                                class="bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-colors"
                                {{ empty($selectedTaskIndices) ? 'disabled' : '' }}>
                            Approve Selected ({{ count($selectedTaskIndices) }})
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
