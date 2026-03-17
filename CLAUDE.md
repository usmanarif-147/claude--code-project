# Portfolio Project

## Tech Stack
- Laravel 12, Livewire 4.1, Tailwind CSS 4 (with Vite 7)
- PHP 8.2+

## Architecture
- Livewire components live in app/Livewire/ (admin components in Admin/ subdirectory)
- Blade views live in resources/views/livewire/ (matching subdirectory structure)
- Admin components use the layout attribute: #[Layout('components.layouts.admin')]
- Routes are in routes/web.php — admin routes are under /admin prefix with auth middleware
- Models are in app/Models/

## Styling
- Dark theme with custom Tailwind colors defined in resources/css/app.css via @theme:
  - dark-900 (#0a0a0f) — page background
  - dark-800 (#12121a) — card backgrounds
  - dark-700 (#1a1a2e) — borders
  - accent (#6366f1) — primary indigo
  - accent-light (#818cf8) — hover/light variant
- Fonts: Inter (sans), Fira Code (mono)
- All UI uses Tailwind utility classes only — no custom component CSS
- Cards use: bg-dark-800 border border-dark-700 rounded-xl
- Text: text-white for headings, text-gray-400 for secondary text

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
