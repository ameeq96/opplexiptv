<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_id')->constrained('blogs')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('title');
            $table->string('slug');
            $table->string('excerpt', 500)->nullable();
            $table->longText('content');
            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();
            $table->string('seo_keywords')->nullable();
            $table->string('og_title')->nullable();
            $table->string('og_description')->nullable();
            $table->string('og_image')->nullable();
            $table->string('canonical_url')->nullable();
            $table->json('schema_json')->nullable();
            $table->timestamps();

            $table->unique(['blog_id', 'locale']);
            $table->unique(['locale', 'slug']);
            $table->index(['locale']);
            $table->index(['slug']);
            $table->index(['title']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_translations');
    }
};
