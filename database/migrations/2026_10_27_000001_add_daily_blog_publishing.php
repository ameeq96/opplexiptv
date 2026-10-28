<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->string('site_key', 32)->default('main')->index();
        });

        Schema::create('blog_site_settings', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('site_key', 32)->unique();
            $table->boolean('enabled')->default(false);
            $table->string('timezone', 64)->default('Asia/Karachi');
            $table->string('publish_time', 5)->default('10:00');
            $table->json('locales');
            $table->timestamps();
        });

        Schema::create('blog_generation_runs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('site_key', 32);
            $table->date('publishing_date')->nullable();
            $table->foreignId('blog_id')->nullable()->constrained('blogs')->nullOnDelete();
            $table->string('topic', 200);
            $table->string('primary_keyword', 160)->default('');
            $table->char('fingerprint', 64)->nullable();
            $table->char('revision', 64)->nullable();
            $table->json('payload')->nullable();
            $table->json('errors')->nullable();
            $table->string('status', 32)->default('reserved')->index();
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->unique(['site_key', 'publishing_date']);
        });

        Schema::create('blog_publish_deliveries', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->foreignId('run_id')->constrained('blog_generation_runs')->cascadeOnDelete();
            $table->string('channel', 16);
            $table->char('revision', 64);
            $table->string('status', 32)->default('pending');
            $table->unsignedInteger('attempts')->default(0);
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->string('error_code', 160)->nullable();
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('next_attempt_at')->nullable();
            $table->timestamps();
            $table->unique(['run_id', 'channel', 'revision']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_publish_deliveries');
        Schema::dropIfExists('blog_generation_runs');
        Schema::dropIfExists('blog_site_settings');
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropIndex(['site_key']);
            $table->dropColumn('site_key');
        });
    }
};
