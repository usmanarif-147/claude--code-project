<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class LogViewerService
{
    /**
     * Get all log files from storage/logs, sorted by most recent first.
     */
    public function getLogFiles(): array
    {
        $logPath = storage_path('logs');

        if (! File::isDirectory($logPath)) {
            return [];
        }

        $files = collect(File::glob($logPath.'/*.log'))
            ->map(fn (string $path) => [
                'name' => basename($path),
                'size' => File::size($path),
                'modified_at' => File::lastModified($path),
            ])
            ->sortByDesc('modified_at')
            ->values()
            ->all();

        return $files;
    }

    /**
     * Parse log entries from a file with optional filtering.
     */
    public function getLogEntries(
        string $filename,
        ?string $level = null,
        ?string $search = null,
        int $perPage = 50,
        int $page = 1
    ): array {
        $path = $this->getLogFilePath($filename);

        if (! $path || ! File::exists($path)) {
            return ['entries' => [], 'total' => 0, 'hasMore' => false];
        }

        $entries = $this->parseLogFile($path);

        // Filter by level
        if ($level) {
            $entries = array_filter($entries, fn (array $entry) => strtolower($entry['level']) === strtolower($level));
            $entries = array_values($entries);
        }

        // Filter by search term
        if ($search) {
            $searchLower = strtolower($search);
            $entries = array_filter($entries, fn (array $entry) => str_contains(strtolower($entry['message']), $searchLower)
                    || str_contains(strtolower($entry['stack_trace'] ?? ''), $searchLower));
            $entries = array_values($entries);
        }

        $total = count($entries);
        $offset = 0;
        $limit = $perPage * $page;
        $paged = array_slice($entries, $offset, $limit);

        return [
            'entries' => $paged,
            'total' => $total,
            'hasMore' => $total > $limit,
        ];
    }

    /**
     * Delete a log file.
     */
    public function deleteLogFile(string $filename): bool
    {
        $path = $this->getLogFilePath($filename);

        if (! $path || ! File::exists($path)) {
            return false;
        }

        return File::delete($path);
    }

    /**
     * Clear (truncate) a log file to 0 bytes.
     */
    public function clearLogFile(string $filename): bool
    {
        $path = $this->getLogFilePath($filename);

        if (! $path || ! File::exists($path)) {
            return false;
        }

        return File::put($path, '') !== false;
    }

    /**
     * Validate filename and return the full path, or null if invalid.
     */
    public function getLogFilePath(string $filename): ?string
    {
        // Reject path traversal and non-.log files
        if (
            str_contains($filename, '..') ||
            str_contains($filename, '/') ||
            str_contains($filename, '\\') ||
            ! str_ends_with($filename, '.log')
        ) {
            return null;
        }

        $path = storage_path('logs/'.$filename);

        // Ensure the resolved path is within storage/logs
        $realLogDir = realpath(storage_path('logs'));
        $realPath = realpath(dirname($path));

        if ($realPath === false || $realLogDir === false || $realPath !== $realLogDir) {
            return null;
        }

        return $path;
    }

    /**
     * Parse a log file, reading from end for newest-first ordering.
     */
    private function parseLogFile(string $path): array
    {
        $content = File::get($path);

        if (empty(trim($content))) {
            return [];
        }

        $lines = explode("\n", $content);
        $entries = [];
        $currentEntry = null;
        $pattern = '/^\[(\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2}.*?)\]\s+(\w+)\.(\w+):\s+(.*)/';

        foreach ($lines as $line) {
            if (preg_match($pattern, $line, $matches)) {
                // Save previous entry
                if ($currentEntry !== null) {
                    $entries[] = $currentEntry;
                }

                $currentEntry = [
                    'timestamp' => $matches[1],
                    'environment' => $matches[2],
                    'level' => $matches[3],
                    'message' => $matches[4],
                    'stack_trace' => '',
                ];
            } elseif ($currentEntry !== null && trim($line) !== '') {
                // Stack trace continuation
                $currentEntry['stack_trace'] .= ($currentEntry['stack_trace'] ? "\n" : '').$line;
            }
        }

        // Don't forget last entry
        if ($currentEntry !== null) {
            $entries[] = $currentEntry;
        }

        // Reverse so newest entries appear first
        return array_reverse($entries);
    }
}
