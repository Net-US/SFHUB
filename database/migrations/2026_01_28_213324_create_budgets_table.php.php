<?php

// ============================================================
// FILE 4: database/migrations/xxxx_create_budgets_table.php
// ============================================================
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
