<?php
// ============================================================
// FILE 5: database/migrations/xxxx_create_pending_needs_table.php
// (target kebutuhan yang belum tentu kapan dibeli)
// ============================================================
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pending_needs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // akun yang "diblokir" untuk kebutuhan ini
            $table->foreignId('finance_account_id')->nullable()->constrained('finance_accounts')->nullOnDelete();
            $table->string('name');
            $table->decimal('amount', 15, 2);
            $table->string('category')->nullable();
            $table->text('notes')->nullable();
            // pending | purchased | cancelled
            $table->string('status', 20)->default('pending');
            // jika purchased → jadi transaction_id
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_needs');
    }
};
