<div x-data="{
        showMenu: null,
        toggleMenu(id) {
            this.showMenu = this.showMenu === id ? null : id;
        },
        closeMenu() {
            this.showMenu = null;
        }
     }"
     @click.away="closeMenu()"
     x-init="
        $wire.on('scroll-to-bottom', () => {
            setTimeout(() => {
                const el = document.getElementById('chat-messages');
                if (el) el.scrollTop = el.scrollHeight;
            }, 100);
        });
     ">

    {{-- 1. BREADCRUMB --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-400">AI Assistant</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">Private Chat</span>
    </div>

    {{-- 2. PAGE HEADER --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">AI Chat Assistant</h1>
            <p class="text-sm text-gray-500 mt-1">Your personal AI assistant with dashboard context</p>
        </div>
        <div>
            @if($providerStatus === 'gemini')
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    Gemini Active
                </span>
            @elseif($providerStatus === 'groq')
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-blue-500/10 text-blue-400 border border-blue-500/20">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                    Groq Active
                </span>
            @else
                <a href="{{ route('admin.settings.api-keys') }}" wire:navigate
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20 hover:bg-amber-500/20 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    No AI Provider
                </a>
            @endif
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
             class="mb-4">
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

    {{-- 3. MAIN CONTENT: Two-column layout --}}
    <div class="flex gap-5" style="height: calc(100vh - 220px);">

        {{-- LEFT SIDEBAR: Conversation List --}}
        <div class="w-80 flex-shrink-0 bg-dark-800 border border-dark-700 rounded-xl flex flex-col">
            {{-- New Chat Button --}}
            <div class="p-4 border-b border-dark-700">
                <button wire:click="createConversation"
                        class="w-full inline-flex items-center justify-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Chat
                </button>
            </div>

            {{-- Search --}}
            <div class="px-4 py-3 border-b border-dark-700">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text"
                           wire:model.live.debounce.300ms="searchQuery"
                           placeholder="Search conversations..."
                           class="w-full bg-dark-700 border border-dark-600 rounded-lg pl-9 pr-4 py-2 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
                </div>
            </div>

            {{-- Conversation List --}}
            <div class="flex-1 overflow-y-auto">
                @if($conversations->isEmpty())
                    <div class="flex flex-col items-center justify-center h-full px-6 text-center">
                        <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center mb-3">
                            <svg class="w-6 h-6 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        </div>
                        <p class="text-sm text-gray-400 font-medium">No conversations yet</p>
                        <p class="text-xs text-gray-500 mt-1">Start your first conversation</p>
                    </div>
                @else
                    @foreach($conversations as $conversation)
                        <div wire:key="conv-{{ $conversation->id }}"
                             class="relative group cursor-pointer border-b border-dark-700/50 transition-colors
                                    {{ $activeConversationId === $conversation->id ? 'bg-primary/10 border-l-2 border-l-primary' : 'hover:bg-dark-700' }}"
                             wire:click="selectConversation({{ $conversation->id }})">
                            <div class="px-4 py-3">
                                <div class="flex items-start justify-between">
                                    <h3 class="text-sm font-medium text-white truncate pr-2 flex-1">
                                        {{ Str::limit($conversation->title, 30) }}
                                    </h3>
                                    {{-- Three-dot menu --}}
                                    <div class="relative" x-data @click.stop>
                                        <button @click="toggleMenu({{ $conversation->id }})"
                                                class="opacity-0 group-hover:opacity-100 p-1 rounded hover:bg-dark-600 text-gray-500 hover:text-gray-300 transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01"/></svg>
                                        </button>
                                        <div x-show="showMenu === {{ $conversation->id }}"
                                             x-transition:enter="transition ease-out duration-100"
                                             x-transition:enter-start="opacity-0 scale-95"
                                             x-transition:enter-end="opacity-100 scale-100"
                                             x-transition:leave="transition ease-in duration-75"
                                             x-transition:leave-start="opacity-100 scale-100"
                                             x-transition:leave-end="opacity-0 scale-95"
                                             @click.outside="closeMenu()"
                                             class="absolute right-0 top-8 w-40 bg-dark-700 border border-dark-600 rounded-lg shadow-xl z-50 py-1">
                                            @if($activeConversationId === $conversation->id)
                                                <button wire:click="startEditingTitle" @click="closeMenu()"
                                                        class="w-full flex items-center gap-2 px-3 py-2 text-sm text-gray-300 hover:bg-dark-600 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                    Rename
                                                </button>
                                                <button wire:click="clearMessages" wire:confirm="Clear all messages in this conversation?" @click="closeMenu()"
                                                        class="w-full flex items-center gap-2 px-3 py-2 text-sm text-gray-300 hover:bg-dark-600 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    Clear Messages
                                                </button>
                                            @endif
                                            <button wire:click="deleteConversation({{ $conversation->id }})" wire:confirm="Delete this conversation? This cannot be undone." @click="closeMenu()"
                                                    class="w-full flex items-center gap-2 px-3 py-2 text-sm text-red-400 hover:bg-dark-600 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @if($conversation->latestMessage)
                                    <p class="text-xs text-gray-500 mt-1 truncate">
                                        {{ Str::limit($conversation->latestMessage->content, 50) }}
                                    </p>
                                @endif
                                <p class="text-xs text-gray-600 mt-1">
                                    {{ $conversation->last_message_at ? $conversation->last_message_at->diffForHumans() : $conversation->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        {{-- RIGHT CHAT AREA --}}
        <div class="flex-1 bg-dark-800 border border-dark-700 rounded-xl flex flex-col min-w-0">
            @if(!$activeConversation)
                {{-- Empty State: No conversation selected --}}
                <div class="flex-1 flex flex-col items-center justify-center px-8">
                    <div class="w-16 h-16 rounded-2xl bg-primary/10 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    </div>
                    <h2 class="text-lg font-mono font-semibold text-white uppercase tracking-wider mb-2">Start a Conversation</h2>
                    <p class="text-sm text-gray-500 mb-8 text-center">Select a conversation or start a new one. Try one of these prompts:</p>

                    {{-- Suggested Prompts --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full max-w-lg">
                        <button wire:click="sendPrompt('What did I accomplish this week?')"
                                class="bg-dark-700 hover:bg-dark-600 border border-dark-600 hover:border-dark-500 rounded-xl p-4 text-left transition-all duration-200 group">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                </div>
                                <p class="text-sm text-gray-300 group-hover:text-white transition-colors">What did I accomplish this week?</p>
                            </div>
                        </button>
                        <button wire:click="sendPrompt('Summarize my pending tasks')"
                                class="bg-dark-700 hover:bg-dark-600 border border-dark-600 hover:border-dark-500 rounded-xl p-4 text-left transition-all duration-200 group">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-lg bg-amber-500/10 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                </div>
                                <p class="text-sm text-gray-300 group-hover:text-white transition-colors">Summarize my pending tasks</p>
                            </div>
                        </button>
                        <button wire:click="sendPrompt('What are my top job matches?')"
                                class="bg-dark-700 hover:bg-dark-600 border border-dark-600 hover:border-dark-500 rounded-xl p-4 text-left transition-all duration-200 group">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </div>
                                <p class="text-sm text-gray-300 group-hover:text-white transition-colors">What are my top job matches?</p>
                            </div>
                        </button>
                        <button wire:click="sendPrompt('Draft a reply to the last recruiter email')"
                                class="bg-dark-700 hover:bg-dark-600 border border-dark-600 hover:border-dark-500 rounded-xl p-4 text-left transition-all duration-200 group">
                            <div class="flex items-start gap-3">
                                <div class="w-8 h-8 rounded-lg bg-fuchsia-500/10 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-fuchsia-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </div>
                                <p class="text-sm text-gray-300 group-hover:text-white transition-colors">Draft a reply to the last recruiter email</p>
                            </div>
                        </button>
                    </div>
                </div>
            @else
                {{-- Active Conversation --}}

                {{-- Chat Header --}}
                <div class="flex items-center justify-between px-5 py-3 border-b border-dark-700">
                    <div class="flex-1 min-w-0">
                        @if($editingTitle)
                            <form wire:submit="renameConversation" class="flex items-center gap-2">
                                <input type="text"
                                       wire:model="editTitle"
                                       class="bg-dark-700 border border-dark-600 rounded-lg px-3 py-1.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent w-64"
                                       autofocus>
                                <button type="submit"
                                        class="p-1.5 rounded-lg bg-primary hover:bg-primary-hover text-white transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </button>
                                <button type="button" wire:click="cancelEditingTitle"
                                        class="p-1.5 rounded-lg bg-dark-700 hover:bg-dark-600 text-gray-400 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </form>
                            @error('editTitle')
                                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        @else
                            <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider truncate cursor-pointer hover:text-primary-light transition-colors"
                                wire:click="startEditingTitle"
                                title="Click to rename">
                                {{ $activeConversation->title }}
                            </h2>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 ml-3">
                        <button wire:click="clearMessages" wire:confirm="Clear all messages in this conversation?"
                                class="p-2 rounded-lg text-gray-500 hover:text-gray-300 hover:bg-dark-700 transition-colors"
                                title="Clear messages">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Messages Area --}}
                <div id="chat-messages" class="flex-1 overflow-y-auto px-5 py-4 space-y-4">
                    @if($messages->isEmpty() && !$isLoading)
                        {{-- Empty conversation state --}}
                        <div class="flex flex-col items-center justify-center h-full text-center">
                            <div class="w-12 h-12 rounded-xl bg-primary/10 flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                            </div>
                            <p class="text-sm text-gray-400">Send a message to start the conversation</p>
                        </div>
                    @else
                        @foreach($messages as $msg)
                            <div wire:key="msg-{{ $msg->id }}"
                                 class="flex {{ $msg->role === 'user' ? 'justify-end' : 'justify-start' }}">
                                <div class="max-w-[75%] {{ $msg->role === 'user' ? 'bg-primary/20 text-white' : 'bg-dark-700 text-gray-300' }} rounded-xl px-4 py-3">
                                    @if($msg->role === 'assistant')
                                        <div class="prose prose-invert prose-sm max-w-none
                                                    prose-p:text-gray-300 prose-p:my-1
                                                    prose-headings:text-white prose-headings:font-mono prose-headings:uppercase prose-headings:tracking-wider
                                                    prose-strong:text-white
                                                    prose-code:text-primary-light prose-code:bg-dark-800 prose-code:px-1.5 prose-code:py-0.5 prose-code:rounded
                                                    prose-pre:bg-dark-800 prose-pre:border prose-pre:border-dark-600 prose-pre:rounded-lg
                                                    prose-li:text-gray-300 prose-li:my-0.5
                                                    prose-a:text-primary-light prose-a:no-underline hover:prose-a:text-white"
                                             x-data
                                             x-html="marked.parse($el.querySelector('template').innerHTML)"
                                        >
                                            <template>{{ $msg->content }}</template>
                                        </div>
                                    @else
                                        <p class="text-sm whitespace-pre-wrap">{{ $msg->content }}</p>
                                    @endif
                                    <p class="text-xs text-gray-600 mt-2 {{ $msg->role === 'user' ? 'text-right' : '' }}">
                                        {{ $msg->created_at->format('g:i A') }}
                                        @if($msg->provider)
                                            <span class="text-gray-600">&middot; {{ ucfirst($msg->provider) }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endforeach

                        {{-- Loading indicator --}}
                        @if($isLoading)
                            <div class="flex justify-start">
                                <div class="bg-dark-700 rounded-xl px-4 py-3">
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-2 h-2 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0ms;"></div>
                                        <div class="w-2 h-2 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 150ms;"></div>
                                        <div class="w-2 h-2 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 300ms;"></div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

                {{-- Message Input --}}
                <div class="px-5 py-4 border-t border-dark-700">
                    <form wire:submit="sendMessage" class="relative">
                        <textarea wire:model="newMessage"
                                  placeholder="{{ $providerStatus ? 'Type your message...' : 'Configure an AI provider to send messages...' }}"
                                  rows="1"
                                  {{ !$providerStatus || $isLoading ? 'disabled' : '' }}
                                  class="w-full bg-dark-700 border border-dark-600 rounded-xl pl-4 pr-12 py-3 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent resize-none transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                  x-data="{
                                      resize() {
                                          $el.style.height = 'auto';
                                          $el.style.height = Math.min($el.scrollHeight, 120) + 'px';
                                      }
                                  }"
                                  x-on:input="resize()"
                                  x-on:keydown.enter.prevent="
                                      if (!$event.shiftKey) {
                                          $wire.sendMessage();
                                      } else {
                                          let start = $el.selectionStart;
                                          let end = $el.selectionEnd;
                                          $el.value = $el.value.substring(0, start) + '\n' + $el.value.substring(end);
                                          $el.selectionStart = $el.selectionEnd = start + 1;
                                          resize();
                                      }
                                  "
                        ></textarea>
                        <button type="submit"
                                class="absolute right-2 bottom-2 p-2 rounded-lg bg-primary hover:bg-primary-hover text-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                {{ !$providerStatus || $isLoading ? 'disabled' : '' }}>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        </button>
                    </form>
                    <div class="flex items-center justify-between mt-1.5 px-1">
                        <p class="text-xs text-gray-600">
                            @if($providerStatus)
                                Press Enter to send, Shift+Enter for new line
                            @else
                                <a href="{{ route('admin.settings.api-keys') }}" wire:navigate class="text-amber-400 hover:text-amber-300 transition-colors">Configure Gemini or Groq API key</a> to start chatting
                            @endif
                        </p>
                        <p class="text-xs text-gray-600">
                            {{ strlen($newMessage) }} / 5000
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Marked.js for Markdown rendering --}}
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
</div>
