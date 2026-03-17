<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedule_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('schedule_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->text('reason')->nullable();
            $table->foreignId('replaced_by_event_id')->nullable()->constrained('events')->onDelete('set null');
            $table->boolean('is_cancelled')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'date']);
            $table->index(['schedule_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_overrides');
    }
};
