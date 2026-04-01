<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recruiter_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_id')->unique()->constrained('emails')->cascadeOnDelete();
            $table->string('alert_type', 30);
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->json('detected_signals')->nullable();
            $table->boolean('is_read')->default(false);
            $table->boolean('is_dismissed')->default(false);
            $table->string('urgency', 10)->default('normal');
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            $table->index('alert_type');
            $table->index('is_read');
            $table->index('is_dismissed');
            $table->index('urgency');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recruiter_alerts');
    }
};
