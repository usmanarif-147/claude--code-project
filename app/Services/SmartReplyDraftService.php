<?php

namespace App\Services;

use App\Models\ApiKey;
use App\Models\Email\Email;
use App\Models\Email\SmartReplyDraft;
use App\Models\EmailTemplate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class SmartReplyDraftService
{
    public function getAll(?string $search, ?string $status, ?string $tone, int $perPage = 10): LengthAwarePaginator
    {
        $query = SmartReplyDraft::with(['email', 'template'])->latest('created_at');

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('generated_body', 'like', "%{$search}%")
                    ->orWhere('edited_body', 'like', "%{$search}%")
                    ->orWhereHas('email', function ($eq) use ($search) {
                        $eq->where('subject', 'like', "%{$search}%")
                            ->orWhere('from_email', 'like', "%{$search}%")
                            ->orWhere('from_name', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($status)) {
            $query->where('status', $status);
        }

        if (! empty($tone)) {
            $query->where('tone', $tone);
        }

        return $query->paginate($perPage);
    }

    public function getById(int $id): SmartReplyDraft
    {
        return SmartReplyDraft::with(['email', 'template'])->findOrFail($id);
    }

    public function getByEmail(int $emailId): Collection
    {
        return SmartReplyDraft::with(['template'])
            ->where('email_id', $emailId)
            ->latest('created_at')
            ->get();
    }

    public function generate(int $emailId, string $tone, ?int $templateId, ?string $promptContext): SmartReplyDraft
    {
        $email = Email::findOrFail($emailId);
        $template = $templateId ? EmailTemplate::find($templateId) : null;

        $prompt = $this->buildPrompt($email, $tone, $template, $promptContext);
        $result = $this->callAi($prompt);

        return SmartReplyDraft::create([
            'email_id' => $email->id,
            'template_id' => $templateId,
            'tone' => $tone,
            'prompt_context' => $promptContext,
            'generated_body' => $result['content'],
            'status' => 'draft',
            'ai_model_used' => $result['model'],
            'generated_at' => now(),
        ]);
    }

    public function updateEditedBody(int $id, string $editedBody): SmartReplyDraft
    {
        $draft = SmartReplyDraft::findOrFail($id);
        $draft->update(['edited_body' => $editedBody]);

        return $draft->fresh();
    }

    public function markCopied(int $id): SmartReplyDraft
    {
        $draft = SmartReplyDraft::findOrFail($id);
        $draft->update([
            'status' => 'copied',
            'copied_at' => now(),
        ]);

        return $draft->fresh();
    }

    public function markSent(int $id): SmartReplyDraft
    {
        $draft = SmartReplyDraft::findOrFail($id);
        $draft->update(['status' => 'sent']);

        return $draft->fresh();
    }

    public function delete(int $id): void
    {
        SmartReplyDraft::findOrFail($id)->delete();
    }

    public function buildPrompt(Email $email, string $tone, ?EmailTemplate $template, ?string $promptContext): string
    {
        $subject = $email->subject ?? '(No Subject)';
        $from = $email->from_name ? "{$email->from_name} <{$email->from_email}>" : $email->from_email;
        $body = $email->body_preview ?? $email->snippet ?? '';

        $toneInstruction = match ($tone) {
            'friendly' => 'Use a warm, friendly, and approachable tone. Be personable and conversational.',
            'brief' => 'Be very concise and to the point. Use short sentences. No unnecessary pleasantries.',
            default => 'Use a professional, formal tone. Be polite and respectful.',
        };

        $templateSection = '';
        if ($template) {
            $templateSection = <<<SECTION

=== TEMPLATE TO USE AS STARTING POINT ===
Template Name: {$template->name}
Template Body:
{$template->body}

Use this template as a structural guide and adapt it to the specific email being replied to.
SECTION;
        }

        $extraContext = '';
        if (! empty($promptContext)) {
            $extraContext = <<<SECTION

=== EXTRA INSTRUCTIONS FROM USER ===
{$promptContext}
SECTION;
        }

        return <<<PROMPT
You are an email reply assistant. Generate a reply to the following email.

=== ORIGINAL EMAIL ===
From: {$from}
Subject: {$subject}
Body:
{$body}
{$templateSection}
{$extraContext}
=== TONE ===
{$toneInstruction}

=== INSTRUCTIONS ===
Write a reply to this email. Do not include email headers (To, From, Subject, etc.) — just the reply body text. Do not use markdown formatting. Keep the reply appropriate for the context of the original email. Output ONLY the reply text — no commentary.
PROMPT;
    }

    public function callAi(string $prompt): array
    {
        $apiKey = ApiKey::query()
            ->forProvider(ApiKey::PROVIDER_CLAUDE)
            ->connected()
            ->first();

        if (! $apiKey) {
            $apiKey = ApiKey::query()
                ->forProvider(ApiKey::PROVIDER_OPENAI)
                ->connected()
                ->first();
        }

        if (! $apiKey) {
            throw new \RuntimeException('No AI API key configured. Please add a Claude or OpenAI key in Settings > API Keys.');
        }

        if ($apiKey->provider === ApiKey::PROVIDER_CLAUDE) {
            return $this->callClaude($prompt, $apiKey);
        }

        return $this->callOpenAI($prompt, $apiKey);
    }

    private function callClaude(string $prompt, ApiKey $apiKey): array
    {
        $response = Http::timeout(60)
            ->withHeaders([
                'x-api-key' => $apiKey->key_value,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])
            ->post('https://api.anthropic.com/v1/messages', [
                'model' => 'claude-sonnet-4-20250514',
                'max_tokens' => 2048,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Failed to reach the AI service. Please try again.');
        }

        $body = $response->json();

        return [
            'content' => $body['content'][0]['text'] ?? '',
            'model' => $body['model'] ?? 'claude-sonnet-4-20250514',
        ];
    }

    private function callOpenAI(string $prompt, ApiKey $apiKey): array
    {
        $response = Http::timeout(60)
            ->withHeaders([
                'Authorization' => 'Bearer '.$apiKey->key_value,
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'max_tokens' => 2048,
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Failed to reach the AI service. Please try again.');
        }

        $body = $response->json();

        return [
            'content' => $body['choices'][0]['message']['content'] ?? '',
            'model' => $body['model'] ?? 'gpt-4o',
        ];
    }
}
