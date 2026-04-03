<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('youtube_subscription_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->constrained('youtube_subscriptions')->cascadeOnDelete();
            $table->string('video_id', 20);
            $table->string('title', 500);
            $table->text('description')->nullable();
            $table->string('thumbnail_url', 500)->nullable();
            $table->string('channel_title', 255);
            $table->timestamp('published_at');
            $table->string('duration', 20)->nullable();
            $table->unsignedBigInteger('view_count')->default(0);
            $table->unsignedBigInteger('like_count')->default(0);
            $table->unsignedBigInteger('comment_count')->default(0);
            $table->unsignedSmallInteger('category_id')->nullable();
            $table->string('default_language', 10)->nullable();
            $table->json('tags')->nullable();
            $table->boolean('is_new')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'video_id']);
            $table->index(['user_id', 'published_at']);
            $table->index(['subscription_id', 'published_at']);
            $table->index(['user_id', 'is_new']);
            $table->index('category_id');
            $table->index('default_language');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('youtube_subscription_videos');
    }
};
