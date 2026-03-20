<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Media/Files table for file manager
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('filename');
            $table->string('original_name');
            $table->string('filepath');
            $table->string('mime_type');
            $table->bigInteger('size'); // in bytes
            $table->string('extension', 10);
            $table->enum('type', ['image', 'video', 'audio', 'document', 'archive', 'other'])->default('other');
            $table->string('alt_text')->nullable(); // for images
            $table->text('description')->nullable();
            $table->string('folder')->default('/'); // folder path like /uploads/blog/
            $table->boolean('is_public')->default(true);
            $table->timestamps();

            $table->index(['type', 'folder']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
