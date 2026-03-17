<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'linked_subject_id')) {
                $table->foreignId('linked_subject_id')->nullable()->constrained('subjects')->onDelete('set null')->after('user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'linked_subject_id')) {
                $table->dropForeign(['linked_subject_id']);
                $table->dropColumn('linked_subject_id');
            }
        });
    }
};
