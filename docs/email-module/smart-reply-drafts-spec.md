# Smart Reply Drafts — Spec

Side: ADMIN

---

## 1. MODULE OVERVIEW

The Smart Reply Drafts feature uses AI to generate draft replies for emails that need a response, saving time on common reply patterns like interview scheduling, freelance quotes, follow-ups, and thank-you notes. The admin selects an email, optionally picks a tone and a template as a starting point, and the AI generates a draft reply that can be reviewed, edited, and copied to the clipboard for pasting into Gmail.

### Features
- AI-generated reply drafts for any email in the inbox
- Tone selection: formal, friendly, or brief
- Optional email template as a starting point for the AI-generated reply
- Edit the AI-generated draft before copying
- One-click copy to clipboard to paste in Gmail
- History of generated drafts per email
- Draft status tracking (draft, copied, sent)
- Regenerate with different tone or template

### Admin Features
- View list of all generated smart reply drafts with filters (status, tone, date)
- Generate a new smart reply from an email detail view or from the drafts index page
- Choose tone (formal, friendly, brief) before generating
- Optionally select an email template as a starting point
- Edit the generated draft inline
- Copy final draft to clipboard
- Delete old or unwanted drafts
- See which email and template were used for each draft

---

## 2. DATABASE SCHEMA

```
Table: smart_reply_drafts
Columns:
  - id (bigint, primary key, auto increment)
  - email_id (bigint, required) — FK to emails table, the email being replied to
  - template_id (bigint, nullable) — FK to email_templates table, template used as starting point
  - tone (varchar 50, required) — one of: formal, friendly, brief
  - prompt_context (text, nullable) — optional extra instructions the admin provided for the AI
  - generated_body (text, required) — the AI-generated reply body
  - edited_body (text, nullable) — the admin-edited version (null if not yet edited)
  - status (varchar 50, required, default: 'draft') — one of: draft, copied, sent
  - ai_model_used (varchar 100, nullable) — which AI model generated the reply (e.g., "gpt-4o", "claude-sonnet")
  - generated_at (datetime, required) — when the AI generation completed
  - copied_at (datetime, nullable) — when the draft was copied to clipboard
  - created_at, updated_at (timestamps)

Indexes:
  - smart_reply_drafts_email_id_index (email_id)
  - smart_reply_drafts_template_id_index (template_id)
  - smart_reply_drafts_status_index (status)
  - smart_reply_drafts_tone_index (tone)
  - smart_reply_drafts_created_at_index (created_at)

Foreign keys:
  - email_id references emails(id) on delete cascade
  - template_id references email_templates(id) on delete set null
```

---

## 3. FILE MAP

```
MIGRATIONS:
  - database/migrations/2026_04_01_200006_create_smart_reply_drafts_table.php

MODELS:
  - app/Models/Email/SmartReplyDraft.php
    - fillable: email_id, template_id, tone, prompt_context, generated_body, edited_body, status, ai_model_used, generated_at, copied_at
    - relationships:
      - belongsTo(Email::class)
      - belongsTo(EmailTemplate::class, 'template_id')
    - casts:
      - generated_at -> datetime
      - copied_at -> datetime
    - accessors:
      - getFinalBodyAttribute(): string — returns edited_body if set, otherwise generated_body

SERVICES:
  - app/Services/SmartReplyDraftService.php
    - getAll(search, status, tone, perPage): LengthAwarePaginator — list drafts with filters, eager load email and template
    - getById(id): SmartReplyDraft — find by ID or fail with relationships
    - getByEmail(emailId): Collection — get all drafts for a specific email
    - generate(emailId, tone, templateId, promptContext): SmartReplyDraft — call AI to generate a draft reply, store result
    - updateEditedBody(id, editedBody): SmartReplyDraft — save the admin's edited version
    - markCopied(id): SmartReplyDraft — set status to 'copied' and copied_at to now
    - markSent(id): SmartReplyDraft — set status to 'sent'
    - delete(id): void — delete a draft
    - buildPrompt(email, tone, template, promptContext): string — construct the AI prompt from email context, tone, and optional template
    - callAi(prompt): string — send prompt to AI provider and return generated text (uses ApiKey model for credentials)

--- ADMIN FILES ---

LIVEWIRE COMPONENTS:
  - app/Livewire/Admin/Email/SmartReply/SmartReplyIndex.php
    - public properties: $search, $filterStatus, $filterTone, $perPage
    - methods:
      - getDraftsProperty(): LengthAwarePaginator — computed property for paginated drafts list
      - deleteDraft(id): void — delete a draft, flash success
      - markCopied(id): void — update status to copied via service
      - markSent(id): void — update status to sent via service

  - app/Livewire/Admin/Email/SmartReply/SmartReplyForm.php
    - public properties: $draftId, $emailId, $templateId, $tone, $promptContext, $generatedBody, $editedBody, $status, $emailSubject, $emailSnippet, $emailFrom
    - methods:
      - mount(draftId?, emailId?): void — load existing draft or prepare new draft for a given email
      - generate(): void — call service to generate AI reply, populate generatedBody
      - saveEdit(): void — save edited_body via service, flash success
      - copyToClipboard(): void — dispatch browser event to copy, call markCopied on service
      - markSent(): void — update status via service, flash success
      - loadTemplates(): Collection — get available email templates for the dropdown

VIEWS:
  - resources/views/livewire/admin/email/smart-reply/index.blade.php
    - displays list of all smart reply drafts
  - resources/views/livewire/admin/email/smart-reply/form.blade.php
    - generate new draft or view/edit existing draft

ROUTES (admin):
  - routes/admin/email/smart-reply.php
    - GET /admin/email/smart-reply -> SmartReplyIndex -> admin.email.smart-reply.index
    - GET /admin/email/smart-reply/create/{email?} -> SmartReplyForm -> admin.email.smart-reply.create
    - GET /admin/email/smart-reply/{smartReplyDraft}/edit -> SmartReplyForm -> admin.email.smart-reply.edit
```

---

## 4. COMPONENT CONTRACTS

### SmartReplyIndex

```
Component: App\Livewire\Admin\Email\SmartReply\SmartReplyIndex
Namespace: App\Livewire\Admin\Email\SmartReply

Layout: #[Layout('components.layouts.admin')]

Properties:
  - $search (string, default: '') — #[Url] search query against email subject/from or draft body
  - $filterStatus (string, default: '') — #[Url] filter by draft status: draft, copied, sent, or '' for all
  - $filterTone (string, default: '') — #[Url] filter by tone: formal, friendly, brief, or '' for all
  - $perPage (int, default: 10) — pagination size

Methods:
  - getDraftsProperty()
    Input: uses $search, $filterStatus, $filterTone, $perPage
    Does: calls SmartReplyDraftService::getAll() with filters, eager loads email and template relationships
    Output: returns LengthAwarePaginator

  - deleteDraft(int $id)
    Input: draft ID
    Does: calls SmartReplyDraftService::delete($id)
    Output: flash success message "Draft deleted successfully."

  - markCopied(int $id)
    Input: draft ID
    Does: calls SmartReplyDraftService::markCopied($id), dispatches browser event 'copy-to-clipboard' with final body text
    Output: flash success message "Draft copied to clipboard."

  - markSent(int $id)
    Input: draft ID
    Does: calls SmartReplyDraftService::markSent($id)
    Output: flash success message "Draft marked as sent."
```

### SmartReplyForm

```
Component: App\Livewire\Admin\Email\SmartReply\SmartReplyForm
Namespace: App\Livewire\Admin\Email\SmartReply

Layout: #[Layout('components.layouts.admin')]

Properties:
  - $draftId (int|null) — existing draft ID (null for create)
  - $emailId (int|null) — the email being replied to
  - $templateId (int|null, default: null) — selected template ID
  - $tone (string, default: 'formal') — selected tone
  - $promptContext (string, default: '') — optional extra instructions for the AI
  - $generatedBody (string, default: '') — AI-generated reply text
  - $editedBody (string, default: '') — admin-edited version of the reply
  - $status (string, default: 'draft') — current draft status
  - $emailSubject (string) — subject of the source email (display only)
  - $emailSnippet (string) — snippet of the source email (display only)
  - $emailFrom (string) — sender of the source email (display only)
  - $templates (array) — available email templates for dropdown
  - $isGenerating (bool, default: false) — loading state during AI generation

Methods:
  - mount(int $draftId = null, int $emailId = null)
    Input: optional draft ID or email ID
    Does:
      - If $draftId: loads existing draft with relationships, populates all properties
      - If $emailId: loads the email record, populates email display fields, leaves draft fields empty
      - Loads available templates for dropdown
    Output: properties populated

  - generate()
    Input: uses $emailId, $tone, $templateId, $promptContext
    Does:
      1. Validates emailId is set
      2. Sets isGenerating = true
      3. Calls SmartReplyDraftService::generate($emailId, $tone, $templateId, $promptContext)
      4. Populates $generatedBody, $draftId, $status from the created draft
      5. Sets isGenerating = false
    Output: flash success "Reply draft generated." or flash error on failure

  - saveEdit()
    Input: uses $draftId, $editedBody
    Does: validates editedBody is not empty, calls SmartReplyDraftService::updateEditedBody($draftId, $editedBody)
    Output: flash success "Draft updated."

  - copyToClipboard()
    Input: uses $draftId
    Does: calls SmartReplyDraftService::markCopied($draftId), dispatches browser event 'copy-to-clipboard' with final body text (edited_body if set, else generated_body)
    Output: flash success "Copied to clipboard."

  - markSent()
    Input: uses $draftId
    Does: calls SmartReplyDraftService::markSent($draftId), updates $status
    Output: flash success "Marked as sent."

  - updatedTone()
    Input: triggered when tone changes
    Does: resets generatedBody and editedBody if draft has not been saved yet (new draft only)
    Output: none

Validation Rules:
  - emailId: required|exists:emails,id
  - tone: required|in:formal,friendly,brief
  - templateId: nullable|exists:email_templates,id
  - promptContext: nullable|string|max:1000
  - editedBody: nullable|string|max:10000
```

---

## 5. VIEW BLUEPRINTS

### Index View

```
View: resources/views/livewire/admin/email/smart-reply/index.blade.php
Layout: components.layouts.admin
Side: ADMIN
Page title: "SMART REPLY DRAFTS"

Design rules:
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:
  - Breadcrumb: Dashboard > Email > Smart Reply Drafts
  - Page header: "SMART REPLY DRAFTS" title + subtitle "AI-generated reply drafts for your emails." + "New Reply" button linking to create page
  - Filter bar (card):
    - Search input (searches email subject, from, and draft body)
    - Status dropdown: All Status, Draft, Copied, Sent
    - Tone dropdown: All Tones, Formal, Friendly, Brief
  - Drafts table (card):
    - Columns: Email (subject + from), Tone (badge), Status (badge), Template (name or "None"), Generated (relative date), Actions
    - Tone badges: formal = bg-blue-500/10 text-blue-400, friendly = bg-emerald-500/10 text-emerald-400, brief = bg-amber-500/10 text-amber-400
    - Status badges: draft = bg-amber-500/10 text-amber-400, copied = bg-blue-500/10 text-blue-400, sent = bg-emerald-500/10 text-emerald-400
    - Actions: Edit (link to form), Copy (dispatches clipboard event + marks copied), Delete (with wire:confirm)
  - Pagination below table
  - Empty state: "No smart reply drafts yet." with icon and "Generate your first reply" button
```

### Form View

```
View: resources/views/livewire/admin/email/smart-reply/form.blade.php
Layout: components.layouts.admin
Side: ADMIN
Page title: "GENERATE SMART REPLY" (create) / "EDIT SMART REPLY" (edit)

Design rules:
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider

Sections:
  - Breadcrumb: Dashboard > Email > Smart Reply Drafts > Generate / Edit
  - Page header: title + "Back" button to index

  - Two-column layout (xl:grid-cols-3):

    LEFT (xl:col-span-2, space-y-6):

      - Source Email card:
        - Section heading: "SOURCE EMAIL"
        - Display from_name/from_email, subject, snippet in read-only fields
        - bg-dark-700 styled preview block showing the email content being replied to

      - Reply Draft card:
        - Section heading: "REPLY DRAFT"
        - If no draft generated yet: empty state with "Configure options and click Generate"
        - If generated: display generated_body in a bg-dark-700 rounded-lg block with text-gray-300
        - Below generated_body: editable textarea for edited_body with label "Edit Reply"
        - Character count indicator below textarea

    RIGHT (xl:col-span-1, space-y-6):

      - Options card:
        - Section heading: "GENERATION OPTIONS"
        - Tone select: formal, friendly, brief (styled select input)
        - Template select: dropdown of email templates (option value = template ID, "None" as default)
        - Extra context textarea: optional instructions for the AI (max 1000 chars)
        - "Generate Reply" primary button with loading spinner (wire:loading on generate)
          - Disabled if no emailId selected
          - Shows "Generating..." with spinner during AI call

      - Status card (visible only when draft exists):
        - Section heading: "DRAFT STATUS"
        - Status badge (draft/copied/sent)
        - AI model used (text-gray-500)
        - Generated at (relative timestamp)
        - Copied at (if applicable)

      - Actions card (visible only when draft exists):
        - "Copy to Clipboard" button (primary style) — dispatches Alpine.js clipboard event
        - "Mark as Sent" button (secondary/emerald style) — updates status
        - "Regenerate" button (secondary style) — calls generate again with current options
        - "Delete Draft" danger button (with wire:confirm)

  - Alpine.js integration:
    - x-data with copyToClipboard method that uses navigator.clipboard.writeText()
    - Listens for 'copy-to-clipboard' browser event dispatched from Livewire
    - Shows a brief "Copied!" toast/notification on successful copy
```

---

## 6. VALIDATION RULES

```
Form: SmartReplyForm (generate)
  - emailId: required|exists:emails,id
  - tone: required|in:formal,friendly,brief
  - templateId: nullable|exists:email_templates,id
  - promptContext: nullable|string|max:1000

Form: SmartReplyForm (saveEdit)
  - editedBody: required|string|max:10000
```

---

## 7. EDGE CASES & BUSINESS RULES

- **Delete cascade from emails:** When an email is deleted from the emails table, all associated smart_reply_drafts are cascade-deleted.
- **Template deletion:** When an email_template is deleted, template_id on existing drafts is set to null (on delete set null). The generated_body remains intact.
- **Multiple drafts per email:** An email can have multiple drafts (different tones, templates, or regenerated versions). There is no unique constraint on email_id.
- **Edited body vs generated body:** The `final_body` accessor returns `edited_body` if it is not null, otherwise `generated_body`. This is what gets copied to clipboard.
- **AI failure handling:** If the AI call fails (API key missing, rate limit, network error), flash an error message and do not create a draft record. The service should catch exceptions and re-throw a domain-specific exception.
- **API key requirement:** The AI generation depends on a configured API key in the `api_keys` table (from Settings module). If no key is configured, show an error message with a link to Settings > API Keys.
- **Status transitions:** draft -> copied -> sent. Statuses can also go draft -> sent (manually marked). Copying always updates copied_at timestamp.
- **Tone display:** Tone is stored as a lowercase string (formal, friendly, brief) and displayed with a capitalized label and color-coded badge.
- **Empty email body:** If the source email has no body_preview or snippet, the AI prompt should still work using just the subject line. The prompt builder should handle this gracefully.
- **Long generated bodies:** The generated_body and edited_body columns are TEXT type to accommodate long replies. The UI textarea should have a reasonable max height with scroll.
- **Sort order:** Default list sorting is by created_at descending (newest drafts first).

---

## 8. IMPLEMENTATION ORDER

```
1. Migration: database/migrations/2026_04_01_200006_create_smart_reply_drafts_table.php
2. Model: app/Models/Email/SmartReplyDraft.php
3. Service: app/Services/SmartReplyDraftService.php
4. Route file: routes/admin/email/smart-reply.php
5. Livewire component: app/Livewire/Admin/Email/SmartReply/SmartReplyIndex.php
6. Livewire component: app/Livewire/Admin/Email/SmartReply/SmartReplyForm.php
7. View: resources/views/livewire/admin/email/smart-reply/index.blade.php
8. View: resources/views/livewire/admin/email/smart-reply/form.blade.php
9. Sidebar: Add "Smart Reply" link under Email module group in admin layout
```
