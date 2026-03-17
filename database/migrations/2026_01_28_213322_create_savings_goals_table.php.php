<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
// ============================================================
// FILE 3: database/migrations/xxxx_create_savings_goals_table.php
// ============================================================
return new class extends Migration {
    public function up(): void
    {
        Schema::create('savings_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // akun tempat menabung
            $table->foreignId('finance_account_id')->nullable()->constrained('finance_accounts')->nullOnDelete();
            $table->string('name');
            $table->decimal('target_amount', 15, 2);
            $table->decimal('current_amount', 15, 2)->default(0);
            $table->decimal('daily_saving', 15, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('target_date')->nullable();
            $table->text('notes')->nullable();
            // active | completed | cancelled
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_goals');
    }
};

// ============================================================
// FILE 4: database/migrations/xxxx_create_budgets_table.php
// ============================================================
return new class extends Migration {
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('category');
            $table->decimal('amount', 15, 2);
            // monthly | weekly
            $table->string('period', 20)->default('monthly');
            $table->decimal('spent_amount', 15, 2)->default(0);
            $table->integer('alert_threshold')->default(80); // persen
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};

// ============================================================
// FILE 5: database/migrations/xxxx_create_pending_needs_table.php
// (target kebutuhan yang belum tentu kapan dibeli)
// ============================================================
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
