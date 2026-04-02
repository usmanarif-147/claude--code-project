<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BookmarkCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Learning', 'slug' => 'learning', 'is_default' => true, 'sort_order' => 1],
            ['name' => 'Tools', 'slug' => 'tools', 'is_default' => true, 'sort_order' => 2],
            ['name' => 'Articles', 'slug' => 'articles', 'is_default' => true, 'sort_order' => 3],
            ['name' => 'Job Boards', 'slug' => 'job-boards', 'is_default' => true, 'sort_order' => 4],
            ['name' => 'Other', 'slug' => 'other', 'is_default' => true, 'sort_order' => 5],
        ];

        foreach ($categories as $category) {
            DB::table('bookmark_categories')->updateOrInsert(
                ['slug' => $category['slug']],
                array_merge($category, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]),
            );
        }
    }
}
