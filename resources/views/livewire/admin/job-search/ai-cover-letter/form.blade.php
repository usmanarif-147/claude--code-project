<div x-data="{
    copied: false,
    copyToClipboard() {
        const content = $wire.content;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(content).then(() => {
                this.copied = true;
                setTimeout(() => this.copied = false, 2000);
            });
        } else {
            const textarea = document.createElement('textarea');
            textarea.value = content;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            this.copied = true;
            setTimeout(() => this.copied = false, 2000);
        }
    }
}">
    {{-- 1. BREADCRUMB --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-400">Job Search</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('admin.job-search.cover-letters.index') }}" wire:navigate class="hover:text-gray-300 transition-colors">Cover Letters</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">{{ $coverLetterId ? 'Edit' : 'Generate' }}</span>
    </div>

    {{-- 2. PAGE HEADER --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">
                {{ $coverLetterId ? 'Edit Cover Letter' : 'Generate Cover Letter' }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ $coverLetterId ? 'Review and edit your cover letter.' : 'Create a personalized cover letter using AI.' }}
            </p>
        </div>
        <a href="{{ route('admin.job-search.cover-letters.index') }}" wire:navigate
           class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 text-gray-300 text-sm font-medium rounded-lg px-4 py-2.5 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back
        </a>
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
                    <button @click="show = false" class="ml-auto text-emerald-400/60 hover:text-emerald-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-center gap-3 bg-red-500/10 border border-red-500/20 text-red-400 rounded-lg px-4 py-3 text-sm">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p>{{ session('error') }}</p>
                    <button @click="show = false" class="ml-auto text-red-400/60 hover:text-red-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            @endif
        </div>
    @endif

    {{-- 3. FULL-WIDTH FORM LAYOUT --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- MAIN CONTENT (2/3) --}}
        <div class="xl:col-span-2 space-y-6">

            {{-- 3a. JOB SELECTION CARD (create mode only) --}}
            @if(!$coverLetterId)
                <div class="bg-dark-800 border border-dark-700 rounded-xl">
                    <div class="px-6 py-4 border-b border-dark-700">
                        <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Select Job Listing</h2>
                    </div>
                    <div class="p-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                Job Listing <span class="text-red-400">*</span>
                            </label>
                            <select wire:model.live="jobListingId"
                                    class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                                <option value="">Choose a job listing...</option>
                                @foreach($this->jobListings as $job)
                                    <option value="{{ $job->id }}">{{ $job->title }}{{ $job->company_name ? ' - '.$job->company_name : '' }}</option>
                                @endforeach
                            </select>
                            @error('jobListingId')
                                <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        @if($selectedJobTitle)
                            <div class="mt-4 p-4 bg-dark-700 rounded-lg border border-dark-600">
                                <p class="text-sm font-medium text-white">{{ $selectedJobTitle }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- 3b. COVER LETTER CONTENT CARD --}}
            <div class="bg-dark-800 border border-dark-700 rounded-xl">
                <div class="px-6 py-4 border-b border-dark-700">
                    <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Cover Letter</h2>
                </div>
                <div class="p-6 relative">
                    {{-- Loading overlay --}}
                    <div wire:loading wire:target="generate"
                         class="absolute inset-0 bg-dark-800/80 flex items-center justify-center z-10 rounded-b-xl">
                        <div class="flex flex-col items-center gap-3">
                            <svg class="animate-spin w-8 h-8 text-primary" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="text-sm text-gray-400">Generating your cover letter...</p>
                        </div>
                    </div>

                    <textarea wire:model="content" rows="18"
                              placeholder="Click 'Generate' to create your cover letter..."
                              class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-3 text-white text-sm leading-relaxed placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent resize-none transition-all duration-200"
                              style="min-height: 400px;"></textarea>
                    @error('content')
                        <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                    <p class="mt-2 text-xs text-gray-500">{{ \Illuminate\Support\Str::length($content) }} characters</p>
                </div>
            </div>
        </div>

        {{-- SIDEBAR (1/3) --}}
        <div class="space-y-6">

            {{-- 3c. AI PROVIDER CARD --}}
            <div class="bg-dark-800 border border-dark-700 rounded-xl">
                <div class="px-6 py-4 border-b border-dark-700">
                    <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">AI Provider</h2>
                </div>
                <div class="p-6">
                    @if(count($this->availableProviders) === 0)
                        <div class="p-4 bg-amber-500/10 border border-amber-500/20 rounded-lg">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                                <div>
                                    <p class="text-sm text-amber-400 font-medium">No AI API keys configured</p>
                                    <p class="text-xs text-gray-400 mt-1">Add a Claude or OpenAI key in Settings to generate cover letters.</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($this->availableProviders as $provider)
                                <label class="flex items-center gap-3 p-3 bg-dark-700 rounded-lg cursor-pointer hover:bg-dark-600 transition-colors {{ $aiProvider === $provider ? 'ring-2 ring-primary border-transparent' : 'border border-dark-600' }}">
                                    <input type="radio" wire:model.live="aiProvider" value="{{ $provider }}"
                                           class="text-primary focus:ring-primary focus:ring-offset-0 bg-dark-600 border-dark-500"
                                           {{ count($this->availableProviders) === 1 ? 'checked' : '' }}>
                                    <div>
                                        <p class="text-sm font-medium text-white capitalize">{{ $provider }}</p>
                                        @if($provider === 'claude')
                                            <p class="text-xs text-gray-500">Anthropic Claude</p>
                                        @else
                                            <p class="text-xs text-gray-500">OpenAI GPT-4o</p>
                                        @endif
                                    </div>
                                </label>
                            @endforeach

                            @if(count($this->availableProviders) === 1)
                                <p class="text-xs text-gray-500 mt-2">Only provider with a connected API key.</p>
                            @endif
                        </div>
                    @endif
                    @error('aiProvider')
                        <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            {{-- 3d. ACTIONS CARD --}}
            <div class="bg-dark-800 border border-dark-700 rounded-xl">
                <div class="px-6 py-4 border-b border-dark-700">
                    <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Actions</h2>
                </div>
                <div class="p-6 space-y-3">
                    {{-- Generate Button --}}
                    @if(!$coverLetterId || $coverLetterId)
                        <button wire:click="generate"
                                wire:loading.attr="disabled"
                                wire:target="generate"
                                {{ !$jobListingId || count($this->availableProviders) === 0 ? 'disabled' : '' }}
                                class="w-full inline-flex items-center justify-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="generate">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </span>
                            <span wire:loading wire:target="generate">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                            <span wire:loading.remove wire:target="generate">{{ $coverLetterId ? 'Regenerate' : 'Generate' }}</span>
                            <span wire:loading wire:target="generate">Generating...</span>
                        </button>
                    @endif

                    {{-- Save Changes Button --}}
                    @if($coverLetterId && $content)
                        <button wire:click="save"
                                wire:loading.attr="disabled"
                                wire:target="save"
                                class="w-full inline-flex items-center justify-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="save">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <span wire:loading wire:target="save">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                            <span wire:loading.remove wire:target="save">Save Changes</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </button>
                    @endif

                    {{-- Copy to Clipboard --}}
                    @if($content)
                        <button @click="copyToClipboard()"
                                class="w-full inline-flex items-center justify-center gap-2 bg-dark-700 hover:bg-dark-600 border border-dark-600 text-gray-300 hover:text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200">
                            <template x-if="!copied">
                                <span class="inline-flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    Copy to Clipboard
                                </span>
                            </template>
                            <template x-if="copied">
                                <span class="inline-flex items-center gap-2 text-emerald-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Copied!
                                </span>
                            </template>
                        </button>
                    @endif

                    {{-- Download PDF --}}
                    @if($coverLetterId)
                        <button wire:click="downloadPdf"
                                wire:loading.attr="disabled"
                                wire:target="downloadPdf"
                                class="w-full inline-flex items-center justify-center gap-2 bg-dark-700 hover:bg-dark-600 border border-dark-600 text-gray-300 hover:text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200">
                            <span wire:loading.remove wire:target="downloadPdf">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </span>
                            <span wire:loading wire:target="downloadPdf">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                            <span wire:loading.remove wire:target="downloadPdf">Download PDF</span>
                            <span wire:loading wire:target="downloadPdf">Downloading...</span>
                        </button>
                    @endif
                </div>
            </div>

            {{-- 3e. JOB SUMMARY CARD --}}
            @if($jobListingId || $coverLetterId)
                @php
                    $selectedJob = $jobListingId ? \App\Models\JobSearch\JobListing::find($jobListingId) : null;
                    $coverLetterModel = $coverLetterId ? \App\Models\JobSearch\CoverLetter::find($coverLetterId) : null;
                @endphp
                <div class="bg-dark-800 border border-dark-700 rounded-xl">
                    <div class="px-6 py-4 border-b border-dark-700">
                        <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Job Details</h2>
                    </div>
                    <div class="p-6 space-y-3">
                        @if($selectedJob)
                            <p class="text-sm font-medium text-white">{{ $selectedJob->title }}</p>
                            @if($selectedJob->company_name)
                                <p class="text-sm text-gray-400">{{ $selectedJob->company_name }}</p>
                            @endif
                            @if($selectedJob->location)
                                <p class="text-xs text-gray-500">{{ $selectedJob->location }}</p>
                            @endif
                            @if($selectedJob->tech_stack && is_array($selectedJob->tech_stack) && count($selectedJob->tech_stack) > 0)
                                <div class="flex flex-wrap gap-1.5 mt-2">
                                    @foreach($selectedJob->tech_stack as $tech)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-dark-700 text-gray-300">{{ $tech }}</span>
                                    @endforeach
                                </div>
                            @endif
                            @if($selectedJob->job_url)
                                <a href="{{ $selectedJob->job_url }}" target="_blank" rel="noopener noreferrer"
                                   class="inline-flex items-center gap-1 text-xs text-primary-light hover:text-white transition-colors mt-2">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    View Original Listing
                                </a>
                            @endif
                        @elseif($coverLetterModel)
                            <p class="text-sm font-medium text-white">{{ $coverLetterModel->job_title }}</p>
                            @if($coverLetterModel->company_name)
                                <p class="text-sm text-gray-400">{{ $coverLetterModel->company_name }}</p>
                            @endif
                            @if($coverLetterModel->jobListing)
                                @if($coverLetterModel->jobListing->location)
                                    <p class="text-xs text-gray-500">{{ $coverLetterModel->jobListing->location }}</p>
                                @endif
                                @if($coverLetterModel->jobListing->tech_stack && is_array($coverLetterModel->jobListing->tech_stack) && count($coverLetterModel->jobListing->tech_stack) > 0)
                                    <div class="flex flex-wrap gap-1.5 mt-2">
                                        @foreach($coverLetterModel->jobListing->tech_stack as $tech)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-dark-700 text-gray-300">{{ $tech }}</span>
                                        @endforeach
                                    </div>
                                @endif
                                @if($coverLetterModel->jobListing->job_url)
                                    <a href="{{ $coverLetterModel->jobListing->job_url }}" target="_blank" rel="noopener noreferrer"
                                       class="inline-flex items-center gap-1 text-xs text-primary-light hover:text-white transition-colors mt-2">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        View Original Listing
                                    </a>
                                @endif
                            @else
                                <p class="text-xs text-gray-500 mt-1">Original listing no longer available.</p>
                            @endif
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
