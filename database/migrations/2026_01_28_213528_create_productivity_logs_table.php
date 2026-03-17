<?php
// database/migrations/2024_01_01_000003_create_productivity_logs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productivity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Reference to task/subtask
            $table->foreignId('task_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('sub_task_id')->nullable()->constrained('sub_tasks')->onDelete('cascade');

            // Log Details
            $table->date('log_date');
            $table->string('activity_type', 50); // e.g., 'work', 'break', 'meeting', etc.

            // Time Tracking
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->integer('duration_minutes')->default(0);

            // Productivity Metrics
            $table->integer('focus_score')->nullable()->comment('1-100');
            $table->integer('energy_level')->nullable()->comment('1-100');
            $table->text('mood')->nullable();

            // Activity Details
            $table->text('description');
            $table->json('details')->nullable();

            // Task Snapshot (for historical reference)
            $table->json('task_snapshot')->nullable();

            // Platform/App used
            $table->string('platform')->nullable();
            $table->string('app_used')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'log_date']);
            $table->index(['task_id']);
            $table->index(['sub_task_id']);
            $table->index(['activity_type']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productivity_logs');
    }
};
