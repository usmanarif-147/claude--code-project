<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 255);
            $table->string('category', 100);
            $table->string('subject', 500)->nullable();
            $table->text('body');
            $table->boolean('is_favorite')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->index('user_id', 'email_templates_user_id_index');
            $table->index('category', 'email_templates_category_index');
            $table->index('is_favorite', 'email_templates_is_favorite_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
