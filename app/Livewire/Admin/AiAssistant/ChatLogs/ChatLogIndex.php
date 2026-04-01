<?php

namespace App\Livewire\Admin\AiAssistant\ChatLogs;

use App\Models\Chatbot\ChatbotConversation;
use App\Services\ChatbotService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class ChatLogIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    public ?int $selectedConversationId = null;

    public ?ChatbotConversation $selectedConversation = null;

    public function mount(): void
    {
        $this->selectedConversationId = null;
        $this->selectedConversation = null;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function selectConversation(int $id): void
    {
        $service = app(ChatbotService::class);
        $this->selectedConversationId = $id;
        $this->selectedConversation = $service->getConversationWithMessages($id);
    }

    public function clearSelection(): void
    {
        $this->selectedConversationId = null;
        $this->selectedConversation = null;
    }

    public function render()
    {
        $service = app(ChatbotService::class);

        return view('livewire.admin.ai-assistant.chat-logs.index', [
            'conversations' => $service->getConversations($this->search, 20),
        ]);
    }
}
