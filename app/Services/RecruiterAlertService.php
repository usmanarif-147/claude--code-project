<?php

namespace App\Services;

use App\Models\Email\Email;
use App\Models\Email\RecruiterAlert;
use App\Models\Email\RecruiterAlertSetting;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class RecruiterAlertService
{
    public function getAlerts(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = RecruiterAlert::with('email')->latest();

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('email', function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('from_name', 'like', "%{$search}%")
                    ->orWhere('from_email', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['type'])) {
            $query->ofType($filters['type']);
        }

        if (! empty($filters['urgency'])) {
            $query->where('urgency', $filters['urgency']);
        }

        if (! empty($filters['status'])) {
            match ($filters['status']) {
                'unread' => $query->unread()->undismissed(),
                'read' => $query->where('is_read', true)->undismissed(),
                'dismissed' => $query->where('is_dismissed', true),
                default => null,
            };
        }

        return $query->paginate($perPage);
    }

    public function getAlertById(int $id): RecruiterAlert
    {
        return RecruiterAlert::with('email')->findOrFail($id);
    }

    public function markAsRead(int $id): void
    {
        RecruiterAlert::findOrFail($id)->update(['is_read' => true]);
    }

    public function markAsUnread(int $id): void
    {
        RecruiterAlert::findOrFail($id)->update(['is_read' => false]);
    }

    public function dismissAlert(int $id): void
    {
        RecruiterAlert::findOrFail($id)->update(['is_dismissed' => true]);
    }

    public function undismissAlert(int $id): void
    {
        RecruiterAlert::findOrFail($id)->update(['is_dismissed' => false]);
    }

    public function markAllAsRead(): int
    {
        return RecruiterAlert::unread()->update(['is_read' => true]);
    }

    public function dismissAll(): int
    {
        return RecruiterAlert::undismissed()->update(['is_dismissed' => true]);
    }

    public function scanEmails(): int
    {
        $settings = $this->getSettings();

        if (! $settings->is_enabled) {
            return 0;
        }

        $existingEmailIds = RecruiterAlert::pluck('email_id');

        $emails = Email::where('received_at', '>=', now()->subDays(7))
            ->whereNotIn('id', $existingEmailIds)
            ->get();

        $count = 0;

        foreach ($emails as $email) {
            $result = $this->analyzeEmail($email);

            if ($result === null) {
                continue;
            }

            if (! $this->isTypeEnabled($settings, $result['alert_type'])) {
                continue;
            }

            if ($result['confidence_score'] < $settings->min_confidence_score) {
                continue;
            }

            RecruiterAlert::create([
                'email_id' => $email->id,
                'alert_type' => $result['alert_type'],
                'confidence_score' => $result['confidence_score'],
                'detected_signals' => $result['detected_signals'],
                'urgency' => $result['urgency'],
            ]);

            $count++;
        }

        return $count;
    }

    public function analyzeEmail(Email $email): ?array
    {
        $signals = [];
        $confidence = 0;
        $alertType = null;

        $subject = strtolower($email->subject ?? '');
        $body = strtolower($email->body_preview ?? '');
        $sender = strtolower($email->from_email ?? '');
        $senderName = strtolower($email->from_name ?? '');
        $combined = $subject.' '.$body.' '.$sender.' '.$senderName;

        // Recruiter detection
        $recruiterScore = 0;
        $recruiterSignals = [];

        if (str_contains($sender, 'linkedin.com')) {
            $recruiterScore += 35;
            $recruiterSignals[] = 'sender domain: linkedin.com';
        }

        foreach (['recruiter', 'talent acquisition', 'staffing'] as $keyword) {
            if (str_contains($combined, $keyword)) {
                $recruiterScore += 20;
                $recruiterSignals[] = "contains: {$keyword}";
            }
        }

        foreach (['opportunity', 'position', 'role', 'candidate', 'resume'] as $keyword) {
            if (str_contains($combined, $keyword)) {
                $recruiterScore += 10;
                $recruiterSignals[] = "mentions: {$keyword}";
            }
        }

        // Hiring manager detection
        $hiringScore = 0;
        $hiringSignals = [];

        foreach (['hiring for', 'looking for', 'join our team', 'join my team', 'team lead'] as $keyword) {
            if (str_contains($combined, $keyword)) {
                $hiringScore += 25;
                $hiringSignals[] = "contains: {$keyword}";
            }
        }

        foreach (['team', 'direct report', 'report to me', 'department'] as $keyword) {
            if (str_contains($combined, $keyword)) {
                $hiringScore += 10;
                $hiringSignals[] = "mentions: {$keyword}";
            }
        }

        // Freelance client detection
        $freelanceScore = 0;
        $freelanceSignals = [];

        if (str_contains($sender, 'fiverr.com') || str_contains($sender, 'upwork.com')) {
            $freelanceScore += 35;
            $freelanceSignals[] = 'sender domain: freelance platform';
        }

        foreach (['project', 'freelance', 'budget', 'quote', 'proposal'] as $keyword) {
            if (str_contains($combined, $keyword)) {
                $freelanceScore += 15;
                $freelanceSignals[] = "mentions: {$keyword}";
            }
        }

        // Boost from email category
        $category = strtolower($email->category ?? '');
        if (in_array($category, ['job response', 'freelance'])) {
            $recruiterScore += 10;
            $hiringScore += 10;
            $freelanceScore += 10;
        }

        // Select the best match
        $scores = [
            'recruiter' => ['score' => min($recruiterScore, 100), 'signals' => $recruiterSignals],
            'hiring_manager' => ['score' => min($hiringScore, 100), 'signals' => $hiringSignals],
            'freelance_client' => ['score' => min($freelanceScore, 100), 'signals' => $freelanceSignals],
        ];

        $best = null;
        $bestScore = 0;

        foreach ($scores as $type => $data) {
            if ($data['score'] > $bestScore) {
                $bestScore = $data['score'];
                $best = $type;
                $signals = $data['signals'];
            }
        }

        if ($best === null || $bestScore < 20) {
            return null;
        }

        // Determine urgency
        $urgency = 'normal';
        $urgentKeywords = ['interview', 'urgent', 'deadline', 'asap', 'time-sensitive', 'expiring', 'tomorrow', 'this week'];
        foreach ($urgentKeywords as $keyword) {
            if (str_contains($subject, $keyword) || str_contains($body, $keyword)) {
                $urgency = 'urgent';
                break;
            }
        }

        $urgentDomains = ['linkedin.com', 'indeed.com', 'glassdoor.com'];
        foreach ($urgentDomains as $domain) {
            if (str_contains($sender, $domain)) {
                $urgency = 'urgent';
                break;
            }
        }

        return [
            'alert_type' => $best,
            'confidence_score' => $bestScore,
            'detected_signals' => $signals,
            'urgency' => $urgency,
        ];
    }

    public function sendNotifications(RecruiterAlert $alert): void
    {
        $settings = $this->getSettings();

        if ($alert->notified_at !== null) {
            return;
        }

        if ($settings->browser_notification) {
            Log::info('Browser notification would be sent for alert #'.$alert->id);
        }

        if ($settings->email_forward && $settings->forward_email && $alert->urgency === 'urgent') {
            Log::info('Email forward would be sent to '.$settings->forward_email.' for alert #'.$alert->id);
        }

        $alert->update(['notified_at' => now()]);
    }

    public function getStats(): array
    {
        return [
            'total' => RecruiterAlert::count(),
            'unread' => RecruiterAlert::unread()->undismissed()->count(),
            'urgent' => RecruiterAlert::urgent()->undismissed()->count(),
            'by_type' => [
                'recruiter' => RecruiterAlert::ofType('recruiter')->count(),
                'hiring_manager' => RecruiterAlert::ofType('hiring_manager')->count(),
                'freelance_client' => RecruiterAlert::ofType('freelance_client')->count(),
            ],
            'recent_24h' => RecruiterAlert::where('created_at', '>=', now()->subDay())->count(),
        ];
    }

    public function getSettings(): RecruiterAlertSetting
    {
        return RecruiterAlertSetting::getSettings();
    }

    public function updateSettings(array $data): RecruiterAlertSetting
    {
        $settings = RecruiterAlertSetting::getSettings();
        $settings->update($data);

        return $settings->refresh();
    }

    public function deleteAlert(int $id): void
    {
        RecruiterAlert::findOrFail($id)->delete();
    }

    private function isTypeEnabled(RecruiterAlertSetting $settings, string $type): bool
    {
        return match ($type) {
            'recruiter' => $settings->alert_on_recruiter,
            'hiring_manager' => $settings->alert_on_hiring_manager,
            'freelance_client' => $settings->alert_on_freelance_client,
            default => false,
        };
    }
}
