<?php
// database/migrations/2024_01_01_000001_create_tasks_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('workspace_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('tasks')->onDelete('cascade');

            // Basic Task Info
            $table->string('title');
            $table->text('description')->nullable();

            // Category & Type
            $table->string('category', 50)->default('Other');
            $table->string('project_type')->nullable();

            // Priority & Status
            $table->string('priority')
                ->default('not-urgent-not-important');
            $table->enum('status', ['todo', 'doing', 'done', 'archived'])->default('todo');

            // Dates & Time
            $table->date('due_date')->nullable();
            $table->time('estimated_time')->nullable(); // Format: '02:30:00' for 2.5 hour
            $table->time('actual_time')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();

            // Progress & Workflow
            $table->integer('progress')->default(0);
            $table->string('workflow_stage')->default('none');

            // Subtask Management
            $table->integer('total_subtasks')->default(0);
            $table->integer('completed_subtasks')->default(0);

            // Metadata
            $table->json('tags')->nullable();
            $table->json('links')->nullable(); // [{type: 'drive', url: '...', label: '...'}]
            $table->json('attachments')->nullable(); // [{name: '...', path: '...', size: '...'}]

            // Recurring Tasks
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_pattern')->nullable(); // daily, weekly, monthly, yearly
            $table->date('recurring_until')->nullable();

            // Creative Project Specific
            $table->string('client')->nullable();
            $table->decimal('budget', 15, 2)->nullable();
            $table->string('deliverable_format')->nullable(); // mp4, jpg, pdf, etc

            $table->timestamps();
            $table->softDeletes();

            // Indexes for Performance
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'due_date']);
            $table->index(['user_id', 'category']);
            $table->index(['user_id', 'priority']);
            $table->index(['user_id', 'project_type']);
            $table->index(['user_id', 'workflow_stage']);
            $table->index(['parent_id']);
            $table->index(['is_recurring']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
