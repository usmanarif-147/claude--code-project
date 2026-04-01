<div>
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-400">AI Assistant</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">Chat Logs</span>
    </div>

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Chat Logs</h1>
            <p class="text-sm text-gray-500 mt-1">Review visitor chatbot conversations.</p>
        </div>
    </div>

    {{-- Search --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by title or visitor UUID..."
                       class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
            </div>
        </div>
    </div>

    {{-- Split Panel --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Left Panel: Conversation List --}}
        <div class="lg:col-span-1">
            <div class="bg-dark-800 border border-dark-700 rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-dark-700">
                    <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider">Conversations</h2>
                </div>

                <div class="max-h-[600px] overflow-y-auto">
                    @forelse ($conversations as $conversation)
                        <button wire:click="selectConversation({{ $conversation->id }})"
                                class="w-full text-left px-5 py-4 border-b border-dark-700/50 hover:bg-dark-700/30 transition-colors {{ $selectedConversationId === $conversation->id ? 'bg-primary/10 border-l-2 border-l-primary' : '' }}">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-white truncate">
                                        {{ $conversation->title ?? 'Untitled Conversation' }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1 font-mono">
                                        {{ Str::limit($conversation->visitor_uuid, 8, '...') }}
                                    </p>
                                </div>
                                <div class="flex flex-col items-end gap-1 shrink-0">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary-light">
                                        {{ $conversation->message_count }} msgs
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        {{ $conversation->last_message_at ? $conversation->last_message_at->diffForHumans() : $conversation->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                        </button>
                    @empty
                        <div class="px-6 py-12 text-center">
                            <svg class="w-12 h-12 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <p class="text-gray-500 text-sm">No conversations yet.</p>
                        </div>
                    @endforelse
                </div>

                @if ($conversations->hasPages())
                    <div class="px-4 py-3 border-t border-dark-700">
                        {{ $conversations->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Right Panel: Message Thread --}}
        <div class="lg:col-span-2">
            <div class="bg-dark-800 border border-dark-700 rounded-xl overflow-hidden">
                @if ($selectedConversation)
                    {{-- Conversation Header --}}
                    <div class="px-6 py-4 border-b border-dark-700 flex items-start justify-between">
                        <div>
                            <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider">
                                {{ $selectedConversation->title ?? 'Untitled Conversation' }}
                            </h2>
                            <div class="flex flex-wrap items-center gap-3 mt-2 text-xs text-gray-500">
                                <span class="font-mono">UUID: {{ Str::limit($selectedConversation->visitor_uuid, 16, '...') }}</span>
                                @if ($selectedConversation->visitor_ip)
                                    <span>IP: {{ $selectedConversation->visitor_ip }}</span>
                                @endif
                                <span>Started: {{ $selectedConversation->created_at->format('M d, Y H:i') }}</span>
                            </div>
                        </div>
                        <button wire:click="clearSelection" class="text-gray-400 hover:text-white transition-colors p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Message List --}}
                    <div class="p-6 max-h-[500px] overflow-y-auto space-y-4">
                        @forelse ($selectedConversation->messages as $message)
                            <div class="flex {{ $message->role === 'user' ? 'justify-end' : 'justify-start' }}">
                                <div class="max-w-[80%] {{ $message->role === 'user' ? 'bg-primary/10 border border-primary/20' : 'bg-dark-700 border border-dark-600' }} rounded-xl px-4 py-3">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xs font-medium {{ $message->role === 'user' ? 'text-primary-light' : 'text-gray-400' }} uppercase">
                                            {{ $message->role }}
                                        </span>
                                        @if ($message->ai_provider)
                                            <span class="text-xs text-gray-600">({{ $message->ai_provider }})</span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-300 whitespace-pre-wrap">{{ $message->content }}</p>
                                    <p class="text-xs text-gray-600 mt-2">{{ $message->created_at->format('H:i:s') }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-gray-500 text-sm py-8">No messages in this conversation.</p>
                        @endforelse
                    </div>
                @else
                    {{-- Empty State --}}
                    <div class="px-6 py-24 text-center">
                        <svg class="w-16 h-16 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <h3 class="text-lg font-mono font-semibold text-gray-400 uppercase tracking-wider mb-2">Select a Conversation</h3>
                        <p class="text-sm text-gray-500">Choose a conversation from the list to view its messages.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
