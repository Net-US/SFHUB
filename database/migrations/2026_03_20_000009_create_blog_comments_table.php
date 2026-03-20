<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Blog comments table
        Schema::create('blog_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_post_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('author_name')->nullable(); // for guest comments
            $table->string('author_email')->nullable(); // for guest comments
            $table->text('content');
            $table->enum('status', ['pending', 'approved', 'rejected', 'spam'])->default('pending');
            $table->foreignId('parent_id')->nullable()->constrained('blog_comments')->onDelete('cascade');
            $table->timestamps();

            $table->index(['blog_post_id', 'status']);
            $table->index('user_id');
            $table->index('parent_id');
        });

        // Add comments count to blog posts
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->integer('comments_count')->default(0)->after('views');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_comments');
        
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn('comments_count');
        });
    }
};
