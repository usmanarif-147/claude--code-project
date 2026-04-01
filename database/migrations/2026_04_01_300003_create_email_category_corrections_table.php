<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_category_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_id')->constrained('emails')->cascadeOnDelete();
            $table->foreignId('from_category_id')->nullable()->constrained('email_categories')->nullOnDelete();
            $table->foreignId('to_category_id')->constrained('email_categories')->cascadeOnDelete();
            $table->timestamp('corrected_at');
            $table->timestamps();

            $table->index('email_id');
            $table->index('to_category_id');
            $table->index('corrected_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_category_corrections');
    }
};
