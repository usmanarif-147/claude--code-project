<?php

namespace App\Livewire\Admin\Settings\ApiKeys;

use App\Models\ApiKey;
use App\Services\ApiKeyService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class ApiKeysIndex extends Component
{
    public array $keys = [];

    public array $formData = [];

    public ?string $testingProvider = null;

    public function mount(): void
    {
        $this->loadKeys();
        $this->initFormData();
    }

    public function saveKey(string $provider, ApiKeyService $service): void
    {
        if (in_array($provider, [ApiKey::PROVIDER_GMAIL, ApiKey::PROVIDER_YOUTUBE])) {
            $this->validate([
                "formData.{$provider}.client_id" => 'required|string|max:500',
                "formData.{$provider}.client_secret" => 'required|string|max:500',
                "formData.{$provider}.refresh_token" => 'required|string|max:2000',
            ]);

            $data = [
                'key_value' => $provider.'-oauth',
                'extra_data' => [
                    'client_id' => $this->formData[$provider]['client_id'],
                    'client_secret' => $this->formData[$provider]['client_secret'],
                    'refresh_token' => $this->formData[$provider]['refresh_token'],
                ],
            ];
        } else {
            $this->validate([
                "formData.{$provider}.key_value" => 'required|string|max:2000',
            ]);

            $data = [
                'key_value' => $this->formData[$provider]['key_value'],
            ];
        }

        $service->upsertKey(auth()->id(), $provider, $data);
        $this->loadKeys();

        session()->flash('success', 'API key saved successfully.');
    }

    public function testKey(string $provider, ApiKeyService $service): void
    {
        $this->testingProvider = $provider;

        $apiKey = ApiKey::forUser(auth()->id())->forProvider($provider)->first();

        if (! $apiKey) {
            session()->flash('error', 'No API key found for this provider.');
            $this->testingProvider = null;

            return;
        }

        $passed = $service->testKey($apiKey);
        $this->loadKeys();
        $this->testingProvider = null;

        if ($passed) {
            session()->flash('success', ucfirst($provider).' connection test passed.');
        } else {
            session()->flash('error', ucfirst($provider).' connection test failed.');
        }
    }

    public function deleteKey(string $provider, ApiKeyService $service): void
    {
        $apiKey = ApiKey::forUser(auth()->id())->forProvider($provider)->first();

        if ($apiKey) {
            $service->deleteKey($apiKey);
        }

        $this->loadKeys();
        $this->initFormDataForProvider($provider);

        session()->flash('success', 'API key deleted successfully.');
    }

    public function render()
    {
        $providers = $this->getProviderDefinitions();

        return view('livewire.admin.settings.api-keys.index', [
            'providers' => $providers,
        ]);
    }

    private function loadKeys(): void
    {
        $service = app(ApiKeyService::class);
        $keys = $service->getKeysForUser(auth()->id());

        $this->keys = [];
        foreach (ApiKey::ALL_PROVIDERS as $provider) {
            if ($keys->has($provider)) {
                $apiKey = $keys->get($provider);
                $data = $apiKey->toArray() + ['exists' => true];

                // Add masked preview (key_value is hidden from toArray for security)
                if (! in_array($apiKey->provider, [ApiKey::PROVIDER_GMAIL, ApiKey::PROVIDER_YOUTUBE]) && $apiKey->key_value) {
                    $data['key_preview'] = str_repeat('*', 12).substr($apiKey->key_value, -6);
                }

                $this->keys[$provider] = $data;
            } else {
                $this->keys[$provider] = ['exists' => false];
            }
        }
    }

    private function initFormData(): void
    {
        foreach (ApiKey::ALL_PROVIDERS as $provider) {
            $this->initFormDataForProvider($provider);
        }
    }

    private function initFormDataForProvider(string $provider): void
    {
        if (in_array($provider, [ApiKey::PROVIDER_GMAIL, ApiKey::PROVIDER_YOUTUBE])) {
            $this->formData[$provider] = [
                'client_id' => '',
                'client_secret' => '',
                'refresh_token' => '',
            ];
        } else {
            $this->formData[$provider] = [
                'key_value' => '',
            ];
        }
    }

    private function getProviderDefinitions(): array
    {
        return [
            ApiKey::PROVIDER_GMAIL => [
                'name' => 'Gmail',
                'description' => 'Google OAuth2 for sending emails',
                'icon' => 'gmail',
                'color' => 'red',
            ],
            ApiKey::PROVIDER_CLAUDE => [
                'name' => 'Claude',
                'description' => 'Anthropic AI assistant API',
                'icon' => 'claude',
                'color' => 'orange',
            ],
            ApiKey::PROVIDER_OPENAI => [
                'name' => 'OpenAI',
                'description' => 'GPT models and embeddings',
                'icon' => 'openai',
                'color' => 'green',
            ],
            ApiKey::PROVIDER_JSEARCH => [
                'name' => 'JSearch',
                'description' => 'RapidAPI job search endpoint',
                'icon' => 'jsearch',
                'color' => 'blue',
            ],
            ApiKey::PROVIDER_ADZUNA => [
                'name' => 'Adzuna',
                'description' => 'Job listing aggregator API',
                'icon' => 'adzuna',
                'color' => 'teal',
            ],
            ApiKey::PROVIDER_SERPAPI => [
                'name' => 'SerpAPI',
                'description' => 'Search engine results API',
                'icon' => 'serpapi',
                'color' => 'indigo',
            ],
            ApiKey::PROVIDER_YOUTUBE => [
                'name' => 'YouTube',
                'description' => 'YouTube Data API v3',
                'icon' => 'youtube',
                'color' => 'red',
            ],
            ApiKey::PROVIDER_GEMINI => [
                'name' => 'Gemini',
                'description' => 'Google AI for resume template generation & data parsing',
                'icon' => 'gemini',
                'color' => 'blue',
            ],
            ApiKey::PROVIDER_GROQ => [
                'name' => 'Groq',
                'description' => 'Fast AI inference for resume data parsing (backup)',
                'icon' => 'groq',
                'color' => 'orange',
            ],
        ];
    }
}
