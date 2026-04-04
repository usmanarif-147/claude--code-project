# Database Management — Update Spec

## Overview
Added a Database Management feature to the Settings module. Single-page tool for viewing tables, analytics, password-protected table emptying with lockout, and database backups.

## Files Created
- `config/database-management.php` — max_attempts, lockout_minutes, backup_path config
- `app/Services/DatabaseManagementService.php` — table operations, password/lockout, backup management
- `app/Livewire/Admin/Settings/DatabaseManagement/DatabaseManagementIndex.php` — Livewire component with computed properties
- `resources/views/livewire/admin/settings/database-management/index.blade.php` — full-page dark theme view
- `routes/admin/settings/database-management.php` — GET /settings/database-management

## Files Modified
- `resources/views/components/layouts/admin.blade.php` — added Database sidebar link under Settings group
- `.env` / `.env.example` — added DB_MGMT_MAX_ATTEMPTS=3, DB_MGMT_LOCKOUT_MINUTES=60

## Features
- Database analytics stat cards (total tables, rows, data size, index size)
- Filterable/sortable table list (search, engine filter, min rows filter)
- Empty table with FK dependency display and warnings
- Password-protected confirmation (Hash::check against login password)
- Rate-limited attempts (3 tries, 1-hour lockout via Cache, configurable in .env)
- Alpine.js lockout countdown timer
- Full database backup (mysqldump with PHP fallback)
- Per-table backup
- Backup download and delete management
- SQL injection prevention via table name validation

## Cross-Module Impact
None — isolated feature addition.
