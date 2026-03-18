<?php
// ════════════════════════════════════════════════════════════════════════
// Fix: Column 'day' cannot be null pada schedules
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
            // Ubah kolom 'day' menjadi nullable untuk mendukung recurring dengan banyak hari
            $table->string('day')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->string('day')->nullable(false)->change();
        });
    }
};
