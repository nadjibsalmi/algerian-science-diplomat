<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();
            $table->string('template')->default('default'); // default|landing|faq
            $table->boolean('published')->default(false);
            $table->foreignUuid('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('page_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('page_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5); // fr|ar|en
            $table->string('title');
            $table->json('content'); // TipTap JSON blocks
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('og_image')->nullable();
            $table->timestamps();

            $table->unique(['page_id', 'locale']);
        });

        Schema::create('blog_posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();
            $table->string('cover_image')->nullable();
            $table->string('status')->default('draft'); // draft|published|archived
            $table->foreignUuid('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('tags')->nullable();
            $table->string('category')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('view_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('blog_post_translations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('blog_post_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title');
            $table->json('content');
            $table->string('excerpt')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->timestamps();

            $table->unique(['blog_post_id', 'locale']);
        });

        Schema::create('global_announcements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type')->default('info'); // info|warning|success|danger
            $table->json('message'); // {fr: '...', ar: '...', en: '...'}
            $table->string('link')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('global_announcements');
        Schema::dropIfExists('blog_post_translations');
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('page_translations');
        Schema::dropIfExists('pages');
    }
};
