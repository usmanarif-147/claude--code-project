<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->text('caption');
            $table->text('hashtags');
            $table->text('cover_page');
            $table->json('content_pages');
            $table->text('final_page');
            $table->string('template_slug', 50)->default('default');
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->string('status', 20)->default('draft')->index();
            $table->string('rendered_pdf_path', 255)->nullable();
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
        Schema::dropIfExists('scheduled_posts');
    }
};
