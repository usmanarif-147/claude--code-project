<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->string('gmail_id')->unique();
            $table->string('thread_id')->nullable();
            $table->string('from_email');
            $table->string('from_name')->nullable();
            $table->string('to_email')->nullable();
            $table->string('subject')->nullable();
            $table->text('snippet')->nullable();
            $table->text('body_preview')->nullable();
            $table->timestamp('received_at');
            $table->boolean('is_read')->default(false);
            $table->boolean('is_starred')->default(false);
            $table->boolean('is_important')->default(false);
            $table->json('labels')->nullable();
            $table->string('category')->nullable();
            $table->text('ai_summary')->nullable();
            $table->string('gmail_link')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->index('received_at');
            $table->index('is_read');
            $table->index('category');
            $table->index('from_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emails');
    }
};
