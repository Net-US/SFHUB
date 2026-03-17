<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


// ────────────────────────────────────────────────────────────────
// MIGRATION 2: Tambah 'finance_account_id' ke 'investment_instruments'
// Ini yang menghubungkan akun investasi (Indodax, Ajaib) ke instrumen
//
// php artisan make:migration add_account_to_investment_instruments_table
// ────────────────────────────────────────────────────────────────

return new class extends Migration {
    public function up(): void
    {
        Schema::table('investment_instruments', function (Blueprint $table) {
            if (!Schema::hasColumn('investment_instruments', 'finance_account_id')) {
                // nullable() karena instrumen lama belum tentu punya akun
                $table->foreignId('finance_account_id')
                    ->nullable()
                    ->after('user_id')
                    ->constrained('finance_accounts')
                    ->nullOnDelete(); // jika akun dihapus, instrument tidak ikut terhapus
            }
        });
    }

    public function down(): void
    {
        Schema::table('investment_instruments', function (Blueprint $table) {
            if (Schema::hasColumn('investment_instruments', 'finance_account_id')) {
                $table->dropForeign(['finance_account_id']);
                $table->dropColumn('finance_account_id');
            }
        });
    }
};
