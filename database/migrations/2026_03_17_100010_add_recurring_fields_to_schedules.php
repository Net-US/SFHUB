<?php
// ════════════════════════════════════════════════════════════════════════
// File: database/migrations/xxxx_xx_xx_add_recurring_fields_to_schedules.php
// Jalankan: php artisan migrate
// ════════════════════════════════════════════════════════════════════════
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            // Tambah kolom baru hanya jika belum ada
            if (!Schema::hasColumn('schedules', 'frequency')) {
                $table->string('frequency')->default('weekly')->after('is_recurring')
                    ->comment('daily | weekly | monthly');
            }
            if (!Schema::hasColumn('schedules', 'days_of_week')) {
                $table->string('days_of_week')->nullable()->after('frequency')
                    ->comment('Comma-separated: Senin,Rabu,Jumat');
            }
            if (!Schema::hasColumn('schedules', 'day_of_month')) {
                $table->unsignedTinyInteger('day_of_month')->nullable()->after('days_of_week')
                    ->comment('Untuk monthly: angka 1-31');
            }
            if (!Schema::hasColumn('schedules', 'start_date')) {
                $table->date('start_date')->nullable()->after('day_of_month');
            }
            if (!Schema::hasColumn('schedules', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
            if (!Schema::hasColumn('schedules', 'notes')) {
                $table->text('notes')->nullable()->after('end_date');
            }
            if (!Schema::hasColumn('schedules', 'color')) {
                $table->string('color', 20)->nullable()->after('notes');
            }
            // Ubah start_time / end_time menjadi TIME jika masih DATETIME
            // (skip jika sudah TIME agar tidak error)
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('schedules', 'frequency')    ? 'frequency'    : null,
                Schema::hasColumn('schedules', 'days_of_week') ? 'days_of_week' : null,
                Schema::hasColumn('schedules', 'day_of_month') ? 'day_of_month' : null,
                Schema::hasColumn('schedules', 'start_date')   ? 'start_date'   : null,
                Schema::hasColumn('schedules', 'end_date')     ? 'end_date'     : null,
                Schema::hasColumn('schedules', 'notes')        ? 'notes'        : null,
                Schema::hasColumn('schedules', 'color')        ? 'color'        : null,
            ]));
        });
    }
};
