<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('fiverr_url', 255)->nullable()->after('github_url');
            $table->string('youtube_url', 255)->nullable()->after('fiverr_url');
            $table->string('timezone', 100)->nullable()->default('UTC')->after('availability_status');
            $table->string('language', 10)->nullable()->default('en')->after('timezone');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['fiverr_url', 'youtube_url', 'timezone', 'language']);
        });
    }
};
