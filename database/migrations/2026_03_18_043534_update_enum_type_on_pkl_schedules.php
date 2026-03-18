<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        // Memaksa MySQL menerima opsi 'split' ke dalam ENUM
        DB::statement("ALTER TABLE pkl_schedules MODIFY type ENUM('full', 'half', 'off', 'split') DEFAULT 'full'");
    }

    public function down(): void {
        DB::statement("ALTER TABLE pkl_schedules MODIFY type ENUM('full', 'half', 'off') DEFAULT 'full'");
    }
};
