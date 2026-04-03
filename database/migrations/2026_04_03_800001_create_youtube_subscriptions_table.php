<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('youtube_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('channel_id', 64);
            $table->string('channel_title', 255);
            $table->string('channel_thumbnail_url', 500)->nullable();
            $table->text('channel_description')->nullable();
            $table->unsignedBigInteger('subscriber_count')->default(0);
            $table->unsignedInteger('video_count')->default(0);
            $table->timestamp('last_video_at')->nullable();
            $table->timestamp('subscribed_at');
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'channel_id']);
            $table->index(['user_id', 'synced_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('youtube_subscriptions');
    }
};
