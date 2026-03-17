<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('schedules', 'title')) {
                $table->string('title')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('schedules', 'day_of_week')) {
                $table->string('day_of_week')->nullable()->after('title');
            }
            if (!Schema::hasColumn('schedules', 'priority')) {
                $table->string('priority')->nullable()->after('type');
            }
            if (!Schema::hasColumn('schedules', 'color')) {
                $table->string('color')->nullable()->after('priority');
            }
            if (!Schema::hasColumn('schedules', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('is_recurring');
            }
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn(['title', 'day_of_week', 'priority', 'color', 'is_active']);
        });
    }
};
