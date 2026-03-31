# Profile Settings — Spec

> Generated from: `plan/2-settings/profile-settings.md`
> Output path: `docs/settings-module/profile-settings-spec.md`

---

## 1. MODULE OVERVIEW

**Side: ADMIN**

Profile Settings is an admin-only page that lets the authenticated user manage all personal information displayed on the public portfolio and used across the dashboard. It consolidates and extends the existing ProfileEdit component with additional fields and sections.

### Features
- Edit basic info: name (on User model), tagline, bio, availability status
- Edit contact info: secondary email, phone, location
- Edit social links: LinkedIn, GitHub, Fiverr, YouTube
- Set preferences: timezone, language
- Change dashboard password (current password verification via `Hash::check()`)
- Upload / replace / remove profile photo

### Admin features
- Single edit page at `/admin/settings/profile` (no index/list page needed — single-user profile)
- All CRUD logic delegated to `ProfileSettingsService`
- Password change is a separate form action within the same page

---

## 2. DATABASE SCHEMA

### Existing table: `users`
No schema changes. The `name` field is edited via this page but already exists.

```
Table: users (existing — no changes)
Columns:
  - id (bigint, primary key, auto increment)
  - name (string, required) — edited from this page
  - email (string, required, unique) — read-only display
  - password (string, required) — updated via change password form
  - remember_token (string, nullable)
  - email_verified_at (timestamp, nullable)
  - created_at, updated_at (timestamps)
```

### Existing table: `profiles` — with new migration to add columns

```
Table: profiles (existing — add 4 new columns via migration)
Existing columns:
  - id (bigint, primary key, auto increment)
  - user_id (bigint, foreign key → users.id, unique, cascade on delete)
  - tagline (string 255, nullable)
  - bio (text, nullable)
  - profile_image (string 255, nullable)
  - secondary_email (string 255, nullable)
  - phone (string 50, nullable)
  - location (string 255, nullable)
  - linkedin_url (string 255, nullable)
  - github_url (string 255, nullable)
  - availability_status (string 100, nullable)
  - created_at, updated_at (timestamps)

New columns (added by migration):
  - fiverr_url (string 255, nullable) — after github_url
  - youtube_url (string 255, nullable) — after fiverr_url
  - timezone (string 100, nullable, default: 'UTC') — after availability_status
  - language (string 10, nullable, default: 'en') — after timezone

Indexes: existing unique index on user_id
Foreign keys: user_id → users.id (cascade on delete)
```

---

## 3. FILE MAP

```
MIGRATION:
  - database/migrations/YYYY_MM_DD_XXXXXX_add_social_and_preference_fields_to_profiles_table.php
    Adds: fiverr_url, youtube_url, timezone, language to profiles table

MODEL (existing — update only):
  - app/Models/Profile.php
    - Add to $fillable: fiverr_url, youtube_url, timezone, language
    - No new relationships or casts needed

SERVICE:
  - app/Services/ProfileSettingsService.php
    - updateProfile(Profile $profile, array $data): Profile — updates profile fields
    - updateProfileImage(Profile $profile, UploadedFile $image): string — stores image, deletes old, returns new path
    - removeProfileImage(Profile $profile): void — deletes image from storage, nulls field
    - updateUserName(User $user, string $name): void — updates user name
    - changePassword(User $user, string $newPassword): void — hashes and saves new password

--- ADMIN FILES ---

LIVEWIRE COMPONENT:
  - app/Livewire/Admin/Settings/ProfileSettings/ProfileSettingsEdit.php
    Public properties:
      - ?int $profileId
      - string $name (from User model)
      - string $tagline
      - string $bio
      - $profile_image (TemporaryUploadedFile|null — for new upload)
      - ?string $existing_image
      - string $secondary_email
      - string $phone
      - string $location
      - string $linkedin_url
      - string $github_url
      - string $fiverr_url
      - string $youtube_url
      - string $timezone
      - string $language
      - string $availability_status
      - string $current_password
      - string $new_password
      - string $new_password_confirmation
    Methods:
      - mount(): void — loads profile + user data into properties
      - save(): void — validates profile fields, delegates to service, flash success
      - removeImage(): void — delegates to service, resets image properties
      - changePassword(): void — validates passwords, verifies current via Hash::check(), delegates to service, flash success
    Uses traits: WithFileUploads

VIEW:
  - resources/views/livewire/admin/settings/profile-settings/edit.blade.php
    - 2/3 + 1/3 grid layout
    - Left column (2/3): Basic Info card, Contact Info card, Social Links card, Preferences card, Change Password card
    - Right column (1/3): Profile image upload card

ROUTE:
  - routes/admin/settings/profile-settings.php
    - GET /settings/profile → ProfileSettingsEdit → admin.settings.profile
```

---

## 4. COMPONENT CONTRACTS

### Admin Component

```
Component: App\Livewire\Admin\Settings\ProfileSettings\ProfileSettingsEdit
Namespace: App\Livewire\Admin\Settings\ProfileSettings
Layout: #[Layout('components.layouts.admin')]
Traits: WithFileUploads

Properties:
  - $profileId (?int) — existing profile ID or null
  - $name (string) — user's display name (from User model)
  - $tagline (string) — professional headline
  - $bio (string) — about text
  - $profile_image (TemporaryUploadedFile|null) — new image upload
  - $existing_image (?string) — current stored image path
  - $secondary_email (string) — contact email
  - $phone (string) — phone number
  - $location (string) — city/country
  - $linkedin_url (string) — LinkedIn profile URL
  - $github_url (string) — GitHub profile URL
  - $fiverr_url (string) — Fiverr profile URL
  - $youtube_url (string) — YouTube channel URL
  - $timezone (string) — e.g. 'America/New_York'
  - $language (string) — e.g. 'en'
  - $availability_status (string) — e.g. 'Available for freelance'
  - $current_password (string) — for password change verification
  - $new_password (string) — new password
  - $new_password_confirmation (string) — confirmation

Methods:
  - mount()
    Input: none (uses auth()->user())
    Does:
      1. Load auth user and their profile
      2. Populate all properties from User ($name) and Profile (all other fields)
      3. Set defaults for timezone ('UTC') and language ('en') if null
    Output: void

  - save(ProfileSettingsService $service)
    Input: injected service
    Does:
      1. Validate profile fields (see validation rules below)
      2. Call $service->updateUserName() for name
      3. Handle image upload via $service->updateProfileImage() if $profile_image is set
      4. Call $service->updateProfile() with remaining validated data
      5. Flash success message
      6. Redirect to same page with navigate: true
    Output: redirect with flash

  - removeImage(ProfileSettingsService $service)
    Input: injected service
    Does:
      1. Call $service->removeProfileImage()
      2. Reset $existing_image to null and $profile_image to null
    Output: void

  - changePassword(ProfileSettingsService $service)
    Input: injected service
    Does:
      1. Validate current_password, new_password, new_password_confirmation
      2. Verify current_password against stored hash via Hash::check()
      3. If mismatch, add validation error on current_password and return
      4. Call $service->changePassword() with new password
      5. Reset password fields to empty strings
      6. Flash success message
    Output: flash message
```

### Service

```
Service: App\Services\ProfileSettingsService

Methods:
  - updateProfile(Profile $profile, array $data): Profile
    Does: $profile->update($data), returns updated profile

  - updateProfileImage(Profile $profile, UploadedFile $image): string
    Does:
      1. Delete old image from storage if exists
      2. Store new image in 'profile-images' directory on 'public' disk
      3. Update profile's profile_image field
      4. Return new image path

  - removeProfileImage(Profile $profile): void
    Does:
      1. Delete image file from public storage
      2. Set profile_image to null and save

  - updateUserName(User $user, string $name): void
    Does: $user->update(['name' => $name])

  - changePassword(User $user, string $newPassword): void
    Does: $user->update(['password' => Hash::make($newPassword)])
```

---

## 5. VIEW BLUEPRINTS

### Admin View

```
View: resources/views/livewire/admin/settings/profile-settings/edit.blade.php
Layout: components.layouts.admin
Side: ADMIN

Design rules:
  - Cards: rounded-xl, bg-dark-800 border border-dark-700
  - Color alias: primary / primary-light
  - Headings: font-mono uppercase tracking-wider
  - Inputs: bg-dark-700 border border-dark-600 rounded-lg

Page structure:

BREADCRUMB:
  Dashboard > Settings > Profile Settings (active)

PAGE HEADER:
  - Title: "Profile Settings"
  - Subtitle: "Manage your personal information, social links, and account preferences."
  - No action button (this is a single edit page, not a list)

FLASH MESSAGE:
  - Success/error banner at top (same pattern as existing profile-edit)

LAYOUT: <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

LEFT COLUMN (xl:col-span-2, space-y-6):

  CARD 1 — Basic Information:
    Section heading: "Basic Information"
    Fields (single column):
      - Name (text input, required) — from User model
      - Tagline (text input, placeholder: "e.g. Full-Stack Developer")
      - Bio (textarea, 5 rows, placeholder: "Tell visitors about yourself...")
      - Availability Status (text input, placeholder: "e.g. Available for freelance")
    Save button at bottom of this card: "Save Profile"
    Loading state on save button

  CARD 2 — Contact Information:
    Section heading: "Contact Information"
    Fields (2-column grid on sm+):
      - Secondary Email (email input, placeholder: "contact@example.com")
      - Phone (text input, placeholder: "+1 234 567 890")
      - Location (text input, full width or in grid, placeholder: "City, Country")

  CARD 3 — Social Links:
    Section heading: "Social Links"
    Fields (2-column grid on sm+):
      - LinkedIn URL (url input, placeholder: "https://linkedin.com/in/username")
      - GitHub URL (url input, placeholder: "https://github.com/username")
      - Fiverr URL (url input, placeholder: "https://fiverr.com/username")
      - YouTube URL (url input, placeholder: "https://youtube.com/@channel")

  CARD 4 — Preferences:
    Section heading: "Preferences"
    Fields (2-column grid on sm+):
      - Timezone (select dropdown — populated with common timezones)
      - Language (select dropdown — options: English, etc.)

  CARD 5 — Change Password:
    Section heading: "Change Password"
    Description: "Leave blank if you don't want to change your password."
    Fields (single column):
      - Current Password (password input)
      - New Password (password input)
      - Confirm New Password (password input)
    Separate button: "Update Password" (wire:click="changePassword")
    Loading state on update password button

RIGHT COLUMN (1/3, space-y-6):

  CARD — Profile Image:
    Section heading: "Profile Image"
    Content:
      - If $profile_image (new upload): show temporary preview with remove button
      - Elseif $existing_image: show stored image with remove button (wire:confirm)
      - Else: show placeholder avatar icon
      - File input label styled as dashed border upload area
      - Accepted: JPG, PNG, WebP. Max 2MB.
      - Error display for profile_image validation
```

---

## 6. VALIDATION RULES

### Profile Save Form
```
Form: save (profile + user data)
  - name: required|string|max:255
  - tagline: nullable|string|max:255
  - bio: nullable|string|max:5000
  - profile_image: nullable|image|max:2048|mimes:jpg,jpeg,png,webp
  - secondary_email: nullable|email|max:255
  - phone: nullable|string|max:50
  - location: nullable|string|max:255
  - linkedin_url: nullable|url|max:255
  - github_url: nullable|url|max:255
  - fiverr_url: nullable|url|max:255
  - youtube_url: nullable|url|max:255
  - timezone: nullable|string|max:100
  - language: nullable|string|max:10
  - availability_status: nullable|string|max:100
```

### Change Password Form
```
Form: changePassword
  - current_password: required|string
  - new_password: required|string|min:8|confirmed
  - new_password_confirmation: required|string
```

Note: `current_password` is verified manually via `Hash::check()`; if it fails, a validation error is added with `$this->addError('current_password', 'The current password is incorrect.')`.

---

## 7. EDGE CASES & BUSINESS RULES

- **Profile auto-creation**: If the auth user has no profile record yet, use `Profile::updateOrCreate(['user_id' => auth()->id()], $data)` in the service to create it on first save.
- **Image replacement**: When uploading a new image, the old file must be deleted from storage before saving the new path.
- **Image removal**: Deleting the image sets `profile_image` to null and removes the file from the `public` disk.
- **Password verification**: Current password must match the stored hash. On mismatch, add a validation error (do not throw an exception).
- **Password fields isolation**: Password change is a separate action (`changePassword` method) with its own validation. It does not interfere with the profile save action. After successful password change, reset all three password fields to empty strings.
- **Timezone defaults**: If timezone is null in the database, default to `'UTC'` in `mount()`.
- **Language defaults**: If language is null in the database, default to `'en'` in `mount()`.
- **Name field**: The `name` field lives on the `User` model, not `Profile`. The service must update both models separately.
- **No delete**: There is no "delete profile" action — this page only edits.
- **Single user**: This page always operates on `auth()->user()` and their associated profile. No ID is passed via route.
- **Existing ProfileEdit component**: The existing `app/Livewire/Admin/ProfileEdit.php` remains untouched for now. The new `ProfileSettingsEdit` component replaces its functionality under the Settings module. A future cleanup task can deprecate/remove the old component.
- **Social URL format**: URLs are validated with Laravel's `url` rule, which requires the protocol (https://).

---

## 8. IMPLEMENTATION ORDER

```
1. Migration: database/migrations/YYYY_MM_DD_XXXXXX_add_social_and_preference_fields_to_profiles_table.php
   - Add fiverr_url, youtube_url, timezone, language columns

2. Model update: app/Models/Profile.php
   - Add fiverr_url, youtube_url, timezone, language to $fillable array

3. Service: app/Services/ProfileSettingsService.php
   - Create with all 5 methods

4. Route file: routes/admin/settings/profile-settings.php
   - Single GET route for the edit page

5. Livewire component: app/Livewire/Admin/Settings/ProfileSettings/ProfileSettingsEdit.php
   - Properties, mount(), save(), removeImage(), changePassword()

6. Admin view: resources/views/livewire/admin/settings/profile-settings/edit.blade.php
   - Full page with 2/3 + 1/3 grid, all 5 cards + image sidebar

7. Sidebar update: resources/views/components/layouts/admin.blade.php
   - Add "Settings" collapsible group with "Profile" link pointing to admin.settings.profile
```
