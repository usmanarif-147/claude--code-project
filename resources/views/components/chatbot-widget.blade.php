<div x-data="chatbot()" x-cloak>
    {{-- Chat Toggle Button --}}
    <button @click="toggle()"
            class="fixed bottom-6 right-6 z-50 w-14 h-14 bg-accent hover:bg-accent-light text-black rounded-full shadow-lg shadow-accent/20 flex items-center justify-center transition-all duration-300 hover:scale-110"
            :class="open ? 'rotate-0' : ''">
        <template x-if="!open">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
        </template>
        <template x-if="open">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </template>
    </button>

    {{-- Chat Panel --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="fixed bottom-24 right-6 z-50 w-[380px] max-w-[calc(100vw-2rem)] h-[500px] bg-dark-800 border border-white/[0.04] rounded-2xl shadow-2xl shadow-black/40 flex flex-col overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-white/[0.06] bg-dark-800">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-accent/20 flex items-center justify-center">
                    <svg class="w-4 h-4 text-accent-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-white">Chat with AI</h3>
                    <p class="text-xs text-gray-500">Ask about my skills & experience</p>
                </div>
            </div>
            <button @click="toggle()" class="text-gray-400 hover:text-white transition-colors p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Messages --}}
        <div x-ref="messageContainer" class="flex-1 overflow-y-auto px-4 py-4 space-y-3">
            <template x-for="(msg, index) in messages" :key="index">
                <div :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                    <div :class="msg.role === 'user'
                        ? 'bg-accent/20 border border-accent/10 text-white'
                        : 'bg-white/[0.04] border border-white/[0.04] text-gray-300'"
                         class="max-w-[85%] rounded-2xl px-4 py-2.5 text-sm whitespace-pre-wrap"
                         x-text="msg.content">
                    </div>
                </div>
            </template>

            {{-- Typing indicator --}}
            <div x-show="loading" class="flex justify-start">
                <div class="bg-white/[0.04] border border-white/[0.04] rounded-2xl px-4 py-3 flex items-center gap-1.5">
                    <span class="w-2 h-2 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                    <span class="w-2 h-2 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                    <span class="w-2 h-2 bg-gray-500 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                </div>
            </div>
        </div>

        {{-- Input Area --}}
        <div class="px-4 py-3 border-t border-white/[0.06]">
            <form @submit.prevent="sendMessage()" class="flex items-end gap-2">
                <textarea x-model="inputText"
                          @keydown.enter.prevent="if (!$event.shiftKey) sendMessage()"
                          placeholder="Type your message..."
                          rows="1"
                          class="flex-1 bg-white/[0.06] border border-white/[0.08] rounded-xl px-4 py-2.5 text-sm text-white placeholder-gray-500 focus:ring-2 focus:ring-accent focus:border-transparent resize-none"
                          :disabled="loading"
                          x-ref="chatInput"></textarea>
                <button type="submit"
                        :disabled="loading || !inputText.trim()"
                        class="bg-accent hover:bg-accent-light text-black rounded-lg px-3 py-2.5 transition-colors disabled:opacity-50 disabled:cursor-not-allowed shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function chatbot() {
        return {
            open: false,
            messages: [],
            inputText: '',
            loading: false,
            visitorUuid: '',
            conversationId: null,

            init() {
                let uuid = localStorage.getItem('chatbot_visitor_uuid');
                if (!uuid) {
                    uuid = this.generateUUID();
                    localStorage.setItem('chatbot_visitor_uuid', uuid);
                }
                this.visitorUuid = uuid;

                const saved = localStorage.getItem('chatbot_messages');
                if (saved) {
                    try {
                        this.messages = JSON.parse(saved);
                    } catch (e) {
                        this.messages = [];
                    }
                }

                const savedConvId = localStorage.getItem('chatbot_conversation_id');
                if (savedConvId) {
                    this.conversationId = parseInt(savedConvId);
                }

                if (this.messages.length === 0) {
                    this.messages.push({
                        role: 'assistant',
                        content: 'Hi there! I\'m an AI assistant for this portfolio. Feel free to ask me about skills, experience, projects, or anything else you see here.'
                    });
                    this.saveMessages();
                }
            },

            toggle() {
                this.open = !this.open;
                if (this.open) {
                    this.$nextTick(() => this.scrollToBottom());
                }
            },

            async sendMessage() {
                const text = this.inputText.trim();
                if (!text || this.loading) return;

                this.messages.push({ role: 'user', content: text });
                this.inputText = '';
                this.loading = true;
                this.saveMessages();
                this.$nextTick(() => this.scrollToBottom());

                try {
                    const response = await fetch('/chatbot/message', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            message: text,
                            visitor_uuid: this.visitorUuid
                        })
                    });

                    if (response.status === 429) {
                        this.messages.push({
                            role: 'assistant',
                            content: 'Too many requests. Please wait a moment and try again.'
                        });
                    } else if (response.ok) {
                        const data = await response.json();
                        this.messages.push({
                            role: 'assistant',
                            content: data.reply
                        });
                        if (data.conversation_id) {
                            this.conversationId = data.conversation_id;
                            localStorage.setItem('chatbot_conversation_id', data.conversation_id);
                        }
                    } else {
                        const data = await response.json().catch(() => ({}));
                        this.messages.push({
                            role: 'assistant',
                            content: data.error || 'Sorry, something went wrong. Please try again.'
                        });
                    }
                } catch (error) {
                    this.messages.push({
                        role: 'assistant',
                        content: 'Unable to connect. Please check your internet and try again.'
                    });
                }

                this.loading = false;
                this.saveMessages();
                this.$nextTick(() => this.scrollToBottom());
            },

            scrollToBottom() {
                if (this.$refs.messageContainer) {
                    this.$refs.messageContainer.scrollTop = this.$refs.messageContainer.scrollHeight;
                }
            },

            saveMessages() {
                localStorage.setItem('chatbot_messages', JSON.stringify(this.messages));
            },

            generateUUID() {
                return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                    const r = Math.random() * 16 | 0;
                    const v = c === 'x' ? r : (r & 0x3 | 0x8);
                    return v.toString(16);
                });
            }
        };
    }
</script>
