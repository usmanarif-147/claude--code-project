<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('skills', function (Blueprint $table) {
            if (Schema::hasColumn('skills', 'category')) {
                $table->dropColumn('category');
            }
        });

        Schema::table('skills', function (Blueprint $table) {
            $table->foreignId('category_id')
                ->nullable()
                ->after('title')
                ->constrained('categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('skills', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
            $table->string('category', 100)->nullable()->after('title');
        });
    }
};
