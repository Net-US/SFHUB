<?php
// database/migrations/xxxx_add_split_shift_to_pkl_schedules.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pkl_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('pkl_schedules', 'start_time_2')) {
                $table->time('start_time_2')->nullable()->after('end_time')
                    ->comment('Jam mulai sesi ke-2 (split shift)');
            }
            if (!Schema::hasColumn('pkl_schedules', 'end_time_2')) {
                $table->time('end_time_2')->nullable()->after('start_time_2')
                    ->comment('Jam selesai sesi ke-2 (split shift)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pkl_schedules', function (Blueprint $table) {
            $cols = array_filter([
                Schema::hasColumn('pkl_schedules','start_time_2') ? 'start_time_2' : null,
                Schema::hasColumn('pkl_schedules','end_time_2')   ? 'end_time_2'   : null,
            ]);
            if ($cols) $table->dropColumn(array_values($cols));
        });
    }
};
