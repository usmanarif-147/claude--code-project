<?php

namespace App\Services;

use App\Models\JobSearch\JobAlert;
use App\Models\JobSearch\JobAlertNotification;
use App\Models\JobSearch\JobListing;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class JobAlertService
{
    public function getOrCreateConfig(User $user): JobAlert
    {
        return JobAlert::firstOrCreate(
            ['user_id' => $user->id],
            [
                'is_enabled' => true,
                'min_score_threshold' => 80,
                'frequency' => JobAlert::FREQUENCY_INSTANT,
                'notify_dashboard' => true,
                'notify_email' => false,
            ]
        );
    }

    public function updateConfig(User $user, array $data): JobAlert
    {
        $config = $this->getOrCreateConfig($user);

        $config->update([
            'is_enabled' => $data['is_enabled'] ?? $config->is_enabled,
            'min_score_threshold' => $data['min_score_threshold'] ?? $config->min_score_threshold,
            'frequency' => $data['frequency'] ?? $config->frequency,
            'notify_dashboard' => $data['notify_dashboard'] ?? $config->notify_dashboard,
            'notify_email' => $data['notify_email'] ?? $config->notify_email,
        ]);

        return $config->fresh();
    }

    public function evaluateAndNotify(User $user, JobListing $job, int $matchScore, ?string $matchSummary): ?JobAlertNotification
    {
        $config = JobAlert::query()->forUser($user->id)->enabled()->first();

        if (! $config) {
            return null;
        }

        if ($matchScore < $config->min_score_threshold) {
            return null;
        }

        $notification = JobAlertNotification::firstOrCreate(
            [
                'user_id' => $user->id,
                'job_listing_id' => $job->id,
            ],
            [
                'match_score' => $matchScore,
                'match_summary' => $matchSummary,
                'is_read' => false,
                'notified_via' => JobAlertNotification::VIA_DASHBOARD,
                'notified_at' => now(),
            ]
        );

        if ($notification->wasRecentlyCreated && $config->frequency === JobAlert::FREQUENCY_INSTANT) {
            $this->processInstantAlert($notification);
        }

        return $notification;
    }

    public function processInstantAlert(JobAlertNotification $notification): void
    {
        $config = JobAlert::query()->forUser($notification->user_id)->first();

        if (! $config || ! $config->notify_email) {
            return;
        }

        try {
            // Email sending will be implemented when the full email pipeline is built.
            // For now, just update notified_via to indicate email was intended.
            $notification->update(['notified_via' => JobAlertNotification::VIA_BOTH]);
        } catch (\Throwable $e) {
            Log::warning('Failed to send instant job alert email', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function processDailyDigest(User $user): int
    {
        $config = JobAlert::query()->forUser($user->id)->enabled()->first();

        if (! $config || $config->frequency !== JobAlert::FREQUENCY_DAILY) {
            return 0;
        }

        $since = $config->last_digest_sent_at ?? $config->created_at;

        $notifications = JobAlertNotification::query()
            ->forUser($user->id)
            ->where('notified_via', JobAlertNotification::VIA_DASHBOARD)
            ->where('notified_at', '>=', $since)
            ->get();

        if ($notifications->isEmpty()) {
            return 0;
        }

        try {
            // Email sending will be implemented when the full email pipeline is built.
            foreach ($notifications as $notification) {
                $notification->update(['notified_via' => JobAlertNotification::VIA_BOTH]);
            }

            $config->update(['last_digest_sent_at' => now()]);
        } catch (\Throwable $e) {
            Log::warning('Failed to send daily digest email', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $notifications->count();
    }

    public function processWeeklyDigest(User $user): int
    {
        $config = JobAlert::query()->forUser($user->id)->enabled()->first();

        if (! $config || $config->frequency !== JobAlert::FREQUENCY_WEEKLY) {
            return 0;
        }

        $since = $config->last_digest_sent_at ?? $config->created_at;

        $notifications = JobAlertNotification::query()
            ->forUser($user->id)
            ->where('notified_via', JobAlertNotification::VIA_DASHBOARD)
            ->where('notified_at', '>=', $since)
            ->get();

        if ($notifications->isEmpty()) {
            return 0;
        }

        try {
            // Email sending will be implemented when the full email pipeline is built.
            foreach ($notifications as $notification) {
                $notification->update(['notified_via' => JobAlertNotification::VIA_BOTH]);
            }

            $config->update(['last_digest_sent_at' => now()]);
        } catch (\Throwable $e) {
            Log::warning('Failed to send weekly digest email', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $notifications->count();
    }

    public function getNotifications(User $user, array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = JobAlertNotification::query()
            ->forUser($user->id)
            ->with('jobListing')
            ->orderByDesc('notified_at');

        if (! empty($filters['status'])) {
            if ($filters['status'] === 'unread') {
                $query->unread();
            } elseif ($filters['status'] === 'read') {
                $query->read();
            }
        }

        if (! empty($filters['date_from'])) {
            $query->where('notified_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->where('notified_at', '<=', $filters['date_to'].' 23:59:59');
        }

        return $query->paginate($perPage);
    }

    public function markAsRead(JobAlertNotification $notification): void
    {
        $notification->update(['is_read' => true]);
    }

    public function markAsUnread(JobAlertNotification $notification): void
    {
        $notification->update(['is_read' => false]);
    }

    public function markAllAsRead(User $user): int
    {
        return JobAlertNotification::query()
            ->forUser($user->id)
            ->unread()
            ->update(['is_read' => true]);
    }

    public function dismissNotification(JobAlertNotification $notification): void
    {
        $notification->delete();
    }

    public function getUnreadCount(User $user): int
    {
        return JobAlertNotification::query()
            ->forUser($user->id)
            ->unread()
            ->count();
    }

    public function getStats(User $user): array
    {
        $query = JobAlertNotification::query()->forUser($user->id);

        $totalAlerts = (clone $query)->count();
        $unreadCount = (clone $query)->unread()->count();
        $highMatchThisWeek = (clone $query)
            ->where('notified_at', '>=', now()->subDays(7))
            ->count();
        $avgMatchScore = $totalAlerts > 0
            ? (int) round((clone $query)->avg('match_score'))
            : 0;

        return [
            'total_alerts' => $totalAlerts,
            'unread_count' => $unreadCount,
            'high_match_this_week' => $highMatchThisWeek,
            'avg_match_score' => $avgMatchScore,
        ];
    }
}
