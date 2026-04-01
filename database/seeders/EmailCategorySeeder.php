<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmailCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Job Response', 'slug' => 'job-response', 'color' => 'emerald', 'icon' => 'briefcase', 'sort_order' => 1],
            ['name' => 'Freelance', 'slug' => 'freelance', 'color' => 'blue', 'icon' => 'code', 'sort_order' => 2],
            ['name' => 'Important', 'slug' => 'important', 'color' => 'amber', 'icon' => 'star', 'sort_order' => 3],
            ['name' => 'Newsletter', 'slug' => 'newsletter', 'color' => 'primary', 'icon' => 'newspaper', 'sort_order' => 4],
            ['name' => 'Spam/Noise', 'slug' => 'spam-noise', 'color' => 'gray', 'icon' => 'trash', 'sort_order' => 5],
        ];

        foreach ($categories as $category) {
            DB::table('email_categories')->updateOrInsert(
                ['slug' => $category['slug']],
                array_merge($category, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]),
            );
        }
    }
}
