<?php

namespace App\Services;

use App\Models\Email\Email;
use App\Models\Email\EmailSyncLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GmailSyncService
{
    public function syncEmails(): array
    {
        $startTime = microtime(true);
        $fetched = 0;
        $skipped = 0;

        try {
            $messages = $this->fetchMessageList('in:inbox', 50);

            foreach ($messages as $message) {
                $messageId = $message['id'] ?? null;
                if (! $messageId) {
                    continue;
                }

                if (Email::where('gmail_id', $messageId)->exists()) {
                    $skipped++;

                    continue;
                }

                try {
                    $detail = $this->fetchMessageDetail($messageId);
                    $parsed = $this->parseEmailPayload($detail);

                    Email::create([
                        'gmail_id' => $messageId,
                        'thread_id' => $parsed['thread_id'],
                        'from_email' => $parsed['from_email'],
                        'from_name' => $parsed['from_name'],
                        'to_email' => $parsed['to_email'],
                        'subject' => $parsed['subject'],
                        'snippet' => $parsed['snippet'],
                        'body_preview' => $parsed['body_preview'],
                        'received_at' => $parsed['received_at'],
                        'is_read' => $parsed['is_read'],
                        'is_starred' => $parsed['is_starred'],
                        'is_important' => $parsed['is_important'],
                        'labels' => $parsed['labels'],
                        'gmail_link' => $this->buildGmailLink($messageId),
                        'raw_payload' => $detail,
                    ]);

                    $fetched++;
                } catch (\Exception $e) {
                    Log::warning('Failed to fetch email detail', [
                        'message_id' => $messageId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            EmailSyncLog::create([
                'synced_at' => now(),
                'emails_fetched' => $fetched,
                'emails_skipped' => $skipped,
                'status' => 'success',
                'duration_ms' => $durationMs,
            ]);

            return [
                'fetched' => $fetched,
                'skipped' => $skipped,
                'status' => 'success',
            ];
        } catch (\Exception $e) {
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            EmailSyncLog::create([
                'synced_at' => now(),
                'emails_fetched' => $fetched,
                'emails_skipped' => $skipped,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'duration_ms' => $durationMs,
            ]);

            throw $e;
        }
    }

    public function fetchMessageList(string $query, int $maxResults = 50): array
    {
        $token = $this->getAccessToken();

        $response = Http::withToken($token)
            ->get('https://gmail.googleapis.com/gmail/v1/users/me/messages', [
                'q' => $query,
                'maxResults' => $maxResults,
            ]);

        if ($response->status() === 429) {
            throw new \RuntimeException('Gmail API rate limit exceeded. Please try again later.');
        }

        $response->throw();

        return $response->json('messages', []);
    }

    public function fetchMessageDetail(string $messageId): array
    {
        $token = $this->getAccessToken();

        $response = Http::withToken($token)
            ->get("https://gmail.googleapis.com/gmail/v1/users/me/messages/{$messageId}", [
                'format' => 'full',
            ]);

        if ($response->status() === 429) {
            throw new \RuntimeException('Gmail API rate limit exceeded. Please try again later.');
        }

        $response->throw();

        return $response->json();
    }

    public function parseEmailPayload(array $payload): array
    {
        $headers = collect($payload['payload']['headers'] ?? []);

        $fromHeader = $headers->firstWhere('name', 'From')['value'] ?? '';
        $fromName = null;
        $fromEmail = $fromHeader;

        if (preg_match('/^(.+?)\s*<(.+?)>$/', $fromHeader, $matches)) {
            $fromName = trim($matches[1], '" ');
            $fromEmail = $matches[2];
        }

        $toHeader = $headers->firstWhere('name', 'To')['value'] ?? null;
        $subject = $headers->firstWhere('name', 'Subject')['value'] ?? null;
        $dateHeader = $headers->firstWhere('name', 'Date')['value'] ?? null;

        $receivedAt = $dateHeader ? Carbon::parse($dateHeader)->utc() : now();

        $labels = $payload['labelIds'] ?? [];
        $isRead = ! in_array('UNREAD', $labels);
        $isStarred = in_array('STARRED', $labels);
        $isImportant = in_array('IMPORTANT', $labels);

        $snippet = $payload['snippet'] ?? null;

        return [
            'thread_id' => $payload['threadId'] ?? null,
            'from_email' => $fromEmail,
            'from_name' => $fromName,
            'to_email' => $toHeader,
            'subject' => $subject,
            'snippet' => $snippet,
            'body_preview' => $snippet,
            'received_at' => $receivedAt,
            'is_read' => $isRead,
            'is_starred' => $isStarred,
            'is_important' => $isImportant,
            'labels' => $labels,
        ];
    }

    public function buildGmailLink(string $messageId): string
    {
        return "https://mail.google.com/mail/u/0/#inbox/{$messageId}";
    }

    public function getLastSyncTime(): ?Carbon
    {
        $lastSync = EmailSyncLog::where('status', 'success')
            ->latest('synced_at')
            ->first();

        return $lastSync?->synced_at;
    }

    private function getAccessToken(): string
    {
        $apiKey = \App\Models\ApiKey::where('service', 'gmail')
            ->where('is_active', true)
            ->first();

        if (! $apiKey) {
            throw new \RuntimeException('Gmail API token not configured. Go to Settings > API Keys to set up Gmail access.');
        }

        return $apiKey->api_key;
    }
}
