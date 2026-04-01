<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('external_id', 255);
            $table->string('source_platform', 30);
            $table->string('title', 500);
            $table->string('company_name', 255)->nullable();
            $table->string('company_logo_url', 1000)->nullable();
            $table->text('description')->nullable();
            $table->string('location', 255)->nullable();
            $table->string('location_type', 30)->nullable();
            $table->string('country', 100)->nullable();
            $table->unsignedInteger('salary_min')->nullable();
            $table->unsignedInteger('salary_max')->nullable();
            $table->string('salary_currency', 3)->nullable();
            $table->string('salary_text', 255)->nullable();
            $table->json('tech_stack')->nullable();
            $table->string('job_url', 2000);
            $table->timestamp('posted_at')->nullable();
            $table->timestamp('fetched_at');
            $table->string('user_status', 20)->nullable();
            $table->boolean('is_hidden')->default(false);
            $table->string('duplicate_group_id', 100)->nullable();
            $table->boolean('is_duplicate_primary')->default(true);
            $table->timestamps();

            $table->index('user_id');
            $table->index('source_platform');
            $table->index(['user_id', 'source_platform']);
            $table->unique(['user_id', 'external_id', 'source_platform']);
            $table->index('posted_at');
            $table->index('fetched_at');
            $table->index('user_status');
            $table->index('is_hidden');
            $table->index('location_type');
            $table->index('country');
            $table->index('duplicate_group_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_listings');
    }
};
