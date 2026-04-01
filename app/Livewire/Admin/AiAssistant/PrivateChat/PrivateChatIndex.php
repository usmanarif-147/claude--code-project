<?php

namespace App\Livewire\Admin\AiAssistant\PrivateChat;

use App\Models\AiChat\AiChatConversation;
use App\Services\AiChatService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class PrivateChatIndex extends Component
{
    public Collection $conversations;

    public ?int $activeConversationId = null;

    public ?AiChatConversation $activeConversation = null;

    public Collection $messages;

    public string $newMessage = '';

    public string $searchQuery = '';

    public bool $isLoading = false;

    public bool $editingTitle = false;

    public string $editTitle = '';

    public ?string $providerStatus = null;

    public function mount(AiChatService $service): void
    {
        $this->conversations = $service->getConversations(auth()->id());
        $this->messages = collect();

        $providerConfig = $service->getConfiguredProvider(auth()->id());
        $this->providerStatus = $providerConfig ? $providerConfig['provider'] : null;

        if ($this->conversations->isNotEmpty()) {
            $this->selectConversation($this->conversations->first()->id);
        }
    }

    public function loadConversations(): void
    {
        $service = app(AiChatService::class);

        if ($this->searchQuery) {
            $this->conversations = $service->searchConversations(auth()->id(), $this->searchQuery);
        } else {
            $this->conversations = $service->getConversations(auth()->id());
        }
    }

    public function selectConversation(int $conversationId): void
    {
        $service = app(AiChatService::class);

        $this->activeConversationId = $conversationId;
        $this->activeConversation = AiChatConversation::query()
            ->forUser(auth()->id())
            ->findOrFail($conversationId);
        $this->messages = $service->getMessages($this->activeConversation);
        $this->editingTitle = false;
        $this->editTitle = '';

        $this->dispatch('scroll-to-bottom');
    }

    public function createConversation(): void
    {
        $service = app(AiChatService::class);
        $conversation = $service->createConversation(auth()->id());

        $this->loadConversations();
        $this->selectConversation($conversation->id);
    }

    public function renameConversation(): void
    {
        $this->validate([
            'editTitle' => 'required|string|max:100',
        ]);

        $service = app(AiChatService::class);
        $service->renameConversation($this->activeConversation, $this->editTitle);

        $this->activeConversation->refresh();
        $this->editingTitle = false;
        $this->editTitle = '';
        $this->loadConversations();

        session()->flash('success', 'Conversation renamed successfully.');
    }

    public function deleteConversation(int $conversationId): void
    {
        $service = app(AiChatService::class);
        $conversation = AiChatConversation::query()
            ->forUser(auth()->id())
            ->findOrFail($conversationId);

        $service->deleteConversation($conversation);

        $this->loadConversations();

        if ($this->activeConversationId === $conversationId) {
            if ($this->conversations->isNotEmpty()) {
                $this->selectConversation($this->conversations->first()->id);
            } else {
                $this->activeConversationId = null;
                $this->activeConversation = null;
                $this->messages = collect();
            }
        }

        session()->flash('success', 'Conversation deleted successfully.');
    }

    public function clearMessages(): void
    {
        if (! $this->activeConversation) {
            return;
        }

        $service = app(AiChatService::class);
        $service->clearMessages($this->activeConversation);
        $this->messages = collect();

        session()->flash('success', 'Messages cleared successfully.');
    }

    public function sendMessage(): void
    {
        $this->validate([
            'newMessage' => 'required|string|max:5000',
        ]);

        if (! $this->activeConversation) {
            return;
        }

        $this->isLoading = true;
        $message = $this->newMessage;
        $this->newMessage = '';

        try {
            $service = app(AiChatService::class);
            $service->sendMessage($this->activeConversation, $message, auth()->id());

            // Reload messages and conversations
            $this->messages = $service->getMessages($this->activeConversation);
            $this->activeConversation->refresh();
            $this->loadConversations();
        } catch (\Throwable $e) {
            session()->flash('error', 'Failed to send message. Please try again.');
        }

        $this->isLoading = false;
        $this->dispatch('scroll-to-bottom');
    }

    public function startEditingTitle(): void
    {
        if ($this->activeConversation) {
            $this->editingTitle = true;
            $this->editTitle = $this->activeConversation->title;
        }
    }

    public function cancelEditingTitle(): void
    {
        $this->editingTitle = false;
        $this->editTitle = '';
    }

    public function sendPrompt(string $prompt): void
    {
        $this->createConversation();
        $this->newMessage = $prompt;
        $this->sendMessage();
    }

    public function updatedSearchQuery(): void
    {
        $this->loadConversations();
    }

    public function render()
    {
        return view('livewire.admin.ai-assistant.private-chat.index');
    }
}
