<?php

// ────────────────────────────────────────────────────────────────
// MIGRATION 1: Tambah kolom 'category' ke tabel 'assets'
//
// php artisan make:migration add_category_to_assets_table
// ────────────────────────────────────────────────────────────────

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            // Cek dulu apakah kolom sudah ada sebelum menambah
            if (!Schema::hasColumn('assets', 'category')) {
                $table->string('category', 50)->nullable()->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            if (Schema::hasColumn('assets', 'category')) {
                $table->dropColumn('category');
            }
        });
    }
};


