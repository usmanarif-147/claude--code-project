<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_match_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('job_listing_id')->constrained('job_listings')->cascadeOnDelete();
            $table->unsignedTinyInteger('score');
            $table->text('explanation')->nullable();
            $table->json('matched_skills')->nullable();
            $table->json('missing_skills')->nullable();
            $table->json('bonus_factors')->nullable();
            $table->string('ai_provider', 20);
            $table->string('ai_model', 50)->nullable();
            $table->timestamp('scored_at');
            $table->timestamps();

            $table->unique(['user_id', 'job_listing_id']);
            $table->index('user_id');
            $table->index('job_listing_id');
            $table->index('score');
            $table->index(['user_id', 'score']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_match_scores');
    }
};
