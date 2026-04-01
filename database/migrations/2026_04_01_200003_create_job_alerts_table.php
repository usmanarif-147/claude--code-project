<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_enabled')->default(true);
            $table->unsignedTinyInteger('min_score_threshold')->default(80);
            $table->string('frequency', 20)->default('instant');
            $table->boolean('notify_dashboard')->default(true);
            $table->boolean('notify_email')->default(false);
            $table->timestamp('last_digest_sent_at')->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_alerts');
    }
};
