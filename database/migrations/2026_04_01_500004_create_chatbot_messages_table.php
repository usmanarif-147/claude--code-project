<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chatbot_conversation_id')
                ->constrained('chatbot_conversations')
                ->cascadeOnDelete();
            $table->string('role', 20);
            $table->text('content');
            $table->integer('tokens_used')->nullable();
            $table->string('ai_provider', 20)->nullable();
            $table->timestamps();

            $table->index('chatbot_conversation_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_messages');
    }
};
