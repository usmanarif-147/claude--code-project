<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\Process\Process;

class DatabaseManagementService
{
    // ─── Table Operations ──────────────────────────────────

    public function getAllTables(): Collection
    {
        $database = config('database.connections.mysql.database');
        $tables = DB::select("SHOW TABLE STATUS FROM `{$database}`");

        return collect($tables);
    }

    public function getDatabaseAnalytics(): array
    {
        $tables = $this->getAllTables();

        $totalRows = $tables->sum('Rows');
        $totalDataSize = $tables->sum('Data_length');
        $totalIndexSize = $tables->sum('Index_length');
        $totalSize = $totalDataSize + $totalIndexSize;

        return [
            'total_tables' => $tables->count(),
            'total_rows' => $totalRows,
            'total_data_size' => $totalDataSize,
            'total_index_size' => $totalIndexSize,
            'total_size' => $totalSize,
            'formatted_total_rows' => number_format($totalRows),
            'formatted_data_size' => $this->formatBytes($totalDataSize),
            'formatted_index_size' => $this->formatBytes($totalIndexSize),
            'formatted_total_size' => $this->formatBytes($totalSize),
        ];
    }

    public function getTableDependencies(string $tableName): array
    {
        $database = config('database.connections.mysql.database');

        // Tables that reference this table (have FK pointing TO this table)
        $referencedBy = DB::select(
            'SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_COLUMN_NAME
             FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
             WHERE REFERENCED_TABLE_NAME = ? AND TABLE_SCHEMA = ?',
            [$tableName, $database]
        );

        // Tables that this table references (this table has FK pointing to others)
        $references = DB::select(
            'SELECT REFERENCED_TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_COLUMN_NAME
             FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
             WHERE TABLE_NAME = ? AND TABLE_SCHEMA = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$tableName, $database]
        );

        return [
            'referenced_by' => collect($referencedBy)->map(fn ($row) => [
                'table' => $row->TABLE_NAME,
                'column' => $row->COLUMN_NAME,
                'constraint' => $row->CONSTRAINT_NAME,
                'referenced_column' => $row->REFERENCED_COLUMN_NAME,
            ])->toArray(),
            'references' => collect($references)->map(fn ($row) => [
                'table' => $row->REFERENCED_TABLE_NAME,
                'column' => $row->COLUMN_NAME,
                'constraint' => $row->CONSTRAINT_NAME,
                'referenced_column' => $row->REFERENCED_COLUMN_NAME,
            ])->toArray(),
        ];
    }

    public function emptyTable(string $tableName): void
    {
        // Validate table name exists (prevent SQL injection)
        $validTables = $this->getAllTables()->pluck('Name')->toArray();

        if (! in_array($tableName, $validTables)) {
            throw new \InvalidArgumentException("Table '{$tableName}' does not exist.");
        }

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::table($tableName)->truncate();
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    public function emptyAllTables(): array
    {
        $tables = $this->getAllTables()->pluck('Name')->toArray();

        // Only protect users table — keeps login working
        $protected = ['users'];
        $toEmpty = array_diff($tables, $protected);

        $emptied = [];
        $failed = [];

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');

            foreach ($toEmpty as $table) {
                try {
                    DB::table($table)->truncate();
                    $emptied[] = $table;
                } catch (\Exception $e) {
                    $failed[] = ['table' => $table, 'error' => $e->getMessage()];
                }
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        // Clean up uploaded files that are now orphaned
        $cleanupResult = $this->cleanUpStorageFiles();

        return [
            'emptied' => $emptied,
            'failed' => $failed,
            'protected' => $protected,
            'files_deleted' => $cleanupResult['deleted'],
            'storage_errors' => $cleanupResult['errors'],
        ];
    }

    public function cleanUpStorageFiles(): array
    {
        // Directories containing user-uploaded files tied to database records
        $directories = [
            storage_path('app/public/files'),
            storage_path('app/public/profile-images'),
            storage_path('app/public/projects'),
            storage_path('app/public/project-tasks'),
        ];

        $deleted = 0;
        $errors = [];

        foreach ($directories as $dir) {
            if (! File::isDirectory($dir)) {
                continue;
            }

            try {
                // Delete all files inside the directory
                $files = File::allFiles($dir);
                foreach ($files as $file) {
                    // Skip .gitignore files
                    if ($file->getFilename() === '.gitignore') {
                        continue;
                    }
                    File::delete($file->getPathname());
                    $deleted++;
                }

                // Remove empty subdirectories (e.g. projects/gallery, projects/covers)
                $subdirs = File::directories($dir);
                foreach ($subdirs as $subdir) {
                    if (count(File::allFiles($subdir)) === 0) {
                        File::deleteDirectory($subdir);
                    }
                }
            } catch (\Exception $e) {
                $errors[] = basename($dir).': '.$e->getMessage();
            }
        }

        return ['deleted' => $deleted, 'errors' => $errors];
    }

    // ─── Password & Lockout ────────────────────────────────

    public function verifyPassword(string $password): bool
    {
        return Hash::check($password, auth()->user()->password);
    }

    public function checkLockout(int $userId): array
    {
        $lockoutKey = "db_mgmt_lockout_{$userId}";
        $attemptsKey = "db_mgmt_attempts_{$userId}";
        $maxAttempts = config('database-management.max_attempts');

        $lockoutUntil = Cache::get($lockoutKey);
        $attemptsUsed = Cache::get($attemptsKey, 0);

        if ($lockoutUntil && now()->lt($lockoutUntil)) {
            return [
                'locked' => true,
                'remaining_seconds' => (int) now()->diffInSeconds($lockoutUntil),
                'attempts_used' => $maxAttempts,
                'max_attempts' => $maxAttempts,
            ];
        }

        // If lockout has expired, clean up
        if ($lockoutUntil) {
            Cache::forget($lockoutKey);
            Cache::forget($attemptsKey);
            $attemptsUsed = 0;
        }

        return [
            'locked' => false,
            'remaining_seconds' => 0,
            'attempts_used' => $attemptsUsed,
            'max_attempts' => $maxAttempts,
        ];
    }

    public function recordFailedAttempt(int $userId): void
    {
        $attemptsKey = "db_mgmt_attempts_{$userId}";
        $lockoutKey = "db_mgmt_lockout_{$userId}";
        $maxAttempts = config('database-management.max_attempts');
        $lockoutMinutes = config('database-management.lockout_minutes');

        $attempts = Cache::get($attemptsKey, 0) + 1;
        Cache::put($attemptsKey, $attempts, now()->addMinutes($lockoutMinutes));

        if ($attempts >= $maxAttempts) {
            Cache::put($lockoutKey, now()->addMinutes($lockoutMinutes), now()->addMinutes($lockoutMinutes));
            Cache::forget($attemptsKey);
        }
    }

    public function resetAttempts(int $userId): void
    {
        Cache::forget("db_mgmt_attempts_{$userId}");
        Cache::forget("db_mgmt_lockout_{$userId}");
    }

    // ─── Backup ────────────────────────────────────────────

    public function createBackup(?string $tableName = null): string
    {
        $backupPath = config('database-management.backup_path');

        if (! File::isDirectory($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
        }

        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port', 3306);
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');

        $timestamp = now()->format('Y-m-d_His');
        $suffix = $tableName ? "_{$tableName}" : '';
        $filename = "backup_{$timestamp}{$suffix}.sql.gz";
        $filePath = $backupPath.'/'.$filename;

        // Validate table name if provided
        if ($tableName) {
            $validTables = $this->getAllTables()->pluck('Name')->toArray();
            if (! in_array($tableName, $validTables)) {
                throw new \InvalidArgumentException("Table '{$tableName}' does not exist.");
            }
        }

        $tableArg = $tableName ? ' '.escapeshellarg($tableName) : '';

        $command = sprintf(
            'mysqldump -h %s -P %s -u %s -p%s %s%s --single-transaction --routines --triggers | gzip > %s',
            escapeshellarg($host),
            escapeshellarg((string) $port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            $tableArg,
            escapeshellarg($filePath)
        );

        $process = Process::fromShellCommandline($command);
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            // Fallback: PHP-based dump
            $this->createPhpBackup($filePath, $tableName);
        }

        if (! file_exists($filePath)) {
            throw new \RuntimeException('Backup file was not created.');
        }

        return $filename;
    }

    public function listBackups(): array
    {
        $backupPath = config('database-management.backup_path');

        if (! File::isDirectory($backupPath)) {
            return [];
        }

        $files = File::glob($backupPath.'/*.sql.gz');

        return collect($files)
            ->map(fn (string $file) => [
                'name' => basename($file),
                'size' => filesize($file),
                'formatted_size' => $this->formatBytes(filesize($file)),
                'created_at' => date('Y-m-d H:i:s', filemtime($file)),
            ])
            ->sortByDesc('created_at')
            ->values()
            ->toArray();
    }

    public function deleteBackup(string $filename): bool
    {
        // Validate filename — prevent path traversal
        if ($filename !== basename($filename) || ! str_ends_with($filename, '.sql.gz')) {
            throw new \InvalidArgumentException('Invalid backup filename.');
        }

        $filePath = config('database-management.backup_path').'/'.$filename;

        if (! file_exists($filePath)) {
            return false;
        }

        return File::delete($filePath);
    }

    public function getBackupDownloadPath(string $filename): string
    {
        // Validate filename — prevent path traversal
        if ($filename !== basename($filename) || ! str_ends_with($filename, '.sql.gz')) {
            throw new \InvalidArgumentException('Invalid backup filename.');
        }

        $filePath = config('database-management.backup_path').'/'.$filename;

        if (! file_exists($filePath)) {
            throw new \RuntimeException('Backup file not found.');
        }

        return $filePath;
    }

    // ─── Utility ───────────────────────────────────────────

    public function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        if ($bytes <= 0) {
            return '0 B';
        }

        $pow = floor(log($bytes, 1024));
        $pow = min($pow, count($units) - 1);

        return round($bytes / pow(1024, $pow), $precision).' '.$units[$pow];
    }

    // ─── Private ───────────────────────────────────────────

    private function createPhpBackup(string $filePath, ?string $tableName = null): void
    {
        $tables = $tableName ? [$tableName] : $this->getAllTables()->pluck('Name')->toArray();
        $sql = "-- PHP-generated backup\n-- Date: ".now()->toDateTimeString()."\n\nSET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            // Table structure
            $createResult = DB::select("SHOW CREATE TABLE `{$table}`");
            if (! empty($createResult)) {
                $createSql = $createResult[0]->{'Create Table'} ?? '';
                $sql .= "DROP TABLE IF EXISTS `{$table}`;\n{$createSql};\n\n";
            }

            // Table data
            $rows = DB::table($table)->get();
            if ($rows->isNotEmpty()) {
                foreach ($rows as $row) {
                    $values = collect((array) $row)->map(function ($value) {
                        if ($value === null) {
                            return 'NULL';
                        }

                        return DB::getPdo()->quote((string) $value);
                    })->implode(', ');

                    $sql .= "INSERT INTO `{$table}` VALUES ({$values});\n";
                }
                $sql .= "\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        // Gzip and write
        $gz = gzopen($filePath, 'wb9');
        gzwrite($gz, $sql);
        gzclose($gz);
    }
}
