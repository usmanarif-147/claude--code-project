<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('week_start');
            $table->date('week_end');
            $table->unsignedInteger('total_planned')->default(0);
            $table->unsignedInteger('total_completed')->default(0);
            $table->unsignedInteger('total_carried_over')->default(0);
            $table->json('category_breakdown')->nullable();
            $table->text('ai_summary')->nullable();
            $table->json('ai_focus_areas')->nullable();
            $table->timestamp('ai_generated_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'week_start']);
            $table->index('user_id');
            $table->index('week_start');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_reviews');
    }
};
