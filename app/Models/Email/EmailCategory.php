<?php

namespace App\Models\Email;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'color',
        'icon',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function emails(): HasMany
    {
        return $this->hasMany(Email::class, 'category_id');
    }

    public function corrections(): HasMany
    {
        return $this->hasMany(EmailCategoryCorrection::class, 'to_category_id');
    }
}
