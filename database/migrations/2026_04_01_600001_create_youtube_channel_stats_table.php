<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('youtube_channel_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('channel_id');
            $table->string('channel_title');
            $table->string('channel_thumbnail_url')->nullable();
            $table->unsignedBigInteger('subscriber_count')->default(0);
            $table->unsignedBigInteger('total_view_count')->default(0);
            $table->unsignedInteger('video_count')->default(0);
            $table->decimal('estimated_watch_hours', 12, 2)->default(0);
            $table->decimal('monthly_revenue', 10, 2)->nullable();
            $table->timestamp('fetched_at');
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('youtube_channel_stats');
    }
};
