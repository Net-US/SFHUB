<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('platform'); // instagram, youtube, tiktok, etc.
            $table->string('content_type')->nullable();
            $table->enum('frequency', ['weekly', 'monthly'])->default('weekly');
            $table->integer('target_per_period')->default(1);
            $table->integer('completed_count')->default(0);
            $table->date('due_date');
            $table->enum('status', ['active', 'completed', 'overdue'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'platform']);
            $table->index(['user_id', 'due_date']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_schedules');
    }
};
