<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('project_boards', function (Blueprint $table) {
            $table->uuid('share_token')->nullable()->unique()->after('sort_order');
            $table->boolean('is_shared')->default(false)->after('share_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_boards', function (Blueprint $table) {
            $table->dropColumn(['share_token', 'is_shared']);
        });
    }
};
