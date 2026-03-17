<?php
// database/migrations/2024_01_01_000004_create_workspaces_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('color')->nullable();
            $table->string('icon')->nullable();

            // Workspace Type
            $table->string('type', 50)->default('personal');

            // Settings
            $table->json('settings')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_private')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspaces');
    }
};
