<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('investment_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instrument_id')->constrained('investment_instruments')->onDelete('cascade');
            $table->date('purchase_date');
            $table->decimal('amount', 15, 2);
            $table->decimal('quantity', 20, 8);
            $table->decimal('price_per_unit', 20, 8);
            $table->decimal('fees', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_purchases');
    }
};
