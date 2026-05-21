<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smart_reply_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_id')->constrained('emails')->cascadeOnDelete();
            $table->foreignId('template_id')->nullable()->constrained('email_templates')->nullOnDelete();
            $table->string('tone', 50);
            $table->text('prompt_context')->nullable();
            $table->text('generated_body');
            $table->text('edited_body')->nullable();
            $table->string('status', 50)->default('draft');
            $table->string('ai_model_used', 100)->nullable();
            $table->timestamp('generated_at');
            $table->timestamp('copied_at')->nullable();
            $table->timestamps();

            $table->index('email_id', 'smart_reply_drafts_email_id_index');
            $table->index('template_id', 'smart_reply_drafts_template_id_index');
            $table->index('status', 'smart_reply_drafts_status_index');
            $table->index('tone', 'smart_reply_drafts_tone_index');
            $table->index('created_at', 'smart_reply_drafts_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smart_reply_drafts');
    }
};
