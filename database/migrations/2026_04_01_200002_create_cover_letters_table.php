<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cover_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('job_listing_id')->nullable()->constrained('job_listings')->nullOnDelete();
            $table->string('job_title', 500);
            $table->string('company_name', 255)->nullable();
            $table->text('job_description_snippet')->nullable();
            $table->longText('content');
            $table->string('ai_provider', 30);
            $table->string('ai_model', 100)->nullable();
            $table->unsignedInteger('prompt_tokens')->nullable();
            $table->unsignedInteger('completion_tokens')->nullable();
            $table->boolean('is_edited')->default(false);
            $table->timestamps();

            $table->index('user_id');
            $table->index('job_listing_id');
            $table->index(['user_id', 'job_listing_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cover_letters');
    }
};
