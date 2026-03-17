<?php

namespace App\Livewire\Admin;

use App\Models\File;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class FileManager extends Component
{
    use WithFileUploads, WithPagination;

    // Upload
    public array $uploadQueue = [];
    public array $uploadMeta = [];

    // Table
    #[Url]
    public string $search = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public string $dateFrom = '';
    public string $dateTo = '';
    public array $selectedIds = [];
    public bool $selectAll = false;

    // Preview
    public ?int $previewFileId = null;
    public ?string $previewContent = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function updatedUploadQueue(): void
    {
        $existingCount = count($this->uploadMeta);
        $newFiles = array_slice($this->uploadQueue, $existingCount);

        if ($existingCount + count($newFiles) > 20) {
            $allowed = 20 - $existingCount;
            $this->uploadQueue = array_slice($this->uploadQueue, 0, $existingCount + $allowed);
            $newFiles = array_slice($newFiles, 0, $allowed);
            session()->flash('error', 'Maximum 20 files allowed per batch.');
        }

        foreach ($newFiles as $file) {
            $this->uploadMeta[] = [
                'note' => '',
                'tags' => [],
                'tagInput' => '',
            ];
        }
    }

    public function removeFromQueue(int $index): void
    {
        array_splice($this->uploadQueue, $index, 1);
        array_splice($this->uploadMeta, $index, 1);
        $this->uploadQueue = array_values($this->uploadQueue);
        $this->uploadMeta = array_values($this->uploadMeta);
    }

    public function addTag(int $index, string $tag): void
    {
        $tag = trim($tag);
        if ($tag === '' || count($this->uploadMeta[$index]['tags']) >= 5) {
            return;
        }

        if (in_array($tag, $this->uploadMeta[$index]['tags'])) {
            return;
        }

        $this->uploadMeta[$index]['tags'][] = $tag;
        $this->uploadMeta[$index]['tagInput'] = '';
    }

    public function removeTag(int $index, int $tagIndex): void
    {
        array_splice($this->uploadMeta[$index]['tags'], $tagIndex, 1);
        $this->uploadMeta[$index]['tags'] = array_values($this->uploadMeta[$index]['tags']);
    }

    public function saveFiles(): void
    {
        $this->validate([
            'uploadQueue' => 'required|array|min:1',
            'uploadQueue.*' => 'file|max:10240|mimes:txt,pdf,png,jpg,webp,docx,csv,doc,md',
            'uploadMeta.*.note' => 'nullable|string|max:500',
            'uploadMeta.*.tags' => 'required|array|min:1|max:5',
            'uploadMeta.*.tags.*' => 'string|max:50',
        ], [
            'uploadMeta.*.tags.required' => 'Each file must have at least 1 tag.',
            'uploadMeta.*.tags.min' => 'Each file must have at least 1 tag.',
        ]);

        $count = 0;
        foreach ($this->uploadQueue as $index => $file) {
            $path = Storage::disk('public')->putFile('files', $file);
            $sizeBytes = $file->getSize();
            $sizeKb = round($sizeBytes / 1024, 2);
            $sizeMb = round($sizeBytes / (1024 * 1024), 2);

            File::create([
                'file_title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'file_path' => $path,
                'mime_type' => $file->getMimeType(),
                'size_kb' => $sizeKb,
                'size_mb' => $sizeMb,
                'note' => $this->uploadMeta[$index]['note'] ?: null,
                'tags' => $this->uploadMeta[$index]['tags'],
            ]);
            $count++;
        }

        $this->uploadQueue = [];
        $this->uploadMeta = [];

        session()->flash('success', "{$count} file(s) uploaded successfully.");
    }

    public function clearQueue(): void
    {
        $this->uploadQueue = [];
        $this->uploadMeta = [];
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function delete(int $id): void
    {
        $file = File::findOrFail($id);
        Storage::disk('public')->delete($file->file_path);
        $file->delete();

        $this->selectedIds = array_values(array_diff($this->selectedIds, [$id]));
        session()->flash('success', 'File deleted successfully.');
    }

    public function bulkDelete(): void
    {
        $files = File::whereIn('id', $this->selectedIds)->get();

        foreach ($files as $file) {
            Storage::disk('public')->delete($file->file_path);
            $file->delete();
        }

        $count = $files->count();
        $this->selectedIds = [];
        $this->selectAll = false;

        session()->flash('success', "{$count} file(s) deleted successfully.");
    }

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $this->selectedIds = File::query()
                ->when($this->search, fn ($q) => $q->where('file_title', 'like', "%{$this->search}%")
                    ->orWhere('tags', 'like', "%{$this->search}%"))
                ->when($this->dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $this->dateFrom))
                ->when($this->dateTo, fn ($q) => $q->whereDate('created_at', '<=', $this->dateTo))
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->toArray();
        } else {
            $this->selectedIds = [];
        }
    }

    public function toggleFileSelection(int $id): void
    {
        if (in_array($id, $this->selectedIds)) {
            $this->selectedIds = array_values(array_diff($this->selectedIds, [$id]));
        } else {
            $this->selectedIds[] = $id;
        }
    }

    public function openPreview(int $id): void
    {
        $this->previewFileId = $id;
        $file = File::findOrFail($id);

        if ($file->preview_type === 'text') {
            $this->previewContent = Storage::disk('public')->get($file->file_path);
        } else {
            $this->previewContent = null;
        }
    }

    public function closePreview(): void
    {
        $this->previewFileId = null;
        $this->previewContent = null;
    }

    public function render()
    {
        $query = File::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('file_title', 'like', "%{$this->search}%")
                    ->orWhere('tags', 'like', "%{$this->search}%");
            });
        }

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        $query->orderBy($this->sortField, $this->sortDirection);

        $previewFile = $this->previewFileId ? File::find($this->previewFileId) : null;

        return view('livewire.admin.file-manager', [
            'files' => $query->paginate(15),
            'previewFile' => $previewFile,
        ]);
    }
}
