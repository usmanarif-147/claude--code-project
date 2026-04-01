<?php

namespace App\Livewire\Admin\Youtube\ContentCalendar;

use App\Models\ContentCalendarItem;
use App\Services\ContentCalendarService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class ContentCalendarForm extends Component
{
    public ?int $itemId = null;

    public string $title = '';

    public string $type = 'video';

    public string $description = '';

    public string $planned_date = '';

    public string $status = 'planned';

    public ?string $color = null;

    public function mount(?ContentCalendarItem $contentCalendarItem = null): void
    {
        if ($contentCalendarItem && $contentCalendarItem->exists) {
            $this->itemId = $contentCalendarItem->id;
            $this->title = $contentCalendarItem->title;
            $this->type = $contentCalendarItem->type;
            $this->description = $contentCalendarItem->description ?? '';
            $this->planned_date = $contentCalendarItem->planned_date->format('Y-m-d');
            $this->status = $contentCalendarItem->status;
            $this->color = $contentCalendarItem->color;
        } else {
            $this->planned_date = now()->format('Y-m-d');
        }
    }

    public function save(ContentCalendarService $service): void
    {
        $validated = $this->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:video,blog',
            'description' => 'nullable|string|max:2000',
            'planned_date' => 'required|date|date_format:Y-m-d',
            'status' => 'required|in:planned,published',
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        if ($this->itemId) {
            $item = ContentCalendarItem::findOrFail($this->itemId);
            $service->updateItem($item, $validated);
            $message = 'Content updated successfully.';
        } else {
            $service->createItem($validated);
            $message = 'Content created successfully.';
        }

        session()->flash('success', $message);
        $this->redirect(route('admin.youtube.content-calendar.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.youtube.content-calendar.form');
    }
}
