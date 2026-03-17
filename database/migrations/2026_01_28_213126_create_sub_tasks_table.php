<?php
// database/migrations/2024_01_01_000002_create_sub_tasks_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Subtask Details
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['stage', 'checklist', 'milestone', 'review'])->default('stage');

            // Order & Dependency
            $table->integer('order')->default(0);
            $table->foreignId('depends_on')->nullable()->constrained('sub_tasks')->onDelete('set null');

            // Status & Progress
            $table->enum('status', ['pending', 'in_progress', 'completed', 'blocked'])->default('pending');
            $table->integer('progress')->default(0);

            // Time Estimation
            $table->integer('estimated_minutes')->nullable();
            $table->integer('actual_minutes')->nullable();

            // Dates
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->date('due_date')->nullable();

            // Workflow Stage (for creative projects)
            $table->string('stage_key')->nullable(); // 'script', 'recording', 'editing', etc
            $table->string('stage_label')->nullable(); // 'Naskah/Script', 'Rekaman', etc

            // Metadata
            $table->json('notes')->nullable();
            $table->json('attachments')->nullable();
            $table->json('checklist')->nullable(); // [{text: '...', checked: true}]

            // Creative Project Specific
            $table->string('deliverable')->nullable();
            $table->json('specifications')->nullable();
            $table->string('quality_standard')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['task_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['task_id', 'order']);
            $table->index(['stage_key']);
            $table->index(['due_date']);
            $table->index(['scheduled_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_tasks');
    }
};
