# Logs Viewer — Update Spec

## Overview
Added a custom Logs Viewer feature to the Settings module. No external package — custom Livewire component with service-based log parsing.

## Files Created
- `app/Services/LogViewerService.php` — log file scanning, parsing, validation, delete/clear
- `app/Livewire/Admin/Settings/Logs/LogsIndex.php` — Livewire component with URL-bound filters
- `resources/views/livewire/admin/settings/logs/index.blade.php` — dark theme view with level badges, expandable stack traces
- `routes/admin/settings/logs.php` — GET /settings/logs (admin.settings.logs)

## Files Modified
- `resources/views/components/layouts/admin.blade.php` — added Logs sidebar link under Settings group

## Features
- View all .log files in storage/logs/
- Filter by log level (emergency, alert, critical, error, warning, notice, info, debug)
- Search log messages
- Expandable stack traces (Alpine.js toggle)
- Download, clear, delete log files (with confirmation)
- Load More pagination
- Path traversal protection on all file operations

## Cross-Module Impact
None — isolated feature addition.
