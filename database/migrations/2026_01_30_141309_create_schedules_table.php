<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('day'); // Senin, Selasa, etc.
            $table->time('start_time');
            $table->time('end_time');
            $table->string('activity');
            $table->string('type'); // academic, pkl, creative, etc.
            $table->string('location')->nullable();
            $table->string('instructor')->nullable(); // Untuk mata kuliah
            $table->string('course_code')->nullable(); // Kode mata kuliah
            $table->boolean('is_recurring')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
