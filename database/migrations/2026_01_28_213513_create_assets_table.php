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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('type', 50);
            $table->decimal('purchase_value', 15, 2);
            $table->decimal('current_value', 15, 2);
            $table->date('purchase_date');
            $table->text('description')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('location')->nullable();
            $table->json('photos')->nullable();
            $table->boolean('is_insured')->default(false);
            $table->date('insurance_expiry')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
