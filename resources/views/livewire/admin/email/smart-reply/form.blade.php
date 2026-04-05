<div x-data="{
        copied: false,
        copyText(text) {
            navigator.clipboard.writeText(text).then(() => {
                this.copied = true;
                setTimeout(() => this.copied = false, 2000);
            });
        }
     }"
     @copy-to-clipboard.window="copyText($event.detail.text)">

    {{-- 1. BREADCRUMB --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-400">Email</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('admin.email.smart-reply.index') }}" wire:navigate class="hover:text-gray-300 transition-colors">Smart Reply Drafts</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">{{ $draftId ? 'Edit' : 'Generate' }}</span>
    </div>

    {{-- 2. PAGE HEADER --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">
                {{ $draftId ? 'Edit Smart Reply' : 'Generate Smart Reply' }}
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ $draftId ? 'Review and edit your AI-generated reply draft.' : 'Generate an AI-powered reply for an email.' }}
            </p>
        </div>
        <a href="{{ route('admin.email.smart-reply.index') }}" wire:navigate
           class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 text-gray-300 text-sm font-medium rounded-lg px-4 py-2.5 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back
        </a>
    </div>

    {{-- FLASH MESSAGES --}}
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

    {{-- Copied toast --}}
    <div x-show="copied"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed bottom-6 right-6 z-50 bg-emerald-500/90 text-white text-sm font-medium rounded-lg px-4 py-2.5 shadow-lg"
         style="display: none;">
        Copied to clipboard!
    </div>

    {{-- 3. TWO-COLUMN LAYOUT --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- LEFT COLUMN (2/3) --}}
        <div class="xl:col-span-2 space-y-6">

            {{-- Source Email Card --}}
            <div class="bg-dark-800 border border-dark-700 rounded-xl">
                <div class="px-6 py-4 border-b border-dark-700">
                    <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Source Email</h2>
                </div>
                <div class="p-6 space-y-4">
                    @if($emailId)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">From</p>
                                <p class="text-sm text-gray-300">{{ $emailFrom }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Subject</p>
                                <p class="text-sm text-gray-300">{{ $emailSubject }}</p>
                            </div>
                        </div>
                        @if($emailSnippet)
                            <div>
                                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Email Content</p>
                                <div class="bg-dark-700 rounded-lg p-4 text-sm text-gray-300 max-h-48 overflow-y-auto">
                                    {{ $emailSnippet }}
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-8">
                            <div class="w-10 h-10 rounded-lg bg-dark-700 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <p class="text-sm text-gray-500">No email selected. Go to the inbox and choose an email to reply to.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Reply Draft Card --}}
            <div class="bg-dark-800 border border-dark-700 rounded-xl">
                <div class="px-6 py-4 border-b border-dark-700">
                    <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Reply Draft</h2>
                </div>
                <div class="p-6 space-y-5">
                    @if($generatedBody)
                        {{-- Generated Body Display --}}
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Generated Reply</p>
                            <div class="bg-dark-700 rounded-lg p-4 text-sm text-gray-300 max-h-64 overflow-y-auto whitespace-pre-wrap">{{ $generatedBody }}</div>
                        </div>

                        {{-- Editable Textarea --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Edit Reply</label>
                            <textarea wire:model="editedBody" rows="8"
                                      placeholder="Edit the generated reply here..."
                                      class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent resize-none transition-all duration-200"></textarea>
                            <div class="flex items-center justify-between mt-1.5">
                                <p class="text-xs text-gray-500">{{ mb_strlen($editedBody) }} / 10,000 characters</p>
                                @if($draftId && $editedBody)
                                    <button wire:click="saveEdit"
                                            class="inline-flex items-center gap-1.5 text-xs font-medium text-primary-light hover:text-white transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Save Edit
                                    </button>
                                @endif
                            </div>
                            @error('editedBody')
                                <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    @else
                        {{-- Empty State --}}
                        <div class="text-center py-12">
                            <div class="w-12 h-12 rounded-xl bg-dark-700 flex items-center justify-center mx-auto mb-4">
                                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                            </div>
                            <h3 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-1">No Draft Generated</h3>
                            <p class="text-sm text-gray-500">Configure options on the right and click "Generate Reply" to create a draft.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN (1/3) --}}
        <div class="space-y-6">

            {{-- Options Card --}}
            <div class="bg-dark-800 border border-dark-700 rounded-xl">
                <div class="px-6 py-4 border-b border-dark-700">
                    <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Generation Options</h2>
                </div>
                <div class="p-6 space-y-5">
                    {{-- Tone Select --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            Tone <span class="text-red-400">*</span>
                        </label>
                        <select wire:model.live="tone"
                                class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                            <option value="formal">Formal</option>
                            <option value="friendly">Friendly</option>
                            <option value="brief">Brief</option>
                        </select>
                        @error('tone')
                            <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Template Select --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Template (Optional)</label>
                        <select wire:model="templateId"
                                class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                            <option value="">None</option>
                            @foreach($templates as $template)
                                <option value="{{ $template['id'] }}">{{ $template['name'] }} ({{ ucfirst(str_replace('_', ' ', $template['category'])) }})</option>
                            @endforeach
                        </select>
                        @error('templateId')
                            <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Extra Context --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Extra Instructions</label>
                        <textarea wire:model="promptContext" rows="3"
                                  placeholder="Any additional context or instructions for the AI..."
                                  class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent resize-none transition-all duration-200"></textarea>
                        <p class="text-xs text-gray-500 mt-1">{{ mb_strlen($promptContext) }} / 1,000 characters</p>
                        @error('promptContext')
                            <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Generate Button --}}
                    <button wire:click="generate"
                            wire:loading.attr="disabled"
                            @if(!$emailId) disabled @endif
                            class="w-full inline-flex items-center justify-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="generate">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </span>
                        <span wire:loading wire:target="generate">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        </span>
                        <span wire:loading.remove wire:target="generate">Generate Reply</span>
                        <span wire:loading wire:target="generate">Generating...</span>
                    </button>
                    @error('emailId')
                        <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Status Card (visible when draft exists) --}}
            @if($draftId)
                <div class="bg-dark-800 border border-dark-700 rounded-xl">
                    <div class="px-6 py-4 border-b border-dark-700">
                        <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Draft Status</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        {{-- Status Badge --}}
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-400">Status</span>
                            @php
                                $statusClasses = match($status) {
                                    'draft' => 'bg-amber-500/10 text-amber-400',
                                    'copied' => 'bg-blue-500/10 text-blue-400',
                                    'sent' => 'bg-emerald-500/10 text-emerald-400',
                                    default => 'bg-gray-500/10 text-gray-400',
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $statusClasses }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $status === 'sent' ? 'bg-emerald-400' : ($status === 'copied' ? 'bg-blue-400' : 'bg-amber-400') }}"></span>
                                {{ ucfirst($status) }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Actions Card --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl">
                    <div class="px-6 py-4 border-b border-dark-700">
                        <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Actions</h2>
                    </div>
                    <div class="p-6 space-y-3">
                        {{-- Copy to Clipboard --}}
                        <button wire:click="copyToClipboard"
                                class="w-full inline-flex items-center justify-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            Copy to Clipboard
                        </button>

                        {{-- Mark as Sent --}}
                        @if($status !== 'sent')
                            <button wire:click="markSent"
                                    class="w-full inline-flex items-center justify-center gap-2 bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 hover:text-emerald-300 text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Mark as Sent
                            </button>
                        @endif

                        {{-- Regenerate --}}
                        <button wire:click="generate"
                                wire:loading.attr="disabled"
                                class="w-full inline-flex items-center justify-center gap-2 bg-dark-700 hover:bg-dark-600 border border-dark-600 text-gray-300 hover:text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200">
                            <span wire:loading.remove wire:target="generate">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            </span>
                            <span wire:loading wire:target="generate">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            </span>
                            <span wire:loading.remove wire:target="generate">Regenerate</span>
                            <span wire:loading wire:target="generate">Generating...</span>
                        </button>

                        {{-- Delete --}}
                        <x-admin.confirm-button
                            title="Delete Draft?"
                            text="This draft will be permanently removed."
                            action="$wire.deleteDraft()"
                            confirm-text="Yes, delete it"
                            class="w-full inline-flex items-center justify-center gap-1.5 bg-red-500/10 hover:bg-red-500/20 text-red-400 hover:text-red-300 text-sm font-medium rounded-lg px-4 py-2.5 transition-all duration-200"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Delete Draft
                        </x-admin.confirm-button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
