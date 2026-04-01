<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recruiter_alert_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_enabled')->default(true);
            $table->boolean('alert_on_recruiter')->default(true);
            $table->boolean('alert_on_hiring_manager')->default(true);
            $table->boolean('alert_on_freelance_client')->default(true);
            $table->integer('min_confidence_score')->default(70);
            $table->boolean('browser_notification')->default(false);
            $table->boolean('email_forward')->default(false);
            $table->string('forward_email', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recruiter_alert_settings');
    }
};
