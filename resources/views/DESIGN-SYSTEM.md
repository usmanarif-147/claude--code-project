# Design System — Xintra-Inspired Dark Admin Theme

Every admin view MUST follow these patterns exactly. No new design patterns allowed.
This file is the single source of truth for all frontend agents.

---

## 1. Color Tokens

Defined in `resources/css/app.css` via `@theme`:

| Token | Hex | Usage |
|-------|-----|-------|
| `dark-950` | `#050508` | Deepest bg (modal overlays) |
| `dark-900` | `#0a0a0f` | Page background (body) |
| `dark-800` | `#111118` | Card backgrounds, sidebar |
| `dark-700` | `#1a1a24` | Input bg, table header bg, borders |
| `dark-600` | `#25253a` | Input borders, dividers, hover bg |
| `primary` | `#7c3aed` | Primary purple (buttons, focus rings, active states) |
| `primary-light` | `#a78bfa` | Links, active nav text, icon colors |
| `primary-dark` | `#6d28d9` | Pressed/active button state |
| `primary-hover` | `#8b5cf6` | Button hover state |
| `accent` | `#7c3aed` | Alias for primary (backward compat) |
| `accent-light` | `#a78bfa` | Alias for primary-light (backward compat) |

### Text Colors
| Class | Usage |
|-------|-------|
| `text-white` | Page titles, card headings, input values |
| `text-gray-300` | Body text, form labels |
| `text-gray-400` | Secondary text, table cell text |
| `text-gray-500` | Muted text, descriptions, placeholders |
| `text-gray-600` | Disabled text, empty state dashes |

### Status Colors
| Status | Background | Text | Usage |
|--------|-----------|------|-------|
| Success | `bg-emerald-500/10` | `text-emerald-400` | Active badges, success alerts |
| Warning | `bg-amber-500/10` | `text-amber-400` | Draft badges, warning alerts |
| Danger | `bg-red-500/10` | `text-red-400` | Errors, delete buttons, inactive |
| Info | `bg-blue-500/10` | `text-blue-400` | Info badges, links |
| Purple | `bg-primary/10` | `text-primary-light` | Active nav, featured items |

### Gradient Accents (use sparingly — for featured/premium elements only)
```
bg-gradient-to-r from-primary via-fuchsia-500 to-orange-500  — badge/tag highlight
bg-gradient-to-br from-primary/20 to-fuchsia-600/20         — CTA card background
bg-gradient-to-r from-primary to-fuchsia-500                 — progress bars
```

---

## 2. Typography

**Fonts:** Inter (sans-serif) for body, **Fira Code (monospace) for all headings**.
Both loaded via Google Fonts in the admin layout.

**CRITICAL RULE:** All headings (`h1`, `h2`, `h3`) in the admin panel MUST use `font-mono uppercase tracking-wider`. This gives the developer/hacker aesthetic. Body text, labels, inputs, and buttons stay in Inter (sans-serif).

| Element | Classes |
|---------|---------|
| Page title | `text-2xl font-mono font-bold text-white uppercase tracking-wider` |
| Page subtitle | `text-gray-500 mt-1` (Inter, normal case) |
| Card heading | `text-lg font-mono font-semibold text-white uppercase tracking-wider` |
| Section heading | `text-base font-mono font-semibold text-white uppercase tracking-wider` |
| Form label | `text-sm font-medium text-gray-300 mb-2` (Inter, normal case) |
| Body text | `text-sm text-gray-400` (Inter) |
| Table header | `text-xs font-mono font-medium text-gray-400 uppercase tracking-wider` |
| Stat label | `text-sm text-gray-500` (Inter) |
| Stat value | `text-3xl font-bold text-white` (Inter — numbers look better in sans) |
| Badge text | `text-xs font-medium` (Inter) |
| Button text | `text-sm font-medium` (Inter) |
| Nav link text | `text-sm font-medium` (Inter) |

**What uses font-mono:** h1, h2, h3, section titles, table headers, sidebar section labels
**What stays sans (Inter):** labels, body text, inputs, buttons, badges, nav links, stat numbers

---

## 3. Page Headers

### Index Page (with action button)
```blade
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Page Title</h1>
        <p class="text-gray-500 mt-1">Short description of the page.</p>
    </div>
    <a href="{{ route('admin.things.create') }}" wire:navigate
       class="bg-primary hover:bg-primary-hover text-white font-medium rounded-lg px-5 py-2.5 transition-colors text-sm inline-flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Thing
    </a>
</div>
```

### Form Page (no action button)
```blade
<div class="mb-8">
    <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">{{ $model ? 'Edit Thing' : 'Create Thing' }}</h1>
    <p class="text-gray-500 mt-1">{{ $model ? 'Update thing details.' : 'Add a new thing.' }}</p>
</div>
```

---

## 4. Cards

### Standard Card
```blade
<div class="bg-dark-800 border border-dark-700 rounded-xl p-6 space-y-5">
    {{-- content --}}
</div>
```

### Card with Heading
```blade
<div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
    <h2 class="text-lg font-mono font-semibold text-white uppercase tracking-wider mb-5">Section Title</h2>
    {{-- content --}}
</div>
```

### Stat Card (Dashboard)
```blade
<div class="bg-dark-800 border border-dark-700 rounded-xl p-5">
    <div class="flex items-center justify-between mb-3">
        <span class="text-sm text-gray-500">{{ $label }}</span>
        <span class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center">
            <svg class="w-5 h-5 text-primary-light" ...>...</svg>
        </span>
    </div>
    <p class="text-3xl font-bold text-white">{{ $value }}</p>
</div>
```

### Gradient CTA Card (for premium/promo sections — use sparingly)
```blade
<div class="bg-gradient-to-br from-primary/20 to-fuchsia-600/20 border border-primary/30 rounded-xl p-6">
    <h3 class="text-lg font-mono font-semibold text-white uppercase tracking-wider mb-2">Feature Title</h3>
    <p class="text-gray-400 text-sm">Description text here.</p>
</div>
```

---

## 5. Form Inputs

### Text Input
```blade
<div>
    <label for="name" class="block text-sm font-medium text-gray-300 mb-2">Name <span class="text-red-400">*</span></label>
    <input type="text" id="name" wire:model="name"
           placeholder="Enter name"
           class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-colors">
    @error('name') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
</div>
```

### Select
```blade
<select wire:model="status"
        class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent">
    <option value="">Select...</option>
    <option value="option1">Option 1</option>
</select>
```

### Textarea
```blade
<textarea wire:model="description" rows="4"
          class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent resize-none"></textarea>
```

### Two-Column Grid
```blade
<div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
    <div>{{-- field 1 --}}</div>
    <div>{{-- field 2 --}}</div>
</div>
```

### Toggle Switch
```blade
<label class="relative inline-flex items-center cursor-pointer">
    <input type="checkbox" wire:model="is_active" class="sr-only peer">
    <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
    <span class="ml-3 text-sm font-medium text-gray-400">Active</span>
</label>
```

---

## 6. Buttons

### Primary Button
```blade
<button type="submit"
        class="bg-primary hover:bg-primary-hover text-white font-medium rounded-lg px-5 py-2.5 transition-colors text-sm inline-flex items-center gap-2">
    <span wire:loading.remove wire:target="save">Save</span>
    <span wire:loading wire:target="save">
        <svg class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
    </span>
</button>
```

### Secondary Button (outlined)
```blade
<button class="bg-transparent border border-dark-600 text-gray-300 hover:bg-dark-700 font-medium rounded-lg px-5 py-2.5 transition-colors text-sm">
    Cancel
</button>
```

### Danger Button
```blade
<button class="bg-red-500/10 text-red-400 hover:bg-red-500/20 font-medium rounded-lg px-4 py-2 transition-colors text-sm">
    Delete
</button>
```

### Small Action Button (table rows)
```blade
<a href="{{ route('admin.things.edit', $thing) }}" wire:navigate
   class="text-primary-light hover:text-white transition-colors">
    <svg class="w-4.5 h-4.5" ...>{{-- edit icon --}}</svg>
</a>
<button wire:click="delete({{ $thing->id }})" wire:confirm="Are you sure?"
        class="text-gray-400 hover:text-red-400 transition-colors">
    <svg class="w-4.5 h-4.5" ...>{{-- trash icon --}}</svg>
</button>
```

### Gradient Button (special CTAs only)
```blade
<button class="bg-gradient-to-r from-primary to-fuchsia-500 hover:from-primary-hover hover:to-fuchsia-400 text-white font-medium rounded-lg px-5 py-2.5 transition-all text-sm">
    Upgrade
</button>
```

---

## 7. Tables

### Full Table Structure
```blade
<div class="bg-dark-800 border border-dark-700 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-dark-700/50 border-b border-dark-700">
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-dark-700">
                @forelse($items as $item)
                    <tr class="hover:bg-dark-700/30 transition-colors">
                        <td class="px-6 py-4 text-sm text-white font-medium">{{ $item->name }}</td>
                        <td class="px-6 py-4 text-sm">
                            {{-- status badge --}}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-3">
                                {{-- action icons --}}
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-12 text-center text-gray-500">No items found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($items->hasPages())
        <div class="px-6 py-4 border-t border-dark-700">
            {{ $items->links() }}
        </div>
    @endif
</div>
```

---

## 8. Badges

### Status Badges
```blade
{{-- Active/Published --}}
<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400">Active</span>

{{-- Inactive/Hidden --}}
<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-500/10 text-gray-400">Inactive</span>

{{-- Draft/Pending --}}
<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400">Draft</span>

{{-- Featured --}}
<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary-light">Featured</span>

{{-- Category tag --}}
<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-500/10 text-blue-400">Category</span>
```

### Removable Tag Badge
```blade
<span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm bg-primary/10 text-primary-light">
    {{ $tag }}
    <button type="button" wire:click="removeTag({{ $index }})" class="text-primary-light/60 hover:text-red-400">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
</span>
```

---

## 9. Filter/Search Bar

```blade
<div class="bg-dark-800 border border-dark-700 rounded-xl p-4 mb-6">
    <div class="flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search..."
                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
        </div>
        <select wire:model.live="statusFilter"
                class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
            <option value="all">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </select>
    </div>
</div>
```

---

## 10. Flash Messages

```blade
@if (session('success') || session('error'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="mb-6">
        @if (session('success'))
            <div class="bg-emerald-500/10 border border-emerald-500/20 rounded-lg px-4 py-3 text-emerald-400 text-sm flex items-center gap-2">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-500/10 border border-red-500/20 rounded-lg px-4 py-3 text-red-400 text-sm flex items-center gap-2">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('error') }}
            </div>
        @endif
    </div>
@endif
```

---

## 11. Modals

```blade
<div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
     x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="fixed inset-0 bg-dark-950/80" @click="showModal = false"></div>
    <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 w-full max-w-lg relative z-10 shadow-2xl">
        <h3 class="text-lg font-mono font-semibold text-white uppercase tracking-wider mb-4">Modal Title</h3>
        <div class="text-gray-400 text-sm mb-6">Content here.</div>
        <div class="flex justify-end gap-3">
            <button @click="showModal = false" class="bg-transparent border border-dark-600 text-gray-300 hover:bg-dark-700 font-medium rounded-lg px-4 py-2 text-sm transition-colors">Cancel</button>
            <button class="bg-primary hover:bg-primary-hover text-white font-medium rounded-lg px-4 py-2 text-sm transition-colors">Confirm</button>
        </div>
    </div>
</div>
```

---

## 12. Sidebar Navigation

### Top-level nav link
```blade
<a href="{{ route('admin.feature') }}"
   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('admin.feature*') ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white hover:bg-dark-700' }} transition-colors">
    <svg class="w-5 h-5" ...>...</svg>
    Feature Name
</a>
```

### Collapsible sub-nav link
```blade
<a href="{{ route('admin.sub.index') }}"
   class="flex items-center gap-3 pl-10 pr-3 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('admin.sub.*') ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white hover:bg-dark-700' }} transition-colors">
    <svg class="w-5 h-5" ...>...</svg>
    Sub Feature
</a>
```

---

## 13. Star Ratings

### Display (read-only)
```blade
<div class="flex gap-0.5">
    @for($i = 1; $i <= 5; $i++)
        <svg class="w-4 h-4 {{ $i <= $rating ? 'text-amber-400' : 'text-gray-600' }}" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
        </svg>
    @endfor
</div>
```

### Interactive (clickable)
```blade
<div class="flex gap-1" x-data="{ hovered: 0 }">
    @for($i = 1; $i <= 5; $i++)
        <button type="button" wire:click="$set('rating', {{ $i }})"
                @mouseenter="hovered = {{ $i }}" @mouseleave="hovered = 0" class="focus:outline-none">
            <svg class="w-8 h-8 transition-colors {{ $i <= $rating ? 'text-amber-400' : 'text-gray-600' }}"
                 :class="hovered >= {{ $i }} ? 'text-amber-300' : ''" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
        </button>
    @endfor
</div>
```

---

## 14. Progress Bars

### Simple
```blade
<div class="w-full bg-dark-700 rounded-full h-2">
    <div class="bg-primary h-2 rounded-full" style="width: {{ $percentage }}%"></div>
</div>
```

### Gradient (for featured metrics)
```blade
<div class="w-full bg-dark-700 rounded-full h-2">
    <div class="bg-gradient-to-r from-primary to-fuchsia-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
</div>
```

---

## 15. Empty States

```blade
<tr>
    <td colspan="{{ $colCount }}" class="px-6 py-12 text-center text-gray-500">
        <svg class="w-12 h-12 mx-auto mb-3 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
        </svg>
        No items found.
    </td>
</tr>
```

---

## Rules for Frontend Agents

1. **Always read this file first** before creating any view
2. **Never invent new patterns** — only use what's documented here
3. **Headings are monospace** — every `h1`, `h2`, `h3` MUST use `font-mono uppercase tracking-wider` (Fira Code). This is non-negotiable.
4. **Body text stays sans-serif** — labels, inputs, buttons, badges, nav links use Inter (default sans). Do NOT apply font-mono to these.
5. **Purple is the primary color** — use `primary` tokens, not `indigo` or `blue`
6. **All backgrounds are dark** — never use white or light gray backgrounds
7. **Consistent spacing** — p-6 cards, gap-5 form fields, mb-8 page headers, py-2.5 inputs
8. **Consistent rounding** — rounded-xl for cards, rounded-lg for inputs/buttons, rounded-full for badges
9. **Hover states on everything interactive** — buttons, links, table rows, nav items
10. **Status colors are semantic** — green=success/active, amber=warning/draft, red=danger/error, blue=info
11. **Gradients are premium** — only for featured elements, CTAs, and special highlights
12. **Responsive always** — use `sm:`, `lg:` breakpoints; mobile-first approach
