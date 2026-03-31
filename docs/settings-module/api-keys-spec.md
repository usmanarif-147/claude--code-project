# API Keys Management — Spec

Side: **ADMIN**

---

## 1. MODULE OVERVIEW

This feature provides a centralized admin page for storing, managing, and testing API keys used by automations throughout the application. All keys are encrypted at rest using Laravel's `encrypted` cast.

### Features
- View all supported API providers as a card grid with connection status
- Add or update API credentials per provider (inline form via Alpine.js toggle)
- Test each API key with a live HTTP check (5-second timeout, provider-specific endpoint)
- Delete a stored API key
- Gmail provider uses three fields (client_id, client_secret, refresh_token) stored in `extra_data`; all other providers use a single `key_value` field

### Admin Features
- Upsert (create or update) credentials for any supported provider
- Test connection for any configured provider
- See at-a-glance status: connected/disconnected, test result (passed/failed/untested), last tested timestamp
- Delete credentials for a provider

---

## 2. DATABASE SCHEMA

```
Table: api_keys
Columns:
  - id              BIGINT, primary key, auto increment
  - user_id         BIGINT UNSIGNED, NOT NULL, FK -> users.id ON DELETE CASCADE
  - provider        VARCHAR(50), NOT NULL (enum-like: gmail, claude, openai, jsearch, adzuna, serpapi, youtube)
  - key_value       TEXT, NOT NULL (encrypted via Laravel encrypted cast)
  - extra_data      TEXT, NULLABLE (encrypted JSON — used by Gmail for client_id, client_secret, refresh_token)
  - is_connected    BOOLEAN, DEFAULT false
  - test_status     VARCHAR(20), NULLABLE (passed, failed, untested)
  - last_tested_at  TIMESTAMP, NULLABLE
  - created_at      TIMESTAMP
  - updated_at      TIMESTAMP

Indexes:
  - UNIQUE(user_id, provider)

Foreign keys:
  - user_id references users(id) ON DELETE CASCADE
```

---

## 3. FILE MAP

```
MIGRATIONS:
  - database/migrations/xxxx_xx_xx_xxxxxx_create_api_keys_table.php

MODELS:
  - app/Models/ApiKey.php  (single model — flat in Models/)
    - fillable: user_id, provider, key_value, extra_data, is_connected, test_status, last_tested_at
    - casts: key_value -> encrypted, extra_data -> encrypted:array, is_connected -> boolean, last_tested_at -> datetime
    - relationships: belongsTo(User)
    - constants: PROVIDERS array, PROVIDER_GMAIL, PROVIDER_CLAUDE, PROVIDER_OPENAI, PROVIDER_JSEARCH, PROVIDER_ADZUNA, PROVIDER_SERPAPI, PROVIDER_YOUTUBE
    - scopes: scopeForUser($query, $userId), scopeForProvider($query, $provider), scopeConnected($query)

SERVICES:
  - app/Services/ApiKeyService.php
    - upsertKey(int $userId, string $provider, array $data): ApiKey — creates or updates the key for user+provider, sets is_connected=true, test_status='untested'
    - testKey(ApiKey $apiKey): bool — makes HTTP request to provider-specific test endpoint with 5s timeout, updates test_status and last_tested_at, returns true/false
    - getKeysForUser(int $userId): Collection — returns all api_keys for user, keyed by provider
    - deleteKey(ApiKey $apiKey): void — deletes the api_key record, no cascade needed

--- ADMIN FILES ---

LIVEWIRE COMPONENTS:
  - app/Livewire/Admin/Settings/ApiKeys/ApiKeysIndex.php
    - public properties: $keys (array, keyed by provider), $formData (array), $testingProvider (string|null)
    - methods: mount(), saveKey(string $provider), testKey(string $provider), deleteKey(string $provider), render()
  - resources/views/livewire/admin/settings/api-keys/index.blade.php
    - Provider card grid with inline forms

ROUTES (admin):
  - routes/admin/settings/api-keys.php
    - GET /admin/settings/api-keys -> ApiKeysIndex -> admin.settings.api-keys
```

---

## 4. COMPONENT CONTRACTS

### Component: App\Livewire\Admin\Settings\ApiKeys\ApiKeysIndex

```
Namespace: App\Livewire\Admin\Settings\ApiKeys
Layout: components.layouts.admin

Properties:
  - $keys (array) — associative array keyed by provider name, value is the ApiKey model or null
  - $formData (array) — keyed by provider, holds current form input values (key_value, client_id, client_secret, refresh_token)
  - $testingProvider (string|null) — provider currently being tested (for loading state)

Methods:
  - mount()
    Input: none (uses auth()->id())
    Does: loads all API keys for current user via ApiKeyService::getKeysForUser(), populates $keys and initializes $formData with empty strings for each provider
    Output: sets component state

  - saveKey(string $provider)
    Input: provider name
    Does:
      1. Validates $formData[$provider] fields based on provider type
      2. For Gmail: validates client_id, client_secret, refresh_token (all required strings)
      3. For others: validates key_value (required string)
      4. Calls ApiKeyService::upsertKey() with appropriate data
      5. Refreshes $keys state
    Output: flash success message

  - testKey(string $provider)
    Input: provider name
    Does:
      1. Sets $testingProvider = $provider (triggers loading spinner in view)
      2. Finds the ApiKey for this provider from $keys
      3. Calls ApiKeyService::testKey()
      4. Refreshes $keys state
      5. Clears $testingProvider
    Output: flash success or error based on test result

  - deleteKey(string $provider)
    Input: provider name
    Does:
      1. Finds ApiKey for this provider
      2. Calls ApiKeyService::deleteKey()
      3. Refreshes $keys state, clears formData for that provider
    Output: flash success message

  - render()
    Does: returns view with provider definitions (name, icon, description, fields config)

Validation Rules:
  Gmail provider:
    - formData.gmail.client_id: required|string|max:500
    - formData.gmail.client_secret: required|string|max:500
    - formData.gmail.refresh_token: required|string|max:2000

  All other providers:
    - formData.{provider}.key_value: required|string|max:2000
```

---

## 5. VIEW BLUEPRINTS

### View: resources/views/livewire/admin/settings/api-keys/index.blade.php

```
Layout: components.layouts.admin
Side: ADMIN
Page title: "API Keys"

Design rules (from CLAUDE.md admin side):
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:

  1. Breadcrumb
     Dashboard > Settings > API Keys

  2. Page Header
     - Title: "API Keys"
     - Subtitle: "Manage API credentials for integrations and automations."
     - No add button (providers are predefined)

  3. Provider Card Grid (responsive: 1 col mobile, 2 col md, 3 col lg)
     Each provider card (bg-dark-800 border border-dark-700 rounded-xl p-6):

       Card header row:
         - Left: Provider icon (w-10 h-10 rounded-lg with colored bg/10) + provider name (font-mono uppercase tracking-wider text-white) + short description (text-xs text-gray-500)
         - Right: Connection status badge
           - Connected + passed: bg-emerald-500/10 text-emerald-400 "Connected"
           - Connected + failed: bg-red-500/10 text-red-400 "Failed"
           - Connected + untested: bg-amber-500/10 text-amber-400 "Untested"
           - Not configured: bg-gray-500/10 text-gray-500 "Not Connected"

       Card body (shown when key exists):
         - Last tested: text-xs text-gray-500 showing relative timestamp or "Never tested"
         - Key preview: masked (e.g., "sk-...abc123") in text-xs text-gray-400 font-mono

       Card actions row:
         - "Configure" button (Alpine x-data toggle, bg-dark-700 hover:bg-dark-600 text-gray-300 rounded-lg px-4 py-2 text-sm) — toggles inline form visibility
         - "Test" button (bg-primary/10 text-primary-light hover:bg-primary/20 rounded-lg px-4 py-2 text-sm) — wire:click="testKey('provider')" with loading spinner when $testingProvider matches
         - "Delete" button (bg-red-500/10 text-red-400 hover:bg-red-500/20 rounded-lg px-3 py-2 text-sm, only shown if key exists) — wire:click="deleteKey('provider')" wire:confirm

       Inline form (Alpine x-show with transition, hidden by default):
         - For Gmail: 3 fields (Client ID, Client Secret, Refresh Token) — all text inputs, full width, stacked
         - For others: 1 field (API Key) — text input, full width
         - All inputs use standard design system input styles (bg-dark-700 border border-dark-600 rounded-lg)
         - "Save" button (bg-primary hover:bg-primary-hover text-white rounded-lg px-4 py-2.5 text-sm) with Livewire loading state
         - "Cancel" button (Alpine @click to close form, bg-dark-700 text-gray-300 rounded-lg)

  4. Empty state per card (when no key configured):
     - Muted text: "No API key configured"
     - Configure button is still available

  5. Flash messages
     - Rendered via session flash at top of page (standard pattern)
```

---

## 6. VALIDATION RULES

```
Form: Save Gmail API Key
  - formData.gmail.client_id: required|string|max:500
  - formData.gmail.client_secret: required|string|max:500
  - formData.gmail.refresh_token: required|string|max:2000

Form: Save Provider API Key (claude, openai, jsearch, adzuna, serpapi, youtube)
  - formData.{provider}.key_value: required|string|max:2000
```

---

## 7. EDGE CASES & BUSINESS RULES

- **Unique constraint**: Each user can have only one key per provider (UNIQUE on user_id + provider). Upsert uses updateOrCreate.
- **Encryption**: key_value and extra_data use Laravel `encrypted` / `encrypted:array` casts. Values are never displayed in full — view shows only a masked preview (last 6 chars).
- **Provider list is fixed**: The supported providers are defined as constants on the ApiKey model. No dynamic provider creation.
- **Test connection per provider**: Each provider has a specific test endpoint:
  - Gmail: validate refresh_token by hitting Google OAuth2 token endpoint
  - Claude: GET https://api.anthropic.com/v1/models with API key header
  - OpenAI: GET https://api.openai.com/v1/models with Bearer token
  - JSearch: GET https://jsearch.p.rapidapi.com/search with RapidAPI key header (minimal query)
  - Adzuna: GET https://api.adzuna.com/v1/api/version with app_id+app_key
  - SerpAPI: GET https://serpapi.com/account.json with api_key param
  - YouTube: GET https://www.googleapis.com/youtube/v3/channels?part=id&mine=true with API key
- **5-second timeout**: All test HTTP requests use a 5-second timeout. Any exception or non-2xx response = failed.
- **test_status values**: "passed", "failed", "untested" (set to "untested" on upsert, updated on test).
- **is_connected**: Set to true when a key is saved (upsert). Set to false is NOT done on failed test — it stays true to indicate a key exists. Only deletion removes the connection.
- **Delete behavior**: Hard delete. No soft deletes. No cascade to other tables (api_keys is a leaf table).
- **Auth scoping**: All queries are scoped to the authenticated user. A user can only see/manage their own keys.
- **No pagination**: Fixed number of providers (7), displayed as card grid. No search or filtering needed.

---

## 8. IMPLEMENTATION ORDER

```
1. Migration: database/migrations/xxxx_xx_xx_xxxxxx_create_api_keys_table.php
2. Model: app/Models/ApiKey.php
3. Service: app/Services/ApiKeyService.php
4. Route: routes/admin/settings/api-keys.php
5. Livewire component: app/Livewire/Admin/Settings/ApiKeys/ApiKeysIndex.php
6. View: resources/views/livewire/admin/settings/api-keys/index.blade.php
7. Sidebar: add Settings module group with API Keys link to admin layout
```
