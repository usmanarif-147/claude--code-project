<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('experiences', function (Blueprint $table) {
            $table->string('type', 20)->default('work')->after('id');
            $table->string('description', 500)->nullable()->after('is_current');
            $table->string('degree', 255)->nullable()->after('description');
            $table->string('field_of_study', 255)->nullable()->after('degree');
        });
    }

    public function down(): void
    {
        Schema::table('experiences', function (Blueprint $table) {
            $table->dropColumn(['type', 'description', 'degree', 'field_of_study']);
        });
    }
};
