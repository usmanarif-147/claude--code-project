<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_board_columns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained('project_boards')->cascadeOnDelete();
            $table->string('name');
            $table->string('color', 7)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_completed_column')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_board_columns');
    }
};
