<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoIdea extends Model
{
    protected $fillable = [
        'title',
        'description',
        'priority',
        'status',
        'content_calendar_item_id',
    ];

    public function contentCalendarItem(): BelongsTo
    {
        return $this->belongsTo(ContentCalendarItem::class);
    }
}
