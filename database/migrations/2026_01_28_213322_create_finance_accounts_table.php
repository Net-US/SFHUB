<?php
// ============================================================
// FILE 1: database/migrations/xxxx_create_finance_accounts_table.php
// ============================================================
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('finance_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            // cash | bank | e-wallet | investment | receivable
            $table->string('type')->default('cash');
            $table->string('account_number')->nullable();
            $table->decimal('balance', 15, 2)->default(0);
            $table->string('currency', 10)->default('IDR');
            $table->string('color', 20)->default('#6b7280');
            $table->string('icon', 50)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_accounts');
    }
};
