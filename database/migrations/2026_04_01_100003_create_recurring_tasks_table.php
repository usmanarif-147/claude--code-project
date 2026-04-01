<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recurring_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('task_categories')->nullOnDelete();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('frequency', 20);
            $table->tinyInteger('day_of_week')->unsigned()->nullable();
            $table->tinyInteger('day_of_month')->unsigned()->nullable();
            $table->string('priority', 20)->default('medium');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_generated_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
            $table->index('frequency');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_tasks');
    }
};
