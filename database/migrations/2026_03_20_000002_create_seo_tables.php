<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_settings', function (Blueprint $table) {
            $table->id();
            $table->string('page')->unique(); // home, features, pricing, blog, contact
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('keywords')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image')->nullable();
            $table->float('priority')->default(0.8);
            $table->string('change_freq')->default('weekly');
            $table->timestamps();

            $table->index('page');
        });

        Schema::create('global_seo', function (Blueprint $table) {
            $table->id();
            $table->string('default_title');
            $table->text('default_description');
            $table->text('default_keywords')->nullable();
            $table->string('author')->default('SFHUB Team');
            $table->string('robots')->default('index, follow');
            $table->string('google_analytics_id')->nullable();
            $table->string('facebook_pixel_id')->nullable();
            $table->boolean('analytics_active')->default(false);
            $table->timestamps();
        });

        Schema::create('meta_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., description, keywords, author
            $table->string('content');
            $table->enum('type', ['name', 'property', 'http-equiv'])->default('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('sitemap_settings', function (Blueprint $table) {
            $table->id();
            $table->timestamp('last_generated')->nullable();
            $table->integer('url_count')->default(0);
            $table->boolean('auto_generate')->default(true);
            $table->string('sitemap_path')->default('sitemap.xml');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sitemap_settings');
        Schema::dropIfExists('meta_tags');
        Schema::dropIfExists('global_seo');
        Schema::dropIfExists('seo_settings');
    }
};
