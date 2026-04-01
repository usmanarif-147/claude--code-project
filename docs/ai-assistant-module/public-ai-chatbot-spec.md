# Public AI Chatbot — Spec

Side: BOTH

---

## 1. MODULE OVERVIEW

A public-facing AI chatbot widget on the portfolio landing page that lets visitors ask questions about the portfolio owner's skills, experience, projects, and background. The AI answers using cached portfolio data as context, keeping conversations professional and relevant. An admin chat log viewer lets the portfolio owner review all visitor conversations.

### Features
- Floating chat widget on public portfolio page (Alpine.js + fetch API)
- AI-powered responses using Google Gemini API (primary) / Groq API (backup)
- Portfolio context building from existing models (Profile, Skills, Technologies, Experiences, Projects, Testimonials), cached for 1 hour
- Visitor identification via localStorage UUID (no login required)
- Rate limiting at 20 requests/minute per IP
- Admin chat log viewer to browse visitor conversations and messages

### Public Features (what visitors can do)
- Open/close a floating chat widget on the portfolio page
- Send messages and receive AI-generated answers about the portfolio owner
- Conversation persists across page reloads via localStorage UUID
- No login required

### Admin Features (what admin can do)
- View list of all chatbot conversations with visitor info
- Read full message history for any conversation
- See conversation metadata (visitor UUID, IP, message count, timestamps)
- Search and filter conversations

---

## 2. DATABASE SCHEMA

```
Table: chatbot_conversations
Columns:
  - id (bigint, primary key, auto increment)
  - visitor_uuid (char(36), required) — localStorage UUID identifying the visitor
  - visitor_ip (string(45), nullable) — visitor IP address for rate limiting context
  - visitor_user_agent (text, nullable) — browser user agent string
  - title (string(255), nullable) — auto-generated from first message, truncated
  - message_count (integer, default: 0) — denormalized count for list display
  - last_message_at (timestamp, nullable) — timestamp of most recent message
  - created_at, updated_at (timestamps)

Indexes:
  - index on visitor_uuid
  - index on last_message_at (for sorting)
  - index on created_at

Foreign keys: none
```

```
Table: chatbot_messages
Columns:
  - id (bigint, primary key, auto increment)
  - chatbot_conversation_id (bigint unsigned, required) — FK to chatbot_conversations
  - role (string(20), required) — 'user' or 'assistant'
  - content (text, required) — message body
  - tokens_used (integer, nullable) — token count from AI response (null for user messages)
  - ai_provider (string(20), nullable) — 'gemini' or 'groq' (null for user messages)
  - created_at, updated_at (timestamps)

Indexes:
  - index on chatbot_conversation_id
  - index on created_at

Foreign keys:
  - chatbot_conversation_id references chatbot_conversations(id) on delete cascade
```

---

## 3. FILE MAP

```
MIGRATIONS:
  - database/migrations/2026_04_01_500003_create_chatbot_conversations_table.php
  - database/migrations/2026_04_01_500004_create_chatbot_messages_table.php

MODELS (2 related models — subfolder):
  - app/Models/Chatbot/ChatbotConversation.php
    - fillable: visitor_uuid, visitor_ip, visitor_user_agent, title, message_count, last_message_at
    - casts: last_message_at → datetime, message_count → integer
    - relationships: hasMany(ChatbotMessage)

  - app/Models/Chatbot/ChatbotMessage.php
    - fillable: chatbot_conversation_id, role, content, tokens_used, ai_provider
    - casts: tokens_used → integer
    - relationships: belongsTo(ChatbotConversation)

SERVICES:
  - app/Services/ChatbotService.php
    - getOrCreateConversation(string $visitorUuid, ?string $ip, ?string $userAgent): ChatbotConversation — find existing or create new conversation for visitor
    - addMessage(ChatbotConversation $conversation, string $role, string $content, ?int $tokens, ?string $provider): ChatbotMessage — store a message and update conversation denormalized fields
    - getConversations(?string $search, int $perPage): LengthAwarePaginator — paginated list for admin, ordered by last_message_at desc
    - getConversationWithMessages(int $conversationId): ChatbotConversation — single conversation with all messages loaded
    - generateTitle(string $firstMessage): string — truncate first user message to create conversation title

  - app/Services/AiChatbotService.php
    - chat(ChatbotConversation $conversation, string $userMessage): string — orchestrates: build context, get history, call AI, return response
    - buildPortfolioContext(): string — fetches Profile, Skills, Technologies, Experiences, Projects, Testimonials and formats as system prompt context (cached 1 hour)
    - getConversationHistory(ChatbotConversation $conversation, int $limit): array — get recent messages formatted for AI provider
    - callGemini(string $systemPrompt, array $messages): array — call Google Gemini API, returns ['content' => string, 'tokens' => int]
    - callGroq(string $systemPrompt, array $messages): array — call Groq API as fallback, returns ['content' => string, 'tokens' => int]
    - getSystemPrompt(string $portfolioContext): string — combines portfolio context with behavioral instructions (professional, relevant, no hallucination)

--- PUBLIC FILES ---

CONTROLLER:
  - app/Http/Controllers/ChatbotController.php (new dedicated controller — NOT added to PortfolioController)
    - sendMessage(Request $request): JsonResponse — POST /chatbot/message — validates input, calls services, returns AI response as JSON

VIEWS (public):
  - resources/views/components/chatbot-widget.blade.php (Blade component)
    - Floating chat bubble + expandable chat panel
    - Pure Alpine.js — NO Livewire
    - Uses fetch API to POST to /chatbot/message

ROUTES (public):
  - routes/web.php (add one route)
    - POST /chatbot/message → ChatbotController@sendMessage → route name: chatbot.message (rate limited, no auth)

--- ADMIN FILES ---

LIVEWIRE COMPONENTS:
  - app/Livewire/Admin/AiAssistant/ChatLogs/ChatLogIndex.php
    - public properties: $search, $selectedConversationId, $selectedConversation
    - methods: mount(), getConversationsProperty(), selectConversation($id), clearSelection()
    - Uses ChatbotService for data fetching

VIEWS (admin):
  - resources/views/livewire/admin/ai-assistant/chat-logs/index.blade.php
    - Split panel: conversation list on left, message thread on right
    - Search bar to filter conversations
    - Conversation list shows title, visitor UUID (truncated), message count, last activity
    - Message thread shows user/assistant messages in chat bubble style

ROUTES (admin):
  - routes/admin/ai-assistant/chat-logs.php
    - GET /admin/ai-assistant/chat-logs → ChatLogIndex → admin.ai-assistant.chat-logs.index
```

---

## 4. COMPONENT CONTRACTS

### Public Controller

```
Controller: App\Http\Controllers\ChatbotController (new dedicated controller)

Method: sendMessage(Request $request): JsonResponse
  Input: JSON body with { message: string, visitor_uuid: string }
  Validates:
    - message: required|string|max:1000
    - visitor_uuid: required|string|uuid
  Does:
    1. Validate request
    2. Call ChatbotService::getOrCreateConversation(visitor_uuid, ip, user_agent)
    3. Call ChatbotService::addMessage(conversation, 'user', message)
    4. Call AiChatbotService::chat(conversation, message)
    5. Call ChatbotService::addMessage(conversation, 'assistant', response, tokens, provider)
    6. Return JSON { reply: string, conversation_id: int }
  Error handling:
    - Rate limit exceeded → 429 with { error: 'Too many requests. Please wait a moment.' }
    - AI provider failure → 500 with { error: 'Sorry, I am unable to respond right now. Please try again later.' }
    - Validation failure → 422 with standard Laravel validation errors
```

### Admin Livewire Component

```
Component: App\Livewire\Admin\AiAssistant\ChatLogs\ChatLogIndex
Namespace: App\Livewire\Admin\AiAssistant\ChatLogs

Layout: #[Layout('components.layouts.admin')]

Properties:
  - $search (string, #[Url]) — search filter for conversations
  - $selectedConversationId (int|null) — currently selected conversation ID
  - $selectedConversation (ChatbotConversation|null) — loaded conversation with messages

Methods:
  - mount()
    Does: initialize properties

  - getConversationsProperty() (computed)
    Does: calls ChatbotService::getConversations($this->search, 20)
    Output: paginated conversation list

  - selectConversation(int $id)
    Does: calls ChatbotService::getConversationWithMessages($id), sets selectedConversation
    Output: updates right panel with message thread

  - clearSelection()
    Does: resets selectedConversationId and selectedConversation to null

No validation rules (read-only view).
```

---

## 5. VIEW BLUEPRINTS

### Public View — Chat Widget

```
View: resources/views/components/chatbot-widget.blade.php
Layout: included in welcome.blade.php (components.layouts.app)
Side: PUBLIC

Design rules (from CLAUDE.md public side):
  - Cards: rounded-2xl, border-white/[0.04]
  - Color alias: accent / accent-light
  - NO Livewire — pure Alpine.js + fetch API
  - Dark theme consistent with portfolio page

Sections:
  - Chat toggle button: fixed bottom-right floating circle button with chat icon
    - bg-accent text-black, rounded-full, shadow-lg
    - Shows unread dot indicator when there are unread assistant messages
  - Chat panel (shown/hidden via Alpine x-show):
    - Fixed bottom-right, ~380px wide, ~500px tall
    - Header bar: "Chat with AI" title, close button
    - Message area: scrollable container with chat bubbles
      - User messages: right-aligned, bg-accent/20 text-white
      - Assistant messages: left-aligned, bg-white/[0.04] text-gray-300
      - Typing indicator: three animated dots while waiting for response
    - Input area: text input + send button at bottom
      - Input: bg-white/[0.06] border-white/[0.08] rounded-xl text-white placeholder-gray-500
      - Send button: bg-accent text-black rounded-lg
    - Welcome message: pre-populated assistant greeting on first open

Alpine.js data/interactions:
  - x-data="chatbot()" with methods: toggle(), sendMessage(), scrollToBottom()
  - State: open (boolean), messages (array), inputText (string), loading (boolean), visitorUuid (string from localStorage)
  - On mount: generate UUID if not in localStorage, load any previous messages from localStorage
  - On send: POST /chatbot/message with { message, visitor_uuid }, append response to messages, save to localStorage
  - Auto-scroll to bottom on new message
  - Disable send button while loading
  - Enter key sends message, Shift+Enter for newline
```

### Admin View — Chat Log Index

```
View: resources/views/livewire/admin/ai-assistant/chat-logs/index.blade.php
Layout: components.layouts.admin
Side: ADMIN

Design rules (from CLAUDE.md admin side):
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:
  - Breadcrumb: Dashboard > AI Assistant > Chat Logs
  - Page header: "Chat Logs" title, subtitle "Review visitor chatbot conversations."
  - Search/filter bar: search input to filter by conversation title or visitor UUID
  - Split panel layout (grid grid-cols-1 lg:grid-cols-3 gap-5):
    - Left panel (lg:col-span-1): Conversation list
      - Each item shows: truncated title, visitor UUID (first 8 chars), message count badge, relative time
      - Active/selected item: bg-primary/10 border-primary/30
      - Scrollable with max height
      - Empty state when no conversations
    - Right panel (lg:col-span-2): Message thread
      - Shows when a conversation is selected
      - Header: conversation title, visitor UUID, IP, created date
      - Message list: chat bubbles (user right-aligned primary/10, assistant left-aligned dark-700)
      - Each message shows role label, content, timestamp
      - Empty state: "Select a conversation to view messages"
  - Pagination for conversation list below the list panel
```

---

## 6. VALIDATION RULES

```
Form: ChatbotController@sendMessage (public API endpoint)
  - message: required|string|max:1000
  - visitor_uuid: required|string|uuid

No admin forms — admin side is read-only.
```

---

## 7. EDGE CASES & BUSINESS RULES

- **Rate limiting:** 20 requests/minute per IP using Laravel's built-in throttle middleware on the POST route. Return 429 JSON on exceeded.
- **Visitor UUID:** Generated client-side in localStorage. If a visitor clears storage, a new conversation starts. Old conversation remains in DB.
- **Conversation title:** Auto-generated from first user message, truncated to 100 characters with ellipsis.
- **Message count:** Denormalized on `chatbot_conversations.message_count`. Incremented on every new message (both user and assistant).
- **Last message timestamp:** Updated on `chatbot_conversations.last_message_at` with every new message.
- **AI provider fallback:** Try Gemini first. If Gemini fails (network error, rate limit, invalid key), fall back to Groq. If both fail, return error response.
- **API key lookup:** Use existing `ApiKeyService` to fetch keys from `api_keys` table. Add `PROVIDER_GEMINI = 'gemini'` and `PROVIDER_GROQ = 'groq'` constants to `ApiKey` model.
- **Portfolio context caching:** Cache the built portfolio context string for 1 hour using Laravel cache (`Cache::remember('chatbot_portfolio_context', 3600, ...)`). Invalidated automatically by TTL.
- **System prompt guardrails:** AI must be instructed to only answer questions about the portfolio owner's professional background, skills, and projects. Decline off-topic requests politely. Never fabricate information not present in the portfolio data.
- **Conversation history limit:** Send only the last 20 messages to the AI provider to stay within token limits.
- **Message content sanitization:** Strip HTML tags from user input before storing. Render assistant markdown responses as plain text in the widget (no HTML injection).
- **No delete from admin:** Admin can view but not delete conversations (read-only log viewer). Future enhancement if needed.
- **Empty state handling:** Public widget shows a welcome greeting. Admin shows "No conversations yet" when table is empty, "Select a conversation" when no conversation is selected.
- **Cascade delete:** Deleting a conversation cascades to its messages (FK constraint). Not exposed in UI currently.
- **Sidebar placement:** Chat Logs link nested under "AI Assistant" parent group in sidebar, never standalone.

---

## 8. IMPLEMENTATION ORDER

```
1. database/migrations/2026_04_01_500003_create_chatbot_conversations_table.php
2. database/migrations/2026_04_01_500004_create_chatbot_messages_table.php
3. app/Models/Chatbot/ChatbotConversation.php
4. app/Models/Chatbot/ChatbotMessage.php
5. Add PROVIDER_GEMINI and PROVIDER_GROQ constants to app/Models/ApiKey.php
6. app/Services/ChatbotService.php
7. app/Services/AiChatbotService.php
8. routes/admin/ai-assistant/chat-logs.php
9. app/Livewire/Admin/AiAssistant/ChatLogs/ChatLogIndex.php
10. resources/views/livewire/admin/ai-assistant/chat-logs/index.blade.php
11. Add "AI Assistant" sidebar group with "Chat Logs" link to resources/views/components/layouts/admin.blade.php
12. app/Http/Controllers/ChatbotController.php
13. Add POST /chatbot/message route to routes/web.php (with throttle middleware)
14. resources/views/components/chatbot-widget.blade.php
15. Include <x-chatbot-widget /> in resources/views/welcome.blade.php (before closing body or after footer)
```

---

## 9. SIDEBAR NAVIGATION

```
AI Assistant (parent, collapsible)
  └── Chat Logs
```

Icon for AI Assistant group: chat bubble or robot/sparkle icon.
Nested inside sidebar after existing module groups (Portfolio, Tasks, Job Search, Email, Settings).
