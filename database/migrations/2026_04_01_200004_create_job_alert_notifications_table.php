<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_alert_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('job_listing_id')->constrained('job_listings')->cascadeOnDelete();
            $table->unsignedTinyInteger('match_score');
            $table->text('match_summary')->nullable();
            $table->boolean('is_read')->default(false);
            $table->string('notified_via', 20)->default('dashboard');
            $table->timestamp('notified_at');
            $table->timestamps();

            $table->index('user_id');
            $table->index('job_listing_id');
            $table->index(['user_id', 'is_read']);
            $table->index('notified_at');
            $table->index(['user_id', 'notified_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_alert_notifications');
    }
};
