# Design System — Admin Panel
## Xintra-Inspired Dark Theme — Professional & Motivating

This file is the SINGLE SOURCE OF TRUTH for all admin views.
Read this file COMPLETELY before writing a single line of HTML.
Never invent patterns. Never guess. Copy exactly from here.

---

## CRITICAL RULES (Read First)

1. **NO empty black space** — every page must use the full width. Forms are full width. Content fills the grid.
2. **NO flat cards** — every card has depth: border, subtle shadow, icon backgrounds with opacity
3. **Every stat card** shows an icon with colored bg, a value, and a description/subtitle
4. **Every page** has a breadcrumb trail above the page title
5. **Every form** is full width, divided into labeled sections, uses two-column grid for fields
6. **Every table** uses progress bars, colored badges, and a three-dot action dropdown
7. **Charts** use purple/fuchsia gradient fills with visible grid lines
8. **Sidebar** keeps current structure but with improved active states and section spacing
9. **Animations** — use Alpine.js for subtle transitions on load, hover, and state changes
10. **font-mono + uppercase + tracking-wider** on ALL h1, h2, h3 — non-negotiable

---

## 1. Color Tokens

```
Backgrounds:
  dark-950  #050508   deepest black — page overlays, modal backdrops
  dark-900  #0a0a0f   page background (body)
  dark-800  #111118   card backgrounds, sidebar
  dark-700  #1a1a24   input backgrounds, table header, dividers
  dark-600  #25253a   input borders, hover backgrounds

Primary Purple:
  primary       #7c3aed   buttons, focus rings, active states
  primary-light #a78bfa   links, active nav text, icon colors
  primary-dark  #6d28d9   pressed button state
  primary-hover #8b5cf6   button hover

Text:
  text-white     headings, stat values, table primary text
  text-gray-300  form labels, body text
  text-gray-400  secondary text, table cells
  text-gray-500  muted, descriptions, placeholders
  text-gray-600  disabled, empty states

Status:
  emerald-400 / bg-emerald-500/10   active, success, published
  amber-400   / bg-amber-500/10     draft, warning, pending
  red-400     / bg-red-500/10       danger, error, inactive, delete
  blue-400    / bg-blue-500/10      info, links
  primary-light / bg-primary/10     featured, selected, active nav
```

---

## 2. Typography

**Rule: font-mono on all headings. Inter (sans) on everything else.**

```
Page title:      text-2xl font-mono font-bold text-white uppercase tracking-wider
Page subtitle:   text-sm text-gray-500 mt-1
Breadcrumb:      text-xs text-gray-500 (with > separator, last item text-gray-300)
Card heading:    text-base font-mono font-semibold text-white uppercase tracking-wider
Section label:   text-xs font-mono font-medium text-gray-500 uppercase tracking-widest
Form label:      text-sm font-medium text-gray-300 mb-2
Body text:       text-sm text-gray-400
Table header:    text-xs font-mono font-medium text-gray-400 uppercase tracking-wider
Stat value:      text-3xl font-bold text-white
Stat label:      text-sm text-gray-500
Badge:           text-xs font-medium
Button:          text-sm font-medium
```

---

## 3. Breadcrumbs (Every Page Must Have This)

```blade
<div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
    <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('admin.module.index') }}" wire:navigate class="hover:text-gray-300 transition-colors">Module</a>
    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-gray-300">Current Page</span>
</div>
```

---

## 4. Page Headers

### Index Page (with Add button)
```blade
{{-- Breadcrumb above --}}
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Page Title</h1>
        <p class="text-sm text-gray-500 mt-1">Short description of this section.</p>
    </div>
    <button wire:click="create" wire:navigate
        class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Item
    </button>
</div>
```

### Form Page (Create/Edit with breadcrumb)
```blade
{{-- Breadcrumb above --}}
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">
            {{ $itemId ? 'Edit Item' : 'Create Item' }}
        </h1>
        <p class="text-sm text-gray-500 mt-1">
            {{ $itemId ? 'Update the details below.' : 'Fill in the details to create a new item.' }}
        </p>
    </div>
    <a href="{{ route('admin.module.index') }}" wire:navigate
       class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 text-gray-300 text-sm font-medium rounded-lg px-4 py-2.5 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back
    </a>
</div>
```

---

## 5. Stat Cards (Dashboard & Analytics)

### Standard Stat Card
```blade
<div class="bg-dark-800 border border-dark-700 rounded-xl p-5 hover:border-dark-600 transition-colors"
     x-data x-intersect="$el.classList.add('opacity-100', 'translate-y-0')"
     style="opacity:0; transform:translateY(8px); transition: opacity 0.4s ease, transform 0.4s ease;">
    <div class="flex items-start justify-between mb-4">
        <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
            <svg class="w-5 h-5 text-primary-light" ...>...</svg>
        </div>
        {{-- Optional trend badge --}}
        <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-400 bg-emerald-500/10 px-2 py-1 rounded-full">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17l9.2-9.2M17 17V7H7"/></svg>
            +12%
        </span>
    </div>
    <p class="text-3xl font-bold text-white mb-1">{{ $value }}</p>
    <p class="text-sm text-gray-500">{{ $label }}</p>
</div>
```

### Stat Card Grid (4 columns)
```blade
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    {{-- 4 stat cards --}}
</div>
```

### Icon Background Colors (vary per stat — do not use same color for all)
```
Skills/Content:   bg-primary/10    text-primary-light
Analytics/Views:  bg-blue-500/10   text-blue-400
Success/Active:   bg-emerald-500/10 text-emerald-400
Warning/Pending:  bg-amber-500/10  text-amber-400
Downloads/Files:  bg-fuchsia-500/10 text-fuchsia-400
Visitors/Users:   bg-cyan-500/10   text-cyan-400
```

---

## 6. Cards

### Standard Content Card
```blade
<div class="bg-dark-800 border border-dark-700 rounded-xl">
    <div class="flex items-center justify-between px-6 py-4 border-b border-dark-700">
        <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider">Section Title</h2>
        <a href="#" class="text-xs text-primary-light hover:text-white transition-colors">View All</a>
    </div>
    <div class="p-6">
        {{-- content --}}
    </div>
</div>
```

### Form Section Card (for grouping form fields)
```blade
<div class="bg-dark-800 border border-dark-700 rounded-xl mb-6">
    <div class="px-6 py-4 border-b border-dark-700">
        <h2 class="text-sm font-mono font-semibold text-white uppercase tracking-wider">Section Name</h2>
        <p class="text-xs text-gray-500 mt-0.5">Optional description of this section.</p>
    </div>
    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
        {{-- form fields --}}
    </div>
</div>
```

### Full-Width Form Layout
```blade
{{-- Page header --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    {{-- Main content: 2/3 width --}}
    <div class="xl:col-span-2 space-y-6">
        {{-- Section card 1 --}}
        {{-- Section card 2 --}}
    </div>
    {{-- Sidebar: 1/3 width --}}
    <div class="space-y-6">
        {{-- Meta card (status, visibility, dates) --}}
        {{-- Image upload card --}}
        {{-- Submit card --}}
    </div>
</div>
```

---

## 7. Form Inputs

### Text Input
```blade
<div>
    <label class="block text-sm font-medium text-gray-300 mb-2">
        Field Label <span class="text-red-400">*</span>
    </label>
    <input type="text" wire:model="field"
           placeholder="Placeholder text"
           class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
    @error('field')
        <p class="mt-1.5 text-xs text-red-400 flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ $message }}
        </p>
    @enderror
</div>
```

### Select
```blade
<div>
    <label class="block text-sm font-medium text-gray-300 mb-2">Select Label</label>
    <select wire:model="field"
            class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all duration-200">
        <option value="">Choose...</option>
        <option value="a">Option A</option>
    </select>
</div>
```

### Textarea
```blade
<textarea wire:model="field" rows="4"
          placeholder="Enter description..."
          class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent resize-none transition-all duration-200"></textarea>
```

### Toggle Switch
```blade
<div class="flex items-center justify-between p-4 bg-dark-700 rounded-lg">
    <div>
        <p class="text-sm font-medium text-gray-300">Toggle Label</p>
        <p class="text-xs text-gray-500 mt-0.5">Short description</p>
    </div>
    <label class="relative inline-flex items-center cursor-pointer">
        <input type="checkbox" wire:model="field" class="sr-only peer">
        <div class="w-11 h-6 bg-dark-600 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
    </label>
</div>
```

### Range Slider (for proficiency/percentage)
```blade
<div>
    <div class="flex items-center justify-between mb-2">
        <label class="text-sm font-medium text-gray-300">Proficiency</label>
        <span class="text-sm font-semibold text-primary-light">{{ $proficiency }}%</span>
    </div>
    <input type="range" wire:model.live="proficiency" min="0" max="100" step="5"
           class="w-full h-2 bg-dark-700 rounded-full appearance-none cursor-pointer accent-primary">
    <div class="w-full bg-dark-700 rounded-full h-1.5 mt-2">
        <div class="bg-gradient-to-r from-primary to-fuchsia-500 h-1.5 rounded-full transition-all duration-300"
             style="width: {{ $proficiency }}%"></div>
    </div>
</div>
```

---

## 8. Buttons

### Primary Button (with loading state)
```blade
<button wire:click="save"
        class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200 shadow-lg shadow-primary/20 disabled:opacity-50 disabled:cursor-not-allowed"
        wire:loading.attr="disabled">
    <span wire:loading.remove wire:target="save">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    </span>
    <span wire:loading wire:target="save">
        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
    </span>
    <span wire:loading.remove wire:target="save">Save Changes</span>
    <span wire:loading wire:target="save">Saving...</span>
</button>
```

### Secondary / Cancel Button
```blade
<button wire:click="cancel"
        class="inline-flex items-center gap-2 bg-dark-700 hover:bg-dark-600 border border-dark-600 text-gray-300 hover:text-white text-sm font-medium rounded-lg px-5 py-2.5 transition-all duration-200">
    Cancel
</button>
```

### Danger Button
```blade
<button wire:click="delete({{ $id }})" wire:confirm="Are you sure you want to delete this?"
        class="inline-flex items-center gap-1.5 bg-red-500/10 hover:bg-red-500/20 text-red-400 hover:text-red-300 text-sm font-medium rounded-lg px-4 py-2 transition-all duration-200">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
    Delete
</button>
```

### Icon Action Buttons (table rows — edit & delete)
```blade
<div class="flex items-center justify-end gap-1">
    <a href="{{ route('admin.module.edit', $item) }}" wire:navigate
       class="p-2 text-gray-400 hover:text-primary-light hover:bg-primary/10 rounded-lg transition-all duration-200">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
    </a>
    <button wire:click="delete({{ $item->id }})" wire:confirm="Delete this item?"
            class="p-2 text-gray-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-all duration-200">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
    </button>
</div>
```

---

## 9. Tables

### Full Table with Filter Bar
```blade
{{-- Filter Bar --}}
<div class="bg-dark-800 border border-dark-700 rounded-xl p-4 mb-5">
    <div class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
            <input type="text" wire:model.live.debounce.300ms="search"
                   placeholder="Search..."
                   class="w-full bg-dark-700 border border-dark-600 rounded-lg pl-9 pr-4 py-2.5 text-white text-sm placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
        </div>
        <select wire:model.live="filterStatus"
                class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white text-sm focus:ring-2 focus:ring-primary focus:border-transparent transition-all min-w-[140px]">
            <option value="">All Status</option>
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
                <tr class="border-b border-dark-700">
                    <th class="px-6 py-4 text-left text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-4 text-left text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-4 text-left text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-right text-xs font-mono font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-dark-700/50">
                @forelse($items as $item)
                    <tr class="hover:bg-dark-700/30 transition-colors duration-150 group">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                {{-- Optional: icon/avatar --}}
                                <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                                    <span class="text-xs font-semibold text-primary-light">{{ strtoupper(substr($item->name, 0, 2)) }}</span>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-white">{{ $item->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $item->subtitle ?? '' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-500/10 text-blue-400">
                                {{ $item->category }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($item->is_active)
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                    Active
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-500/10 text-gray-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                    Inactive
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            {{-- Use Icon Action Buttons from section 8 --}}
                        </td>
                    </tr>
                @empty
                    {{-- Use Empty State from section 14 --}}
                @endforelse
            </tbody>
        </table>
    </div>
    @if($items->hasPages())
        <div class="px-6 py-4 border-t border-dark-700 flex items-center justify-between">
            <p class="text-sm text-gray-500">Showing {{ $items->firstItem() }}–{{ $items->lastItem() }} of {{ $items->total() }} results</p>
            {{ $items->links() }}
        </div>
    @endif
</div>
```

### Table Row with Inline Progress Bar (for skills/proficiency)
```blade
<td class="px-6 py-4">
    <div class="flex items-center gap-3">
        <div class="flex-1 bg-dark-700 rounded-full h-1.5 min-w-[80px]">
            <div class="bg-gradient-to-r from-primary to-fuchsia-500 h-1.5 rounded-full"
                 style="width: {{ $item->proficiency }}%"></div>
        </div>
        <span class="text-xs text-gray-400 w-8 text-right">{{ $item->proficiency }}%</span>
    </div>
</td>
```

---

## 10. Badges

```blade
{{-- Active / Published / Visible --}}
<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400">
    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>Active
</span>

{{-- Inactive / Hidden --}}
<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-500/10 text-gray-400">
    <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span>Inactive
</span>

{{-- Draft / Pending --}}
<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400">
    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>Draft
</span>

{{-- Featured / Selected --}}
<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary-light">
    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
    Featured
</span>

{{-- Category (color varies by category) --}}
<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-500/10 text-blue-400">Backend</span>
<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-fuchsia-500/10 text-fuchsia-400">Frontend</span>
<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400">Database & Tools</span>
```

---

## 11. Flash Messages

```blade
@if(session('success') || session('error'))
    <div x-data="{ show: true }"
         x-show="show"
         x-init="setTimeout(() => show = false, 4000)"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="mb-6">
        @if(session('success'))
            <div class="flex items-center gap-3 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 rounded-lg px-4 py-3 text-sm">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p>{{ session('success') }}</p>
                <button @click="show = false" class="ml-auto text-emerald-400/60 hover:text-emerald-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        @endif
        @if(session('error'))
            <div class="flex items-center gap-3 bg-red-500/10 border border-red-500/20 text-red-400 rounded-lg px-4 py-3 text-sm">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p>{{ session('error') }}</p>
                <button @click="show = false" class="ml-auto text-red-400/60 hover:text-red-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        @endif
    </div>
@endif
```

---

## 12. Chart.js Patterns (Analytics Pages)

### Standard Chart Config (Purple/Fuchsia Gradient — Always Use This)
```javascript
// In Alpine x-init or <script>
const ctx = document.getElementById('myChart').getContext('2d');

// Purple gradient fill
const gradient = ctx.createLinearGradient(0, 0, 0, 300);
gradient.addColorStop(0, 'rgba(124, 58, 237, 0.4)');
gradient.addColorStop(1, 'rgba(124, 58, 237, 0.0)');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: @json($labels),
        datasets: [{
            label: 'Visitors',
            data: @json($data),
            borderColor: '#7c3aed',
            borderWidth: 2,
            backgroundColor: gradient,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#7c3aed',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { intersect: false, mode: 'index' },
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#111118',
                borderColor: '#1a1a24',
                borderWidth: 1,
                titleColor: '#fff',
                bodyColor: '#9ca3af',
                padding: 12,
            }
        },
        scales: {
            x: {
                grid: { color: 'rgba(255,255,255,0.04)' },
                ticks: { color: '#6b7280', font: { size: 11 } },
                border: { color: '#1a1a24' }
            },
            y: {
                grid: { color: 'rgba(255,255,255,0.04)' },
                ticks: { color: '#6b7280', font: { size: 11 } },
                border: { color: '#1a1a24' }
            }
        }
    }
});
```

### Bar Chart Config
```javascript
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: @json($labels),
        datasets: [{
            label: 'Downloads',
            data: @json($data),
            backgroundColor: 'rgba(124, 58, 237, 0.5)',
            borderColor: '#7c3aed',
            borderWidth: 1,
            borderRadius: 6,
            borderSkipped: false,
        }]
    },
    options: {
        // same options as line chart above
    }
});
```

### Chart Container (always use this wrapper)
```blade
<div class="bg-dark-800 border border-dark-700 rounded-xl">
    <div class="flex items-center justify-between px-6 py-4 border-b border-dark-700">
        <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider">Chart Title</h2>
        {{-- Optional: time range buttons --}}
        <div class="flex items-center gap-1 bg-dark-700 rounded-lg p-1">
            <button wire:click="setRange('7')"
                    class="px-3 py-1.5 rounded-md text-xs font-medium transition-all {{ $range === '7' ? 'bg-primary text-white' : 'text-gray-400 hover:text-white' }}">7D</button>
            <button wire:click="setRange('30')"
                    class="px-3 py-1.5 rounded-md text-xs font-medium transition-all {{ $range === '30' ? 'bg-primary text-white' : 'text-gray-400 hover:text-white' }}">30D</button>
            <button wire:click="setRange('90')"
                    class="px-3 py-1.5 rounded-md text-xs font-medium transition-all {{ $range === '90' ? 'bg-primary text-white' : 'text-gray-400 hover:text-white' }}">90D</button>
        </div>
    </div>
    <div class="p-6">
        <div class="relative h-64">
            <canvas id="myChart"></canvas>
        </div>
    </div>
</div>
```

---

## 13. Sidebar Navigation

Keep existing structure. Improve with these exact classes:

### Sidebar Container
```blade
<nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
```

### Top-Level Link (Dashboard, File Manager, Resume)
```blade
<a href="{{ route('admin.dashboard') }}" wire:navigate
   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
          {{ request()->routeIs('admin.dashboard') ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white hover:bg-dark-700' }}">
    <svg class="w-5 h-5 shrink-0" ...>...</svg>
    Dashboard
</a>
```

### Collapsible Module Group (Portfolio, Tasks, etc.)
```blade
<div x-data="{ open: {{ request()->routeIs('admin.portfolio.*') ? 'true' : 'false' }} }">
    <button @click="open = !open"
            class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
                   {{ request()->routeIs('admin.portfolio.*') ? 'bg-primary/10 text-primary-light' : 'text-gray-400 hover:text-white hover:bg-dark-700' }}">
        <div class="flex items-center gap-3">
            <svg class="w-5 h-5 shrink-0" ...>...</svg>
            Portfolio
        </div>
        <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    <div x-show="open" x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="mt-1 ml-4 pl-4 border-l border-dark-700 space-y-1">
        <a href="{{ route('admin.portfolio.skills.index') }}" wire:navigate
           class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-all duration-200
                  {{ request()->routeIs('admin.portfolio.skills.*') ? 'text-primary-light bg-primary/5' : 'text-gray-500 hover:text-white hover:bg-dark-700' }}">
            <svg class="w-4 h-4 shrink-0" ...>...</svg>
            Skills
        </a>
        {{-- more sub-links --}}
    </div>
</div>
```

---

## 14. Empty States

```blade
{{-- Inside table tbody --}}
<tr>
    <td colspan="5" class="px-6 py-16 text-center">
        <div class="flex flex-col items-center gap-3">
            <div class="w-14 h-14 rounded-xl bg-dark-700 flex items-center justify-center">
                <svg class="w-7 h-7 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-300">No items found</p>
                <p class="text-xs text-gray-500 mt-1">Get started by adding your first item.</p>
            </div>
            <button wire:click="create"
                    class="inline-flex items-center gap-2 bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-4 py-2 transition-all duration-200 shadow-lg shadow-primary/20 mt-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add First Item
            </button>
        </div>
    </td>
</tr>
```

---

## 15. Page Load Animations

Apply to all major sections — cards, tables, form sections:

```blade
{{-- Fade in up on page load --}}
<div x-data x-init="
        $el.style.opacity = '0';
        $el.style.transform = 'translateY(12px)';
        setTimeout(() => {
            $el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            $el.style.opacity = '1';
            $el.style.transform = 'translateY(0)';
        }, 50)">
    {{-- content --}}
</div>

{{-- Staggered animation for multiple cards (add delay per card) --}}
{{-- Card 1: delay 0ms, Card 2: delay 100ms, Card 3: delay 200ms --}}
<div x-data x-init="
        $el.style.opacity = '0';
        $el.style.transform = 'translateY(12px)';
        setTimeout(() => {
            $el.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            $el.style.opacity = '1';
            $el.style.transform = 'translateY(0)';
        }, 100)"> {{-- change delay per card --}}
    {{-- content --}}
</div>
```

---

## 16. Modals (Confirmation & Form)

```blade
<div x-show="showModal"
     x-transition:enter="ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="fixed inset-0 bg-dark-950/80 backdrop-blur-sm" @click="showModal = false"></div>
    <div class="bg-dark-800 border border-dark-700 rounded-xl w-full max-w-md relative z-10 shadow-2xl"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">
        <div class="flex items-center justify-between px-6 py-4 border-b border-dark-700">
            <h3 class="text-base font-mono font-semibold text-white uppercase tracking-wider">Modal Title</h3>
            <button @click="showModal = false" class="text-gray-500 hover:text-white transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-6">
            <p class="text-sm text-gray-400">Modal content here.</p>
        </div>
        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-dark-700">
            <button @click="showModal = false"
                    class="bg-dark-700 hover:bg-dark-600 border border-dark-600 text-gray-300 text-sm font-medium rounded-lg px-4 py-2 transition-all">Cancel</button>
            <button class="bg-primary hover:bg-primary-hover text-white text-sm font-medium rounded-lg px-4 py-2 transition-all shadow-lg shadow-primary/20">Confirm</button>
        </div>
    </div>
</div>
```

---

## Rules for All Frontend Work

1. **Read this file completely** before creating any admin view
2. **No empty space** — if right side is empty, use it for meta/status/image sidebar
3. **Every page has a breadcrumb** — no exceptions
4. **Stat cards always vary icon colors** — never same color for all 4 cards
5. **Badges always have a dot indicator** — the small circle before the text
6. **Buttons always have shadow-primary/20** on primary actions
7. **Charts always use purple gradient fill** — never solid colors, never plain lines
8. **Animations on page load** — stagger cards with 100ms delay increments
9. **Tables always show record count** in pagination footer
10. **Forms always use 2/3 + 1/3 grid** — main fields left, meta/actions right