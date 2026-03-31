<?php

namespace App\Models\Experience;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExperienceResponsibility extends Model
{
    protected $fillable = [
        'experience_id',
        'description',
        'sort_order',
    ];

    public function experience(): BelongsTo
    {
        return $this->belongsTo(Experience::class);
    }
}
