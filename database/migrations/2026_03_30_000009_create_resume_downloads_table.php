<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resume_downloads', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45);
            $table->string('country', 100)->nullable();
            $table->string('referrer', 500)->nullable();
            $table->string('template_used', 50)->nullable();
            $table->timestamp('downloaded_at');

            $table->index('downloaded_at');
            $table->index('ip_address');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resume_downloads');
    }
};
