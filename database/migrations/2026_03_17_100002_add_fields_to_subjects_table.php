<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            if (!Schema::hasColumn('subjects', 'progress')) {
                $table->integer('progress')->default(0)->after('is_active');
            }
            if (!Schema::hasColumn('subjects', 'drive_link')) {
                $table->string('drive_link')->nullable()->after('progress');
            }
            if (!Schema::hasColumn('subjects', 'notes')) {
                $table->text('notes')->nullable()->after('drive_link');
            }
        });
    }

    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn(['progress', 'drive_link', 'notes']);
        });
    }
};
