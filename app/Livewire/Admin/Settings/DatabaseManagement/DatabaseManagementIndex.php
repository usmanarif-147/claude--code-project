<?php

namespace App\Livewire\Admin\Settings\DatabaseManagement;

use App\Services\DatabaseManagementService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class DatabaseManagementIndex extends Component
{
    #[Url]
    public string $search = '';

    public string $sortBy = 'Name';

    public string $sortDirection = 'asc';

    public string $filterEngine = '';

    public int $filterMinRows = 0;

    // Modal
    public bool $showEmptyModal = false;

    public string $emptyingTable = '';

    public array $tableDependencies = [];

    public string $password = '';

    public string $passwordError = '';

    public bool $emptyAllMode = false;

    // Backup
    public bool $showBackupSection = true;

    #[Computed]
    public function tables(): \Illuminate\Support\Collection
    {
        $service = app(DatabaseManagementService::class);
        $tables = $service->getAllTables();

        // Apply search filter
        if ($this->search) {
            $tables = $tables->filter(
                fn ($table) => str_contains(strtolower($table->Name), strtolower($this->search))
            );
        }

        // Apply engine filter
        if ($this->filterEngine) {
            $tables = $tables->filter(fn ($table) => $table->Engine === $this->filterEngine);
        }

        // Apply min rows filter
        if ($this->filterMinRows > 0) {
            $tables = $tables->filter(fn ($table) => ($table->Rows ?? 0) >= $this->filterMinRows);
        }

        // Apply sorting
        $sortBy = $this->sortBy;
        $tables = $tables->sortBy(function ($table) use ($sortBy) {
            return $table->{$sortBy} ?? '';
        }, SORT_REGULAR, $this->sortDirection === 'desc');

        return $tables->values();
    }

    #[Computed]
    public function analytics(): array
    {
        return app(DatabaseManagementService::class)->getDatabaseAnalytics();
    }

    #[Computed]
    public function lockoutInfo(): array
    {
        return app(DatabaseManagementService::class)->checkLockout(auth()->id());
    }

    #[Computed]
    public function backups(): array
    {
        return app(DatabaseManagementService::class)->listBackups();
    }

    public function confirmEmpty(string $table): void
    {
        $service = app(DatabaseManagementService::class);
        $this->emptyingTable = $table;
        $this->tableDependencies = $service->getTableDependencies($table);
        $this->password = '';
        $this->passwordError = '';
        $this->emptyAllMode = false;
        $this->showEmptyModal = true;
    }

    public function confirmEmptyAll(): void
    {
        $this->emptyingTable = '';
        $this->tableDependencies = [];
        $this->password = '';
        $this->passwordError = '';
        $this->emptyAllMode = true;
        $this->showEmptyModal = true;
    }

    public function executeEmpty(): void
    {
        $service = app(DatabaseManagementService::class);
        $userId = auth()->id();

        // Check lockout
        $lockout = $service->checkLockout($userId);
        if ($lockout['locked']) {
            $minutes = ceil($lockout['remaining_seconds'] / 60);
            $this->passwordError = "Account locked. Try again in {$minutes} minute(s).";

            return;
        }

        // Verify password
        if (! $service->verifyPassword($this->password)) {
            $service->recordFailedAttempt($userId);
            $lockout = $service->checkLockout($userId);

            if ($lockout['locked']) {
                $this->passwordError = 'Too many failed attempts. Account locked for '.config('database-management.lockout_minutes').' minutes.';
            } else {
                $remaining = $lockout['max_attempts'] - $lockout['attempts_used'];
                $this->passwordError = "Incorrect password. {$remaining} attempt(s) remaining.";
            }

            return;
        }

        // Password correct — reset attempts and empty table(s)
        $service->resetAttempts($userId);

        try {
            if ($this->emptyAllMode) {
                $result = $service->emptyAllTables();
                $count = count($result['emptied']);
                $failCount = count($result['failed']);

                $msg = "{$count} tables emptied successfully.";
                $msg .= ' Users table preserved.';

                if ($result['files_deleted'] > 0) {
                    $msg .= " {$result['files_deleted']} uploaded files cleaned up.";
                }

                if ($failCount > 0) {
                    $msg .= " {$failCount} tables failed.";
                    session()->flash('error', $msg);
                } else {
                    session()->flash('success', $msg);
                }
            } else {
                $service->emptyTable($this->emptyingTable);
                session()->flash('success', "Table '{$this->emptyingTable}' has been emptied successfully.");
            }
        } catch (\Exception $e) {
            session()->flash('error', "Failed to empty table(s): {$e->getMessage()}");
        }

        $this->cancelEmpty();
    }

    public function cancelEmpty(): void
    {
        $this->showEmptyModal = false;
        $this->emptyingTable = '';
        $this->tableDependencies = [];
        $this->password = '';
        $this->passwordError = '';
        $this->emptyAllMode = false;
    }

    public function sortByColumn(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    public function createFullBackup(): void
    {
        try {
            $filename = app(DatabaseManagementService::class)->createBackup();
            session()->flash('success', "Full backup created: {$filename}");
        } catch (\Exception $e) {
            session()->flash('error', "Backup failed: {$e->getMessage()}");
        }
    }

    public function createTableBackup(string $table): void
    {
        try {
            $filename = app(DatabaseManagementService::class)->createBackup($table);
            session()->flash('success', "Backup created for table '{$table}': {$filename}");
        } catch (\Exception $e) {
            session()->flash('error', "Backup failed: {$e->getMessage()}");
        }
    }

    public function downloadBackup(string $filename): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $path = app(DatabaseManagementService::class)->getBackupDownloadPath($filename);

        return response()->download($path);
    }

    public function deleteBackup(string $filename): void
    {
        try {
            app(DatabaseManagementService::class)->deleteBackup($filename);
            session()->flash('success', "Backup '{$filename}' deleted.");
        } catch (\Exception $e) {
            session()->flash('error', "Failed to delete backup: {$e->getMessage()}");
        }
    }

    public function refreshData(): void
    {
        // Computed properties will re-evaluate on next render
    }

    public function render()
    {
        $service = app(DatabaseManagementService::class);

        // Get unique engines for filter dropdown
        $allTables = $service->getAllTables();
        $engines = $allTables->pluck('Engine')->unique()->filter()->sort()->values();

        return view('livewire.admin.settings.database-management.index', [
            'tables' => $this->tables,
            'analytics' => $this->analytics,
            'lockoutInfo' => $this->lockoutInfo,
            'backups' => $this->backups,
            'engines' => $engines,
        ]);
    }
}
