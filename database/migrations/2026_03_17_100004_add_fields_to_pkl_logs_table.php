<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pkl_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('pkl_logs', 'task')) {
                $table->string('task')->nullable()->after('activity');
            }
            if (!Schema::hasColumn('pkl_logs', 'category')) {
                $table->string('category')->nullable()->after('task');
                // Values: Design, Development, Marketing, Meeting, Social Media, Administration, Presentation, Lainnya
            }
            if (!Schema::hasColumn('pkl_logs', 'hours')) {
                $table->decimal('hours', 4, 1)->default(0)->after('category');
            }
            if (!Schema::hasColumn('pkl_logs', 'status')) {
                $table->enum('status', ['done', 'todo', 'in_progress'])->default('done')->after('hours');
            }
            if (!Schema::hasColumn('pkl_logs', 'notes')) {
                $table->text('notes')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pkl_logs', function (Blueprint $table) {
            $table->dropColumn(['task', 'category', 'hours', 'status', 'notes']);
        });
    }
};
