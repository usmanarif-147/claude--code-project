<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('youtube_saved_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('video_id', 20);
            $table->string('title', 500);
            $table->string('thumbnail_url', 500)->nullable();
            $table->string('channel_title', 255);
            $table->string('channel_id', 64);
            $table->timestamp('published_at')->nullable();
            $table->string('duration', 20)->nullable();
            $table->unsignedBigInteger('view_count')->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('saved_at');
            $table->timestamps();

            $table->unique(['user_id', 'video_id']);
            $table->index(['user_id', 'saved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('youtube_saved_videos');
    }
};
