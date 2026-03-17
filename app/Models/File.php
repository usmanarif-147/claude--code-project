<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $fillable = [
        'file_title',
        'file_path',
        'mime_type',
        'size_kb',
        'size_mb',
        'note',
        'tags',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'size_kb' => 'decimal:2',
            'size_mb' => 'decimal:2',
        ];
    }

    protected function previewType(): Attribute
    {
        return Attribute::get(function () {
            if (str_starts_with($this->mime_type, 'image/')) {
                return 'image';
            }

            if ($this->mime_type === 'application/pdf') {
                return 'pdf';
            }

            if (str_starts_with($this->mime_type, 'text/') || $this->mime_type === 'text/markdown') {
                return 'text';
            }

            return 'download';
        });
    }

    protected function extension(): Attribute
    {
        return Attribute::get(fn () => pathinfo($this->file_path, PATHINFO_EXTENSION));
    }
}
