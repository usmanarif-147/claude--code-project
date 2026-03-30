# UI Pattern Reference -- Admin Panel

All new admin views MUST use these exact patterns. No new design patterns allowed.

---

## Table of Contents

1. [Color System](#color-system)
2. [Page Headers](#page-headers)
3. [Card Containers](#card-containers)
4. [Form Inputs](#form-inputs)
5. [Tables](#tables)
6. [Buttons](#buttons)
7. [Validation Errors](#validation-errors)
8. [Empty States](#empty-states)
9. [Pagination](#pagination)
10. [Filter/Search Bar](#filtersearch-bar)
11. [Status Badges](#status-badges)
12. [Category Badges](#category-badges)
13. [Flash Messages](#flash-messages)
14. [Loading States](#loading-states)
15. [Modals](#modals)
16. [Confirmation Dialogs](#confirmation-dialogs)
17. [Toggle/Checkbox](#togglecheckbox)
18. [File Upload](#file-upload)
19. [Section Headers Inside Cards](#section-headers-inside-cards)
20. [Sidebar Navigation Links](#sidebar-navigation-links)
21. [Action Icons (Edit/Delete/Preview)](#action-icons)
22. [Dynamic List (Add/Remove Items)](#dynamic-list)
23. [Collapsible Sections](#collapsible-sections)
24. [Bulk Actions](#bulk-actions)
25. [Sortable Table Headers](#sortable-table-headers)

---

## Color System

Defined in `resources/css/app.css` via `@theme`:

| Token          | Value       | Usage                          |
|----------------|-------------|--------------------------------|
| `dark-900`     | `#0a0a0f`   | Page background (`body`)       |
| `dark-800`     | `#12121a`   | Card backgrounds               |
| `dark-700`     | `#1a1a2e`   | Borders, table header bg       |
| `dark-600`     | (implicit)  | Input borders, toggle bg       |
| `accent-500`   | `#6366f1`   | Primary indigo (buttons, focus)|
| `accent-400`   | (lighter)   | Links, icon colors, badges     |
| `accent-600`   | (darker)    | Button hover                   |
| `text-white`   |             | Headings, primary text         |
| `text-gray-400`|             | Secondary text, labels         |
| `text-gray-500`|             | Descriptions, placeholders     |
| `text-red-400` |             | Errors, danger actions         |
| `text-green-400`|            | Success badges/messages        |

---

## Page Headers

### Index Page Header (with action button)

Used on: skill-index, technology-index, experience-index, file-manager

```blade
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold text-white">Page Title</h1>
        <p class="text-gray-500 mt-1">Short description of the page.</p>
    </div>
    <a href="{{ route('admin.things.create') }}" wire:navigate
       class="bg-accent-500 hover:bg-accent-600 text-white font-medium rounded-lg px-4 py-2.5 transition-colors text-sm flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Thing
    </a>
</div>
```

### Form Page Header (no action button)

Used on: skill-form, technology-form, experience-form, profile-edit, dashboard

```blade
<div class="mb-8">
    <h1 class="text-2xl font-bold text-white">{{ $model ? 'Edit Thing' : 'Create Thing' }}</h1>
    <p class="text-gray-500 mt-1">{{ $model ? 'Update thing details.' : 'Add a new thing.' }}</p>
</div>
```

---

## Card Containers

### Standard Card

```blade
<div class="bg-dark-800 border border-dark-700 rounded-xl p-6 space-y-5">
    {{-- content --}}
</div>
```

### Card with Section Title

```blade
<div class="bg-dark-800 border border-dark-700 rounded-xl p-6 space-y-5">
    <h2 class="text-lg font-semibold text-white">Section Title</h2>
    {{-- fields --}}
</div>
```

### Card with Header + Action Link

Used in dashboard overview cards:

```blade
<div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-lg font-semibold text-white">Section Title</h2>
        <a href="{{ route('admin.things.index') }}" wire:navigate class="text-xs text-accent-400 hover:underline">View all</a>
    </div>
    {{-- content --}}
</div>
```

### Stats Card (Dashboard)

```blade
<div class="bg-dark-800 border border-dark-700 rounded-xl p-5">
    <div class="flex items-center justify-between mb-3">
        <span class="text-sm text-gray-500">{{ $stat['label'] }}</span>
        <span class="w-9 h-9 rounded-lg bg-accent-500/10 flex items-center justify-center">
            <svg class="w-5 h-5 text-accent-400" ...>...</svg>
        </span>
    </div>
    <p class="text-3xl font-bold text-white">{{ $stat['value'] }}</p>
</div>
```

### Form Container Width

- Simple forms (skill, technology): `<form wire:submit="save" class="max-w-2xl">`
- Complex forms (experience): `<form wire:submit="save" class="max-w-3xl space-y-6">`
- Multi-column forms (profile): full width with `grid grid-cols-1 lg:grid-cols-3 gap-6`

---

## Form Inputs

### Text Input

```blade
<div>
    <label for="field_name" class="block text-sm font-medium text-gray-400 mb-1.5">Label <span class="text-red-400">*</span></label>
    <input type="text" id="field_name" wire:model="field_name"
           class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-accent-500 focus:border-transparent"
           placeholder="e.g. Example">
    @error('field_name') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
</div>
```

Notes:
- Required fields show `<span class="text-red-400">*</span>` after the label text.
- Optional fields omit the asterisk.
- The `for` and `id` attributes must match.

### Number Input

Same as text input but with `type="number"` and optional `min="0"`.

### Date Input

```blade
<div>
    <label for="start_date" class="block text-sm font-medium text-gray-400 mb-1.5">Start Date <span class="text-red-400">*</span></label>
    <input type="date" id="start_date" wire:model="start_date"
           class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-accent-500 focus:border-transparent">
    @error('start_date') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
</div>
```

### Disabled Date Input (conditional)

Add: `disabled:opacity-50 disabled:cursor-not-allowed` to the class, and `{{ $condition ? 'disabled' : '' }}` attribute.

### Select Input

```blade
<div>
    <label for="category" class="block text-sm font-medium text-gray-400 mb-1.5">Category <span class="text-red-400">*</span></label>
    <select id="category" wire:model="category"
            class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-accent-500 focus:border-transparent">
        <option value="option1">Option 1</option>
        <option value="option2">Option 2</option>
    </select>
    @error('category') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
</div>
```

### Textarea

```blade
<div>
    <label for="bio" class="block text-sm font-medium text-gray-400 mb-1.5">Bio</label>
    <textarea id="bio" wire:model="bio" rows="5"
              class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-accent-500 focus:border-transparent"
              placeholder="Tell visitors about yourself..."></textarea>
    @error('bio') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
</div>
```

For monospace textareas (e.g. SVG/code input), add `font-mono text-sm` to the class.

### Two-Column Grid for Fields

```blade
<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
    <div>{{-- field 1 --}}</div>
    <div>{{-- field 2 --}}</div>
</div>
```

---

## Toggle / Checkbox

### Toggle Switch (is_active, is_current, etc.)

```blade
<label class="relative inline-flex items-center cursor-pointer">
    <input type="checkbox" wire:model="is_active" class="sr-only peer">
    <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-accent-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-accent-500"></div>
    <span class="ml-3 text-sm font-medium text-gray-400">Active</span>
</label>
```

When placed alongside a sort_order field in a 2-col grid, wrap with:

```blade
<div class="flex items-center pt-6">
    {{-- toggle here --}}
</div>
```

For live-updating toggles (like `is_current` that disables another field), use `wire:model.live`.

### Standard Checkbox (login "remember me")

```blade
<input type="checkbox" wire:model="remember" id="remember"
       class="w-4 h-4 rounded border-dark-600 bg-dark-700 text-accent-500 focus:ring-accent-500 focus:ring-offset-0">
<label for="remember" class="ml-2 text-sm text-gray-400">Remember me</label>
```

### Table Row Checkbox (for bulk select)

```blade
<input type="checkbox" wire:model.live="selectAll"
       class="rounded border-dark-600 bg-dark-700 text-accent-500 focus:ring-accent-500">
```

---

## Tables

### Full Table Structure

```blade
<div class="bg-dark-800 border border-dark-700 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-dark-700/50">
                    <th class="text-left text-xs font-medium text-gray-400 uppercase tracking-wider px-6 py-3">Column</th>
                    {{-- ... more columns ... --}}
                    <th class="text-right text-xs font-medium text-gray-400 uppercase tracking-wider px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-dark-700">
                @forelse ($items as $item)
                    <tr class="hover:bg-dark-700/30 transition-colors">
                        <td class="px-6 py-4 text-sm text-white font-medium">{{ $item->name }}</td>
                        {{-- ... more cells ... --}}
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                {{-- action buttons --}}
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            No items found. <a href="{{ route('admin.things.create') }}" wire:navigate class="text-accent-400 hover:underline">Create one</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($items->hasPages())
        <div class="px-6 py-4 border-t border-dark-700">
            {{ $items->links() }}
        </div>
    @endif
</div>
```

### Table Cell Text Styles

- Primary column (name/title): `text-sm text-white font-medium`
- Secondary columns: `text-sm text-gray-400`
- Truncated cells: add `max-w-[200px] truncate`
- Empty/null value placeholder: `<span class="text-gray-500 text-sm">---</span>` (em dash)

---

## Buttons

### Primary Submit Button (with loading state)

```blade
<button type="submit"
        class="bg-accent-500 hover:bg-accent-600 text-white font-medium rounded-lg px-6 py-2.5 transition-colors flex items-center gap-2">
    <span wire:loading.remove wire:target="save">{{ $model ? 'Update Thing' : 'Create Thing' }}</span>
    <span wire:loading wire:target="save" class="flex items-center gap-2">
        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
        Saving...
    </span>
</button>
```

### Primary Action Button (smaller, for headers/upload)

```blade
<button wire:click="saveFiles" wire:loading.attr="disabled"
        class="bg-accent-500 hover:bg-accent-600 disabled:opacity-50 text-white font-medium rounded-lg px-4 py-2.5 transition-colors text-sm flex items-center gap-2">
    {{-- loading spinner + text --}}
</button>
```

### Cancel / Secondary Link Button

```blade
<a href="{{ route('admin.things.index') }}" wire:navigate
   class="text-gray-400 hover:text-white font-medium rounded-lg px-6 py-2.5 transition-colors">
    Cancel
</a>
```

### Secondary Outlined Button

```blade
<button wire:click="clearQueue"
        class="text-gray-400 hover:text-white font-medium rounded-lg px-4 py-2.5 transition-colors text-sm border border-dark-600 hover:border-dark-500">
    Clear All
</button>
```

### Danger/Delete Bulk Action Button

```blade
<button wire:click="bulkDelete" wire:confirm="Are you sure?"
        class="bg-red-500/10 hover:bg-red-500/20 text-red-400 font-medium rounded-lg px-3 py-1.5 transition-colors text-sm flex items-center gap-1.5">
    <svg class="w-4 h-4" ...>{{-- trash icon --}}</svg>
    Delete Selected
</button>
```

### Full-Width Submit Button (login)

```blade
<button type="submit"
        class="w-full py-2.5 px-4 bg-accent-500 hover:bg-accent-600 text-white font-medium rounded-lg transition-colors flex items-center justify-center gap-2 disabled:opacity-50"
        wire:loading.attr="disabled">
    {{-- spinner + text --}}
</button>
```

### Form Action Bar Layout

```blade
<div class="mt-6 flex items-center gap-3">
    {{-- primary submit button --}}
    {{-- cancel link --}}
</div>
```

---

## Validation Errors

Always placed directly after the input, inside the same `<div>` wrapper:

```blade
@error('field_name') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
```

For nested/array fields:

```blade
@error("responsibilities.{$index}.description") <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
```

For file upload arrays:

```blade
@error('uploadQueue.*')
    <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
@enderror
```

---

## Empty States

### Table Empty State

```blade
@empty
    <tr>
        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
            No items found. <a href="{{ route('admin.things.create') }}" wire:navigate class="text-accent-400 hover:underline">Create one</a>.
        </td>
    </tr>
@endforelse
```

### Dashboard Card Empty State

```blade
<p class="text-gray-500 text-sm text-center py-4">No items added yet. <a href="{{ route('admin.things.create') }}" wire:navigate class="text-accent-400 hover:underline">Add one</a>.</p>
```

### Inline Empty State (inside a form section)

```blade
<p class="text-gray-500 text-sm text-center py-4">No responsibilities added yet. Click "Add Responsibility" to begin.</p>
```

---

## Pagination

```blade
@if ($items->hasPages())
    <div class="px-6 py-4 border-t border-dark-700">
        {{ $items->links() }}
    </div>
@endif
```

This goes inside the table card container, after the `</div>` that closes `overflow-x-auto`.

---

## Filter/Search Bar

### As Separate Card Above Table

Used on: skill-index, technology-index, experience-index

```blade
<div class="bg-dark-800 border border-dark-700 rounded-xl p-4 mb-6">
    <div class="flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search items..."
                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-accent-500 focus:border-transparent text-sm">
        </div>
        <select wire:model.live="activeFilter"
                class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-accent-500 focus:border-transparent text-sm">
            <option value="all">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>
</div>
```

### Embedded Inside Table Card

Used on: file-manager (filter bar inside the table card with `border-b border-dark-700`)

```blade
<div class="p-4 border-b border-dark-700">
    <div class="flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search..."
                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-accent-500 focus:border-transparent text-sm">
        </div>
        {{-- additional filter inputs --}}
    </div>
</div>
```

### Key Livewire Binding

- Search: `wire:model.live.debounce.300ms="search"`
- Select filters: `wire:model.live="filterName"`
- Date filters: `wire:model.live="dateFrom"` / `wire:model.live="dateTo"`

---

## Status Badges

### Active/Inactive

```blade
@if ($item->is_active)
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500/10 text-green-400">Active</span>
@else
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-500/10 text-gray-400">Inactive</span>
@endif
```

---

## Category Badges

Color-coded by category value:

```blade
@switch($item->category)
    @case('frontend')
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-500/10 text-blue-400">Frontend</span>
        @break
    @case('backend')
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-500/10 text-purple-400">Backend</span>
        @break
    @case('database_tools')
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400">Database & Tools</span>
        @break
@endswitch
```

### Generic Accent Badge (file type, tags)

```blade
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-accent-500/10 text-accent-400">
    {{ $value }}
</span>
```

### Neutral Tag Badge

```blade
<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-dark-700 text-gray-300">{{ $tag }}</span>
```

### Removable Tag Badge

```blade
<span class="inline-flex items-center gap-1 bg-accent-500/10 text-accent-400 text-xs font-medium px-2 py-0.5 rounded-full">
    {{ $tag }}
    <button wire:click="removeTag({{ $index }}, {{ $tagIndex }})" class="hover:text-red-400 transition-colors">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
</span>
```

### Count Badge (upload queue)

```blade
<span class="bg-accent-500/20 text-accent-400 text-xs font-medium px-2 py-0.5 rounded-full">{{ count($items) }} queued</span>
```

---

## Flash Messages

Defined in `components/layouts/admin.blade.php`. Auto-dismiss after 4 seconds.

### Success Flash

```blade
<div class="bg-green-500/10 border border-green-500/20 rounded-lg px-4 py-3 text-green-400 text-sm flex items-center gap-2">
    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    {{ session('success') }}
</div>
```

### Error Flash

```blade
<div class="bg-red-500/10 border border-red-500/20 rounded-lg px-4 py-3 text-red-400 text-sm flex items-center gap-2">
    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    {{ session('error') }}
</div>
```

### Flash Wrapper (with Alpine auto-dismiss)

```blade
@if (session('success') || session('error'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="mb-6">
        {{-- success or error div --}}
    </div>
@endif
```

Flash messages are dispatched from Livewire components with:

```php
session()->flash('success', 'Thing saved successfully.');
session()->flash('error', 'Something went wrong.');
```

---

## Loading States

### Submit Button Loading (standard pattern)

```blade
<span wire:loading.remove wire:target="save">Save Thing</span>
<span wire:loading wire:target="save" class="flex items-center gap-2">
    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
    Saving...
</span>
```

### Spinner SVG (reusable)

```blade
<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
</svg>
```

### Login Button Loading (slightly different: uses wire:loading.attr="disabled")

```blade
<button type="submit" ... wire:loading.attr="disabled">
    <svg wire:loading class="animate-spin w-4 h-4" ...>...</svg>
    <span wire:loading.remove>Sign In</span>
    <span wire:loading>Signing in...</span>
</button>
```

### File Upload Loading

```blade
<span wire:loading.remove wire:target="profile_image">Choose Image</span>
<span wire:loading wire:target="profile_image">Uploading...</span>
```

### Disable During Loading

```blade
wire:loading.attr="disabled"
{{-- combined with --}}
class="... disabled:opacity-50"
```

---

## Modals

### Full Modal Structure (overlay + content)

Used in: file-manager preview modal

```blade
@if ($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-data
         @keydown.escape.window="$wire.closeModal()">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/70" wire:click="closeModal"></div>

        {{-- Modal Content --}}
        <div class="relative bg-dark-800 border border-dark-700 rounded-xl w-full max-w-4xl max-h-[90vh] flex flex-col z-10">
            {{-- Modal Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-dark-700">
                <div>
                    <h3 class="text-white font-medium">Modal Title</h3>
                    <p class="text-gray-500 text-xs mt-0.5">Subtitle</p>
                </div>
                <button wire:click="closeModal" class="text-gray-400 hover:text-white transition-colors p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="flex-1 overflow-auto p-6">
                {{-- content --}}
            </div>

            {{-- Modal Footer (optional) --}}
            <div class="flex items-center gap-4 px-6 py-3 border-t border-dark-700 text-xs text-gray-500">
                {{-- metadata --}}
            </div>
        </div>
    </div>
@endif
```

Key details:
- Escape key closes via `@keydown.escape.window="$wire.closeModal()"`
- Backdrop click closes via `wire:click="closeModal"`
- Max width: `max-w-4xl` (adjust per use case)
- Max height: `max-h-[90vh]`
- Uses `flex flex-col` so body scrolls independently

---

## Confirmation Dialogs

Uses Livewire's built-in `wire:confirm` attribute (browser native confirm):

```blade
<button wire:click="delete({{ $item->id }})" wire:confirm="Are you sure you want to delete this item?"
        class="text-gray-400 hover:text-red-400 transition-colors p-1">
    {{-- trash icon --}}
</button>
```

---

## Action Icons

### Edit Action

```blade
<a href="{{ route('admin.things.edit', $item) }}" wire:navigate
   class="text-gray-400 hover:text-accent-400 transition-colors p-1">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
</a>
```

### Delete Action

```blade
<button wire:click="delete({{ $item->id }})" wire:confirm="Are you sure you want to delete this item?"
        class="text-gray-400 hover:text-red-400 transition-colors p-1">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
</button>
```

### Preview/View Action

```blade
<button wire:click="openPreview({{ $item->id }})"
        class="text-gray-400 hover:text-accent-400 transition-colors p-1">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
</button>
```

### Action Cell Container

```blade
<td class="px-6 py-4 text-right">
    <div class="flex items-center justify-end gap-2">
        {{-- edit, delete, preview icons --}}
    </div>
</td>
```

---

## File Upload

### Simple File Upload (profile image)

```blade
<div>
    <label for="profile_image"
           class="block w-full text-center cursor-pointer bg-dark-700 border border-dark-600 border-dashed rounded-lg px-4 py-3 text-sm text-gray-400 hover:text-white hover:border-accent-500 transition-colors">
        <span wire:loading.remove wire:target="profile_image">Choose Image</span>
        <span wire:loading wire:target="profile_image">Uploading...</span>
    </label>
    <input type="file" id="profile_image" wire:model="profile_image" class="hidden" accept="image/jpg,image/jpeg,image/png,image/webp">
</div>
@error('profile_image') <p class="text-sm text-red-400">{{ $message }}</p> @enderror
<p class="text-xs text-gray-500">JPG, PNG or WebP. Max 2MB.</p>
```

### Drag & Drop Upload Zone (file-manager)

```blade
<div x-data="{ dragging: false }"
     x-on:dragover.prevent="dragging = true"
     x-on:dragleave.prevent="dragging = false"
     x-on:drop.prevent="dragging = false; /* handle files */"
     class="border-2 border-dashed rounded-xl p-8 text-center transition-colors cursor-pointer"
     :class="dragging ? 'border-accent-400 bg-accent-500/5' : 'border-dark-600 hover:border-dark-500'"
     @click="$refs.fileInput.click()">
    <svg class="w-10 h-10 mx-auto mb-3 text-gray-500" ...>...</svg>
    <p class="text-gray-400 text-sm">Drag & drop files here or <span class="text-accent-400">browse</span></p>
    <p class="text-gray-500 text-xs mt-1">Accepted formats -- max 10MB each</p>
    <input x-ref="fileInput" type="file" wire:model="uploadQueue" multiple accept="..." class="hidden">
</div>
```

---

## Dynamic List (Add/Remove Items)

Used in: experience-form responsibilities

### Section Header with Add Button

```blade
<div class="flex items-center justify-between mb-5">
    <h2 class="text-lg font-semibold text-white">Items</h2>
    <button type="button" wire:click="addItem"
            class="text-accent-400 hover:text-accent-300 text-sm font-medium flex items-center gap-1 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Item
    </button>
</div>
```

### Item Row with Remove Button

```blade
<div class="flex gap-3 items-start" wire:key="item-{{ $index }}">
    <div class="w-20 shrink-0">
        <label class="block text-xs font-medium text-gray-500 mb-1">Order</label>
        <input type="number" wire:model="items.{{ $index }}.sort_order" min="0"
               class="w-full bg-dark-700 border border-dark-600 rounded-lg px-3 py-2.5 text-white text-sm focus:ring-2 focus:ring-accent-500 focus:border-transparent">
    </div>
    <div class="flex-1">
        <label class="block text-xs font-medium text-gray-500 mb-1">Description</label>
        <textarea wire:model="items.{{ $index }}.description" rows="2"
                  class="w-full bg-dark-700 border border-dark-600 rounded-lg px-3 py-2.5 text-white placeholder-gray-500 text-sm focus:ring-2 focus:ring-accent-500 focus:border-transparent"
                  placeholder="Describe the item..."></textarea>
        @error("items.{$index}.description") <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
    </div>
    <button type="button" wire:click="removeItem({{ $index }})"
            class="mt-6 text-gray-400 hover:text-red-400 transition-colors p-1 shrink-0">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
    </button>
</div>
```

---

## Collapsible Sections

Used in: file-manager upload section, sidebar navigation

```blade
<div class="bg-dark-800 border border-dark-700 rounded-xl" x-data="{ open: false }">
    <button @click="open = !open"
            class="w-full flex items-center justify-between px-6 py-4 text-left">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 text-accent-400" ...>...</svg>
            <span class="text-white font-medium text-sm">Section Title</span>
        </div>
        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div x-show="open" x-collapse>
        <div class="px-6 pb-6 border-t border-dark-700 pt-4">
            {{-- collapsible content --}}
        </div>
    </div>
</div>
```

---

## Bulk Actions

Shown conditionally when items are selected:

```blade
@if (count($selectedIds) > 0)
    <div class="mt-3 flex items-center gap-3">
        <span class="text-gray-400 text-sm">{{ count($selectedIds) }} selected</span>
        <button wire:click="bulkDelete" wire:confirm="Are you sure you want to delete {{ count($selectedIds) }} file(s)?"
                class="bg-red-500/10 hover:bg-red-500/20 text-red-400 font-medium rounded-lg px-3 py-1.5 transition-colors text-sm flex items-center gap-1.5">
            <svg class="w-4 h-4" ...>{{-- trash icon --}}</svg>
            Delete Selected
        </button>
    </div>
@endif
```

---

## Sortable Table Headers

```blade
<th class="text-left text-xs font-medium text-gray-400 uppercase tracking-wider px-6 py-3 cursor-pointer select-none"
    wire:click="sortBy('column_name')">
    <span class="flex items-center gap-1">
        Column Name
        @if ($sortField === 'column_name')
            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                @if ($sortDirection === 'asc')
                    <path d="M5.293 9.707l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 7.414l-3.293 3.293a1 1 0 01-1.414-1.414z"/>
                @else
                    <path d="M14.707 10.293l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 12.586l3.293-3.293a1 1 0 111.414 1.414z"/>
                @endif
            </svg>
        @endif
    </span>
</th>
```

---

## Sidebar Navigation Links

### Top-Level Link

```blade
<a href="{{ route('admin.dashboard') }}"
   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-accent-500/10 text-accent-400' : 'text-gray-400 hover:text-white hover:bg-dark-700' }} transition-colors">
    <svg class="w-5 h-5" ...>...</svg>
    Dashboard
</a>
```

### Nested/Sub Link

```blade
<a href="{{ route('admin.skills.index') }}"
   class="flex items-center gap-3 pl-10 pr-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.skills.*') ? 'bg-accent-500/10 text-accent-400' : 'text-gray-400 hover:text-white hover:bg-dark-700' }} transition-colors">
    <svg class="w-5 h-5" ...>...</svg>
    Skills
</a>
```

Active state classes: `bg-accent-500/10 text-accent-400`
Inactive state classes: `text-gray-400 hover:text-white hover:bg-dark-700`

---

## Common SVG Icons

### Plus (Add)
```blade
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
```

### Edit (Pencil)
```blade
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
```

### Delete (Trash)
```blade
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
```

### Close (X)
```blade
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
```

### Eye (Preview)
```blade
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
```

### Spinner (Loading)
```blade
<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
```

### Chevron Down
```blade
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
```

### Chevron Right
```blade
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
```

---

## Quick Reference: Complete Page Templates

### Index Page Skeleton

```blade
<div>
    {{-- Header with Add button --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-white">Things</h1>
            <p class="text-gray-500 mt-1">Manage your things.</p>
        </div>
        <a href="{{ route('admin.things.create') }}" wire:navigate
           class="bg-accent-500 hover:bg-accent-600 text-white font-medium rounded-lg px-4 py-2.5 transition-colors text-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Thing
        </a>
    </div>

    {{-- Filters --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search things..."
                       class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-accent-500 focus:border-transparent text-sm">
            </div>
            <select wire:model.live="activeFilter"
                    class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-accent-500 focus:border-transparent text-sm">
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-dark-700/50">
                        <th class="text-left text-xs font-medium text-gray-400 uppercase tracking-wider px-6 py-3">Name</th>
                        <th class="text-left text-xs font-medium text-gray-400 uppercase tracking-wider px-6 py-3">Status</th>
                        <th class="text-right text-xs font-medium text-gray-400 uppercase tracking-wider px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700">
                    @forelse ($things as $thing)
                        <tr class="hover:bg-dark-700/30 transition-colors">
                            <td class="px-6 py-4 text-sm text-white font-medium">{{ $thing->name }}</td>
                            <td class="px-6 py-4">
                                @if ($thing->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500/10 text-green-400">Active</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-500/10 text-gray-400">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.things.edit', $thing) }}" wire:navigate
                                       class="text-gray-400 hover:text-accent-400 transition-colors p-1">
                                        {{-- edit icon --}}
                                    </a>
                                    <button wire:click="delete({{ $thing->id }})" wire:confirm="Are you sure?"
                                            class="text-gray-400 hover:text-red-400 transition-colors p-1">
                                        {{-- trash icon --}}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center text-gray-500">
                                No things found. <a href="{{ route('admin.things.create') }}" wire:navigate class="text-accent-400 hover:underline">Create one</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($things->hasPages())
            <div class="px-6 py-4 border-t border-dark-700">
                {{ $things->links() }}
            </div>
        @endif
    </div>
</div>
```

### Form Page Skeleton

```blade
<div>
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-white">{{ $model ? 'Edit Thing' : 'Create Thing' }}</h1>
        <p class="text-gray-500 mt-1">{{ $model ? 'Update thing details.' : 'Add a new thing.' }}</p>
    </div>

    <form wire:submit="save" class="max-w-2xl">
        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 space-y-5">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-400 mb-1.5">Name <span class="text-red-400">*</span></label>
                <input type="text" id="name" wire:model="name"
                       class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-accent-500 focus:border-transparent"
                       placeholder="e.g. Example">
                @error('name') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            {{-- more fields --}}

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-400 mb-1.5">Sort Order</label>
                    <input type="number" id="sort_order" wire:model="sort_order" min="0"
                           class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-accent-500 focus:border-transparent">
                    @error('sort_order') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center pt-6">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="is_active" class="sr-only peer">
                        <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-accent-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-accent-500"></div>
                        <span class="ml-3 text-sm font-medium text-gray-400">Active</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="mt-6 flex items-center gap-3">
            <button type="submit"
                    class="bg-accent-500 hover:bg-accent-600 text-white font-medium rounded-lg px-6 py-2.5 transition-colors flex items-center gap-2">
                <span wire:loading.remove wire:target="save">{{ $model ? 'Update Thing' : 'Create Thing' }}</span>
                <span wire:loading wire:target="save" class="flex items-center gap-2">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    Saving...
                </span>
            </button>
            <a href="{{ route('admin.things.index') }}" wire:navigate
               class="text-gray-400 hover:text-white font-medium rounded-lg px-6 py-2.5 transition-colors">
                Cancel
            </a>
        </div>
    </form>
</div>
```
