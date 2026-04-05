<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });

        Schema::dropIfExists('recurring_tasks');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('task_categories');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
