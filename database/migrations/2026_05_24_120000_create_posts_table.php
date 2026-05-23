<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->string('type', 20)->index();
            $table->text('caption');
            $table->text('hashtags')->nullable();
            $table->json('meta')->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->string('linkedin_post_id', 255)->nullable();
            $table->string('linkedin_post_url', 500)->nullable();
            $table->text('linkedin_error')->nullable();
            $table->unsignedTinyInteger('linkedin_attempts')->default(0);
            $table->timestamp('linkedin_last_attempted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
