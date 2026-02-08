<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });

        Schema::create('blog_category_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_category_id')->constrained('blog_categories')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('title');
            $table->string('slug');
            $table->timestamps();

            $table->unique(['blog_category_id', 'locale']);
            $table->unique(['locale', 'slug']);
            $table->index(['locale', 'slug']);
        });

        Schema::create('blog_blog_category', function (Blueprint $table) {
            $table->foreignId('blog_id')->constrained('blogs')->cascadeOnDelete();
            $table->foreignId('blog_category_id')->constrained('blog_categories')->cascadeOnDelete();
            $table->primary(['blog_id', 'blog_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_blog_category');
        Schema::dropIfExists('blog_category_translations');
        Schema::dropIfExists('blog_categories');
    }
};