<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'task_type')) {
                $table->string('task_type')->nullable()->after('category');
                // Values: assignment, lab, skripsi, quiz, personal, etc.
            }
            if (!Schema::hasColumn('tasks', 'notes')) {
                $table->text('notes')->nullable()->after('description');
            }
            if (!Schema::hasColumn('tasks', 'deadline')) {
                $table->date('deadline')->nullable()->after('due_date');
            }
            if (!Schema::hasColumn('tasks', 'drive_link')) {
                $table->string('drive_link')->nullable()->after('notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['task_type', 'notes', 'deadline', 'drive_link']);
        });
    }
};
