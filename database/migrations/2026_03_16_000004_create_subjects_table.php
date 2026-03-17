<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('code')->nullable();
            $table->integer('sks')->default(2);
            $table->integer('semester')->default(1);
            $table->string('day_of_week'); // Senin, Selasa, etc.
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('room')->nullable();
            $table->string('lecturer')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'day_of_week']);
            $table->index(['user_id', 'semester']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
