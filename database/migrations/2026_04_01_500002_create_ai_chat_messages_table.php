<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('ai_chat_conversations')->cascadeOnDelete();
            $table->string('role', 20);
            $table->text('content');
            $table->text('context_summary')->nullable();
            $table->integer('tokens_used')->nullable();
            $table->string('provider', 50)->nullable();
            $table->timestamps();

            $table->index('conversation_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_chat_messages');
    }
};
