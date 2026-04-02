<?php

namespace App\Models\Bookmark;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bookmark extends Model
{
    protected $fillable = [
        'title',
        'url',
        'description',
        'bookmark_category_id',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BookmarkCategory::class, 'bookmark_category_id');
    }
}
