<?php

namespace App\Services;

use App\Models\Email\Email;
use App\Models\Email\EmailDigest;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmailDigestService
{
    public function generateDigest(?string $date = null): EmailDigest
    {
        $digestDate = $date ? Carbon::parse($date) : today();
        $start = $digestDate->copy()->startOfDay();
        $end = $digestDate->copy()->endOfDay();

        $digest = EmailDigest::updateOrCreate(
            ['digest_date' => $digestDate->toDateString()],
            [
                'period_start' => $start,
                'period_end' => $end,
                'status' => 'generating',
            ]
        );

        try {
            $emails = $this->getEmailsForDigest($start, $end);

            $emails = $this->categorizeEmails($emails);

            foreach ($emails as $email) {
                if (! $email->ai_summary) {
                    $email->ai_summary = $this->summarizeEmail($email);
                    $email->save();
                }
            }

            $categoriesBreakdown = $emails->groupBy(fn ($e) => $e->category ?? 'other')
                ->map->count()
                ->toArray();

            $highlights = $emails->take(20)->map(fn ($email) => [
                'email_id' => $email->id,
                'from_name' => $email->from_name ?? $email->from_email,
                'subject' => $email->subject,
                'ai_summary' => $email->ai_summary,
                'gmail_link' => $email->gmail_link,
                'category' => $email->category ?? 'other',
            ])->values()->toArray();

            $summary = $this->buildDigestSummary($emails, $categoriesBreakdown);

            $digest->update([
                'total_emails' => $emails->count(),
                'unread_count' => $emails->where('is_read', false)->count(),
                'summary' => $summary,
                'categories_breakdown' => $categoriesBreakdown,
                'highlights' => $highlights,
                'ai_model_used' => 'gemini-2.0-flash',
                'generated_at' => now(),
                'status' => 'completed',
                'error_message' => null,
            ]);

            return $digest;
        } catch (\Exception $e) {
            Log::error('Digest generation failed', ['error' => $e->getMessage()]);

            $digest->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return $digest;
        }
    }

    public function getEmailsForDigest(Carbon $start, Carbon $end): Collection
    {
        return Email::receivedBetween($start, $end)
            ->latest('received_at')
            ->get();
    }

    public function categorizeEmails(Collection $emails): Collection
    {
        foreach ($emails as $email) {
            if ($email->category) {
                continue;
            }

            try {
                $category = $this->aiCategorize($email);
                $email->category = $category;
                $email->save();
            } catch (\Exception $e) {
                Log::warning('Failed to categorize email', [
                    'email_id' => $email->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $emails;
    }

    public function summarizeEmail(Email $email): string
    {
        try {
            $prompt = "Summarize this email in one concise sentence (max 100 characters):\n\n"
                ."From: {$email->from_name} <{$email->from_email}>\n"
                ."Subject: {$email->subject}\n"
                ."Preview: {$email->snippet}";

            return $this->callAi($prompt);
        } catch (\Exception $e) {
            Log::warning('Failed to summarize email', [
                'email_id' => $email->id,
                'error' => $e->getMessage(),
            ]);

            return $email->snippet ?? 'No summary available';
        }
    }

    public function buildDigestSummary(Collection $emails, array $categoriesBreakdown): string
    {
        if ($emails->isEmpty()) {
            return 'No emails received during this period.';
        }

        try {
            $categoryList = collect($categoriesBreakdown)
                ->map(fn ($count, $cat) => ucfirst(str_replace('_', ' ', $cat)).": {$count}")
                ->implode(', ');

            $prompt = "Write a brief 2-3 sentence morning email digest summary.\n\n"
                ."Total emails: {$emails->count()}\n"
                ."Unread: {$emails->where('is_read', false)->count()}\n"
                ."Categories: {$categoryList}\n\n"
                .'Key subjects: '.$emails->take(10)->pluck('subject')->filter()->implode(', ');

            return $this->callAi($prompt);
        } catch (\Exception $e) {
            $total = $emails->count();
            $unread = $emails->where('is_read', false)->count();

            return "You received {$total} emails, {$unread} unread. Categories: "
                .collect($categoriesBreakdown)->map(fn ($count, $cat) => ucfirst(str_replace('_', ' ', $cat))." ({$count})")->implode(', ').'.';
        }
    }

    public function getDigestHistory(int $perPage = 10): LengthAwarePaginator
    {
        return EmailDigest::query()
            ->latest('digest_date')
            ->paginate($perPage);
    }

    public function getLatestDigest(): ?EmailDigest
    {
        return EmailDigest::completed()
            ->latest('digest_date')
            ->first();
    }

    private function aiCategorize(Email $email): string
    {
        $prompt = "Categorize this email into exactly one of: job_response, freelance, personal, newsletter, other.\n\n"
            ."From: {$email->from_name} <{$email->from_email}>\n"
            ."Subject: {$email->subject}\n"
            ."Preview: {$email->snippet}\n\n"
            .'Reply with ONLY the category name, nothing else.';

        $result = strtolower(trim($this->callAi($prompt)));

        $validCategories = ['job_response', 'freelance', 'personal', 'newsletter', 'other'];

        return in_array($result, $validCategories) ? $result : 'other';
    }

    private function callAi(string $prompt): string
    {
        $apiKey = \App\Models\ApiKey::where('service', 'gemini')
            ->where('is_active', true)
            ->first();

        if (! $apiKey) {
            throw new \RuntimeException('Gemini API key not configured. Go to Settings > API Keys to set it up.');
        }

        $response = Http::post(
            "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey->api_key}",
            [
                'contents' => [
                    ['parts' => [['text' => $prompt]]],
                ],
            ]
        );

        $response->throw();

        return $response->json('candidates.0.content.parts.0.text', 'Unable to generate summary.');
    }
}
