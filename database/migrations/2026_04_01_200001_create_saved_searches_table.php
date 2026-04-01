<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saved_searches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 255);
            $table->json('preferred_titles')->nullable();
            $table->json('preferred_tech')->nullable();
            $table->string('location_type', 30)->nullable();
            $table->string('location_value', 255)->nullable();
            $table->unsignedInteger('min_salary')->nullable();
            $table->string('salary_currency', 3)->default('USD');
            $table->string('experience_level', 20)->nullable();
            $table->json('enabled_platforms')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('user_id');
            $table->index(['user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_searches');
    }
};
