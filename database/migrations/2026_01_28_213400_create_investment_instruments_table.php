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
        Schema::create('investment_instruments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('symbol');
            $table->string('type', 50 );
            $table->text('notes')->nullable();
            $table->decimal('current_price', 20, 8)->nullable();
            $table->decimal('total_invested', 20, 2)->default(0);
            $table->decimal('total_quantity', 20, 8)->default(0);
            $table->decimal('average_price', 20, 8)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_instruments');
    }
};
