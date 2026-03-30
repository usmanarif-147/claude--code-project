# Portfolio Project

## Tech Stack
- Laravel 12, Livewire 4.1, Tailwind CSS 4 (with Vite 7)
- PHP 8.2+
- Alpine.js for interactive UI elements

## Architecture
- Livewire components live in app/Livewire/ (admin components in Admin/ subdirectory)
- Blade views live in resources/views/livewire/ (matching subdirectory structure)
- Admin components use the layout attribute: #[Layout('components.layouts.admin')]
- Feature routes live in routes/admin/portfolio/*.php (auto-loaded via bootstrap/app.php)
- Core routes (login, logout, dashboard, profile, files) stay in routes/web.php
- Models are in app/Models/
- Service classes are in app/Services/ — ALL business logic goes here, not in controllers/components
- Livewire components are thin: validation, flash messages, redirects only

## Design System (Xintra-Inspired Dark Theme)

### IMPORTANT: Read `resources/views/DESIGN-SYSTEM.md` for complete component snippets.

### Color Palette (defined in resources/css/app.css @theme)
- **Backgrounds:** dark-950 (#050508), dark-900 (#0a0a0f page bg), dark-800 (#111118 cards), dark-700 (#1a1a24 borders/inputs), dark-600 (#25253a input borders)
- **Primary accent:** primary (#7c3aed purple), primary-light (#a78bfa), primary-dark (#6d28d9), primary-hover (#8b5cf6)
- **Backward compat aliases:** accent = primary (#7c3aed), accent-light = primary-light (#a78bfa)
- **Status:** success (#22c55e), warning (#f59e0b), danger (#ef4444), info (#3b82f6)
- **Gradients:** purple-to-fuchsia-to-orange for premium CTAs and highlights
- **Text:** text-white (headings), text-gray-300 (body), text-gray-400 (secondary), text-gray-500 (muted/placeholders)

### Typography
- **Headings font:** Fira Code (monospace) — all page titles, card headings, section titles use `font-mono uppercase tracking-wider`
- **Body font:** Inter (sans-serif) — labels, body text, table cells, buttons, inputs
- Headings: `font-mono font-bold text-white uppercase tracking-wider`
- Labels: `text-sm font-medium text-gray-300` (Inter, normal case)
- Body text: `text-gray-400` (Inter)
- Muted: `text-gray-500` (Inter)
- RULE: Every `<h1>`, `<h2>`, `<h3>` in admin panel MUST use `font-mono uppercase tracking-wider`

### Component Patterns
- Cards: `bg-dark-800 border border-dark-700 rounded-xl p-6`
- Inputs: `bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent`
- Primary buttons: `bg-primary hover:bg-primary-hover text-white font-medium rounded-lg px-5 py-2.5 transition-colors`
- Badges: `px-2.5 py-1 rounded-full text-xs font-medium` with status-specific bg/text colors
- Tables: dark-800 bg, dark-700 header bg, dark-700/50 row borders
- Sidebar active: `bg-primary/10 text-primary-light`

### Gradient Accents (for special UI elements)
- Badge highlight: `bg-gradient-to-r from-primary via-fuchsia-500 to-orange-500`
- CTA cards: `bg-gradient-to-br from-primary/20 to-fuchsia-600/20 border-primary/30`
- Progress bars: `bg-gradient-to-r from-primary to-fuchsia-500`

### Design Rules
1. NEVER use light backgrounds — everything is dark-800/700/900
2. Purple (#7c3aed) is the ONLY primary accent — no indigo, no blue as primary
3. Use gradient accents sparingly — only for featured/premium elements
4. All interactive elements need hover states and transitions
5. Cards always have border + rounded-xl — never sharp corners
6. Status colors are fixed: green=success, amber=warning, red=danger, blue=info
7. Maintain consistent spacing: p-6 for cards, gap-5 for form fields, mb-8 for page headers

## Livewire Patterns
- All components extend Livewire\Component
- Use PHP attributes: #[Layout], #[Url], #[Validate]
- Flash messages: session()->flash('success' or 'error', $message)
- Navigation: $this->redirect(route('...'), navigate: true)
- Use WithPagination trait for list pages
- Form validation with $this->validate() and $this->validateOnly()

## Playground Section
- Interactive demos/games live under admin routes
- Route pattern: /admin/{feature-name} with name admin.playground.*
- These are standalone Livewire components (no database models needed)
- Current playground: /admin/calculator

## Commands
- Dev server: composer dev (runs at localhost:8021)
- Tests: php artisan test
- Lint/Format: ./vendor/bin/pint
- Build: npm run build
- All commands in Docker: docker compose exec app <command>
