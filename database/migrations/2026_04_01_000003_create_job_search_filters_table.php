<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_search_filters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->json('preferred_titles')->nullable();
            $table->json('preferred_tech')->nullable();
            $table->string('location_type', 30)->nullable();
            $table->string('location_value', 255)->nullable();
            $table->unsignedInteger('min_salary')->nullable();
            $table->string('salary_currency', 3)->default('USD');
            $table->string('experience_level', 20)->nullable();
            $table->json('enabled_platforms')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_search_filters');
    }
};
