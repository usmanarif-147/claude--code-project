<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('youtube_weekly_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('week_start');
            $table->unsignedBigInteger('subscriber_count')->default(0);
            $table->unsignedBigInteger('view_count')->default(0);
            $table->unsignedInteger('video_count')->default(0);
            $table->decimal('estimated_watch_hours', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'week_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('youtube_weekly_snapshots');
    }
};
