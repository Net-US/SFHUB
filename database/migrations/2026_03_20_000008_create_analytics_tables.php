<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table to store daily/weekly/monthly analytics snapshots
        Schema::create('analytics_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('snapshot_date');
            $table->enum('period', ['daily', 'weekly', 'monthly'])->default('daily');
            $table->integer('active_users')->default(0);
            $table->integer('new_users')->default(0);
            $table->integer('total_users')->default(0);
            $table->integer('tasks_completed')->default(0);
            $table->decimal('revenue', 15, 2)->default(0);
            $table->json('metadata')->nullable(); // additional metrics
            $table->timestamps();

            $table->unique(['snapshot_date', 'period']);
            $table->index(['period', 'snapshot_date']);
        });

        // Table for tracking user sessions/activity
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('device_type', 20)->nullable(); // desktop, mobile, tablet
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_sessions');
        Schema::dropIfExists('analytics_snapshots');
    }
};
