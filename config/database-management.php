<?php

return [
    'max_attempts' => (int) env('DB_MGMT_MAX_ATTEMPTS', 3),
    'lockout_minutes' => (int) env('DB_MGMT_LOCKOUT_MINUTES', 60),
    'backup_path' => storage_path('app/private/backups/database'),
];
