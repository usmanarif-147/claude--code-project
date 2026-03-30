<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolio_visitors', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45);
            $table->string('country', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('page_visited', 255);
            $table->string('referrer', 500)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('device_type', 20)->nullable();
            $table->timestamp('visited_at');

            $table->index('ip_address');
            $table->index('page_visited');
            $table->index('visited_at');
            $table->index('referrer');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_visitors');
    }
};
