<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('file_title');
            $table->string('file_path');
            $table->string('mime_type');
            $table->decimal('size_kb', 10, 2);
            $table->decimal('size_mb', 10, 2);
            $table->text('note')->nullable();
            $table->json('tags');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
