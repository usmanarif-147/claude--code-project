<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_fetch_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('platform', 30);
            $table->string('status', 20);
            $table->unsignedInteger('jobs_fetched')->default(0);
            $table->unsignedInteger('duplicates_found')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('platform');
            $table->index(['user_id', 'platform']);
            $table->index('started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_fetch_logs');
    }
};
