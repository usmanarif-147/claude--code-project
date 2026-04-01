<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_calendar_items', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->string('type', 20);
            $table->text('description')->nullable();
            $table->date('planned_date');
            $table->string('status', 20)->default('planned');
            $table->timestamp('published_at')->nullable();
            $table->string('color', 7)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('planned_date');
            $table->index('status');
            $table->index('type');
            $table->index(['planned_date', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_calendar_items');
    }
};
