<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">File Manager</h1>
            <p class="text-gray-500 mt-1">Upload, tag, search, and preview research documents.</p>
        </div>
    </div>

    {{-- Upload Section --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl mb-6" x-data="{ uploadOpen: false }">
        <button @click="uploadOpen = !uploadOpen"
                class="w-full flex items-center justify-between px-6 py-4 text-left">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-primary-light" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <span class="text-white font-medium text-sm">Upload Files</span>
                @if (count($uploadQueue) > 0)
                    <span class="bg-primary/20 text-primary-light text-xs font-medium px-2 py-0.5 rounded-full">{{ count($uploadQueue) }} queued</span>
                @endif
            </div>
            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="uploadOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="uploadOpen" x-collapse>
            <div class="px-6 pb-6 border-t border-dark-700 pt-4">
                {{-- Drag & Drop Zone --}}
                <div x-data="{ dragging: false }"
                     x-on:dragover.prevent="dragging = true"
                     x-on:dragleave.prevent="dragging = false"
                     x-on:drop.prevent="
                         dragging = false;
                         const input = $refs.fileInput;
                         const dt = new DataTransfer();
                         for (const file of input.files) { dt.items.add(file); }
                         for (const file of $event.dataTransfer.files) { dt.items.add(file); }
                         input.files = dt.files;
                         input.dispatchEvent(new Event('change', { bubbles: true }));
                     "
                     class="border-2 border-dashed rounded-xl p-8 text-center transition-colors cursor-pointer"
                     :class="dragging ? 'border-primary-light bg-primary/5' : 'border-dark-600 hover:border-dark-500'"
                     @click="$refs.fileInput.click()">
                    <svg class="w-10 h-10 mx-auto mb-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <p class="text-gray-400 text-sm">Drag & drop files here or <span class="text-primary-light">browse</span></p>
                    <p class="text-gray-500 text-xs mt-1">txt, pdf, png, jpg, webp, docx, csv, doc, md — max 10MB each</p>
                    <input x-ref="fileInput" type="file" wire:model="uploadQueue" multiple
                           accept=".txt,.pdf,.png,.jpg,.webp,.docx,.csv,.doc,.md"
                           class="hidden">
                </div>

                {{-- Validation Errors --}}
                @error('uploadQueue.*')
                    <p class="text-red-400 text-sm mt-2">{{ $message }}</p>
                @enderror

                {{-- Upload Batch Cards --}}
                @if (count($uploadQueue) > 0)
                    <div class="mt-4 space-y-3">
                        @foreach ($uploadQueue as $index => $file)
                            <div class="bg-dark-700/50 border border-dark-600 rounded-lg p-4" wire:key="upload-{{ $index }}">
                                <div class="flex items-start gap-4">
                                    {{-- Preview thumbnail --}}
                                    <div class="shrink-0 w-16 h-16 rounded-lg bg-dark-800 flex items-center justify-center overflow-hidden">
                                        @if (str_starts_with($file->getMimeType(), 'image/'))
                                            <img src="{{ $file->temporaryUrl() }}" alt="" class="w-full h-full object-cover rounded-lg">
                                        @else
                                            <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        @endif
                                    </div>

                                    {{-- File info --}}
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between mb-2">
                                            <p class="text-white text-sm font-medium truncate">{{ $file->getClientOriginalName() }}</p>
                                            <button wire:click="removeFromQueue({{ $index }})" class="text-gray-400 hover:text-red-400 transition-colors p-1 shrink-0">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                        <p class="text-gray-500 text-xs mb-3">{{ number_format($file->getSize() / 1024, 1) }} KB — {{ $file->getMimeType() }}</p>

                                        {{-- Note --}}
                                        <textarea wire:model="uploadMeta.{{ $index }}.note" placeholder="Add a note (optional)..."
                                                  rows="2"
                                                  class="w-full bg-dark-800 border border-dark-600 rounded-lg px-3 py-2 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent text-xs resize-none"></textarea>

                                        {{-- Tags --}}
                                        <div class="mt-2">
                                            <div class="flex flex-wrap gap-1.5 mb-2">
                                                @foreach ($uploadMeta[$index]['tags'] ?? [] as $tagIndex => $tag)
                                                    <span class="inline-flex items-center gap-1 bg-primary/10 text-primary-light text-xs font-medium px-2 py-0.5 rounded-full">
                                                        {{ $tag }}
                                                        <button wire:click="removeTag({{ $index }}, {{ $tagIndex }})" class="hover:text-red-400 transition-colors">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                        </button>
                                                    </span>
                                                @endforeach
                                            </div>
                                            @if (count($uploadMeta[$index]['tags'] ?? []) < 5)
                                                <div x-data="{ tagInput: '' }">
                                                    <input type="text" x-model="tagInput" placeholder="Add tag + Enter"
                                                           @keydown.enter.prevent="if(tagInput.trim()) { $wire.addTag({{ $index }}, tagInput.trim()); tagInput = ''; }"
                                                           @keydown.comma.prevent="if(tagInput.trim()) { $wire.addTag({{ $index }}, tagInput.trim()); tagInput = ''; }"
                                                           class="w-full bg-dark-800 border border-dark-600 rounded-lg px-3 py-1.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent text-xs">
                                                </div>
                                            @endif
                                            @error("uploadMeta.{$index}.tags")
                                                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Upload Actions --}}
                    <div class="flex items-center gap-3 mt-4">
                        <button wire:click="saveFiles" wire:loading.attr="disabled"
                                class="bg-primary hover:bg-primary-hover disabled:opacity-50 text-white font-medium rounded-lg px-4 py-2.5 transition-colors text-sm flex items-center gap-2">
                            <svg wire:loading wire:target="saveFiles" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="saveFiles">Upload All ({{ count($uploadQueue) }})</span>
                            <span wire:loading wire:target="saveFiles">Uploading...</span>
                        </button>
                        <button wire:click="clearQueue"
                                class="text-gray-400 hover:text-white font-medium rounded-lg px-4 py-2.5 transition-colors text-sm border border-dark-600 hover:border-dark-500">
                            Clear All
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- File Table --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl overflow-hidden">
        {{-- Filter Bar --}}
        <div class="p-4 border-b border-dark-700">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search files by name or tag..."
                           class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                </div>
                <input type="date" wire:model.live="dateFrom" placeholder="From"
                       class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                <input type="date" wire:model.live="dateTo" placeholder="To"
                       class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
            </div>

            {{-- Bulk Actions --}}
            @if (count($selectedIds) > 0)
                <div class="mt-3 flex items-center gap-3">
                    <span class="text-gray-400 text-sm">{{ count($selectedIds) }} selected</span>
                    <x-admin.confirm-button
                        title="Delete Selected Files?"
                        text="Are you sure you want to delete {{ count($selectedIds) }} file(s)?"
                        action="$wire.bulkDelete()"
                        confirm-text="Yes, delete them"
                        class="bg-red-500/10 hover:bg-red-500/20 text-red-400 font-medium rounded-lg px-3 py-1.5 transition-colors text-sm flex items-center gap-1.5"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Delete Selected
                    </x-admin.confirm-button>
                </div>
            @endif
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-dark-700/50">
                        <th class="px-6 py-3 text-left">
                            <input type="checkbox" wire:model.live="selectAll"
                                   class="rounded border-dark-600 bg-dark-700 text-primary focus:ring-primary">
                        </th>
                        <th class="text-left text-xs font-mono font-medium text-gray-400 uppercase tracking-wider px-6 py-3 cursor-pointer select-none"
                            wire:click="sortBy('file_title')">
                            <span class="flex items-center gap-1">
                                File Title
                                @if ($sortField === 'file_title')
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        @if ($sortDirection === 'asc')
                                            <path d="M5.293 9.707l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 7.414l-3.293 3.293a1 1 0 01-1.414-1.414z"/>
                                        @else
                                            <path d="M14.707 10.293l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 12.586l3.293-3.293a1 1 0 111.414 1.414z"/>
                                        @endif
                                    </svg>
                                @endif
                            </span>
                        </th>
                        <th class="text-left text-xs font-mono font-medium text-gray-400 uppercase tracking-wider px-6 py-3 cursor-pointer select-none"
                            wire:click="sortBy('mime_type')">
                            <span class="flex items-center gap-1">
                                Type
                                @if ($sortField === 'mime_type')
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        @if ($sortDirection === 'asc')
                                            <path d="M5.293 9.707l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 7.414l-3.293 3.293a1 1 0 01-1.414-1.414z"/>
                                        @else
                                            <path d="M14.707 10.293l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 12.586l3.293-3.293a1 1 0 111.414 1.414z"/>
                                        @endif
                                    </svg>
                                @endif
                            </span>
                        </th>
                        <th class="text-left text-xs font-mono font-medium text-gray-400 uppercase tracking-wider px-6 py-3 cursor-pointer select-none"
                            wire:click="sortBy('size_kb')">
                            <span class="flex items-center gap-1">
                                Size
                                @if ($sortField === 'size_kb')
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        @if ($sortDirection === 'asc')
                                            <path d="M5.293 9.707l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 7.414l-3.293 3.293a1 1 0 01-1.414-1.414z"/>
                                        @else
                                            <path d="M14.707 10.293l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 12.586l3.293-3.293a1 1 0 111.414 1.414z"/>
                                        @endif
                                    </svg>
                                @endif
                            </span>
                        </th>
                        <th class="text-left text-xs font-mono font-medium text-gray-400 uppercase tracking-wider px-6 py-3">Tags</th>
                        <th class="text-left text-xs font-mono font-medium text-gray-400 uppercase tracking-wider px-6 py-3">Note</th>
                        <th class="text-left text-xs font-mono font-medium text-gray-400 uppercase tracking-wider px-6 py-3 cursor-pointer select-none"
                            wire:click="sortBy('created_at')">
                            <span class="flex items-center gap-1">
                                Date
                                @if ($sortField === 'created_at')
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                        @if ($sortDirection === 'asc')
                                            <path d="M5.293 9.707l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 7.414l-3.293 3.293a1 1 0 01-1.414-1.414z"/>
                                        @else
                                            <path d="M14.707 10.293l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 12.586l3.293-3.293a1 1 0 111.414 1.414z"/>
                                        @endif
                                    </svg>
                                @endif
                            </span>
                        </th>
                        <th class="text-right text-xs font-mono font-medium text-gray-400 uppercase tracking-wider px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700">
                    @forelse ($files as $file)
                        <tr class="hover:bg-dark-700/30 transition-colors" wire:key="file-{{ $file->id }}">
                            <td class="px-6 py-4">
                                <input type="checkbox" value="{{ $file->id }}" wire:click="toggleFileSelection({{ $file->id }})"
                                       @checked(in_array($file->id, $selectedIds))
                                       class="rounded border-dark-600 bg-dark-700 text-primary focus:ring-primary">
                            </td>
                            <td class="px-6 py-4 text-sm text-white font-medium max-w-[200px] truncate">{{ $file->file_title }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary-light">
                                    {{ $file->extension }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-400">
                                @if ($file->size_mb >= 1)
                                    {{ $file->size_mb }} MB
                                @else
                                    {{ $file->size_kb }} KB
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($file->tags as $tag)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-dark-700 text-gray-300">{{ $tag }}</span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-400 max-w-[150px] truncate">{{ $file->note ?? '—' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-400">{{ $file->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="openPreview({{ $file->id }})"
                                            class="text-gray-400 hover:text-primary-light transition-colors p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                    <x-admin.confirm-button
                                        title="Delete File?"
                                        text="Are you sure you want to delete this file?"
                                        action="$wire.delete({{ $file->id }})"
                                        confirm-text="Yes, delete it"
                                        class="text-gray-400 hover:text-red-400 transition-colors p-1"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </x-admin.confirm-button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                No files found. Upload some files to get started.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($files->hasPages())
            <div class="px-6 py-4 border-t border-dark-700">
                {{ $files->links() }}
            </div>
        @endif
    </div>

    {{-- Preview Modal --}}
    @if ($previewFile)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-data
             @keydown.escape.window="$wire.closePreview()">
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/70" wire:click="closePreview"></div>

            {{-- Modal Content --}}
            <div class="relative bg-dark-800 border border-dark-700 rounded-xl w-full max-w-4xl max-h-[90vh] flex flex-col z-10">
                {{-- Modal Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-dark-700">
                    <div>
                        <h3 class="text-white font-mono font-semibold uppercase tracking-wider">{{ $previewFile->file_title }}.{{ $previewFile->extension }}</h3>
                        <p class="text-gray-500 text-xs mt-0.5">{{ $previewFile->mime_type }}</p>
                    </div>
                    <button wire:click="closePreview" class="text-gray-400 hover:text-white transition-colors p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="flex-1 overflow-auto p-6">
                    @switch($previewFile->preview_type)
                        @case('image')
                            <img src="{{ Storage::url($previewFile->file_path) }}" alt="{{ $previewFile->file_title }}"
                                 class="max-w-full h-auto mx-auto rounded-lg">
                            @break

                        @case('pdf')
                            <iframe src="{{ Storage::url($previewFile->file_path) }}"
                                    class="w-full h-[70vh] rounded-lg border border-dark-700"></iframe>
                            @break

                        @case('text')
                            <pre class="bg-dark-900 border border-dark-700 rounded-lg p-4 text-gray-300 text-sm font-mono overflow-auto max-h-[70vh] whitespace-pre-wrap">{{ $previewContent }}</pre>
                            @break

                        @default
                            <div class="text-center py-12">
                                <svg class="w-16 h-16 mx-auto mb-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-gray-400 mb-4">This file type cannot be previewed in the browser.</p>
                                <a href="{{ Storage::url($previewFile->file_path) }}" download
                                   class="bg-primary hover:bg-primary-hover text-white font-medium rounded-lg px-4 py-2.5 transition-colors text-sm inline-flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Download File
                                </a>
                            </div>
                    @endswitch
                </div>

                {{-- Modal Footer --}}
                <div class="flex items-center gap-4 px-6 py-3 border-t border-dark-700 text-xs text-gray-500">
                    <span>{{ $previewFile->size_mb >= 1 ? $previewFile->size_mb . ' MB' : $previewFile->size_kb . ' KB' }}</span>
                    <span>{{ $previewFile->mime_type }}</span>
                    <span>Uploaded {{ $previewFile->created_at->format('M d, Y \a\t g:i A') }}</span>
                </div>
            </div>
        </div>
    @endif
</div>
