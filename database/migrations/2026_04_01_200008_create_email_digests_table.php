<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_digests', function (Blueprint $table) {
            $table->id();
            $table->date('digest_date')->unique();
            $table->timestamp('period_start');
            $table->timestamp('period_end');
            $table->integer('total_emails')->default(0);
            $table->integer('unread_count')->default(0);
            $table->text('summary')->nullable();
            $table->json('categories_breakdown')->nullable();
            $table->json('highlights')->nullable();
            $table->string('ai_model_used')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('generated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_digests');
    }
};
