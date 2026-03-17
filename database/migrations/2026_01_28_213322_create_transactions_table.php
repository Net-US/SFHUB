<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // akun sumber (wajib untuk expense/income/transfer)
            $table->foreignId('finance_account_id')->nullable()->constrained('finance_accounts')->nullOnDelete();
            // akun tujuan (untuk transfer)
            $table->foreignId('to_account_id')->nullable()->constrained('finance_accounts')->nullOnDelete();
            // income | expense | transfer
            $table->string('type', 20);
            $table->decimal('amount', 15, 2);
            $table->decimal('fee', 15, 2)->default(0); // admin transfer
            $table->string('category')->nullable();
            $table->string('description')->nullable();
            $table->date('transaction_date');
            $table->string('payment_method')->nullable();
            $table->text('notes')->nullable();
            $table->json('tags')->nullable();
            $table->string('receipt_url')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_pattern')->nullable();
            $table->foreignId('related_transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
