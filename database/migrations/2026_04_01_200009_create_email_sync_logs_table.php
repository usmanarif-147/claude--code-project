<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamp('synced_at');
            $table->integer('emails_fetched')->default(0);
            $table->integer('emails_skipped')->default(0);
            $table->string('status', 20)->default('success');
            $table->text('error_message')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->timestamps();

            $table->index('synced_at');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_sync_logs');
    }
};
