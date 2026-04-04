<?php

namespace App\Livewire\Admin\Settings\Logs;

use App\Services\LogViewerService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class LogsIndex extends Component
{
    #[Url]
    public string $selectedFile = '';

    #[Url]
    public string $levelFilter = '';

    #[Url]
    public string $search = '';

    public int $page = 1;

    public function mount(LogViewerService $service): void
    {
        if ($this->selectedFile === '') {
            $files = $service->getLogFiles();
            if (count($files) > 0) {
                $this->selectedFile = $files[0]['name'];
            }
        }
    }

    public function updatedSelectedFile(): void
    {
        $this->page = 1;
    }

    public function updatedLevelFilter(): void
    {
        $this->page = 1;
    }

    public function updatedSearch(): void
    {
        $this->page = 1;
    }

    public function deleteFile(LogViewerService $service): void
    {
        if ($this->selectedFile && $service->deleteLogFile($this->selectedFile)) {
            session()->flash('success', "Log file '{$this->selectedFile}' deleted successfully.");
            $this->selectedFile = '';

            // Auto-select next file
            $files = $service->getLogFiles();
            if (count($files) > 0) {
                $this->selectedFile = $files[0]['name'];
            }
        } else {
            session()->flash('error', 'Failed to delete log file.');
        }

        $this->page = 1;
    }

    public function clearFile(LogViewerService $service): void
    {
        if ($this->selectedFile && $service->clearLogFile($this->selectedFile)) {
            session()->flash('success', "Log file '{$this->selectedFile}' cleared successfully.");
        } else {
            session()->flash('error', 'Failed to clear log file.');
        }

        $this->page = 1;
    }

    public function downloadFile(LogViewerService $service)
    {
        if (! $this->selectedFile) {
            return null;
        }

        $path = $service->getLogFilePath($this->selectedFile);

        if (! $path || ! file_exists($path)) {
            session()->flash('error', 'Log file not found.');

            return null;
        }

        return $this->streamDownload(function () use ($path) {
            echo file_get_contents($path);
        }, $this->selectedFile, [
            'Content-Type' => 'text/plain',
        ]);
    }

    public function loadMore(): void
    {
        $this->page++;
    }

    public function render(LogViewerService $service)
    {
        $logFiles = $service->getLogFiles();

        $entries = [];
        $total = 0;
        $hasMore = false;

        if ($this->selectedFile) {
            $result = $service->getLogEntries(
                $this->selectedFile,
                $this->levelFilter ?: null,
                $this->search ?: null,
                50,
                $this->page
            );

            $entries = $result['entries'];
            $total = $result['total'];
            $hasMore = $result['hasMore'];
        }

        return view('livewire.admin.settings.logs.index', [
            'logFiles' => $logFiles,
            'entries' => $entries,
            'total' => $total,
            'hasMore' => $hasMore,
        ]);
    }
}
