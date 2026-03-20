<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action'); // create, update, delete, login, logout
            $table->string('entity_type')->nullable(); // User, BlogPost, etc.
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->text('description')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['entity_type', 'entity_id']);
            $table->index('action');
        });

        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->string('level'); // info, warning, error, critical
            $table->string('channel')->default('system');
            $table->text('message');
            $table->json('context')->nullable();
            $table->timestamp('logged_at');

            $table->index(['level', 'logged_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_logs');
        Schema::dropIfExists('activity_logs');
    }
};
