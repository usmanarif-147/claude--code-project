<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('experiences', function (Blueprint $table) {
            if (Schema::hasColumn('experiences', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('experiences', 'degree')) {
                $table->dropColumn('degree');
            }
            if (Schema::hasColumn('experiences', 'field_of_study')) {
                $table->dropColumn('field_of_study');
            }
        });
    }

    public function down(): void
    {
        Schema::table('experiences', function (Blueprint $table) {
            $table->string('type', 20)->default('work')->after('id');
            $table->string('degree', 255)->nullable()->after('description');
            $table->string('field_of_study', 255)->nullable()->after('degree');
        });
    }
};
