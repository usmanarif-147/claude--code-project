<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_listing_id')->nullable()->constrained('job_listings')->nullOnDelete();
            $table->string('company', 255);
            $table->string('position', 255);
            $table->string('status', 50)->default('saved');
            $table->date('applied_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('salary_offered', 100)->nullable();
            $table->string('url', 2048)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('status');
            $table->index('job_listing_id');
            $table->index('company');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
