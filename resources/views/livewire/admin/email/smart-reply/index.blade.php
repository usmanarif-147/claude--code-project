<div>
    {{-- 1. BREADCRUMB --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-400">Email</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">Smart Reply Drafts</span>
    </div>

    {{-- 2. PAGE HEADER --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Smart Reply Drafts</h1>
            <p class="text-sm text-gray-500 mt-1">AI-generated reply drafts for your emails.</p>
        </div>
        <a href="{{ route('admin.email.smart-reply.create') }}" wire:navigate
           class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Reply
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

    {{-- 3. FILTER BAR --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl p-4 mb-5">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Search by email subject, sender, or draft body..."
                       class="w-full bg-dark-700 border border-dark-600 rounded-lg pl-9 pr-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
            </div>
            <select wire:model.live="filterStatus"
                    class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-primary focus:border-transparent transition-all min-w-[140px]">
                <option value="">All Status</option>
                <option value="draft">Draft</option>
                <option value="copied">Copied</option>
                <option value="sent">Sent</option>
            </select>
            <select wire:model.live="filterTone"
                    class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-primary focus:border-transparent transition-all min-w-[140px]">
                <option value="">All Tones</option>
                <option value="formal">Formal</option>
                <option value="friendly">Friendly</option>
                <option value="brief">Brief</option>
            </select>
        </div>
    </div>

    {{-- 4. DRAFTS TABLE --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl overflow-hidden"
         x-data="{
             copyText(text) {
                 navigator.clipboard.writeText(text).then(() => {
                     $dispatch('notify', { message: 'Copied!' });
                 });
             }
         }"
         @copy-to-clipboard.window="copyText($event.detail.text)">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-dark-700">
                        <th class="px-6 py-4 text-left text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-4 text-left text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">Tone</th>
                        <th class="px-6 py-4 text-left text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">Template</th>
                        <th class="px-6 py-4 text-left text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">Generated</th>
                        <th class="px-6 py-4 text-right text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700/50">
                    @forelse($drafts as $draft)
                        <tr class="hover:bg-dark-700/30 transition-colors duration-150 group">
                            {{-- Email --}}
                            <td class="px-6 py-4 max-w-xs">
                                <div>
                                    <p class="text-sm font-medium text-white truncate">{{ $draft->email->subject ?? '(No Subject)' }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ $draft->email->from_name ?? $draft->email->from_email ?? '--' }}</p>
                                </div>
                            </td>

                            {{-- Tone --}}
                            <td class="px-6 py-4">
                                @php
                                    $toneClasses = match($draft->tone) {
                                        'formal' => 'bg-blue-500/10 text-blue-400',
                                        'friendly' => 'bg-emerald-500/10 text-emerald-400',
                                        'brief' => 'bg-amber-500/10 text-amber-400',
                                        default => 'bg-gray-500/10 text-gray-400',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $toneClasses }}">
                                    {{ ucfirst($draft->tone) }}
                                </span>
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-4">
                                @php
                                    $statusClasses = match($draft->status) {
                                        'draft' => 'bg-amber-500/10 text-amber-400',
                                        'copied' => 'bg-blue-500/10 text-blue-400',
                                        'sent' => 'bg-emerald-500/10 text-emerald-400',
                                        default => 'bg-gray-500/10 text-gray-400',
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $statusClasses }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $draft->status === 'sent' ? 'bg-emerald-400' : ($draft->status === 'copied' ? 'bg-blue-400' : 'bg-amber-400') }}"></span>
                                    {{ ucfirst($draft->status) }}
                                </span>
                            </td>

                            {{-- Template --}}
                            <td class="px-6 py-4 text-sm text-gray-400">
                                {{ $draft->template->name ?? 'None' }}
                            </td>

                            {{-- Generated --}}
                            <td class="px-6 py-4 text-sm text-gray-400 whitespace-nowrap">
                                {{ $draft->created_at->diffForHumans() }}
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    {{-- Edit --}}
                                    <a href="{{ route('admin.email.smart-reply.edit', $draft) }}" wire:navigate
                                       class="p-2 text-gray-400 hover:text-primary-light hover:bg-primary/10 rounded-lg transition-all duration-200"
                                       title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    {{-- Copy --}}
                                    <button wire:click="markCopied({{ $draft->id }})"
                                            class="p-2 text-gray-400 hover:text-blue-400 hover:bg-blue-500/10 rounded-lg transition-all duration-200"
                                            title="Copy to Clipboard">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    </button>
                                    {{-- Delete --}}
                                    <button wire:click="deleteDraft({{ $draft->id }})" wire:confirm="Are you sure you want to delete this draft?"
                                            class="p-2 text-gray-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-all duration-200"
                                            title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="w-12 h-12 rounded-xl bg-dark-700 flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                </div>
                                <h3 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-1">No smart reply drafts yet</h3>
                                <p class="text-sm text-gray-500 mb-4">Generate your first AI-powered reply draft.</p>
                                <a href="{{ route('admin.email.smart-reply.create') }}" wire:navigate
                                   class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-all duration-200">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    Generate Your First Reply
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($drafts->hasPages())
            <div class="px-6 py-4 border-t border-dark-700 flex items-center justify-between">
                <p class="text-sm text-gray-500">Showing {{ $drafts->firstItem() }}--{{ $drafts->lastItem() }} of {{ $drafts->total() }} results</p>
                {{ $drafts->links() }}
            </div>
        @endif
    </div>
</div>
