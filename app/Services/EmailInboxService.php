<?php

namespace App\Services;

use App\Models\Email\Email;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EmailInboxService
{
    public function getEmails(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Email::query()->latest('received_at');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('from_email', 'like', "%{$search}%")
                    ->orWhere('from_name', 'like', "%{$search}%")
                    ->orWhere('snippet', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (! empty($filters['read'])) {
            if ($filters['read'] === 'read') {
                $query->where('is_read', true);
            } elseif ($filters['read'] === 'unread') {
                $query->where('is_read', false);
            }
        }

        return $query->paginate($perPage);
    }

    public function getUnreadCount(): int
    {
        return Email::unread()->count();
    }

    public function getCategoryBreakdown(): array
    {
        return Email::query()
            ->selectRaw('COALESCE(category, \'uncategorized\') as category, count(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();
    }

    public function getRecentStats(): array
    {
        return [
            'total' => Email::count(),
            'unread' => Email::unread()->count(),
            'important' => Email::important()->count(),
            'today' => Email::whereDate('received_at', today())->count(),
        ];
    }

    public function markAsRead(int $emailId): void
    {
        Email::where('id', $emailId)->update(['is_read' => true]);
    }

    public function searchEmails(string $query, int $perPage = 20): LengthAwarePaginator
    {
        return Email::query()
            ->where(function ($q) use ($query) {
                $q->where('subject', 'like', "%{$query}%")
                    ->orWhere('from_email', 'like', "%{$query}%")
                    ->orWhere('from_name', 'like', "%{$query}%")
                    ->orWhere('snippet', 'like', "%{$query}%");
            })
            ->latest('received_at')
            ->paginate($perPage);
    }
}
