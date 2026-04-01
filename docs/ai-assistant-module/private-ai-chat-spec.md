# Private AI Chat — Spec

Side: ADMIN

---

## 1. MODULE OVERVIEW

The Private AI Chat is a personal AI assistant that lives inside the admin panel. It allows the authenticated user to have natural-language conversations with an AI that has contextual awareness of their dashboard data (tasks, emails, job applications, projects). The user can create multiple conversations, switch between them, delete old ones, and scroll through message history. The AI gathers context from existing models before each response.

Features:
- Create, rename, and delete chat conversations
- Send messages and receive AI-generated responses
- AI context gathering from existing modules (tasks, emails, job listings, projects)
- Conversation list sidebar with search
- Real-time message streaming (or loading indicator while AI responds)
- Message history persisted per conversation
- Provider fallback: Google Gemini API (primary), Groq API (backup)

Admin features:
- Full CRUD on conversations (create, rename, delete)
- Send messages within any conversation
- View complete message history
- Clear conversation messages without deleting the conversation

---

## 2. DATABASE SCHEMA

```
Table: ai_chat_conversations
Columns:
  - id (bigint, primary key, auto increment)
  - user_id (bigint, required, foreign key -> users.id)
  - title (string 255, required, default: 'New Conversation')
  - last_message_at (timestamp, nullable) — updated when a new message is added
  - created_at, updated_at (timestamps)

Indexes:
  - index on user_id
  - index on last_message_at (for sorting conversations by recency)

Foreign keys:
  - user_id references users(id) on delete cascade
```

```
Table: ai_chat_messages
Columns:
  - id (bigint, primary key, auto increment)
  - conversation_id (bigint, required, foreign key -> ai_chat_conversations.id)
  - role (string 20, required) — 'user' or 'assistant'
  - content (text, required) — message body (plain text or markdown)
  - context_summary (text, nullable) — snapshot of what dashboard context was provided to AI (for debugging/reference, only on assistant messages)
  - tokens_used (integer, nullable) — token count returned by the API (for tracking usage)
  - provider (string 50, nullable) — which AI provider was used ('gemini' or 'groq')
  - created_at, updated_at (timestamps)

Indexes:
  - index on conversation_id
  - index on created_at (for ordering messages chronologically)

Foreign keys:
  - conversation_id references ai_chat_conversations(id) on delete cascade
```

---

## 3. FILE MAP

```
MIGRATIONS:
  - database/migrations/2026_04_01_500001_create_ai_chat_conversations_table.php
  - database/migrations/2026_04_01_500002_create_ai_chat_messages_table.php

MODELS:
  - app/Models/AiChat/AiChatConversation.php
    - fillable: user_id, title, last_message_at
    - casts: last_message_at -> datetime
    - relationships:
      - user(): belongsTo(User::class)
      - messages(): hasMany(AiChatMessage::class, 'conversation_id')
      - latestMessage(): hasOne(AiChatMessage::class, 'conversation_id')->latestOfMany()
    - scopes:
      - scopeForUser(Builder $query, int $userId)
      - scopeRecent(Builder $query) — orderBy last_message_at desc

  - app/Models/AiChat/AiChatMessage.php
    - fillable: conversation_id, role, content, context_summary, tokens_used, provider
    - casts: tokens_used -> integer
    - constants: ROLE_USER = 'user', ROLE_ASSISTANT = 'assistant'
    - relationships:
      - conversation(): belongsTo(AiChatConversation::class, 'conversation_id')

SERVICES:
  - app/Services/AiChatService.php
    - getConfiguredProvider(int $userId): ?array — returns ['provider' => 'gemini'|'groq', 'apiKey' => ApiKey] or null. Checks Gemini first, falls back to Groq.
    - getConversations(int $userId): Collection — all conversations for user, ordered by last_message_at desc
    - searchConversations(int $userId, string $query): Collection — filter conversations by title containing query
    - createConversation(int $userId, ?string $title = null): AiChatConversation — creates a new conversation with default or given title
    - renameConversation(AiChatConversation $conversation, string $title): AiChatConversation — updates conversation title
    - deleteConversation(AiChatConversation $conversation): void — deletes conversation and all its messages (cascade)
    - clearMessages(AiChatConversation $conversation): void — deletes all messages in conversation but keeps the conversation
    - getMessages(AiChatConversation $conversation, int $limit = 50): Collection — returns messages ordered by created_at asc
    - sendMessage(AiChatConversation $conversation, string $userMessage, int $userId): AiChatMessage — saves user message, gathers context, calls AI API, saves assistant response, returns assistant message
    - gatherDashboardContext(int $userId): string — queries tasks (pending count, overdue, today's), emails (recent unread count, latest subjects), job applications (active count, recent matches), projects (count, latest) and builds a context string
    - buildPrompt(string $userMessage, string $dashboardContext, Collection $recentMessages): string — constructs the full prompt with system instructions, context, conversation history, and user message
    - callGeminiApi(string $apiKey, string $prompt, array $conversationHistory): string — calls Google Gemini API (generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent) and returns response text
    - callGroqApi(string $apiKey, string $prompt, array $conversationHistory): string — calls Groq API (api.groq.com/openai/v1/chat/completions with llama-3.3-70b-versatile model) and returns response text
    - parseResponse(string $responseText): string — cleans up AI response text
    - autoGenerateTitle(AiChatConversation $conversation, string $firstMessage): void — uses AI to generate a short conversation title from the first user message (max 50 chars)

--- ADMIN FILES ---

LIVEWIRE COMPONENTS:
  - app/Livewire/Admin/AiAssistant/PrivateChat/PrivateChatIndex.php
    - public properties, methods — see Component Contracts below
  - resources/views/livewire/admin/ai-assistant/private-chat/index.blade.php
    - chat interface with conversation sidebar and message area

ROUTES (admin):
  - routes/admin/ai-assistant/private-chat.php
    - GET /admin/ai-assistant/chat → PrivateChatIndex → admin.ai-assistant.chat.index
```

---

## 4. COMPONENT CONTRACTS

### Admin Components

```
Component: App\Livewire\Admin\AiAssistant\PrivateChat\PrivateChatIndex
Namespace:  App\Livewire\Admin\AiAssistant\PrivateChat
Layout:     #[Layout('components.layouts.admin')]

Properties:
  - $conversations (Collection) — all user conversations, loaded on mount
  - $activeConversationId (int|null) — currently selected conversation ID
  - $activeConversation (AiChatConversation|null) — currently selected conversation model
  - $messages (Collection) — messages for active conversation
  - $newMessage (string) — text input bound to the message textarea
  - $searchQuery (string) — search/filter for conversation list sidebar
  - $isLoading (bool) — true while waiting for AI response
  - $editingTitle (bool) — true when user is editing conversation title
  - $editTitle (string) — bound to title edit input
  - $providerStatus (string|null) — 'gemini', 'groq', or null (no key configured)

Methods:
  - mount()
    Input: none
    Does: loads conversations via AiChatService, checks configured provider, selects first conversation if any exist
    Output: sets initial state

  - loadConversations()
    Input: none
    Does: refreshes $conversations from service, applies $searchQuery filter if set
    Output: updates $conversations property

  - selectConversation(int $conversationId)
    Input: conversation ID
    Does: sets activeConversationId, loads conversation model and its messages
    Output: updates $activeConversation, $messages, scrolls chat to bottom

  - createConversation()
    Input: none
    Does: calls AiChatService->createConversation(), selects the new conversation
    Output: adds conversation to list, sets as active, clears messages

  - renameConversation()
    Input: uses $editTitle property
    Does: validates title (required, max:100), calls AiChatService->renameConversation()
    Output: updates conversation title, exits editing mode, flash success

  - deleteConversation(int $conversationId)
    Input: conversation ID
    Does: calls AiChatService->deleteConversation(), selects next conversation or clears active
    Output: removes from list, flash success

  - clearMessages()
    Input: none
    Does: calls AiChatService->clearMessages() on active conversation
    Output: clears $messages, flash success

  - sendMessage()
    Input: uses $newMessage property
    Does: validates message (required, max:5000), sets isLoading=true, calls AiChatService->sendMessage(), adds both user and assistant messages to $messages, clears $newMessage, sets isLoading=false, dispatches browser event to scroll to bottom
    Output: updates messages list, updates conversation last_message_at

  - updatedSearchQuery()
    Input: triggered by search input change
    Does: calls loadConversations() with search filter
    Output: filters conversation sidebar list

Validation Rules:
  - newMessage: required|string|max:5000
  - editTitle: required|string|max:100
```

---

## 5. VIEW BLUEPRINTS

### Admin View

```
View: resources/views/livewire/admin/ai-assistant/private-chat/index.blade.php
Layout: components.layouts.admin
Side: ADMIN

Design rules (from CLAUDE.md admin side):
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Page structure: Full-width split layout (no standard page header — chat apps are immersive)

Sections:

  1. Breadcrumb
     - Dashboard > AI Assistant > Private Chat

  2. Page Header
     - Title: "AI CHAT ASSISTANT" (font-mono uppercase tracking-wider)
     - Subtitle: "Your personal AI assistant with dashboard context"
     - Right side: Provider status badge showing which AI provider is active
       - Gemini connected: emerald badge "Gemini Active"
       - Groq connected: blue badge "Groq Active"
       - No provider: amber badge "No AI Provider" with link to Settings > API Keys

  3. Main Content: Two-column layout (conversation sidebar + chat area)

     LEFT SIDEBAR (w-80, fixed width):
       - "New Chat" button at top (primary button, full width)
       - Search input to filter conversations by title
       - Conversation list (scrollable):
         - Each item shows: title (truncated), last message preview (truncated), timestamp
         - Active conversation: bg-primary/10 border-l-2 border-primary
         - Hover: bg-dark-700
         - Three-dot menu on hover: Rename, Clear Messages, Delete
       - Empty state when no conversations: "Start your first conversation" with icon

     RIGHT CHAT AREA (flex-1):
       - When no conversation selected:
         - Centered empty state with AI icon
         - "Select a conversation or start a new one"
         - Suggested prompts grid (4 cards, 2x2):
           - "What did I accomplish this week?"
           - "Summarize my pending tasks"
           - "What are my top job matches?"
           - "Draft a reply to the last recruiter email"
         - Each prompt card: bg-dark-700 hover:bg-dark-600 rounded-xl p-4 cursor-pointer
         - Clicking a prompt: creates new conversation and sends that prompt

       - When conversation is active:
         - Chat header bar:
           - Conversation title (editable on click — inline edit with input field)
           - Clear messages button (icon, with wire:confirm)
         - Messages area (scrollable, flex-1):
           - User messages: aligned right, bg-primary/20 text-white rounded-xl px-4 py-3 max-w-[75%]
           - Assistant messages: aligned left, bg-dark-700 text-gray-300 rounded-xl px-4 py-3 max-w-[75%]
           - Assistant messages render markdown (bold, lists, code blocks)
           - Timestamp below each message: text-xs text-gray-600
           - Loading indicator when waiting for AI: three animated dots in an assistant-style bubble
         - Message input area (bottom, sticky):
           - Textarea (auto-resize, max 4 rows): bg-dark-700 border border-dark-600 rounded-xl
           - Send button (primary, icon only — paper plane icon) inside the textarea on the right
           - Disabled state while isLoading
           - Submit on Enter (without Shift), Shift+Enter for newline (Alpine.js @keydown handler)
           - Character count: "X / 5000" in text-xs text-gray-600

  4. Flash messages: shown as toast at top of chat area
```

---

## 6. VALIDATION RULES

```
Form: Send Message
  - newMessage: required|string|max:5000

Form: Rename Conversation
  - editTitle: required|string|max:100
```

---

## 7. EDGE CASES & BUSINESS RULES

- **Delete conversation**: cascade deletes all messages (handled by foreign key ON DELETE CASCADE)
- **Clear messages**: deletes all messages but keeps the conversation record intact; resets last_message_at to null
- **No AI provider configured**: show amber warning badge in header and disable the send button; show a message directing user to Settings > API Keys to configure Gemini or Groq
- **Provider fallback**: try Gemini first; if no Gemini key, try Groq; if neither, show "no provider" state
- **API failure**: catch exceptions from AI calls, save an assistant message with content like "I'm sorry, I couldn't process your request. Please try again." and flash an error message
- **Rate limiting**: respect Gemini free tier (15 req/min, 1500 req/day) — if 429 response, show user-friendly "Rate limit reached, please wait a moment" message
- **Context gathering**: the service queries other modules' models (Task, Email, JobApplication, Project) but does NOT fail if those tables are empty — gracefully returns "No data available" for empty modules
- **Conversation auto-title**: after the first user message, the AI generates a short title (max 50 chars) asynchronously; until then, the title is "New Conversation"
- **Message ordering**: always chronological (created_at asc) within a conversation
- **Conversation ordering**: most recently active first (last_message_at desc, nulls last)
- **Empty conversation**: a conversation with no messages shows the suggested prompts view in the chat area
- **Long messages**: user input capped at 5000 characters; AI responses are not capped but stored as-is
- **Conversation history in prompts**: include the last 10 messages (5 user + 5 assistant pairs) as conversation context when calling the AI API, so the AI remembers the thread
- **Provider prerequisite**: Gemini and Groq providers must be added to `ApiKey::ALL_PROVIDERS` and the `ApiKeyService` test methods. This is a prerequisite before this module can function — constants `PROVIDER_GEMINI = 'gemini'` and `PROVIDER_GROQ = 'groq'` must be added to `app/Models/ApiKey.php`, and corresponding `testGemini()` and `testGroq()` methods must be added to `app/Services/ApiKeyService.php`
- **Markdown rendering**: assistant messages should render basic markdown (bold, italic, lists, code blocks, inline code) — use a simple Blade approach or Alpine.js markdown renderer (e.g., marked.js loaded via CDN)
- **Scroll behavior**: auto-scroll to bottom when new messages appear; do not auto-scroll if user has scrolled up (reading history)

---

## 8. IMPLEMENTATION ORDER

```
1. Add PROVIDER_GEMINI and PROVIDER_GROQ constants to app/Models/ApiKey.php
2. Add testGemini() and testGroq() methods to app/Services/ApiKeyService.php
3. Migration: database/migrations/2026_04_01_500001_create_ai_chat_conversations_table.php
4. Migration: database/migrations/2026_04_01_500002_create_ai_chat_messages_table.php
5. Model: app/Models/AiChat/AiChatConversation.php
6. Model: app/Models/AiChat/AiChatMessage.php
7. Service: app/Services/AiChatService.php
8. Route: routes/admin/ai-assistant/private-chat.php
9. Livewire component: app/Livewire/Admin/AiAssistant/PrivateChat/PrivateChatIndex.php
10. View: resources/views/livewire/admin/ai-assistant/private-chat/index.blade.php
11. Sidebar: add "AI Assistant" collapsible group to resources/views/components/layouts/admin.blade.php with "Private Chat" sub-link
12. Update docs/PROJECT-STATUS.md with AI Assistant module entry
```

---

## 9. SIDEBAR UPDATE

Add to `resources/views/components/layouts/admin.blade.php` — a new collapsible "AI Assistant" module group:

```
AI Assistant (parent, collapsible)
  - icon: chat bubble / sparkles icon
  - auto-open when route matches admin.ai-assistant.*
  Children:
    - Private Chat → route('admin.ai-assistant.chat.index')
      - icon: message/chat icon
```

Position: after the Email module group, before Settings.

---

## 10. ASSUMPTIONS & OPEN QUESTIONS

Assumptions made:
1. **Gemini and Groq providers do not yet exist** in the `ApiKey` model — they must be added as a prerequisite (steps 1-2 in implementation order).
2. **Google Gemini API endpoint**: using `generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent` with API key passed as query parameter `?key=API_KEY`. Free tier supports this without OAuth.
3. **Groq API endpoint**: using `api.groq.com/openai/v1/chat/completions` with OpenAI-compatible format and `llama-3.3-70b-versatile` model. API key passed as Bearer token.
4. **No streaming**: initial implementation uses standard HTTP request/response (not SSE/streaming). The loading indicator shows while waiting. Streaming can be added as a future enhancement.
5. **Dashboard context models**: the service will query `Task`, `Email`, `JobApplication`, `Project` models. If any of these tables don't exist yet or are empty, the context gracefully degrades.
6. **Markdown rendering**: using a client-side JS library (marked.js) for rendering assistant message markdown in the browser, rather than server-side parsing.

Open questions for review:
1. Should conversation history have a maximum retention period, or keep all history forever?
2. Should there be a maximum number of conversations per user, or unlimited?
3. Should the auto-generated title call the AI API (costs an extra API call) or use a simpler heuristic (first 50 chars of first message)?
