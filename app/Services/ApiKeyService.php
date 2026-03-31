<?php

namespace App\Services;

use App\Models\ApiKey;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class ApiKeyService
{
    public function getKeysForUser(int $userId): Collection
    {
        return ApiKey::forUser($userId)->get()->keyBy('provider');
    }

    public function upsertKey(int $userId, string $provider, array $data): ApiKey
    {
        $attributes = [
            'key_value' => $data['key_value'] ?? '',
            'extra_data' => $data['extra_data'] ?? null,
            'is_connected' => true,
            'test_status' => 'untested',
        ];

        return ApiKey::updateOrCreate(
            ['user_id' => $userId, 'provider' => $provider],
            $attributes,
        );
    }

    public function testKey(ApiKey $apiKey): bool
    {
        try {
            $passed = match ($apiKey->provider) {
                ApiKey::PROVIDER_GMAIL => $this->testGmail($apiKey),
                ApiKey::PROVIDER_CLAUDE => $this->testClaude($apiKey),
                ApiKey::PROVIDER_OPENAI => $this->testOpenai($apiKey),
                ApiKey::PROVIDER_JSEARCH => $this->testJsearch($apiKey),
                ApiKey::PROVIDER_ADZUNA => $this->testAdzuna($apiKey),
                ApiKey::PROVIDER_SERPAPI => $this->testSerpapi($apiKey),
                ApiKey::PROVIDER_YOUTUBE => $this->testYoutube($apiKey),
                default => false,
            };
        } catch (\Throwable) {
            $passed = false;
        }

        $apiKey->update([
            'test_status' => $passed ? 'passed' : 'failed',
            'last_tested_at' => now(),
        ]);

        return $passed;
    }

    public function deleteKey(ApiKey $apiKey): void
    {
        $apiKey->delete();
    }

    private function testGmail(ApiKey $apiKey): bool
    {
        $extra = $apiKey->extra_data;

        $response = Http::timeout(5)->asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => $extra['client_id'] ?? '',
            'client_secret' => $extra['client_secret'] ?? '',
            'refresh_token' => $extra['refresh_token'] ?? '',
            'grant_type' => 'refresh_token',
        ]);

        return $response->successful();
    }

    private function testClaude(ApiKey $apiKey): bool
    {
        $response = Http::timeout(5)
            ->withHeaders([
                'x-api-key' => $apiKey->key_value,
                'anthropic-version' => '2023-06-01',
            ])
            ->get('https://api.anthropic.com/v1/models');

        return $response->successful();
    }

    private function testOpenai(ApiKey $apiKey): bool
    {
        $response = Http::timeout(5)
            ->withToken($apiKey->key_value)
            ->get('https://api.openai.com/v1/models');

        return $response->successful();
    }

    private function testJsearch(ApiKey $apiKey): bool
    {
        $response = Http::timeout(5)
            ->withHeaders([
                'X-RapidAPI-Key' => $apiKey->key_value,
                'X-RapidAPI-Host' => 'jsearch.p.rapidapi.com',
            ])
            ->get('https://jsearch.p.rapidapi.com/search', [
                'query' => 'test',
                'num_pages' => 1,
            ]);

        return $response->successful();
    }

    private function testAdzuna(ApiKey $apiKey): bool
    {
        $extra = $apiKey->extra_data;

        $response = Http::timeout(5)
            ->get('https://api.adzuna.com/v1/api/jobs/us/search/1', [
                'app_id' => $extra['app_id'] ?? $apiKey->key_value,
                'app_key' => $extra['app_key'] ?? $apiKey->key_value,
                'what' => 'test',
            ]);

        return $response->successful();
    }

    private function testSerpapi(ApiKey $apiKey): bool
    {
        $response = Http::timeout(5)
            ->get('https://serpapi.com/account.json', [
                'api_key' => $apiKey->key_value,
            ]);

        return $response->successful();
    }

    private function testYoutube(ApiKey $apiKey): bool
    {
        $response = Http::timeout(5)
            ->get('https://www.googleapis.com/youtube/v3/channels', [
                'part' => 'id',
                'mine' => 'true',
                'key' => $apiKey->key_value,
            ]);

        return $response->successful();
    }
}
