<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pkl_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('day'); // Senin, Selasa, etc.
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->enum('type', ['full', 'half', 'off'])->default('full');
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pkl_schedules');
    }
};
