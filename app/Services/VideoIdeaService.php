<?php

namespace App\Services;

use App\Models\ContentCalendarItem;
use App\Models\VideoIdea;
use Illuminate\Pagination\LengthAwarePaginator;

class VideoIdeaService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = VideoIdea::query()
            ->with('contentCalendarItem')
            ->orderByDesc('created_at');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        return $query->paginate(10);
    }

    public function store(array $data): VideoIdea
    {
        return VideoIdea::create($data);
    }

    public function update(VideoIdea $videoIdea, array $data): VideoIdea
    {
        $videoIdea->update($data);

        return $videoIdea;
    }

    public function delete(VideoIdea $videoIdea): void
    {
        $videoIdea->delete();
    }

    public function moveToContentCalendar(VideoIdea $videoIdea): VideoIdea
    {
        if ($videoIdea->content_calendar_item_id) {
            throw new \RuntimeException('This idea is already scheduled on the content calendar.');
        }

        $calendarItem = ContentCalendarItem::create([
            'title' => $videoIdea->title,
            'type' => 'video',
            'planned_date' => null,
            'status' => 'planned',
        ]);

        $videoIdea->update([
            'content_calendar_item_id' => $calendarItem->id,
        ]);

        return $videoIdea;
    }
}
